<?php
/** @var array $data */
$equipments = $data['equipments'];
$types = $data['types'];
$filters = $data['filters'];
$currentPage = $data['currentPage'];
$totalPages = $data['pages'];

$statusColors = [
    'In Use' => 'bg-green-100 text-green-700',
    'Available' => 'bg-blue-100 text-blue-700',
    'Under Repair' => 'bg-orange-100 text-orange-700',
    'Retired' => 'bg-slate-100 text-slate-700',
    'Lost/Stolen' => 'bg-red-100 text-red-700'
];
?>

<div class="max-w-7xl mx-auto space-y-8" x-data="equipmentList()">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage and track all company hardware assets.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Bulk Actions -->
            <div x-show="selectedIds.length > 0" x-cloak x-transition class="flex items-center gap-2 pr-4 border-r border-slate-200 dark:border-slate-800">
                <span class="text-xs font-bold text-blue-600" x-text="selectedIds.length + ' selected'"></span>
                <button @click="bulkDelete" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-all">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </div>

            <button @click="openColumnModal" class="p-2.5 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl text-slate-500 hover:text-blue-600 transition-all shadow-sm">
                <i class="bi bi-layout-three-columns"></i>
            </button>
            <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
            <a href="index.php?route=add_equipment" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center">
                <i class="bi bi-plus-lg mr-2"></i> Add Equipment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm transition-colors">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="filters.search" @input.debounce.500ms="applyFilters" placeholder="Search serial, brand, model..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-slate-100">
            </div>
            <select x-model="filters.type_id" @change="applyFilters" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-slate-100">
                <option value="">All Types</option>
                <?php foreach($types as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select x-model="filters.status" @change="applyFilters" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-slate-100">
                <option value="">All Status</option>
                <?php foreach(array_keys($statusColors) as $s): ?>
                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
            <button @click="clearFilters" class="text-sm font-bold text-slate-500 hover:text-red-600 transition-colors">Clear All Filters</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <template x-for="col in visibleColumns" :key="col.id">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-blue-600 transition-colors">
                                <span x-text="col.label"></span>
                            </th>
                        </template>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($equipments as $e): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="<?php echo $e['id']; ?>" x-model="selectedIds" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <template x-for="col in visibleColumns" :key="col.id">
                                <td class="px-6 py-4">
                                    <template x-if="col.id === 'type'">
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($e['type_name']); ?></span>
                                    </template>
                                    <template x-if="col.id === 'brand_model'">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars(($e['brand'] ?? '') . ' ' . ($e['model'] ?? '')); ?></span>
                                            <span class="text-[10px] text-slate-400 font-medium"><?php echo htmlspecialchars($e['serial_number'] ?? ''); ?></span>
                                        </div>
                                    </template>
                                    <template x-if="col.id === 'status'">
                                        <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider <?php echo $statusColors[$e['status']] ?? 'bg-slate-100'; ?>">
                                            <?php echo $e['status']; ?>
                                        </span>
                                    </template>
                                    <template x-if="col.id === 'location'">
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($e['location'] ?? 'Not Set'); ?></span>
                                    </template>
                                    <template x-if="col.id === 'network'">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-blue-600"><?php echo htmlspecialchars($e['ip_address'] ?? 'No IP'); ?></span>
                                            <span class="text-[9px] text-slate-400"><?php echo htmlspecialchars($e['mac_address'] ?? ''); ?></span>
                                        </div>
                                    </template>
                                    <template x-if="col.id === 'warranty'">
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                            <?php echo $e['warranty_expiry'] ? date('d/m/Y', strtotime($e['warranty_expiry'])) : 'No Warranty'; ?>
                                        </span>
                                    </template>
                                    <template x-if="col.id === 'created_at'">
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                            <?php echo date('d/m/Y', strtotime($e['created_at'])); ?>
                                        </span>
                                    </template>
                                </td>
                            </template>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="index.php?route=view_equipment&id=<?php echo $e['id']; ?>" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="index.php?route=edit_equipment&id=<?php echo $e['id']; ?>" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button @click="deleteItem(<?php echo $e['id']; ?>, '<?php echo htmlspecialchars($e['serial_number'] ?? ''); ?>')" class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($equipments)): ?>
                        <tr>
                            <td colspan="100" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-inbox text-4xl text-slate-200 mb-2"></i>
                                    <p class="text-slate-400 text-sm italic">No equipment found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
        <div class="flex items-center justify-between pt-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></p>
            <div class="flex gap-2">
                <a href="index.php?route=list_equipment&page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" 
                    class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-all <?php echo $currentPage <= 1 ? 'pointer-events-none opacity-50' : ''; ?>">
                    Previous
                </a>
                <a href="index.php?route=list_equipment&page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" 
                    class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-all <?php echo $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : ''; ?>">
                    Next
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Column Customization Modal -->
    <template x-teleport="body">
        <div x-show="showColumnModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <template x-if="showColumnModal">
                <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden" @click.away="showColumnModal = false">
                    <div class="px-8 py-6 bg-slate-800 text-white flex justify-between items-center">
                        <h3 class="text-lg font-bold">Customize Columns</h3>
                        <button @click="showColumnModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="p-8 grid grid-cols-1 gap-4">
                        <template x-for="col in allColumns" :key="col.id">
                            <label class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer transition-colors">
                                <input type="checkbox" :checked="col.visible" @change="toggleColumn(col.id)" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200" x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                    <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button @click="showColumnModal = false" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20">Close</button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
