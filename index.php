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
<script>
    const incomeCats = <?php echo json_encode($income_categories); ?>;
    const expenseCats = <?php echo json_encode($expense_categories); ?>;
</script>

<div id="main-content"
    class="pb-20 max-w-md mx-auto bg-gray-50 dark:bg-gray-900 min-h-screen transition-all duration-200">

    <!-- Top Header (Reduced Padding) -->
    <div class="bg-blue-600 pb-12 pt-6 px-5 rounded-b-[2.5rem] shadow-xl relative z-0 overflow-hidden">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>

        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <p class="text-blue-100 text-xs opacity-90">Hi <?php echo htmlspecialchars($username); ?></p>
                <h1 class="text-2xl font-bold mt-0.5 text-white" id="total-balance">
                    <?php echo formatRupiah($balance); ?>
                </h1>
            </div>
            <a href="profile.php"
                class="h-9 w-9 bg-white/20 rounded-full flex items-center justify-center overflow-hidden hover:bg-white/30 transition-colors">
                <?php if (isset($_SESSION['avatar']) && $_SESSION['avatar'] != 'default.png'): ?>
                    <img src="assets/uploads/avatars/<?php echo $_SESSION['avatar']; ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <i class="fas fa-user text-lg text-white"></i>
                <?php endif; ?>
            </a>
        </div>

        <div class="flex space-x-3 relative z-10">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl p-3 flex-1 shadow-md">
                <p class="text-[9px] text-gray-500 mb-0.5 uppercase font-semibold">Pemasukan</p>
                <p class="font-bold text-xs text-green-600" id="total-income">
                    <?php echo formatRupiah($monthly_summary['income']); ?>
                </p>
            </div>
            <div class="bg-white/95 backdrop-blur-sm rounded-xl p-3 flex-1 shadow-md">
                <p class="text-[9px] text-gray-500 mb-0.5 uppercase font-semibold">Pengeluaran</p>
                <p class="font-bold text-xs text-red-600" id="total-expense">
                    <?php echo formatRupiah($monthly_summary['expense']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Budgets Section (More Compact) -->
    <div class="px-5 mt-4">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-base font-bold text-gray-800 dark:text-white">Anggaran</h3>
            <?php if (count($dashboard_budgets) > 0): ?>
                <a href="budgets.php" class="text-xs text-blue-500">Semua</a>
            <?php endif; ?>
        </div>

        <?php if (count($dashboard_budgets) > 0): ?>
            <div class="flex space-x-3 overflow-x-auto pb-1 scrollbar-hide">
                <?php foreach ($dashboard_budgets as $index => $b):
                    $spent = $b['spent'] ?: 0;
                    $percent = $b['amount'] > 0 ? min(100, ($spent / $b['amount']) * 100) : 0;
                    $color = $percent > 90 ? '#EF4444' : ($percent > 50 ? '#F59E0B' : '#3B82F6');
                    ?>
                    <div
                        class="min-w-[100px] bg-white dark:bg-gray-800 rounded-xl p-3 flex flex-col items-center shadow-sm border border-gray-50">
                        <div class="relative h-14 w-14 mb-2">
                            <canvas id="budget-chart-<?php echo $index; ?>" class="budget-chart"
                                data-percent="<?php echo $percent; ?>" data-color="<?php echo $color; ?>"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                                <i class="fas fa-<?php echo $b['icon']; ?> text-sm" style="color: <?php echo $color; ?>"></i>
                            </div>
                        </div>
                        <p class="text-[10px] font-bold text-gray-700 truncate w-full text-center">
                            <?php echo htmlspecialchars($b['name']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 p-3 rounded-lg text-center">
                <p class="text-xs text-blue-800">Belum ada anggaran.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Transactions (Reduced Sizing) -->
    <div class="px-5 mt-4">
        <div class="flex justify-between items-end mb-2">
            <h3 class="text-base font-bold text-gray-800 dark:text-white">Transaksi</h3>
            <a href="transactions.php" class="text-xs text-blue-500">Semua</a>
        </div>
        <div class="space-y-2">
            <?php foreach ($recent_transactions as $t): ?>
                <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm">
                    <div class="flex items-center">
                        <div
                            class="h-9 w-9 rounded-lg flex items-center justify-center <?php echo $t['category_type'] == 'income' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'; ?>">
                            <i class="fas fa-<?php echo $t['icon']; ?> text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="font-bold text-gray-800 dark:text-white text-xs truncate max-w-[120px]">
                                <?php echo htmlspecialchars($t['description'] ?: $t['category_name']); ?>
                            </h4>
                            <p class="text-[10px] text-gray-400"><?php echo date('d M', strtotime($t['date'])); ?></p>
                        </div>
                    </div>
                    <div
                        class="font-bold text-xs <?php echo $t['category_type'] == 'income' ? 'text-green-500' : 'text-red-500'; ?>">
                        <?php echo ($t['category_type'] == 'income' ? '+' : '-') . number_format($t['amount'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js"></script>
</body>

</html>