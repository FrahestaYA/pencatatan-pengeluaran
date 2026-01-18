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

// Fetch Transactions
$stmt = $pdo->prepare("
    SELECT e.*, c.name as category_name, c.type as category_type, c.icon 
    FROM expenses e 
    JOIN categories c ON e.category_id = c.id 
    WHERE e.user_id = ? AND DATE_FORMAT(e.date, '%Y-%m') = ?
    ORDER BY e.date DESC, e.id DESC 
");
$stmt->execute([$user_id, $month]);
$transactions = $stmt->fetchAll();

// Calculate Totals
$total_income = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    if ($t['category_type'] === 'income') $total_income += $t['amount'];
    else $total_expense += $t['amount'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div id="main-content" class="w-full md:pl-64 bg-gray-50 dark:bg-gray-900 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header -->
    <div class="bg-blue-600 dark:bg-dark-accent pb-20 pt-8 px-6 md:px-10 rounded-b-[2.5rem] shadow-lg transition-colors duration-300">
        <div class="max-w-5xl mx-auto flex justify-between items-center text-white mb-6">
            <h1 class="font-bold text-xl md:text-2xl">Riwayat Transaksi</h1>
            <div class="flex items-center space-x-2">
                <button onclick="changeMonth(-1)" class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full hover:bg-white/30 transition-colors">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>
                <input type="month" id="monthInput" value="<?php echo $month; ?>"
                    onchange="window.location.href='?month='+this.value"
                    class="bg-transparent text-white border-none font-bold text-sm focus:ring-0 cursor-pointer text-center w-24 p-0">
                <button onclick="changeMonth(1)" class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full hover:bg-white/30 transition-colors">
                    <i class="fas fa-chevron-right text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="max-w-5xl mx-auto grid grid-cols-3 gap-3 md:gap-6 mt-6">
            <!-- Income -->
            <div class="bg-white/10 backdrop-blur-md p-3 md:p-4 rounded-2xl border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-blue-100 text-[10px] md:text-xs">Pemasukan</span>
                <span class="text-white font-bold text-sm md:text-lg"><?php echo formatRupiah($total_income); ?></span>
            </div>
            <!-- Expense -->
            <div class="bg-white/10 backdrop-blur-md p-3 md:p-4 rounded-2xl border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-blue-100 text-[10px] md:text-xs">Pengeluaran</span>
                <span class="text-white font-bold text-sm md:text-lg"><?php echo formatRupiah($total_expense); ?></span>
            </div>
            <!-- Balance -->
            <div class="bg-white/10 backdrop-blur-md p-3 md:p-4 rounded-2xl border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-blue-100 text-[10px] md:text-xs">Selisih</span>
                <span class="text-white font-bold text-sm md:text-lg"><?php echo formatRupiah($total_income - $total_expense); ?></span>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="px-6 md:px-10 -mt-8 max-w-5xl mx-auto space-y-4">
        <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $t): 
                $is_income = $t['category_type'] === 'income';
                $color_class = $is_income ? 'text-green-500' : 'text-red-500';
                $bg_class = $is_income ? 'bg-green-100 dark:bg-green-500/10' : 'bg-red-100 dark:bg-red-500/10';
                $icon_color = $is_income ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
            ?>
            <div onclick="openEditTransaction(<?php echo $t['id']; ?>, <?php echo $t['amount']; ?>, '<?php echo $t['date']; ?>', <?php echo $t['category_id']; ?>, '<?php echo htmlspecialchars($t['description']); ?>', '<?php echo $t['category_type']; ?>')"
                 class="bg-white dark:bg-gray-800 p-4 md:p-5 rounded-2xl shadow-sm hover:shadow-md transition-all cursor-pointer flex items-center justify-between group border border-transparent hover:border-blue-50 dark:hover:border-gray-700">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-full <?php echo $bg_class; ?> flex items-center justify-center <?php echo $icon_color; ?> mr-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-<?php echo $t['icon']; ?> text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white text-sm md:text-base"><?php echo htmlspecialchars($t['category_name']); ?></h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            <?php echo date('d M Y', strtotime($t['date'])); ?> 
                            <?php if($t['description']) echo '• ' . htmlspecialchars($t['description']); ?>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="font-bold text-sm md:text-base <?php echo $color_class; ?>">
                        <?php echo $is_income ? '+' : '-'; ?> <?php echo formatRupiah($t['amount']); ?>
                    </span>
                    <div class="text-[10px] text-gray-300 dark:text-gray-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        Klik untuk edit
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10">
                <div class="bg-gray-100 dark:bg-gray-800 h-20 w-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-invoice text-gray-300 text-3xl"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400">Belum ada transaksi bulan ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function changeMonth(offset) {
        const input = document.getElementById('monthInput');
        const current = new Date(input.value + '-01');
        current.setMonth(current.getMonth() + offset);
        const y = current.getFullYear();
        const m = String(current.getMonth() + 1).padStart(2, '0');
        window.location.href = '?month=' + y + '-' + m;
    }
</script>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>