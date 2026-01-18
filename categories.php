<?php
session_start();
require_once 'conf/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initial Fetch with Usage Count
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM expenses WHERE category_id = c.id) as usage_count 
    FROM categories c 
    WHERE c.user_id IS NULL OR c.user_id = ? 
    ORDER BY c.type, c.name
");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

$income_cats = array_filter($categories, fn($c) => $c['type'] === 'income');
$expense_cats = array_filter($categories, fn($c) => $c['type'] === 'expense');

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div id="main-content"
    class="w-full md:pl-64 bg-gray-50 dark:bg-gray-900 min-h-screen pb-24 transition-opacity duration-200">
    <div
        class="bg-blue-600 dark:bg-dark-accent pb-10 pt-8 px-6 md:px-10 rounded-b-[2.5rem] shadow-lg transition-colors duration-300">
        <div class="max-w-5xl mx-auto flex justify-between items-center text-white mb-6">
            <h1 class="font-bold text-xl md:text-2xl">Kategori</h1>
            <button onclick="openManageCategoryModal()"
                class="bg-white/20 hover:bg-white/30 p-2 rounded-lg transition-colors">
                <i class="fas fa-plus text-white"></i>
            </button>
        </div>
    </div>

    <div class="px-6 md:px-10 -mt-8 max-w-5xl mx-auto space-y-6">
        <!-- Expense Categories -->
        <div>
            <h2 class="font-bold text-white dark:text-white mb-3 px-2">Pengeluaran</h2>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-3 md:gap-4">
                <?php foreach ($categories as $c):
                    if ($c['type'] !== 'expense')
                        continue;
                    ?>
                    <div
                        class="bg-white dark:bg-gray-800 p-3 md:p-4 rounded-2xl shadow-sm flex flex-col items-center justify-center relative group hover:shadow-md transition-all border border-transparent hover:border-blue-100 dark:hover:border-gray-700">
                        <!-- Edit/Delete Actions (Only for user categories) -->
                        <?php if ($c['user_id']): ?>
                            <div
                                class="absolute top-1 right-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                                <button
                                    onclick="openEditCategory(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['name']); ?>', 'expense', '<?php echo $c['icon']; ?>')"
                                    class="bg-blue-100 text-blue-600 p-1 rounded-md text-[10px]"><i
                                        class="fas fa-edit"></i></button>
                            </div>
                        <?php endif; ?>

                        <div
                            class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center text-red-500 dark:text-red-400 mb-2">
                            <i class="fas fa-<?php echo $c['icon']; ?> text-lg md:text-xl"></i>
                        </div>
                        <span
                            class="font-bold text-gray-800 dark:text-gray-200 text-xs md:text-sm text-center truncate w-full px-1"><?php echo htmlspecialchars($c['name']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Income Categories -->
        <div>
            <h2 class="font-bold text-gray-700 dark:text-gray-300 mb-3 px-2">Pemasukan</h2>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-3 md:gap-4">
                <?php foreach ($categories as $c):
                    if ($c['type'] !== 'income')
                        continue;
                    ?>
                    <div
                        class="bg-white dark:bg-gray-800 p-3 md:p-4 rounded-2xl shadow-sm flex flex-col items-center justify-center relative group hover:shadow-md transition-all">
                        <?php if ($c['user_id']): ?>
                            <div
                                class="absolute top-1 right-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                                <button
                                    onclick="openEditCategory(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['name']); ?>', 'income', '<?php echo $c['icon']; ?>')"
                                    class="bg-blue-100 text-blue-600 p-1 rounded-md text-[10px]"><i
                                        class="fas fa-edit"></i></button>
                            </div>
                        <?php endif; ?>
                        <div
                            class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-green-100 dark:bg-green-500/10 flex items-center justify-center text-green-500 dark:text-green-400 mb-2">
                            <i class="fas fa-<?php echo $c['icon']; ?> text-lg md:text-xl mb-1"></i>
                        </div>
                        <span
                            class="font-bold text-gray-800 dark:text-gray-200 text-xs md:text-sm text-center truncate w-full px-1"><?php echo htmlspecialchars($c['name']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button (Mobile Only) -->
<div class="fixed bottom-24 right-6 md:hidden z-30">
    <button onclick="openManageCategoryModal()"
        class="bg-blue-600 dark:bg-dark-accent hover:bg-blue-700 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-transform active:scale-90">
        <i class="fas fa-plus text-xl"></i>
    </button>
</div>

<!-- Manage Modal (Shared for Add/Edit) -->
<div id="manageCatModal" class="fixed inset-0 z-50 hidden flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeManageModal()"></div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 w-full max-w-sm relative z-10 shadow-2xl animate-bounce-in">
        <h3 id="modalTitle" class="font-bold text-lg mb-4 text-center text-gray-800 dark:text-white">Tambah Kategori
        </h3>

        <form id="manageCatForm" class="space-y-4">
            <input type="hidden" name="action" id="catAction" value="add">
            <input type="hidden" name="id" id="catId" value="">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Kategori</label>
                <input type="text" name="name" id="catName" required
                    class="w-full bg-gray-50 dark:bg-gray-700 dark:text-white border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-100 shadow-inner">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe</label>
                <div class="flex bg-gray-100 dark:bg-gray-700 p-1 rounded-xl">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="expense" id="typeExpense" checked class="peer hidden">
                        <div
                            class="py-2 text-center text-sm font-bold text-gray-500 dark:text-gray-300 peer-checked:bg-white dark:peer-checked:bg-gray-600 peer-checked:text-red-500 peer-checked:shadow-sm rounded-lg transition-all">
                            Pengeluaran</div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="income" id="typeIncome" class="peer hidden">
                        <div
                            class="py-2 text-center text-sm font-bold text-gray-500 dark:text-gray-300 peer-checked:bg-white dark:peer-checked:bg-gray-600 peer-checked:text-green-500 peer-checked:shadow-sm rounded-lg transition-all">
                            Pemasukan</div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Icon (FontAwesome)</label>
                <div
                    class="grid grid-cols-5 gap-2 h-32 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-700 rounded-xl inner-shadow">
                    <?php
                    $icons = ['utensils', 'bus', 'shopping-cart', 'heartbeat', 'film', 'gamepad', 'book', 'tshirt', 'home', 'bolt', 'wifi', 'mobile-alt', 'gift', 'baby', 'dog', 'cat', 'car', 'bicycle', 'plane', 'briefcase', 'money-bill-wave', 'store', 'wallet', 'piggy-bank', 'chart-line'];
                    foreach ($icons as $icon): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="<?php echo $icon; ?>" class="peer hidden">
                            <div
                                class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-400 peer-checked:bg-blue-500 peer-checked:text-white hover:bg-gray-200 dark:hover:bg-gray-600 transition-all icon-option">
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <!-- Default hidden logic fallback -->
                <input type="radio" name="icon" value="circle" class="hidden" checked>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 dark:bg-dark-accent text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-dark-accent/30 active:scale-95 transition-transform">Simpan</button>
        </form>
    </div>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>