<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside
    class="hidden md:flex flex-col w-64 h-screen fixed top-0 left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-40 transition-colors duration-300">
    <!-- Logo -->
    <div class="h-20 flex items-center px-8 border-b border-gray-100 dark:border-gray-700">
        <div
            class="h-10 w-10 bg-blue-600 dark:bg-dark-accent rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 dark:shadow-dark-accent/30 mr-3">
            <i class="fas fa-wallet text-white text-xl"></i>
        </div>
        <h1 class="font-bold text-xl text-gray-800 dark:text-white tracking-tight">Pengeluaran<span
                class="text-blue-500 dark:text-dark-accent">Kita</span></h1>
    </div>

    <!-- Nav Links -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="index.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'index.php' || $current_page == '' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-home w-6 text-lg <?php echo $current_page == 'index.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Dashboard</span>
        </a>

        <a href="transactions.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'transactions.php' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-list w-6 text-lg <?php echo $current_page == 'transactions.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Transaksi</span>
        </a>

        <a href="reports.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'reports.php' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-chart-pie w-6 text-lg <?php echo $current_page == 'reports.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Laporan</span>
        </a>

        <a href="budgets.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'budgets.php' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-wallet w-6 text-lg <?php echo $current_page == 'budgets.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Anggaran</span>
        </a>

        <a href="categories.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'categories.php' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-tags w-6 text-lg <?php echo $current_page == 'categories.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Kategori</span>
        </a>

        <a href="profile.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo $current_page == 'profile.php' ? 'bg-blue-50 dark:bg-white/5 text-blue-600 dark:text-dark-accent font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'; ?>">
            <i
                class="fas fa-user w-6 text-lg <?php echo $current_page == 'profile.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span>Profil</span>
        </a>
    </nav>

    <!-- Add Button in Sidebar -->
    <div class="p-6 border-t border-gray-100 dark:border-gray-700">
        <button onclick="openAddModal()"
            class="w-full bg-blue-600 dark:bg-dark-accent hover:opacity-90 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-dark-accent/30 transition-all active:scale-95 flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Tambah Data</span>
        </button>
    </div>
</aside>