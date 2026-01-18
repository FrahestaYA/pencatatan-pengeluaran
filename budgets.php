<?php
session_start();
require_once 'conf/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch Current Budgets
$stmt = $pdo->prepare("
    SELECT b.*, c.name, c.icon 
    FROM budgets b 
    JOIN categories c ON b.category_id = c.id 
    WHERE b.user_id = ? AND b.month = ?
");
$stmt->execute([$user_id, $month]);
$budgets_raw = $stmt->fetchAll();

// Fetch Actual Spending
$stmt = $pdo->prepare("
    SELECT category_id, SUM(amount) as total 
    FROM expenses 
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? 
    GROUP BY category_id
");
$stmt->execute([$user_id, $month]);
$spending_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [cat_id => total]

$budgets = [];
foreach ($budgets_raw as $b) {
    $b['spent'] = $spending_data[$b['category_id']] ?? 0;
    $budgets[] = $b;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div id="main-content"
    class="w-full md:pl-64 bg-gray-50 dark:bg-gray-900 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header -->
    <div
        class="bg-blue-600 dark:bg-dark-accent pb-20 pt-8 px-6 md:px-10 rounded-b-[2.5rem] shadow-lg transition-colors duration-300 relative overflow-hidden">
        <!-- Optional Decor -->
        <div
            class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-5xl mx-auto flex justify-between items-center text-white mb-6 relative z-10">
            <h1 class="font-bold text-xl md:text-2xl">Anggaran Bulanan</h1>
            <input type="month" value="<?php echo $month; ?>" onchange="window.location.href='?month='+this.value"
                class="bg-white/20 hover:bg-white/30 text-white border-0 rounded-lg text-sm px-3 py-1 outline-none font-bold cursor-pointer transition-colors backdrop-blur-sm">
        </div>
    </div>

    <!-- Budgets List -->
    <div
        class="px-6 md:px-10 -mt-16 md:-mt-12 max-w-5xl mx-auto space-y-4 md:grid md:grid-cols-2 md:space-y-0 md:gap-6 relative z-10">
        <?php foreach ($budgets as $b):
            $percent = ($b['spent'] / $b['amount']) * 100;
            $color = $percent >= 100 ? 'text-red-500 dark:text-red-400' : ($percent >= 75 ? 'text-yellow-500' : 'text-blue-500 dark:text-dark-accent');
            $bg_color = $percent >= 100 ? 'bg-red-500 dark:bg-red-400' : ($percent >= 75 ? 'bg-yellow-500' : 'bg-blue-500 dark:bg-dark-accent');
            $icon_bg = $percent >= 100 ? 'bg-red-50 dark:bg-red-500/10' : ($percent >= 75 ? 'bg-yellow-50 dark:bg-yellow-500/10' : 'bg-blue-50 dark:bg-dark-accent/10');
            ?>
            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm flex flex-col justify-between hover:shadow-md transition-all border border-transparent hover:border-blue-50 dark:hover:border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 rounded-full <?php echo $icon_bg; ?> <?php echo $color; ?> flex items-center justify-center mr-4">
                            <i class="fas fa-<?php echo $b['icon']; ?> text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-lg">
                                <?php echo htmlspecialchars($b['name']); ?></h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Terpakai: <span
                                    class="font-bold <?php echo $color; ?>"><?php echo formatRupiah($b['spent']); ?></span>
                            </p>
                        </div>
                    </div>
                    <button
                        onclick="openBudgetModal('<?php echo $b['category_id']; ?>', '<?php echo htmlspecialchars($b['name']); ?>', '<?php echo $b['amount']; ?>')"
                        class="text-gray-300 hover:text-blue-500 transition-colors">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>

                <div>
                    <div class="flex justify-between mb-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Limit:
                            <?php echo formatRupiah($b['amount']); ?></span>
                        <span class="font-bold <?php echo $color; ?>"><?php echo number_format($percent, 0); ?>%</span>
                    </div>
                    <div class="h-3 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 <?php echo $bg_color; ?>"
                            style="width: <?php echo min($percent, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($budgets) == 0): ?>
        <div class="text-center py-10">
            <div class="bg-gray-100 dark:bg-gray-800 h-20 w-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-pie text-gray-300 text-3xl"></i>
            </div>
            <p class="text-gray-500 dark:text-gray-400">Belum ada anggaran bulan ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>