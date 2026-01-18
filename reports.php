<?php
session_start();
require_once 'conf/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$month = $_GET['month'] ?? date('Y-m');
$type = $_GET['type'] ?? 'expense';

// Get Category Breakdown
$stmt = $pdo->prepare("
    SELECT c.name, SUM(e.amount) as total, c.icon 
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ? AND c.type = ? AND DATE_FORMAT(e.date, '%Y-%m') = ?
    GROUP BY c.id
    ORDER BY total DESC
");
$stmt->execute([$user_id, $type, $month]);
$breakdown = $stmt->fetchAll();

$total_amount = array_sum(array_column($breakdown, 'total'));

// Prepare Chart Data for JS
$chartData = [
    'labels' => array_column($breakdown, 'name'),
    'data' => array_column($breakdown, 'total')
];

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

        <div class="max-w-5xl mx-auto relative z-10">
            <div class="flex justify-between items-center text-white mb-6">
                <h1 class="font-bold text-xl md:text-2xl">Laporan Keuangan</h1>
                <input type="month" value="<?php echo $month; ?>"
                    onchange="window.location.href='?month='+this.value+'&type=<?php echo $type; ?>'"
                    class="bg-white/20 hover:bg-white/30 text-white border-0 rounded-lg text-sm px-3 py-1 outline-none font-bold cursor-pointer transition-colors backdrop-blur-sm">
            </div>

            <div
                class="flex bg-blue-800 dark:bg-black/20 p-1 rounded-xl mb-4 backdrop-blur-sm max-w-sm mx-auto md:mx-0">
                <a href="?month=<?php echo $month; ?>&type=expense"
                    class="flex-1 text-center py-2 rounded-lg text-sm font-bold transition-all <?php echo $type === 'expense' ? 'bg-white text-blue-600 dark:text-dark-accent shadow-sm' : 'text-blue-200 dark:text-white/60 hover:text-white'; ?>">Pengeluaran</a>
                <a href="?month=<?php echo $month; ?>&type=income"
                    class="flex-1 text-center py-2 rounded-lg text-sm font-bold transition-all <?php echo $type === 'income' ? 'bg-white text-blue-600 dark:text-dark-accent shadow-sm' : 'text-blue-200 dark:text-white/60 hover:text-white'; ?>">Pemasukan</a>
            </div>
        </div>
    </div>

    <div class="px-6 md:px-10 -mt-8 max-w-5xl mx-auto md:grid md:grid-cols-3 md:gap-8 relative z-10">
        <!-- Chart Section (Col 2) -->
        <div
            class="md:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 md:mb-0 text-center border border-transparent dark:border-gray-700">
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Total
                <?php echo $type === 'expense' ? 'Pengeluaran' : 'Pemasukan'; ?>
            </p>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white"><?php echo formatRupiah($total_amount); ?></h2>

            <div class="h-64 md:h-80 mt-6 relative">
                <?php if ($total_amount > 0): ?>
                    <canvas id="reportChart"></canvas>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-600">
                        <i class="fas fa-chart-pie text-5xl mb-3"></i>
                        <p class="text-sm">Tidak ada data untuk ditampilkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breakdown Section (Col 1) -->
        <div class="md:col-span-1">
            <h3
                class="font-bold text-gray-800 dark:text-white mb-3 text-lg pl-2 border-l-4 border-blue-500 dark:border-dark-accent  ml-1">
                Rincian Kategori</h3>
            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                <?php foreach ($breakdown as $item):
                    $percent = $total_amount > 0 ? ($item['total'] / $total_amount) * 100 : 0;
                    ?>
                    <div
                        class="bg-white dark:bg-gray-800 p-4 rounded-xl flex items-center justify-between shadow-sm border border-transparent hover:border-blue-100 dark:hover:border-gray-700 transition-all">
                        <div class="flex items-center flex-1">
                            <div
                                class="h-10 w-10 rounded-full bg-blue-50 text-blue-500 dark:bg-dark-accent/10 dark:text-dark-accent flex items-center justify-center">
                                <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                            </div>
                            <div class="ml-3 flex-1 px-2">
                                <div class="flex justify-between mb-1">
                                    <span
                                        class="text-sm font-bold text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span
                                        class="text-xs font-bold text-gray-500 dark:text-gray-400"><?php echo number_format($percent, 1); ?>%</span>
                                </div>
                                <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 dark:bg-dark-accent rounded-full"
                                        style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm font-bold text-gray-600 dark:text-gray-300 ml-2">
                            <?php echo formatRupiah($item['total']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($breakdown) == 0): ?>
                    <p class="text-gray-400 text-center py-4 text-sm">Belum ada transaksi.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script id="chart-data" type="application/json">
    <?php echo json_encode($chartData); ?>
</script>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>