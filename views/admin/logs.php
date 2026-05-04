<?php
/** @var array $data */
$logs = $data['logs'];
$totalPages = $data['pages'];
$currentPage = $data['currentPage'];
$filters = $data['filters'];

$levelColors = [
    'info' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'error' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    'critical' => 'bg-red-600 text-white dark:bg-red-600 dark:text-white'
];

$categories = ['auth', 'equipment', 'task', 'network', 'user', 'system', 'security', 'tool'];

// Build query string for pagination
$queryString = http_build_query(array_filter($filters));
?>

<div class="max-w-7xl mx-auto space-y-6" x-data="logManager()">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">System Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Audit trail of all system events and user actions.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?route=admin_logs&export=csv&<?php echo $queryString; ?>" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-all flex items-center">
                <i class="bi bi-file-earmark-spreadsheet mr-2"></i> Export CSV
            </a>
            <a href="index.php?route=admin_logs&export=txt&<?php echo $queryString; ?>" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-all flex items-center">
                <i class="bi bi-file-earmark-text mr-2"></i> Export TXT
            </a>
            <button @click="clearLogs" class="px-5 py-2.5 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold hover:bg-rose-100 transition-all flex items-center">
                <i class="bi bi-trash3 mr-2"></i> Clear Logs
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm transition-colors">
        <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="route" value="admin_logs">
            
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date Range</label>
                <div class="flex gap-2">
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Level</label>
                <select name="level" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Levels</option>
                    <option value="info" <?php echo $filters['level'] === 'info' ? 'selected' : ''; ?>>Info</option>
                    <option value="warning" <?php echo $filters['level'] === 'warning' ? 'selected' : ''; ?>>Warning</option>
                    <option value="error" <?php echo $filters['level'] === 'error' ? 'selected' : ''; ?>>Error</option>
                    <option value="critical" <?php echo $filters['level'] === 'critical' ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category</label>
                <select name="category" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $filters['category'] === $cat ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Message</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Search text..." 
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all">Apply Filters</button>
                <a href="index.php?route=admin_logs" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-bold hover:bg-slate-200 transition-all flex items-center justify-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Level</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User / IP</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Message</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                <?php echo date('d/m/Y, h:i A', strtotime($log['timestamp'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider <?php echo $levelColors[$log['level']] ?? ''; ?>">
                                    <?php echo $log['level']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($log['category']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></span>
                                    <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400">
                                <?php echo htmlspecialchars($log['message']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="viewDetails(<?php echo $log['id']; ?>)" 
                                    class="px-3 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg text-[10px] font-bold text-blue-600 hover:bg-blue-50 transition-all">
                                    Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                        <i class="bi bi-info-circle text-2xl"></i>
                                    </div>
                                    <p class="text-slate-400 text-sm italic">No logs found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <p class="text-xs text-slate-500">Showing page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> (Total: <?php echo $data['total']; ?> entries)</p>
            <div class="flex gap-2">
                <?php if ($currentPage > 1): ?>
                    <a href="index.php?route=admin_logs&page=<?php echo $currentPage - 1; ?>&<?php echo $queryString; ?>" 
                       class="px-5 py-2 text-xs font-bold text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 shadow-sm transition-all flex items-center">
                       <i class="bi bi-chevron-left mr-2"></i> Previous
                    </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="index.php?route=admin_logs&page=<?php echo $currentPage + 1; ?>&<?php echo $queryString; ?>" 
                       class="px-5 py-2 text-xs font-bold text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 shadow-sm transition-all flex items-center">
                       Next <i class="bi bi-chevron-right ml-2"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Log Detail Modal -->
    <template x-teleport="body">
        <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition x-cloak>
            <template x-if="showModal">
                <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]" @click.away="showModal = false">
                    <div class="px-8 py-5 bg-slate-800 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-bold">Log Event Details</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="selectedLog?.timestamp || 'Loading...'"></p>
                        </div>
                        <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors"><i class="bi bi-x-lg"></i></button>
                    </div>
                    
                    <div class="p-8 space-y-6 overflow-y-auto custom-scrollbar" x-show="selectedLog" x-transition>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Severity Level</p>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider inline-block" :class="getLevelClass(selectedLog?.level)" x-text="selectedLog?.level"></span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</p>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest" x-text="selectedLog?.category"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">User / IP</p>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="(selectedLog?.username || 'System') + ' (' + selectedLog?.ip_address + ')'"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</p>
                                <button @click="copyContext" class="text-[10px] font-bold text-blue-600 hover:underline flex items-center gap-1">
                                    <i class="bi bi-clipboard"></i> Copy JSON Context
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Message</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800" x-text="selectedLog?.message"></p>
                        </div>

                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">User Agent</p>
                            <p class="text-[10px] font-mono text-slate-500 break-all" x-text="selectedLog?.user_agent"></p>
                        </div>

                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Context (JSON)</p>
                            <div class="bg-slate-950 p-6 rounded-2xl overflow-x-auto">
                                <pre class="text-[11px] font-mono text-emerald-400" x-text="formatJSON(selectedLog?.context)"></pre>
                            </div>
                        </div>
                    </div>

                    <div x-show="!selectedLog" class="p-20 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
                        <p class="text-slate-400 text-sm italic">Loading log details...</p>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
function logManager() {
    return {
        showModal: false,
        selectedLog: null,
        getLevelClass(level) {
            const classes = {
                'info': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                'warning': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                'error': 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                'critical': 'bg-red-600 text-white'
            };
            return classes[level] || '';
        },
        async viewDetails(id) {
            this.selectedLog = null;
            this.showModal = true;
            try {
                const response = await fetch(`index.php?route=admin_log_detail&id=${id}`);
                const result = await response.json();
                if (result.success) {
                    this.selectedLog = result.log;
                } else {
                    Alpine.store('app').addToast('Error', result.message, 'error');
                    this.showModal = false;
                }
            } catch (e) {
                Alpine.store('app').addToast('Error', 'Failed to fetch log details.', 'error');
                this.showModal = false;
            }
        },
        formatJSON(jsonStr) {
            if (!jsonStr) return 'No context available.';
            try {
                const obj = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
                return JSON.stringify(obj, null, 2);
            } catch (e) {
                return jsonStr;
            }
        },
        copyContext() {
            if (!this.selectedLog?.context) return;
            const text = this.formatJSON(this.selectedLog.context);
            navigator.clipboard.writeText(text).then(() => {
                Alpine.store('app').addToast('Copied', 'JSON context copied to clipboard.', 'success');
            });
        },
        clearLogs() {
            Alpine.store('app').confirm('Clear System Logs', 'Are you sure you want to permanently delete ALL logs? This action is logged and cannot be undone.', async () => {
                try {
                    const response = await fetch('index.php?route=admin_clear_logs');
                    const result = await response.json();
                    if (result.success) {
                        Alpine.store('app').addToast('Success', 'Logs cleared successfully.', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } catch (e) {
                    Alpine.store('app').addToast('Error', 'Failed to clear logs.', 'error');
                }
            });
        }
    }
}
</script>
