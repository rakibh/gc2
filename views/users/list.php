<?php
/** @var array $data */
$users = $data['users'];
$currentPage = $data['current_page'];
$totalPages = $data['pages'];
$sortBy = $data['sort_by'];
$sortDir = $data['sort_dir'];

function sortUrl($field, $currentSortBy, $currentSortDir) {
    $dir = ($field === $currentSortBy && $currentSortDir === 'ASC') ? 'DESC' : 'ASC';
    return "index.php?route=list_users&page=1&sort_by=$field&sort_dir=$dir";
}
?>

<div x-data="userManagement()">
    <!-- Header Actions -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden mb-6 transition-colors duration-300">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-100"><?php echo \Core\I18n::t('system_users'); ?></h3>
            <div class="flex items-center space-x-3">
                <button @click="exportUsers" class="text-slate-600 dark:text-slate-400 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-bold flex items-center transition-all">
                    <i class="bi bi-download mr-2"></i> Export
                </button>
                <button @click="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center transition-all shadow-sm">
                    <i class="bi bi-person-plus mr-2"></i> <?php echo \Core\I18n::t('add_user'); ?>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 font-medium">Profile</th>
                        <th class="px-6 py-3 font-medium">
                            <a href="<?php echo sortUrl('username', $sortBy, $sortDir); ?>" class="flex items-center hover:text-blue-600 transition-colors">
                                Name / Username <?php if($sortBy === 'username') echo $sortDir === 'ASC' ? '<i class="bi bi-arrow-up ml-1"></i>' : '<i class="bi bi-arrow-down ml-1"></i>'; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 font-medium">
                            <a href="<?php echo sortUrl('employee_id', $sortBy, $sortDir); ?>" class="flex items-center hover:text-blue-600 transition-colors">
                                <?php echo \Core\I18n::t('employee_id'); ?> <?php if($sortBy === 'employee_id') echo $sortDir === 'ASC' ? '<i class="bi bi-arrow-up ml-1"></i>' : '<i class="bi bi-arrow-down ml-1"></i>'; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 font-medium">Contact</th>
                        <th class="px-6 py-3 font-medium">
                            <a href="<?php echo sortUrl('role', $sortBy, $sortDir); ?>" class="flex items-center hover:text-blue-600 transition-colors">
                                <?php echo \Core\I18n::t('role'); ?> <?php if($sortBy === 'role') echo $sortDir === 'ASC' ? '<i class="bi bi-arrow-up ml-1"></i>' : '<i class="bi bi-arrow-down ml-1"></i>'; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 font-medium">
                            <a href="<?php echo sortUrl('status', $sortBy, $sortDir); ?>" class="flex items-center hover:text-blue-600 transition-colors">
                                <?php echo \Core\I18n::t('status'); ?> <?php if($sortBy === 'status') echo $sortDir === 'ASC' ? '<i class="bi bi-arrow-up ml-1"></i>' : '<i class="bi bi-arrow-down ml-1"></i>'; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 font-medium text-right"><?php echo \Core\I18n::t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-50 dark:border-blue-900/50">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">@<?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-medium"><?php echo htmlspecialchars($user['employee_id']); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-xs space-y-0.5">
                                    <span class="text-slate-700 dark:text-slate-300 font-medium"><i class="bi bi-envelope mr-1 text-slate-400"></i> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
                                    <span class="text-slate-500 dark:text-slate-400"><i class="bi bi-phone mr-1 text-slate-400"></i> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center text-slate-700 dark:text-slate-300">
                                    <span class="w-2 h-2 rounded-full mr-2 <?php echo $user['status'] === 'active' ? 'bg-green-500' : 'bg-slate-300 dark:bg-slate-600'; ?>"></span>
                                    <span class="capitalize"><?php echo $user['status']; ?></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-1">
                                    <button @click="openModal('view', <?php echo htmlspecialchars(json_encode($user)); ?>)" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button @click="openModal('edit', <?php echo htmlspecialchars(json_encode($user)); ?>)" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Edit User">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button @click="openModal('reset_password', <?php echo htmlspecialchars(json_encode($user)); ?>)" class="p-2 text-slate-400 hover:text-orange-600 transition-colors" title="Reset Password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button @click="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" :disabled="loading" class="p-2 text-slate-400 hover:text-red-600 transition-colors disabled:opacity-50" title="Delete User">
                                            <i class="bi bi-trash" x-show="!loading"></i>
                                            <i class="bi bi-arrow-repeat animate-spin" x-show="loading"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing page <span class="font-bold text-slate-800 dark:text-slate-100"><?php echo $currentPage; ?></span> of <span class="font-bold text-slate-800 dark:text-slate-100"><?php echo $totalPages; ?></span> (<?php echo $data['total']; ?> users)</p>
            <div class="flex space-x-1">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?route=list_users&page=<?php echo $i; ?>&sort_by=<?php echo $sortBy; ?>&sort_dir=<?php echo $sortDir; ?>" 
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo $i === $currentPage ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- User Form Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/75" @click="showModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showModal" x-transition.scale.95 
                class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border dark:border-slate-800">
                
                <div class="bg-slate-800 dark:bg-slate-950 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalTitle()"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <!-- FORM MODAL -->
                <div x-show="modalMode === 'add' || modalMode === 'edit'">
                    <form @submit.prevent="submitUser" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">First Name (Optional)</label>
                                <input type="text" x-model="formData.first_name" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Last Name (Optional)</label>
                                <input type="text" x-model="formData.last_name" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 tracking-tight">Username <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formData.username" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold bg-slate-50/50 dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 tracking-tight">Employee ID <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formData.employee_id" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold bg-slate-50/50 dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Email (Optional)</label>
                                <input type="email" x-model="formData.email" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Phone (Optional)</label>
                                <input type="text" x-model="formData.phone" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Designation (Optional)</label>
                                <input type="text" x-model="formData.designation" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 tracking-tight">Role <span class="text-red-500">*</span></label>
                                <select x-model="formData.role" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-slate-50/50 dark:bg-slate-800 dark:text-slate-100 font-bold">
                                    <option value="user">Standard User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 tracking-tight" x-text="modalMode === 'add' ? 'Password *' : 'Password (leave blank to keep)'"></label>
                                <input type="password" x-model="formData.password" :required="modalMode === 'add'" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100" :class="modalMode === 'add' ? 'bg-slate-50/50 font-bold' : ''">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 tracking-tight">Status <span class="text-red-500">*</span></label>
                                <select x-model="formData.status" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-slate-50/50 dark:bg-slate-800 dark:text-slate-100 font-bold">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t dark:border-slate-800">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100">Cancel</button>
                            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center shadow-sm disabled:opacity-50 transition-all">
                                <span x-show="!loading" x-text="modalMode === 'add' ? 'Create User' : 'Update User'"></span>
                                <span x-show="loading" class="animate-spin mr-2"><i class="bi bi-arrow-repeat"></i></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- VIEW MODAL WITH REVISION HISTORY -->
                <div x-show="modalMode === 'view'" class="p-6">
                    <div class="flex items-center space-x-6 mb-8 pb-8 border-b border-slate-100 dark:border-slate-800">
                        <div x-show="formData.profile_photo">
                            <img :src="formData.profile_photo" class="w-24 h-24 rounded-2xl object-cover border-4 border-slate-50 dark:border-slate-800 shadow-sm">
                        </div>
                        <div x-show="!formData.profile_photo" class="w-24 h-24 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-4xl font-bold border-4 border-slate-50 dark:border-slate-800 shadow-sm" x-text="formData.username ? formData.username.charAt(0).toUpperCase() : ''"></div>
                        
                        <div>
                            <h4 class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="(formData.first_name || '') + ' ' + (formData.last_name || '')"></h4>
                            <p class="text-slate-500 dark:text-slate-400 font-medium" x-text="formData.designation || 'No Designation'"></p>
                            <div class="flex mt-2 space-x-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400" x-text="formData.role"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="formData.status === 'active' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" x-text="formData.status"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Employee ID</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="formData.employee_id"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Username</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="'@' + formData.username"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Email Address</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="formData.email || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Phone Number</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="formData.phone || 'N/A'"></p>
                        </div>
                    </div>

                    <!-- Revision History -->
                    <div class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-8">
                        <div class="flex items-center justify-between mb-6">
                            <h5 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center">
                                <i class="bi bi-clock-history mr-2 text-blue-600"></i> Revision History
                            </h5>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Last 15 Revisions</span>
                        </div>

                        <div class="flow-root overflow-y-auto max-h-64 pr-2 custom-scrollbar">
                            <ul role="list" class="-mb-8">
                                <template x-for="(rev, index) in revisions" :key="rev.id">
                                    <li>
                                        <div class="relative pb-8">
                                            <span x-show="index !== revisions.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-800" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-slate-900"
                                                        :class="rev.action === 'create' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'">
                                                        <i class="bi" :class="rev.action === 'create' ? 'bi-plus-lg' : 'bi-pencil-fill text-[10px]'"></i>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                                            <span class="font-bold text-slate-900 dark:text-slate-100" x-text="rev.action === 'create' ? 'User created' : 'Profile updated'"></span>
                                                            by <span class="font-medium text-slate-700 dark:text-slate-300" x-text="rev.responsible_user"></span>
                                                        </p>
                                                        <div x-show="rev.action === 'update' && rev.new_values" class="mt-1 text-xs text-slate-400 italic">
                                                            <template x-for="(val, key) in JSON.parse(rev.new_values)" :key="key">
                                                                <span class="mr-2" x-text="key + ': ' + (val === null ? 'N/A' : val)"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-xs text-slate-400">
                                                        <time x-text="formatDate(rev.created_at)"></time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- RESET PASSWORD MODAL -->
                <div x-show="modalMode === 'reset_password'" class="p-6 space-y-4 text-center">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-key-fill text-3xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">Reset Password</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Enter a new password for <span class="font-bold text-slate-800 dark:text-slate-200" x-text="formData.username"></span>. The user will be forced to change this on their next login.</p>
                    
                    <div class="max-w-xs mx-auto text-left mt-6">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">New Password</label>
                        <input type="password" x-model="formData.password" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm dark:bg-slate-800 dark:text-slate-100">
                    </div>

                    <div class="flex justify-center space-x-3 pt-8">
                        <button type="button" @click="showModal = false" class="px-6 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 transition-all">Cancel</button>
                        <button @click="executeReset" :disabled="loading" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-2 rounded-lg text-sm font-bold shadow-md disabled:opacity-50 transition-all">
                            Reset & Force Change
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function userManagement() {
    return {
        showModal: false,
        modalMode: 'add',
        loading: false,
        revisions: [],
        formData: {
            user_id: '',
            username: '',
            employee_id: '',
            password: '',
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            designation: '',
            profile_photo: '',
            role: 'user',
            status: 'active',
            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
        },
        modalTitle() {
            switch(this.modalMode) {
                case 'add': return 'Create New User';
                case 'edit': return 'Edit User: ' + this.formData.username;
                case 'view': return 'User Details: ' + this.formData.username;
                case 'reset_password': return 'Reset Password';
                default: return 'User Management';
            }
        },
        async openModal(mode, user = null) {
            this.modalMode = mode;
            this.revisions = [];
            if (user) {
                this.formData = { ...this.formData, ...user, user_id: user.id, password: '', csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' };
                if (mode === 'view') {
                    const response = await fetch('index.php?route=user_revision_history&id=' + user.id);
                    const result = await response.json();
                    if (result.success) this.revisions = result.revisions;
                }
            } else {
                this.formData = {
                    user_id: '', username: '', employee_id: '', password: '',
                    first_name: '', last_name: '', email: '', phone: '',
                    designation: '', profile_photo: '', role: 'user', status: 'active',
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                };
            }
            this.showModal = true;
        },
        async submitUser() {
            this.loading = true;
            try {
                const response = await fetch('index.php?route=user_store', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.formData)
                });
                const result = await response.json();
                if (result.success) {
                    Alpine.store('app').addToast('Success', result.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Alpine.store('app').addToast('Error', result.message, 'error');
                    this.loading = false;
                }
            } catch (e) {
                Alpine.store('app').addToast('Error', 'An unexpected error occurred.', 'error');
                this.loading = false;
            }
        },
        async executeReset() {
            this.loading = true;
            try {
                const response = await fetch('index.php?route=user_store', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: this.formData.user_id,
                        password: this.formData.password,
                        force_password_change: 1,
                        csrf_token: this.formData.csrf_token
                    })
                });
                const result = await response.json();
                if (result.success) {
                    Alpine.store('app').addToast('Success', 'Password reset successful.', 'success');
                    setTimeout(() => this.showModal = false, 1000);
                } else {
                    Alpine.store('app').addToast('Error', result.message, 'error');
                }
            } catch (e) {
                Alpine.store('app').addToast('Error', 'Failed to reset password.', 'error');
            } finally {
                this.loading = false;
            }
        },
        deleteUser(id, name) {
            Alpine.store('app').confirm(
                'Delete User',
                'Are you sure you want to delete user ' + name + '? This action is permanent.',
                async () => {
                    this.loading = true;
                    try {
                        const response = await fetch('index.php?route=delete_user&id=' + id);
                        Alpine.store('app').addToast('Success', 'User deleted successfully.', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } catch (e) {
                        Alpine.store('app').addToast('Error', 'Failed to delete user.', 'error');
                        this.loading = false;
                    }
                }
            );
        },
        exportUsers() {
            Alpine.store('app').addToast('Export', 'Preparing CSV download...', 'success');
            window.location.href = 'index.php?route=list_users&export=csv';
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            const d = date.getDate().toString().padStart(2, '0');
            const m = (date.getMonth() + 1).toString().padStart(2, '0');
            const y = date.getFullYear();
            const h = date.getHours().toString().padStart(2, '0');
            const min = date.getMinutes().toString().padStart(2, '0');
            return `${d}/${m}/${y}, ${h}:${min}`;
        }
    }
}
</script>
