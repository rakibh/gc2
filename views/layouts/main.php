<?php
use Core\Session;

/** @var string $title */
/** @var string $view */
/** @var array $data */

$username = Session::get('username', 'User');
$role = Session::get('role', 'user');
$route = $_GET['route'] ?? 'dashboard';

// User Preferences
$userTheme = Session::get('user_theme', 'light');
$toastPosition = Session::get('user_toast_position', 'bottom-right');
$desktopNotifications = Session::get('user_desktop_notifications', false);
?>
<!DOCTYPE html>
<html lang="en" class="h-full <?php echo $userTheme === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'IT Management System'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.store('app', {
                toasts: [],
                confirmModal: {
                    show: false,
                    title: '',
                    message: '',
                    onConfirm: null
                },
                notifications: {
                    unread_count: 0,
                    recent: [],
                    enabled: <?php echo $desktopNotifications ? 'true' : 'false'; ?>,
                    last_id: parseInt(localStorage.getItem('last_notif_id')) || 0
                },
                toastConfig: {
                    position: '<?php echo $toastPosition; ?>'
                },
                addToast(title, message, type = 'success') {
                    const id = Date.now();
                    this.toasts.push({ id, title, message, type, show: true });
                    setTimeout(() => this.removeToast(id), 5000);
                },
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].show = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                },
                confirm(title, message, callback) {
                    this.confirmModal.title = title;
                    this.confirmModal.message = message;
                    this.confirmModal.onConfirm = callback;
                    this.confirmModal.show = true;
                },
                async requestNotificationPermission() {
                    if (!("Notification" in window)) {
                        this.addToast('Not Supported', 'This browser does not support desktop notifications.', 'error');
                        return;
                    }

                    const permission = await Notification.requestPermission();
                    if (permission === "granted") {
                        this.notifications.enabled = true;
                        localStorage.setItem('native_notifications', 'true');
                        this.addToast('Enabled', 'Desktop notifications are now active.', 'success');
                        this.showNativeNotification('Notifications Enabled', 'You will now receive system alerts on your desktop.');
                    } else {
                        this.notifications.enabled = false;
                        localStorage.setItem('native_notifications', 'false');
                        this.addToast('Denied', 'Notification permission was denied.', 'error');
                    }
                },
                showNativeNotification(title, body, data = null) {
                    if (Notification.permission !== "granted") return;

                    const n = new Notification(title, {
                        body: body,
                        icon: 'views/assets/img/logo.png' // Adjust if you have a logo
                    });

                    n.onclick = () => {
                        window.focus();
                        if (data) this.goToNotification(data);
                        n.close();
                    };
                },
                async pollNotifications() {
                    try {
                        const response = await fetch('index.php?route=notifications_poll');
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                // Trigger Native Notifications for NEW items
                                if (this.notifications.enabled && Notification.permission === 'granted') {
                                    data.recent.forEach(n => {
                                        if (parseInt(n.id) > this.notifications.last_id) {
                                            this.showNativeNotification(
                                                n.type.charAt(0).toUpperCase() + n.type.slice(1) + ' Alert',
                                                n.message,
                                                n
                                            );
                                        }
                                    });
                                }

                                // Update last seen ID
                                if (data.recent.length > 0) {
                                    const maxId = Math.max(...data.recent.map(n => parseInt(n.id)));
                                    if (maxId > this.notifications.last_id) {
                                        this.notifications.last_id = maxId;
                                        localStorage.setItem('last_notif_id', maxId.toString());
                                    }
                                }

                                this.notifications.unread_count = data.unread_count;
                                this.notifications.recent = data.recent;
                            }
                        }
                    } catch (error) {
                        console.error('Failed to poll notifications:', error);
                    }
                },
                async markAllAsRead() {
                    try {
                        const response = await fetch('index.php?route=notification_mark_all_read', { method: 'POST' });
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                this.notifications.unread_count = 0;
                                this.notifications.recent.forEach(n => n.is_read = 1);
                            }
                        }
                    } catch (error) {
                        console.error('Failed to mark notifications as read:', error);
                    }
                },
                async goToNotification(n) {
                    // Mark as read first
                    if (!n.is_read) {
                        try {
                            await fetch(`index.php?route=notification_mark_read&id=${n.id}`);
                        } catch (e) {
                            console.error('Failed to mark notification as read');
                        }
                    }

                    // Determine redirect URL
                    let url = 'index.php?route=list_notifications';
                    if (n.type === 'task' && n.data) {
                        try {
                            const data = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                            if (data.task_id) {
                                url = `index.php?route=view_task&id=${data.task_id}`;
                            }
                        } catch (e) {}
                    } else if (n.type === 'equipment' && n.data) {
                        try {
                            const data = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                            if (data.equipment_id) {
                                url = `index.php?route=view_equipment&id=${data.equipment_id}`;
                            }
                        } catch (e) {}
                    } else if (n.type === 'network' && n.data) {
                        try {
                            const data = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                            if (data.network_id) {
                                url = `index.php?route=view_network&id=${data.network_id}`;
                            }
                        } catch (e) {}
                    } else if (n.type === 'user') {
                        url = 'index.php?route=list_users';
                    } else if (n.type === 'warranty' && n.data) {
                        try {
                            const data = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                            if (data.equipment_id) {
                                url = `index.php?route=view_equipment&id=${data.equipment_id}`;
                            }
                        } catch (e) {}
                    }

                    window.location.href = url;
                },
                timeAgo(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const seconds = Math.floor((now - date) / 1000);
                    
                    if (seconds < 60) return 'just now';
                    const minutes = Math.floor(seconds / 60);
                    if (minutes < 60) return `${minutes}m ago`;
                    const hours = Math.floor(minutes / 60);
                    if (hours < 24) return `${hours}h ago`;
                    const days = Math.floor(hours / 24);
                    if (days < 7) return `${days}d ago`;
                    return date.toLocaleDateString();
                }
            });
        });
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-inter overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors duration-300" 
    x-data="{ 
        mobileSidebarOpen: false, 
        darkMode: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.darkMode = !this.darkMode;
            if (this.darkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            localStorage.setItem('darkMode', this.darkMode);
        },
        init() {
            // Start Notification Polling
            Alpine.store('app').pollNotifications();
            setInterval(() => Alpine.store('app').pollNotifications(), 30000); // Every 30 seconds
        }
    }">

    <!-- Toast Notifications -->
    <div class="fixed z-[9999] flex flex-col gap-2 w-80 transition-all duration-500"
         :class="$store.app.toastConfig.position === 'top-right' ? 'top-4 right-4' : 'bottom-4 right-4'">
        <template x-for="toast in $store.app.toasts" :key="toast.id">
            <div x-show="toast.show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-full opacity-0"
                 class="p-4 rounded-xl shadow-2xl border flex items-start space-x-3 bg-white dark:bg-slate-900 dark:border-slate-800"
                 :class="toast.type === 'success' ? 'border-green-100 bg-green-50/50' : 'border-red-100 bg-red-50/50'">
                <div class="flex-shrink-0 mt-0.5">
                    <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill text-green-500' : 'bi-exclamation-circle-fill text-red-500'"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="toast.title"></p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5" x-text="toast.message"></p>
                </div>
                <button @click="$store.app.removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 bg-slate-900/80 lg:hidden" 
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="mobileSidebarOpen = false"></div>

    <div class="flex h-full">
        <!-- Static Sidebar for Desktop -->
        <div class="hidden lg:flex lg:w-72 lg:flex-col lg:fixed lg:inset-y-0 z-50">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>

        <!-- Mobile Sidebar -->
        <div x-show="mobileSidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-50 w-72 lg:hidden"
            x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" 
            x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>

        <!-- Main Area -->
        <div class="flex flex-col flex-1 lg:pl-72 min-h-screen">
            <!-- Header / Top Bar -->
            <?php include __DIR__ . '/header.php'; ?>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 p-6 lg:p-8 transition-colors duration-300">
                <div class="max-w-7xl mx-auto">
                    <!-- Breadcrumbs / Page Header -->
                    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 leading-tight"><?php echo $title; ?></h1>
                            <nav class="flex text-sm text-slate-500 dark:text-slate-400 mt-2">
                                <a href="index.php" class="hover:text-blue-600 dark:hover:text-blue-400 flex items-center">
                                    <i class="bi bi-house-door mr-1.5"></i> Home
                                </a>
                                <span class="mx-2 text-slate-300 dark:text-slate-700">/</span>
                                <span class="text-slate-900 dark:text-slate-300 font-medium"><?php echo $title; ?></span>
                            </nav>
                        </div>
                        
                        <div id="page-actions" class="flex items-center space-x-3">
                            <?php if (isset($data['actions'])) echo $data['actions']; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="animate-in fade-in duration-500">
                        <?php include $view; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Global Confirmation Modal -->
    <div x-show="$store.app.confirmModal.show" x-cloak class="fixed inset-0 z-[110] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="$store.app.confirmModal.show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/80 transition-opacity" @click="$store.app.confirmModal.show = false"></div>

            <div x-show="$store.app.confirmModal.show"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 dark:border-slate-800">
                <div class="p-8">
                    <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 mb-6">
                        <i class="bi bi-question-circle text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2" x-text="$store.app.confirmModal.title"></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400" x-text="$store.app.confirmModal.message"></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 px-8 py-6 flex flex-row-reverse gap-3">
                    <button type="button" @click="$store.app.confirmModal.show = false; if($store.app.confirmModal.onConfirm) $store.app.confirmModal.onConfirm()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 transition-all">
                        Confirm
                    </button>
                    <button type="button" @click="$store.app.confirmModal.show = false"
                            class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 px-6 py-2.5 text-sm font-bold transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
