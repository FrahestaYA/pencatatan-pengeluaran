<?php
session_start();
require_once 'conf/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$month = date('Y-m');

// Fetch Expense Categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE (user_id IS NULL OR user_id = ?) AND type = 'expense'");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// Fetch Current Budgets
$stmt = $pdo->prepare("SELECT * FROM budgets WHERE user_id = ? AND month = ?");
$stmt->execute([$user_id, $month]);
$budgets_raw = $stmt->fetchAll();
$budgets = [];
foreach ($budgets_raw as $b) {
    $budgets[$b['category_id']] = $b['amount'];
}

// Fetch Actual Spending
$stmt = $pdo->prepare("
    SELECT category_id, SUM(amount) as total 
    FROM expenses 
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? 
    GROUP BY category_id
");
$stmt->execute([$user_id, $month]);
$spending_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [cat_id => total]

include 'includes/header.php';
?>

<div id="main-content" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 transition-opacity duration-200">
    <div class="bg-blue-600 pb-10 pt-6 px-6 rounded-b-[2.5rem] shadow-xl relative z-0">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-white font-bold text-xl">Atur Anggaran</h1>
                <p class="text-blue-100 text-xs">Ketuk ikon untuk edit</p>
            </div>
            <div class="bg-blue-500 p-2 rounded-lg text-white font-bold text-sm">
                <?php echo date('M Y'); ?>
            </div>
        </div>
    </div>

    <!-- Grid Layout for Icons -->
    <div class="px-6 -mt-8 relative z-10">
        <div class="grid grid-cols-3 gap-4">
            <?php foreach ($categories as $cat):
                $budget_amt = $budgets[$cat['id']] ?? 0;
                $spent_amt = $spending_data[$cat['id']] ?? 0;
                $percent = $budget_amt > 0 ? ($spent_amt / $budget_amt) * 100 : 0;

                // Color Logic
                $color_class = 'bg-blue-50 text-blue-500';
                if ($budget_amt > 0) {
                    if ($percent > 100)
                        $color_class = 'bg-red-50 text-red-500 border border-red-200';
                    elseif ($percent > 75)
                        $color_class = 'bg-orange-50 text-orange-500 border border-orange-200';
                    else
                        $color_class = 'bg-green-50 text-green-500 border border-green-200';
                }
                ?>
                <div onclick="openBudgetModal('<?php echo $cat['id']; ?>', '<?php echo htmlspecialchars($cat['name']); ?>', '<?php echo $budget_amt; ?>')"
                    class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl shadow-sm active:scale-95 transition-transform cursor-pointer h-32 relative overflow-hidden">

                    <!-- Progress Background (Vertical Fill) -->
                    <?php if ($budget_amt > 0): ?>
                        <div class="absolute bottom-0 left-0 w-full bg-current opacity-10 transition-all duration-500"
                            style="height: <?php echo min(100, $percent); ?>%; color: inherit;"></div>
                    <?php endif; ?>

                    <div
                        class="h-12 w-12 rounded-full mb-2 flex items-center justify-center text-xl <?php echo $color_class; ?> z-10">
                        <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                    </div>

                    <p class="text-xs font-bold text-gray-700 text-center z-10 leading-tight">

                        <?php echo htmlspecialchars($cat['name']); ?>
                    </p>
                    <?php if ($budget_amt > 0): ?>
                        <p class="text-[10px] text-gray-400 mt-1 z-10"><?php echo round($percent); ?>%</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>



<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js"></script>
</body>

</html>