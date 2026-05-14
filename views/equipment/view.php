<?php
/** @var array $data */
include_once __DIR__ . '/../layouts/audit_helper.php';
$e = $data['equipment'];
$customData = $e['custom_data'] ? (is_string($e['custom_data']) ? json_decode($e['custom_data'], true) : $e['custom_data']) : [];
$schema = $e['form_schema'] ? (is_string($e['form_schema']) ? json_decode($e['form_schema'], true) : $e['form_schema']) : [];
$images = $e['images'] ? (is_string($e['images']) ? json_decode($e['images'], true) : $e['images']) : [];
?>

<div class="max-w-5xl mx-auto space-y-8" x-data="{ 
    selectedImage: null,
    showModal: false
}">
    <!-- Image Modal -->
    <template x-teleport="body">
        <div x-show="showModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0" x-cloak>
            
            <template x-if="showModal">
                <div @click.away="showModal = false" class="relative max-w-5xl w-full h-full flex flex-col items-center justify-center">
                    <button @click="showModal = false" class="absolute top-0 -right-12 text-white text-3xl hover:text-blue-400 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <img :src="selectedImage" class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl">
                </div>
            </template>
        </div>
    </template>
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="index.php?route=list_equipment" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:text-blue-600 transition-all shadow-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?php echo htmlspecialchars(($e['name'] ?: $e['type_name']) ?? ''); ?></h1>
                <p class="text-sm text-slate-500 font-medium">Asset ID: #<?php echo $e['id']; ?> &middot; <?php echo htmlspecialchars($e['type_name'] ?? ''); ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?route=edit_equipment&id=<?php echo $e['id']; ?>" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 transition-all">
                <i class="bi bi-pencil-square mr-2"></i> Edit Asset
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info Columns -->
        <div class="lg:col-span-2 space-y-8">
            <!-- 1. Identification -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <i class="bi bi-info-circle-fill text-blue-500"></i>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Identification</h3>
                </div>
                <div class="p-8 grid grid-cols-2 gap-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Brand / Manufacturer</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($e['brand'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Model Number</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($e['model'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Serial Number</p>
                        <p class="text-sm font-mono font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($e['serial_number'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">MAC Address</p>
                        <p class="text-sm font-mono font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($e['mac_address'] ?: 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- 2. Specifications (Custom Fields) -->
            <?php if (!empty($schema)): ?>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <i class="bi bi-cpu-fill text-purple-500"></i>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Specifications</h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach ($schema as $field): ?>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1"><?php echo htmlspecialchars($field['label'] ?? ''); ?></p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                            <?php 
                                $val = $customData[$field['name']] ?? 'N/A';
                                if (is_array($val)) {
                                    echo htmlspecialchars(implode(', ', $val));
                                } else {
                                    echo htmlspecialchars((string)$val);
                                }
                            ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. Network Details (Conditional) -->
            <?php if ($e['network']): ?>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-5 bg-cyan-50 dark:bg-cyan-900/10 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-globe2 text-cyan-600"></i>
                        <h3 class="text-xs font-black text-cyan-700 dark:text-cyan-500 uppercase tracking-widest">Network Configuration</h3>
                    </div>
                    <span class="px-3 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 text-[10px] font-black rounded-full uppercase"><?php echo htmlspecialchars($e['network']['ip_address'] ?? ''); ?></span>
                </div>
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Cable Number</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($e['network']['cable_no'] ?: 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Patch Panel</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                <?php echo htmlspecialchars($e['network']['patch_panel_no'] ?: '-'); ?> / <?php echo htmlspecialchars($e['network']['patch_panel_port'] ?: '-'); ?>
                                <span class="block text-[10px] text-slate-500 font-medium"><?php echo htmlspecialchars($e['network']['patch_panel_location'] ?? ''); ?></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Network Switch</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                <?php echo htmlspecialchars($e['network']['switch_no'] ?: '-'); ?> / <?php echo htmlspecialchars($e['network']['switch_port'] ?: '-'); ?>
                                <span class="block text-[10px] text-slate-500 font-medium"><?php echo htmlspecialchars($e['network']['switch_location'] ?? ''); ?></span>
                            </p>
                        </div>
                    </div>
                    <?php if ($e['network']['remarks']): ?>
                    <div class="pt-4 border-t border-slate-50 dark:border-slate-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Network Remarks</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo nl2br(htmlspecialchars($e['network']['remarks'] ?? '')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4. Equipment Gallery -->
            <?php if (!empty($images)): ?>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <i class="bi bi-images text-blue-500"></i>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Equipment Photos</h3>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <?php foreach ($images as $img): ?>
                        <div @click="selectedImage = '<?php echo $img; ?>'; showModal = true" class="group relative aspect-square rounded-2xl overflow-hidden cursor-zoom-in bg-slate-100 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <img src="<?php echo $img; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                                <i class="bi bi-zoom-in text-white opacity-0 group-hover:opacity-100 transition-opacity text-2xl"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar Info -->
        <div class="space-y-8">
            <!-- Status Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-8 space-y-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Operational Status</p>
                        <?php 
                        $statusColors = [
                            'In Use' => 'bg-blue-500',
                            'Available' => 'bg-green-500',
                            'Under Repair' => 'bg-orange-500',
                            'Retired' => 'bg-slate-500',
                            'Lost/Stolen' => 'bg-red-500'
                        ];
                        $color = $statusColors[$e['status']] ?? 'bg-slate-500';
                        ?>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full <?php echo $color; ?> animate-pulse"></div>
                            <span class="text-sm font-black text-slate-800 dark:text-white"><?php echo $e['status']; ?></span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Physical Condition</p>
                        <?php 
                        $conditionColors = [
                            'excellent' => 'bg-green-500',
                            'good' => 'bg-blue-500',
                            'fair' => 'bg-yellow-500',
                            'poor' => 'bg-orange-500',
                            'broken' => 'bg-red-500'
                        ];
                        // Use !empty to handle empty strings correctly
                        $dbCond = !empty($e['condition']) ? strtolower((string)$e['condition']) : 'excellent';
                        $condColor = $conditionColors[$dbCond] ?? 'bg-slate-500';
                        ?>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full <?php echo $condColor; ?>"></div>
                            <span class="text-sm font-black text-slate-800 dark:text-white uppercase">
                                <?php echo htmlspecialchars($dbCond); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Allocated -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-4 bg-emerald-50 dark:bg-emerald-900/10 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <i class="bi bi-geo-alt-fill text-emerald-600"></i>
                    <h3 class="text-[10px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest">Location</h3>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Office / Floor</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($e['office_location'] ?: 'N/A'); ?></p>
                        <p class="text-xs text-slate-500 font-medium"><?php echo htmlspecialchars($e['floor'] ?: ''); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Department / Room</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($e['department'] ?: 'N/A'); ?></p>
                    </div>
                    <div class="pt-6 border-t border-slate-50 dark:border-slate-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Assigned To</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($e['assigned_to'] ?: 'Unassigned'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warranty Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-8 py-4 bg-orange-50 dark:bg-orange-900/10 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <i class="bi bi-shield-check text-orange-600"></i>
                    <h3 class="text-[10px] font-black text-orange-700 dark:text-orange-500 uppercase tracking-widest">Warranty</h3>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Vendor / Seller</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($e['warranty_seller'] ?: 'N/A'); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Purchased</p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo $e['warranty_purchase_date'] ? date('d/m/Y', strtotime($e['warranty_purchase_date'])) : '-'; ?></p>
                            </div>
                            <div>
                            <p class="text-[10px] text-slate-400 font-medium mb-0.5">Expiry Date</p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo $e['warranty_expiry'] ? date('d/m/Y', strtotime($e['warranty_expiry'])) : '-'; ?></p>
                        </div>
                    </div>
                    <?php if ($e['warranty_file']): ?>
                    <div class="pt-6 border-t border-slate-50 dark:border-slate-800">
                        <a href="<?php echo htmlspecialchars($e['warranty_file']); ?>" target="_blank" class="w-full py-3 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-orange-100 transition-all border border-orange-100 dark:border-orange-900/30">
                            <i class="bi bi-file-earmark-pdf"></i> View Documents
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
