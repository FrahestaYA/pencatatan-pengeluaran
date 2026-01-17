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
?>

<div id="main-content" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 transition-opacity duration-200">
    <div class="bg-blue-600 pb-16 pt-6 px-6 rounded-b-[2.5rem]">
        <div class="flex justify-between items-center text-white mb-6">
            <h1 class="font-bold text-xl">Laporan Keuangan</h1>
            <input type="month" value="<?php echo $month; ?>"
                onchange="window.location.href='?month='+this.value+'&type=<?php echo $type; ?>'"
                class="bg-blue-700 text-white border-0 rounded-lg text-sm px-2 py-1 outline-none">
        </div>

        <div class="flex bg-blue-800 p-1 rounded-xl mb-4">
            <a href="?month=<?php echo $month; ?>&type=expense"
                class="flex-1 text-center py-2 rounded-lg text-sm font-bold <?php echo $type === 'expense' ? 'bg-white text-blue-600' : 'text-blue-200'; ?>">Pengeluaran</a>
            <a href="?month=<?php echo $month; ?>&type=income"
                class="flex-1 text-center py-2 rounded-lg text-sm font-bold <?php echo $type === 'income' ? 'bg-white text-blue-600' : 'text-blue-200'; ?>">Pemasukan</a>
        </div>
    </div>

    <div class="px-6 -mt-12">
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 text-center">
            <p class="text-gray-500 text-sm mb-1">Total <?php echo $type === 'expense' ? 'Pengeluaran' : 'Pemasukan'; ?>
            </p>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo formatRupiah($total_amount); ?></h2>

            <div class="h-64 mt-4 relative">
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <h3 class="font-bold text-gray-800 mb-3">Rincian Kategori</h3>
        <div class="space-y-3">
            <?php foreach ($breakdown as $item):
                $percent = $total_amount > 0 ? ($item['total'] / $total_amount) * 100 : 0;
                ?>
                <div class="bg-white p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center flex-1">
                        <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                        </div>
                        <div class="ml-3 flex-1 px-2">
                            <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-bold text-gray-700"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span
                                    class="text-sm font-bold text-gray-700"><?php echo number_format($percent, 1); ?>%</span>
                            </div>
                            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm font-bold text-gray-600 ml-2">
                        <?php echo number_format($item['total'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script id="chart-data" type="application/json">
    <?php echo json_encode($chartData); ?>
    </script>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js"></script>
</body>

</html>