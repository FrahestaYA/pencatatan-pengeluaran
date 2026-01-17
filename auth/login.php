<?php
session_start();
require_once '../conf/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Username atau Password salah!";
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="flex-grow flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-primary dark:text-indigo-400">Selamat Datang</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Silakan masuk untuk melanjutkan</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p>
                    <?php echo htmlspecialchars($success); ?>
                </p>
            </div>
        <?php endif; ?>

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
                    placeholder="Masukkan username anda">
            </div>

            <div>
                <label for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 dark:text-white"
                    placeholder="Masukkan password anda">
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Masuk
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
            Belum punya akun?
            <a href="register.php" class="font-medium text-primary hover:text-indigo-500 hover:underline">Daftar
                disini</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>