<?php
/** @var array $data */
$notifications = $data['notifications'];
$totalPages = $data['pages'];
$currentPage = $data['currentPage'];
$filters = $data['filters'] ?? ['status' => '', 'date_from' => '', 'date_to' => ''];

$priorityColors = [
    'low' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'medium' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
];
?>

<div class="max-w-5xl mx-auto space-y-6" x-data="notificationList()">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage all your system alerts and activity updates.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="markAllAsRead" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-blue-600 hover:bg-blue-50 transition-all flex items-center shadow-sm">
                <i class="bi bi-check-all mr-1.5 text-lg"></i> Mark all as read
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black uppercase text-slate-400">Status</label>
            <select x-model="filters.status" @change="applyFilters" class="bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border-none rounded-lg text-xs font-bold py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-blue-500">
                <option value="">Active</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        
        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black uppercase text-slate-400">From</label>
            <input type="date" x-model="filters.date_from" @change="applyFilters" class="bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border-none rounded-lg text-xs font-bold py-1.5 px-3 focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black uppercase text-slate-400">To</label>
            <input type="date" x-model="filters.date_to" @change="applyFilters" class="bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border-none rounded-lg text-xs font-bold py-1.5 px-3 focus:ring-2 focus:ring-blue-500">
        </div>

        <button @click="resetFilters" class="text-[10px] font-bold text-slate-400 hover:text-red-500 ml-auto">Reset Filters</button>
    </div>

    <!-- Bulk Actions (Sticky/Floated when items selected) -->
    <div x-show="selectedIds.length > 0" x-cloak x-transition
         class="bg-blue-600 text-white p-4 rounded-2xl shadow-xl shadow-blue-500/20 flex items-center justify-between sticky top-24 z-30">
        <div class="flex items-center gap-4">
            <span class="text-sm font-black"><span x-text="selectedIds.length"></span> items selected</span>
            <div class="h-4 w-px bg-white/20"></div>
            <div class="flex gap-2">
                <button @click="runBulkAction('mark_read')" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Mark Read</button>
                <button @click="runBulkAction('mark_unread')" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Mark Unread</button>
                <button @click="runBulkAction('archive')" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Archive</button>
            </div>
        </div>
        <button @click="selectedIds = []" class="text-white/60 hover:text-white"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="bg-slate-50/50 dark:bg-slate-800/50 px-6 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-4">
            <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Select All</span>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            <?php foreach ($notifications as $n): ?>
                <div class="p-6 flex items-center gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors cursor-pointer <?php echo !$n['is_read'] ? 'bg-blue-50/30 dark:bg-blue-900/10' : ''; ?>"
                     @click.stop="">
                    <div class="flex-shrink-0">
                        <input type="checkbox" :value="<?php echo $n['id']; ?>" x-model="selectedIds" 
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>

                    <div @click="$store.app.goToNotification(<?php echo htmlspecialchars(json_encode($n)); ?>)" class="flex-1 flex items-center gap-4 min-w-0">
                        <div class="p-2.5 rounded-xl flex-shrink-0 <?php echo $priorityColors[$n['priority']] ?? $priorityColors['medium']; ?>">
                            <?php 
                                $icon = 'bi-bell';
                                if ($n['type'] === 'task') $icon = 'bi-list-check';
                                if ($n['type'] === 'equipment') $icon = 'bi-pc-display';
                                if ($n['type'] === 'network') $icon = 'bi-diagram-3';
                                if ($n['type'] === 'user') $icon = 'bi-people';
                                if ($n['type'] === 'warranty') $icon = 'bi-shield-check';
                                if ($n['priority'] === 'urgent') $icon = 'bi-exclamation-triangle-fill';
                            ?>
                            <i class="bi <?php echo $icon; ?> text-lg"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-4 mb-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    <?php echo htmlspecialchars($n['type']); ?> • <?php echo date('d/m/Y, h:i A', strtotime($n['created_at'])); ?>
                                </span>
                                <?php if (!$n['is_read']): ?>
                                    <span class="flex h-2 w-2 rounded-full bg-blue-600"></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1"><?php echo htmlspecialchars($n['message']); ?></p>
                            
                            <?php if ($n['read_at']): ?>
                                <p class="text-[10px] text-slate-400 italic">Read at: <?php echo date('d/m/Y, h:i A', strtotime($n['read_at'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <?php if (!$n['is_read']): ?>
                            <button @click="markAsRead(<?php echo $n['id']; ?>)" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Mark as Read">
                                <i class="bi bi-check2"></i>
                            </button>
                        <?php endif; ?>
                        <button @click="acknowledge(<?php echo $n['id']; ?>)" class="p-2 text-slate-400 hover:text-red-600 transition-colors" title="Archive">
                            <i class="bi bi-archive"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($notifications)): ?>
                <div class="py-20 text-center">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-bell-slash text-2xl text-slate-400"></i>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">No notifications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between pt-4">
            <p class="text-xs text-slate-500">
                Showing page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
            </p>
            <div class="flex gap-2">
                <?php 
                $baseUrl = "index.php?route=list_notifications&status={$filters['status']}&date_from={$filters['date_from']}&date_to={$filters['date_to']}";
                ?>
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage - 1; ?>" 
                       class="px-4 py-2 text-xs font-bold text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage + 1; ?>" 
                       class="px-4 py-2 text-xs font-bold text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function notificationList() {
    return {
        filters: <?php echo json_encode($filters); ?>,
        selectedIds: [],
        availableIds: <?php echo json_encode(array_column($notifications, 'id')); ?>,
        
        get isAllSelected() {
            return this.availableIds.length > 0 && this.availableIds.every(id => this.selectedIds.includes(id));
        },
        
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...new Set([...this.selectedIds, ...this.availableIds])];
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.availableIds.includes(id));
            }
        },

        applyFilters() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `index.php?route=list_notifications&${params.toString()}`;
        },

        resetFilters() {
            window.location.href = 'index.php?route=list_notifications';
        },

        async runBulkAction(action) {
            if (this.selectedIds.length === 0) return;
            
            let confirmMsg = `Are you sure you want to ${action.replace('_', ' ')} for ${this.selectedIds.length} items?`;
            if (action === 'archive') confirmMsg = `Are you sure you want to archive ${this.selectedIds.length} notifications? They will be hidden from your list.`;

            Alpine.store('app').confirm('Bulk Action', confirmMsg, async () => {
                try {
                    const response = await fetch('index.php?route=notification_bulk_action', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: action,
                            ids: this.selectedIds,
                            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        Alpine.store('app').addToast('Success', result.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Alpine.store('app').addToast('Error', result.message, 'error');
                    }
                } catch (e) {
                    Alpine.store('app').addToast('Error', 'Bulk action failed.', 'error');
                }
            });
        },

        async markAsRead(id) {
            try {
                const response = await fetch(`index.php?route=notification_mark_read&id=${id}`);
                const result = await response.json();
                if (result.success) window.location.reload();
            } catch (e) {
                Alpine.store('app').addToast('Error', 'Failed to mark as read.', 'error');
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('index.php?route=notification_mark_all_read');
                const result = await response.json();
                if (result.success) window.location.reload();
            } catch (e) {
                Alpine.store('app').addToast('Error', 'Failed to mark all as read.', 'error');
            }
        },

        async acknowledge(id) {
            try {
                const response = await fetch(`index.php?route=notification_acknowledge&id=${id}`);
                const result = await response.json();
                if (result.success) window.location.reload();
            } catch (e) {
                Alpine.store('app').addToast('Error', 'Failed to archive notification.', 'error');
            }
        }
    }
}
</script>
