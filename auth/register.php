<?php
session_start();
require_once '../conf/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="flex-grow flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-primary dark:text-indigo-400">Buat Akun</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Mulai catat keuanganmu hari ini</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p>
                    <?php echo htmlspecialchars($error); ?>
                </p>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="username"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 dark:text-white"
                    placeholder="Pilih username unik">
            </div>

            <div>
                <label for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 dark:text-white"
                    placeholder="Minimal 6 karakter">
            </div>

            <div>
                <label for="confirm_password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 dark:text-white"
                    placeholder="Ulangi password">
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
            Sudah punya akun?
            <a href="login.php" class="font-medium text-primary hover:text-indigo-500 hover:underline">Masuk disini</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>