function equipmentList() {
    return {
        showColumnModal: false,
        filters: <?php echo json_encode($filters); ?>,
        selectedIds: [],
        allColumns: [
            { id: 'type', label: 'Type/Category', visible: true },
            { id: 'brand_model', label: 'Brand & Model', visible: true },
            { id: 'status', label: 'Status', visible: true },
            { id: 'location', label: 'Location', visible: true },
            { id: 'network', label: 'Network Info', visible: true },
            { id: 'warranty', label: 'Warranty Status', visible: false },
            { id: 'created_at', label: 'Date Added', visible: false }
        ],
        init() {
            const saved = localStorage.getItem('equipment_columns');
            if (saved) {
                const prefs = JSON.parse(saved);
                this.allColumns.forEach(col => {
                    if (prefs[col.id] !== undefined) col.visible = prefs[col.id];
                });
            }
        },
        get visibleColumns() {
            return this.allColumns.filter(c => c.visible);
        },
        get allSelected() {
            return this.selectedIds.length > 0 && this.selectedIds.length === <?php echo count($equipments); ?>;
        },
        toggleAll() {
            if (this.allSelected) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [<?php echo implode(',', array_column($equipments, 'id')); ?>];
            }
        },
        toggleColumn(id) {
            const col = this.allColumns.find(c => c.id === id);
            if (col) col.visible = !col.visible;
            const prefs = {};
            this.allColumns.forEach(c => prefs[c.id] = c.visible);
            localStorage.setItem('equipment_columns', JSON.stringify(prefs));
        },
        applyFilters() {
            const params = new URLSearchParams();
            Object.keys(this.filters).forEach(k => {
                if (this.filters[k]) params.set(k, this.filters[k]);
            });
            window.location.href = 'index.php?route=list_equipment&' + params.toString();
        },
        clearFilters() {
            window.location.href = 'index.php?route=list_equipment';
        },
        openColumnModal() {
            this.showColumnModal = true;
        },
        async deleteItem(id, label) {
            Alpine.store('app').confirm('Delete Asset', `Are you sure you want to delete asset ${label}?`, async () => {
                try {
                    const response = await fetch(`index.php?route=equipment_delete&id=${id}`);
                    const result = await response.json();
                    if (result.success) {
                        Alpine.store('app').addToast('Deleted', result.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Alpine.store('app').addToast('Error', result.message, 'error');
                    }
                } catch (e) {
                    Alpine.store('app').addToast('Error', 'Failed to delete.', 'error');
                }
            });
        },
        async bulkDelete() {
            Alpine.store('app').confirm('Bulk Delete', `Are you sure you want to delete ${this.selectedIds.length} items?`, async () => {
                try {
                    const response = await fetch('index.php?route=equipment_bulk_delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            ids: this.selectedIds,
                            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        Alpine.store('app').addToast('Deleted', result.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Alpine.store('app').addToast('Error', result.message, 'error');
                    }
                } catch (e) {
                    Alpine.store('app').addToast('Error', 'Bulk deletion failed.', 'error');
                }
            });
        }
    }
}
</script>
