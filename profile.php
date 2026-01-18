<?php
session_start();
require_once 'conf/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$avatar = $user['avatar'] ?? 'default.png';
$full_name = $user['full_name'] ?? '';
$email = $user['email'] ?? '';
$bio = $user['bio'] ?? '';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div id="main-content"
    class="w-full md:pl-64 bg-gray-50 dark:bg-gray-900 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header -->
    <div
        class="bg-blue-600 dark:bg-dark-accent pb-20 pt-8 px-6 md:px-10 rounded-b-[2.5rem] shadow-xl relative z-0 overflow-hidden transition-colors duration-300">
        <!-- Decor Circle -->
        <div
            class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-5xl mx-auto flex justify-between items-center mb-6 relative z-10">
            <div>
                <h1 class="text-white font-bold text-2xl tracking-tight">Profil Saya</h1>
                <p class="text-blue-100 dark:text-white/80 text-xs opacity-90">Kelola akun anda</p>
            </div>
            <a href="auth/logout.php" class="bg-white/20 p-2 rounded-xl text-white hover:bg-white/30 transition-colors">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 md:px-10 -mt-16 relative z-10 max-w-5xl mx-auto md:grid md:grid-cols-2 md:gap-8">

        <!-- Left Col: Profile Form -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-6 flex flex-col items-center mb-6 md:mb-0">
            <!-- Avatar Upload -->
            <div class="relative mb-6 group" onclick="document.getElementById('avatarInput').click()">
                <div
                    class="w-28 h-28 rounded-full p-1 bg-white dark:bg-gray-700 shadow-lg cursor-pointer transition-colors">
                    <img id="avatarPreview" src="assets/uploads/avatars/<?php echo htmlspecialchars($avatar); ?>"
                        onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random'"
                        class="w-full h-full rounded-full object-cover">
                </div>
                <!-- Edit Badge -->
                <div
                    class="absolute bottom-1 right-1 bg-blue-500 dark:bg-dark-accent text-white w-8 h-8 rounded-full flex items-center justify-center shadow-md border-2 border-white dark:border-gray-800 cursor-pointer active:scale-90 transition-transform">
                    <i class="fas fa-camera text-xs"></i>
                </div>
            </div>

            <!-- Edit Form -->
            <form id="profileForm" class="w-full space-y-5">
                <input type="file" name="avatar" id="avatarInput" class="hidden" accept="image/*">

                <div>
                    <label
                        class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 pl-1">Informasi
                        Dasar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>"
                            class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl py-3.5 pl-11 pr-4 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 transition-all placeholder-gray-400"
                            placeholder="Nama Lengkap">
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                        class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl py-3.5 pl-11 pr-4 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 transition-all placeholder-gray-400"
                        placeholder="Alamat Email">
                </div>

                <div class="relative">
                    <div class="absolute top-4 left-0 pl-4 flex pointer-events-none">
                        <i class="fas fa-quote-right text-gray-400 text-xs"></i>
                    </div>
                    <textarea name="bio" rows="3"
                        class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl py-3.5 pl-11 pr-4 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 transition-all placeholder-gray-400 resize-none"
                        placeholder="Tulis sedikit tentang anda..."><?php echo htmlspecialchars($bio); ?></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-dark-accent dark:to-orange-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 dark:shadow-dark-accent/30 active:scale-[0.98] transition-all">
                        Simpan Perubahan
                    </button>
                    <div id="save-msg" class="text-center text-xs mt-3 h-4 font-medium transition-all opacity-0"></div>
                </div>
            </form>
        </div>

        <!-- Right Col: Settings -->
        <div>
            <h3 class="font-bold text-gray-800 dark:text-white px-2 mb-3 text-sm">Pengaturan</h3>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden mb-6">
                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 transition-colors">
                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                        <div
                            class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mr-3.5">
                            <i class="fas fa-moon"></i>
                        </div>
                        <div class="text-left">
                            <span class="block font-semibold text-sm">Dark Mode</span>
                            <span class="block text-[10px] text-gray-400">Ganti tema aplikasi</span>
                        </div>
                    </div>
                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full relative transition-colors">
                        <div
                            class="w-5 h-5 bg-white rounded-full shadow-sm absolute top-0.5 left-0.5 transform transition-transform duration-200 dark:translate-x-5">
                        </div>
                    </div>
                </button>

                <div class="h-px bg-gray-100 dark:bg-gray-700 mx-4"></div>

                <!-- Categories Link -->
                <a href="categories.php"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 transition-colors">
                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                        <div
                            class="w-9 h-9 rounded-full bg-blue-50 dark:bg-dark-accent/10 text-blue-500 dark:text-dark-accent flex items-center justify-center mr-3.5">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="text-left">
                            <span class="block font-semibold text-sm">Kategori</span>
                            <span class="block text-[10px] text-gray-400">Atur kategori kustom</span>
                        </div>
                    </div>
                    <div class="text-gray-400">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
                </a>
            </div>

            <div class="p-4 text-center">
                <p class="text-[10px] text-gray-400">Versi Aplikasi 1.0.0</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>