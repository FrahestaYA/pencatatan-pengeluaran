<!-- Add/Edit Transaction Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeAddModal()"></div>
    <div class="absolute bottom-0 w-full max-w-md left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 rounded-t-3xl p-6 transition-transform duration-300 translate-y-full"
        id="addModalContent">
        <div class="w-12 h-1 bg-gray-200 dark:bg-gray-600 rounded-full mx-auto mb-6"></div>

        <h3 id="transModalTitle" class="text-lg font-bold text-gray-800 dark:text-white mb-4">Tambah Transaksi</h3>

        <form id="addExpenseForm" class="space-y-5">
            <input type="hidden" name="action" id="transAction" value="add">
            <input type="hidden" name="id" id="transId" value="">

            <!-- Type Toggle -->
            <div class="flex bg-gray-100 dark:bg-gray-700 p-1 rounded-xl mb-4">
                <button type="button" onclick="setType('expense')" id="btn-expense"
                    class="flex-1 py-2 rounded-lg text-sm font-bold shadow-sm bg-white text-gray-800 transition-all">Pengeluaran</button>
                <button type="button" onclick="setType('income')" id="btn-income"
                    class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-gray-700 transition-all">Pemasukan</button>
            </div>
            <input type="hidden" name="type" id="type-input" value="expense">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Nominal</label>
                <div class="relative">
                    <span class="absolute left-0 top-2 text-2xl font-bold text-gray-400">Rp</span>
                    <input type="text" name="amount" id="transAmount" required inputmode="numeric"
                        class="w-full bg-transparent border-b-2 border-gray-100 dark:border-gray-700 text-3xl font-bold text-gray-800 dark:text-white pl-10 focus:outline-none focus:border-blue-500 dark:focus:border-dark-accent placeholder-gray-200"
                        placeholder="0">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tanggal</label>
                    <input type="date" name="date" id="transDate" required value="<?php echo date('Y-m-d'); ?>"
                        class="w-full bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 text-gray-800 dark:text-gray-100 outline-none">
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Kategori</label>
                    <select name="category_id" id="category_select" required
                        class="w-full bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 text-gray-800 dark:text-gray-100 outline-none appearance-none">
                        <!-- Populated by JS -->
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Catatan</label>
                <input type="text" name="description" id="transDesc"
                    class="w-full bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 dark:focus:ring-dark-accent/50 text-gray-800 dark:text-gray-100 outline-none"
                    placeholder="Contoh: Makan siang...">
            </div>

            <div class="flex space-x-3 mt-4">
                <button type="button" id="btnDeleteTrans" onclick="deleteTransaction()"
                    class="hidden bg-red-100 hover:bg-red-200 text-red-600 dark:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500/30 px-4 py-4 rounded-xl transition-colors">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button type="submit"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 dark:bg-dark-accent dark:hover:bg-opacity-90 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-dark-accent/30 active:scale-95 transition-transform">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Budget Modal -->
<div id="budgetModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
        onclick="document.getElementById('budgetModal').classList.add('hidden')"></div>
    <div class="absolute bottom-0 w-full max-w-md left-1/2 transform -translate-x-1/2 bg-white p-6 rounded-t-3xl"
        id="budgetModalContent">
        <h3 class="font-bold text-lg mb-4">Set Anggaran <span id="modalCatName"></span></h3>
        <form id="budgetForm">
            <input type="hidden" name="category_id" id="modalCatId">
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Limit Anggaran</label>
                <div class="relative">
                    <span class="absolute left-0 top-2 text-xl font-bold text-gray-400">Rp</span>
                    <input type="text" name="amount" id="modalAmount"
                        class="w-full border-b-2 border-gray-200 text-2xl font-bold py-2 pl-8 focus:outline-none focus:border-blue-500"
                        placeholder="0" inputmode="numeric">
                </div>
            </div>
            <button type="submit"
                class="w-full bg-blue-500 text-white font-bold py-3 rounded-xl shadow-lg active:scale-95 transition-transform">Simpan</button>
        </form>
    </div>
</div>