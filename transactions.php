<?php
session_start();
require_once 'conf/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Simple Filter
$month = $_GET['month'] ?? date('Y-m');

$stmt = $pdo->prepare("
    SELECT e.*, c.name as category_name, c.type as category_type, c.icon 
    FROM expenses e 
    JOIN categories c ON e.category_id = c.id 
    WHERE e.user_id = ? AND DATE_FORMAT(e.date, '%Y-%m') = ?
    ORDER BY e.date DESC, e.id DESC 
");
$stmt->execute([$user_id, $month]);
$transactions = $stmt->fetchAll();

// Calculate totals for this view
$total_income = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    if ($t['category_type'] === 'income')
        $total_income += $t['amount'];
    else
        $total_expense += $t['amount'];
}

include 'includes/header.php';
?>

<div id="main-content" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 transition-opacity duration-200">
    <!-- Header -->
    <div class="bg-white p-4 items-center flex justify-between sticky top-0 z-10 shadow-sm">
        <a href="index.php" class="text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-bold text-lg">Transaksi</h1>
        <div class="w-6"></div>
    </div>

    <!-- Month Filter -->
    <div class="p-4 bg-white mb-2">
        <form id="filterForm" class="flex items-center justify-between bg-gray-100 rounded-xl p-2">
            <button type="button" class="p-2 text-gray-500" onclick="changeMonth(-1)"><i
                    class="fas fa-chevron-left"></i></button>
            <input type="month" name="month" id="monthInput" value="<?php echo $month; ?>"
                class="bg-transparent font-bold text-gray-700 outline-none text-center" onchange="this.form.submit()">
            <button type="button" class="p-2 text-gray-500" onclick="changeMonth(1)"><i
                    class="fas fa-chevron-right"></i></button>
        </form>
    </div>

    <!-- Summary -->
    <div class="flex justify-between px-6 py-4 bg-white mb-4">
        <div class="text-center">
            <p class="text-xs text-gray-500">Pemasukan</p>
            <p class="text-green-500 font-bold"><?php echo formatRupiah($total_income); ?></p>
        </div>
        <div class="text-center">
            <p class="text-xs text-gray-500">Pengeluaran</p>
            <p class="text-red-500 font-bold"><?php echo formatRupiah($total_expense); ?></p>
        </div>
        <div class="text-center">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-gray-800 font-bold"><?php echo formatRupiah($total_income - $total_expense); ?></p>
        </div>
    </div>

    <!-- List -->
    <div class="px-4 space-y-3">
        <?php foreach ($transactions as $t): ?>
            <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <div
                        class="h-10 w-10 rounded-full flex items-center justify-center 
                    <?php echo $t['category_type'] == 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                        <i class="fas fa-<?php echo $t['icon']; ?>"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($t['category_name']); ?>
                        </h4>
                        <p class="text-[10px] text-gray-400"><?php echo date('d M Y', strtotime($t['date'])); ?> •
                            <?php echo htmlspecialchars($t['description']); ?>
                        </p>
                    </div>
                </div>
                <div
                    class="font-bold text-sm <?php echo $t['category_type'] == 'income' ? 'text-green-500' : 'text-gray-800'; ?>">
                    <?php echo $t['category_type'] == 'income' ? '+' : '-'; ?>
                    <?php echo number_format($t['amount'], 0, ',', '.'); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        window.changeMonth = function (offset) {
            const input = document.getElementById('monthInput');
            const date = new Date(input.value + '-01');
            date.setMonth(date.getMonth() + offset);
            input.value = date.toISOString().slice(0, 7);
            input.dispatchEvent(new Event('change'));
        }
    </script>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<script src="assets/js/app.js"></script>
</body>

</html>