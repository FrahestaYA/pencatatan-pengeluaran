<?php
session_start();
require_once 'conf/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];


// Fetch Categories (Grouped by Type)
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY type, name");
$stmt->execute([$user_id]);
$all_categories = $stmt->fetchAll();

$income_categories = [];
$expense_categories = [];
foreach ($all_categories as $cat) {
    if ($cat['type'] === 'income')
        $income_categories[] = $cat;
    else
        $expense_categories[] = $cat;
}

// Fetch Data
$balance = getBalance($pdo, $user_id);
$monthly_summary = getMonthlySummary($pdo, $user_id);
$recent_transactions = getRecentTransactions($pdo, $user_id);

// Fetch Top 3 Budgets for Dashboard
$stmt = $pdo->prepare("
    SELECT b.*, c.name, c.icon, 
    (SELECT SUM(amount) FROM expenses WHERE category_id = b.category_id AND DATE_FORMAT(date, '%Y-%m') = b.month) as spent
    FROM budgets b
    JOIN categories c ON b.category_id = c.id
    WHERE b.user_id = ? AND b.month = ?
    ORDER BY b.amount DESC
    LIMIT 3
");
$stmt->execute([$user_id, date('Y-m')]);
$dashboard_budgets = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- Data for JS -->
<!-- Note: Categories are now fetched via AJAX in app.js -->

<?php include 'includes/sidebar.php'; ?>

<div id="main-content"
    class="w-full md:pl-64 bg-gray-50 dark:bg-gray-900 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header Section -->
    <div
        class="bg-blue-600 dark:bg-dark-accent pb-20 pt-8 px-6 md:px-10 rounded-b-[2.5rem] shadow-lg transition-colors duration-300 relative overflow-hidden">
        <!-- Background Decor (Optional for elegance) -->
        <div
            class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-5xl mx-auto relative z-10">
            <!-- Top Section: Name & Balance -->
            <div class="flex flex-row justify-between items-end mb-6">
                <div class="flex-1 min-w-0 mr-4">
                    <p class="text-blue-100 dark:text-white/90 text-xs md:text-sm font-medium opacity-90 mb-1">Selamat
                        Datang,</p>
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white truncate">
                        <?php echo htmlspecialchars($full_name); ?>
                    </h1>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-blue-100 dark:text-white/80 text-[10px] md:text-xs mb-1">Total Saldo</p>
                    <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white tracking-tight">
                        <?php echo formatRupiah($balance); ?>
                    </h2>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3 md:gap-5 mt-4">
                <!-- Income -->
                <div
                    class="bg-white/10 backdrop-blur-xl p-3 md:p-5 rounded-2xl border border-white/10 flex flex-col md:flex-row md:items-center relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-2 opacity-10 md:opacity-100 md:static md:p-0">
                        <div
                            class="h-8 w-8 md:h-12 md:w-12 rounded-full bg-green-400/20 flex items-center justify-center text-green-300 md:mr-4">
                            <i class="fas fa-arrow-down transform rotate-45 text-xs md:text-lg"></i>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-blue-100 dark:text-white/70 text-[10px] md:text-sm font-medium">Pemasukan</p>
                        <p class="text-white font-bold text-sm md:text-xl mt-0.5 md:mt-0 truncate">
                            <?php echo formatRupiah($monthly_summary['income']); ?>
                        </p>
                    </div>
                </div>
                <!-- Expense -->
                <div
                    class="bg-white/10 backdrop-blur-xl p-3 md:p-5 rounded-2xl border border-white/10 flex flex-col md:flex-row md:items-center relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-2 opacity-10 md:opacity-100 md:static md:p-0">
                        <div
                            class="h-8 w-8 md:h-12 md:w-12 rounded-full bg-red-400/20 flex items-center justify-center text-red-300 md:mr-4">
                            <i class="fas fa-arrow-up transform rotate-45 text-xs md:text-lg"></i>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-blue-100 dark:text-white/70 text-[10px] md:text-sm font-medium">Pengeluaran</p>
                        <p class="text-white font-bold text-sm md:text-xl mt-0.5 md:mt-0 truncate">
                            <?php echo formatRupiah($monthly_summary['expense']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="px-6 md:px-10 -mt-16 md:-mt-12 max-w-5xl mx-auto">
        <div class="grid md:grid-cols-3 gap-6">

            <!-- Left Column: Budgets (Desktop: Col 1) -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-6 relative overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 dark:text-white text-lg">Anggaran</h3>
                        <a href="budgets.php"
                            class="text-xs text-blue-500 dark:text-dark-accent font-bold hover:underline">Lihat
                            Semua</a>
                    </div>

                    <?php if (count($dashboard_budgets) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($dashboard_budgets as $b):
                                $spent = $b['spent'] ?: 0;
                                $percent = $b['amount'] > 0 ? ($spent / $b['amount']) * 100 : 0;
                                $color = $percent >= 100 ? '#EF4444' : ($percent >= 75 ? '#F59E0B' : '#3B82F6');
                                if ($percent < 75 && isset($GLOBALS['dark_mode']))
                                    $color = '#F97316'; // Fallback logic manual, better handled by class
                                ?>
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300 mr-3 shrink-0">
                                        <i class="fas fa-<?php echo $b['icon']; ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between mb-1">
                                            <span
                                                class="text-sm font-bold text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($b['name']); ?></span>
                                            <span class="text-xs font-bold"
                                                style="color: <?php echo $color; ?>"><?php echo number_format($percent, 0); ?>%</span>
                                        </div>
                                        <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                style="width: <?php echo min($percent, 100); ?>%; background-color: <?php echo $color; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6">
                            <i class="fas fa-wallet text-gray-300 text-3xl mb-2"></i>
                            <p class="text-sm text-gray-400">Belum ada anggaran.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Transactions (Desktop: Col 2 & 3) -->
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-6 min-h-[300px]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-800 dark:text-white text-lg">Transaksi Terakhir</h3>
                        <a href="transactions.php"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Lihat
                            Semua</a>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($recent_transactions as $t):
                            $desc = htmlspecialchars($t['description'] ?? '');
                            ?>
                            <div onclick="openEditTransaction('<?php echo $t['id']; ?>', '<?php echo $t['amount']; ?>', '<?php echo $t['date']; ?>', '<?php echo $t['category_id']; ?>', '<?php echo $desc; ?>', '<?php echo $t['category_type']; ?>')"
                                class="flex items-center justify-between p-3 -mx-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-xl cursor-pointer transition-colors group">
                                <div class="flex items-center">
                                    <div
                                        class="h-12 w-12 rounded-2xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105 <?php echo $t['category_type'] == 'income' ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400'; ?>">
                                        <i class="fas fa-<?php echo $t['icon']; ?> text-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h4
                                            class="font-bold text-gray-800 dark:text-white text-sm truncate max-w-[150px] md:max-w-[250px]">
                                            <?php echo htmlspecialchars($t['description'] ?: $t['category_name']); ?>
                                        </h4>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            <?php echo htmlspecialchars($t['category_name']); ?> •
                                            <?php echo date('d M Y', strtotime($t['date'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="font-bold text-sm <?php echo $t['category_type'] == 'income' ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'; ?>">
                                    <?php echo ($t['category_type'] == 'income' ? '+' : '-') . number_format($t['amount'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($recent_transactions) == 0): ?>
                            <div class="text-center py-10">
                                <p class="text-gray-400">Belum ada data.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>