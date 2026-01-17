<?php
session_start();
require_once 'conf/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initial Fetch
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY type, name");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

$income_cats = array_filter($categories, fn($c) => $c['type'] === 'income');
$expense_cats = array_filter($categories, fn($c) => $c['type'] === 'expense');

include 'includes/header.php';
?>

<div id="main-content" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header -->
    <div class="bg-blue-600 pb-10 pt-6 px-6 rounded-b-[2.5rem] shadow-xl relative z-0">
        <div class="flex items-center mb-4">
            <a href="profile.php" class="text-white/80 mr-4 text-xl"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h1 class="text-white font-bold text-xl">Kelola Kategori</h1>
                <p class="text-blue-100 text-xs">Tambah kategori anda sendiri</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="px-6 -mt-8 relative z-10 space-y-6">

        <!-- Expense -->
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <h3 class="font-bold text-gray-800 mb-3 flex justify-between items-center">
                Pengeluaran
                <span class="text-xs bg-red-100 text-red-500 px-2 py-1 rounded-lg">
                    <?php echo count($expense_cats); ?>
                </span>
            </h3>
            <div class="space-y-2">
                <?php foreach ($expense_cats as $cat): ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-xl group transition-colors">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                                <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </span>
                        </div>
                        <?php if ($cat['user_id']): ?>
                            <button onclick="deleteCategory(<?php echo $cat['id']; ?>)"
                                class="text-gray-300 hover:text-red-500 transition-colors px-2">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Income -->
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <h3 class="font-bold text-gray-800 mb-3 flex justify-between items-center">
                Pemasukan
                <span class="text-xs bg-green-100 text-green-500 px-2 py-1 rounded-lg">
                    <?php echo count($income_cats); ?>
                </span>
            </h3>
            <div class="space-y-2">
                <?php foreach ($income_cats as $cat): ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-xl group transition-colors">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center">
                                <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </span>
                        </div>
                        <?php if ($cat['user_id']): ?>
                            <button onclick="deleteCategory(<?php echo $cat['id']; ?>)"
                                class="text-gray-300 hover:text-red-500 transition-colors px-2">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- FAB Add -->
    <button onclick="document.getElementById('addCatModal').classList.remove('hidden')"
        class="fixed bottom-24 right-6 bg-blue-600 text-white w-14 h-14 rounded-full shadow-lg shadow-blue-600/40 flex items-center justify-center hover:bg-blue-700 active:scale-90 transition-all z-20">
        <i class="fas fa-plus text-xl"></i>
    </button>

    <!-- Add Modal -->
    <div id="addCatModal" class="fixed inset-0 z-50 hidden flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
            onclick="document.getElementById('addCatModal').classList.add('hidden')"></div>
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm relative z-10 shadow-2xl animate-bounce-in">
            <h3 class="font-bold text-lg mb-4 text-center">Tambah Kategori</h3>

            <form id="addCatForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Kategori</label>
                    <input type="text" name="name" required
                        class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-100 shadow-inner">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe</label>
                    <div class="flex bg-gray-100 p-1 rounded-xl">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="type" value="expense" checked class="peer hidden">
                            <div
                                class="py-2 text-center text-sm font-bold text-gray-500 peer-checked:bg-white peer-checked:text-red-500 peer-checked:shadow-sm rounded-lg transition-all">
                                Pengeluaran</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="type" value="income" class="peer hidden">
                            <div
                                class="py-2 text-center text-sm font-bold text-gray-500 peer-checked:bg-white peer-checked:text-green-500 peer-checked:shadow-sm rounded-lg transition-all">
                                Pemasukan</div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Icon (FontAwesome)</label>
                    <div class="grid grid-cols-5 gap-2 h-32 overflow-y-auto p-2 bg-gray-50 rounded-xl inner-shadow">
                        <?php
                        $icons = ['utensils', 'bus', 'shopping-cart', 'heartbeat', 'film', 'gamepad', 'book', 'tshirt', 'home', 'bolt', 'wifi', 'mobile-alt', 'gift', 'baby', 'dog', 'cat', 'car', 'bicycle', 'plane', 'briefcase', 'money-bill-wave', 'store', 'wallet', 'piggy-bank', 'chart-line'];
                        foreach ($icons as $icon): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="icon" value="<?php echo $icon; ?>" class="peer hidden">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-400 peer-checked:bg-blue-500 peer-checked:text-white hover:bg-gray-200 transition-all">
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <!-- Default hidden logic fallback -->
                    <input type="radio" name="icon" value="circle" class="hidden" checked>
                </div>

                <button type="submit"
                    class="w-full bg-blue-500 text-white font-bold py-3.5 rounded-xl shadow-lg active:scale-95 transition-transform">Simpan</button>
            </form>
        </div>
    </div>

</div>

<!-- Note: No bottom nav here, use back button logic -->
<script src="assets/js/app.js"></script>
</body>

</html>