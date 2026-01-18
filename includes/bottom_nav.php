<div id="bottom-nav"
    class="fixed bottom-0 w-full max-w-md md:hidden bg-white dark:bg-gray-800 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] rounded-t-3xl px-6 py-4 flex justify-between items-center z-30 transition-transform duration-300">
    <a href="index.php"
        class="nav-item text-gray-400 hover:text-blue-500 dark:hover:text-dark-accent flex flex-col items-center group transition-colors"
        data-page="home">
        <i class="fas fa-home text-xl mb-1 group-hover:scale-110 transition-transform"></i>
        <!-- <span class="text-[10px] font-medium">Home</span> -->
    </a>

    <a href="reports.php"
        class="nav-item text-gray-400 hover:text-blue-500 dark:hover:text-dark-accent flex flex-col items-center group transition-colors"
        data-page="reports">
        <i class="fas fa-chart-pie text-xl mb-1 group-hover:scale-110 transition-transform"></i>
    </a>

    <div class="relative -top-8">
        <button onclick="openAddModal()"
            class="bg-blue-600 dark:bg-dark-accent hover:bg-blue-700 dark:hover:bg-opacity-90 text-white h-14 w-14 rounded-full flex items-center justify-center shadow-lg shadow-blue-600/30 dark:shadow-dark-accent/30 border-4 border-gray-50 dark:border-gray-900 active:scale-90 transition-transform duration-200">
            <i class="fas fa-plus text-xl"></i>
        </button>
    </div>

    <a href="budgets.php"
        class="nav-item text-gray-400 hover:text-blue-500 dark:hover:text-dark-accent flex flex-col items-center group transition-colors"
        data-page="budgets">
        <i class="fas fa-wallet text-xl mb-1 group-hover:scale-110 transition-transform"></i>
    </a>

    <a href="profile.php"
        class="nav-item text-gray-400 hover:text-red-500 dark:hover:text-red-400 flex flex-col items-center group transition-colors">
        <i class="fas fa-user text-xl mb-1 group-hover:scale-110 transition-transform"></i>
    </a>
</div>

<?php include __DIR__ . '/modals.php'; ?>