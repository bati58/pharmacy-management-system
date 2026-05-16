<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>

<div class="animate-fade-in max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Account Settings</h2>
        <p class="text-slate-500 mt-1 font-medium">Manage your profile and security preferences.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile Column -->
        <div class="md:col-span-1">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Profile Information</h3>
            <p class="text-sm text-slate-500">Update your account's profile information and email address.</p>
        </div>
        
        <div class="md:col-span-2">
            <div class="card p-8">
                <form id="profileForm" class="space-y-6">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-200 flex items-center justify-center text-white font-bold text-3xl">
                            <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                            <p class="text-sm text-slate-400"><?php echo htmlspecialchars($_SESSION['email'] ?? 'No email set'); ?></p>
                            <span class="inline-block mt-2 px-2 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                <?php echo str_replace('_', ' ', $_SESSION['role']); ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Full Name</label>
                        <input type="text" id="profileName" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Email Address</label>
                        <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl opacity-60 cursor-not-allowed font-medium" disabled>
                        <p class="text-[10px] text-slate-400 mt-2 px-1">Email address cannot be changed. Contact admin for assistance.</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn-premium btn-premium-primary px-8">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-span-full border-t border-slate-100 my-4"></div>

        <!-- Security Column -->
        <div class="md:col-span-1">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Security</h3>
            <p class="text-sm text-slate-500">Ensure your account is using a long, random password to stay secure.</p>
        </div>

        <div class="md:col-span-2">
            <div class="card p-8">
                <form id="passwordForm" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Current Password</label>
                        <div class="relative">
                            <input type="password" id="currentPassword" class="w-full px-4 py-3 pr-12 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                            <button type="button" onclick="togglePassword('currentPassword', 'eyeIconCurrent')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                <i class="fas fa-eye" id="eyeIconCurrent"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="newPassword" class="w-full px-4 py-3 pr-12 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                            <button type="button" onclick="togglePassword('newPassword', 'eyeIconNew')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                <i class="fas fa-eye" id="eyeIconNew"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" class="w-full px-4 py-3 pr-12 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                            <button type="button" onclick="togglePassword('confirmPassword', 'eyeIconConfirm')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                <i class="fas fa-eye" id="eyeIconConfirm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn-premium bg-slate-900 text-white hover:bg-slate-800 px-8">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'manager'): ?>
        <div class="col-span-full border-t border-slate-100 my-4"></div>

        <!-- System Column -->
        <div class="md:col-span-1">
            <h3 class="text-lg font-bold text-slate-800 mb-2">System Maintenance</h3>
            <p class="text-sm text-slate-500">Perform administrative tasks to ensure the system is running smoothly.</p>
        </div>

        <div class="md:col-span-2">
            <div class="card p-8 border-dashed border-2 border-indigo-100 bg-indigo-50/30">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-800">Database Backup</h4>
                        <p class="text-xs text-slate-500 mt-1">Download a full SQL export of the system data for safety.</p>
                    </div>
                    <button onclick="downloadBackup()" class="btn-premium btn-premium-primary whitespace-nowrap">
                        <i class="fas fa-download mr-2"></i> Generate Backup
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }


    // Profile Update
    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('profileName').value;
        try {
            const res = await API.updateProfile({ name });
            if (res.success) {
                showToast('Profile updated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to update profile', 'error');
        }
    });

    // Password Update
    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            showToast('New passwords do not match', 'error');
            return;
        }

        try {
            const res = await API.changePassword({ 
                current_password: currentPassword,
                new_password: newPassword
            });
            if (res.success) {
                showToast('Password changed successfully');
                e.target.reset();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to change password', 'error');
        }
    });

    async function downloadBackup() {
        try {
            showToast('Preparing backup, please wait...');
            // We use direct link for file download
            window.location.href = '../../backend/index.php/system/backup';
        } catch (err) {
            showToast('Failed to start download', 'error');
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
