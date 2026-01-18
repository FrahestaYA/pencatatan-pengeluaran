// app.js - SPA & Logic

document.addEventListener('DOMContentLoaded', () => {
    initSPA();
    initCurrentPage();
    initTheme();
});

// --- Theme Logic ---
function initTheme() {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

window.toggleDarkMode = function () {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    }
};

// --- SPA Router ---
function initSPA() {
    // Intercept Links
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && link.href && link.target !== '_blank' && !link.href.includes('logout.php') && !link.href.includes('#')) {
            const url = new URL(link.href);
            // Only internal links
            if (url.origin === window.location.origin) {
                e.preventDefault();
                loadPage(url.href);
            }
        }
    });

    // Handle Back Button
    window.addEventListener('popstate', () => {
        loadPage(window.location.href, false);
    });
}

async function loadPage(url, pushState = true) {
    // Show Loading
    showLoading();

    try {
        const response = await fetch(url);
        const html = await response.text();

        // Parse HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Swap Content (Assuming #main-content exists in both)
        const newContent = doc.getElementById('main-content');
        const oldContent = document.getElementById('main-content');

        if (newContent && oldContent) {
            // Animation Out
            oldContent.classList.add('opacity-0', 'scale-95');

            setTimeout(() => {
                oldContent.innerHTML = newContent.innerHTML;
                oldContent.classList.remove('opacity-0', 'scale-95');

                // Update Title
                document.title = doc.title;

                // Update URL
                if (pushState) history.pushState({}, '', url);

                // Update Active Nav
                updateActiveNav();

                // Re-init Scripts
                initCurrentPage();

                // Scroll to top
                window.scrollTo(0, 0);
            }, 200); // 200ms match css transition
        } else {
            // Fallback for full reload if structure mismatch
            window.location.href = url;
        }

    } catch (error) {
        console.error('SPA Error:', error);
        window.location.href = url;
    } finally {
        setTimeout(hideLoading, 300);
    }
}

function showLoading() {
    let loader = document.getElementById('spa-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'spa-loader';
        loader.className = 'fixed inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm transition-opacity duration-300';
        loader.innerHTML = '<div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>';
        document.body.appendChild(loader);
    }
    loader.classList.remove('hidden', 'opacity-0');
}

function hideLoading() {
    const loader = document.getElementById('spa-loader');
    if (loader) {
        loader.classList.add('opacity-0');
        setTimeout(() => loader.classList.add('hidden'), 300);
    }
}

function updateActiveNav() {
    const path = window.location.pathname.split('/').pop();
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        const itemPath = item.getAttribute('href');
        let isActive = false;

        // Exact match
        if (path === itemPath) isActive = true;
        // Home fallback
        else if ((path === '' || path === 'index.php') && itemPath === 'index.php') isActive = true;
        // Transactions -> Home
        else if (path === 'transactions.php' && itemPath === 'index.php') isActive = true;
        // Categories -> Profile
        else if (path === 'categories.php' && itemPath === 'profile.php') isActive = true;

        if (isActive) {
            item.classList.add('text-blue-500', 'dark:text-dark-accent');
            item.classList.remove('text-gray-400');
        } else {
            item.classList.remove('text-blue-500', 'dark:text-dark-accent');
            item.classList.add('text-gray-400');
        }
    });
}

// --- Page Initializers ---
function initCurrentPage() {
    // Detect page type
    if (document.getElementById('reportChart')) initReports();
    if (document.getElementById('chart-cat-1')) initDashboard();
    // Modals are now global, but we can re-init them here if needed, 
    // or better yet, init them once since they persist.
    // However, since we might navigate full reload, we should check.
    initGlobalModals();

    if (document.getElementById('profileForm')) initProfile();
    if (document.getElementById('filterForm')) initTransactions();
    if (document.getElementById('manageCatForm')) initCategories();
}

function initCategories() {

    // Open Add Modal
    window.openAddCategoryModal = function () {
        const modal = document.getElementById('manageCatModal');
        document.getElementById('modalTitle').innerText = "Tambah Kategori";
        document.getElementById('catAction').value = "add";
        document.getElementById('catId').value = "";
        document.getElementById('catName').value = "";

        // Reset active state
        document.getElementById('typeExpense').checked = true;

        // Reset icon to default or first
        const icons = document.querySelectorAll('input[name="icon"]');
        if (icons.length > 0) icons[0].checked = true;

        modal.classList.remove('hidden');
    }

    // Open Edit Modal
    window.openEditCategory = function (id, name, type, icon) {
        const modal = document.getElementById('manageCatModal');
        document.getElementById('modalTitle').innerText = "Edit Kategori";
        document.getElementById('catAction').value = "update";
        document.getElementById('catId').value = id;
        document.getElementById('catName').value = name;

        // Set Type
        if (type === 'expense') document.getElementById('typeExpense').checked = true;
        else document.getElementById('typeIncome').checked = true;

        // Set Icon
        const iconInput = document.querySelector(`input[name="icon"][value="${icon}"]`);
        if (iconInput) iconInput.checked = true;

        modal.classList.remove('hidden');
    }

    // Close Modal
    window.closeManageModal = function () {
        document.getElementById('manageCatModal').classList.add('hidden');
    }

    // Delete Logic
    window.deleteCategory = function (id) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: "Kategori ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/manage_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete&id=' + id
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) loadPage(window.location.href, false);
                        else Swal.fire('Error', data.error, 'error');
                    });
            }
        });
    };

    // Add/Update Logic
    const form = document.getElementById('manageCatForm');
    if (form) {
        form.onsubmit = function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            // Action is already in formData from hidden input

            // Loading state
            const btn = form.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Menyimpan...';
            btn.disabled = true;

            fetch('api/manage_category.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.closeManageModal();
                        loadPage(window.location.href, false);
                    } else {
                        Swal.fire('Error', data.error, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'))
                .finally(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                });
        };
    }
}

function initGlobalModals() {
    if (document.getElementById('budgetModal')) initBudgets();
    if (document.getElementById('addExpenseForm')) initAddModal();
}

function initTransactions() {
    const filterForm = document.getElementById('filterForm');
    const monthInput = document.getElementById('monthInput');

    if (monthInput) {
        // Override default submit to use SPA loadPage
        monthInput.addEventListener('change', () => {
            const url = 'transactions.php?month=' + monthInput.value;
            loadPage(url);
        });

        // Override window.changeMonth if it exists or define it
        window.changeMonth = function (offset) {
            const date = new Date(monthInput.value + '-01');
            date.setMonth(date.getMonth() + offset);
            // Format YYYY-MM
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const newVal = `${year}-${month}`;
            monthInput.value = newVal;
            // Trigger change event manually
            monthInput.dispatchEvent(new Event('change'));
        };
    }
}

// Reports Logic
function initReports() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    const dataScript = document.getElementById('chart-data');
    if (!dataScript) return;

    const chartData = JSON.parse(dataScript.textContent);

    if (window.reportChartInstance) window.reportChartInstance.destroy();

    window.reportChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
}

// Profile Logic
function initProfile() {
    const form = document.getElementById('profileForm');
    const fileInput = document.getElementById('avatarInput');
    const preview = document.getElementById('avatarPreview');

    if (!form) return;

    // Image Preview
    if (fileInput) {
        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => preview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        };
    }

    // Form Submit
    form.onsubmit = async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const msg = document.getElementById('save-msg');

        const originalText = btn.innerText;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch('api/update_profile.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                msg.textContent = 'Berhasil disimpan!';
                msg.className = 'text-center text-xs mt-2 h-4 text-green-500 font-bold';
                setTimeout(() => msg.textContent = '', 3000);
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            msg.textContent = 'Gagal: ' + error.message;
            msg.className = 'text-center text-xs mt-2 h-4 text-red-500 font-bold';
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    };
}

// Budgets Logic
function initBudgets() {
    // Expose open function globally so onclicks works
    window.openBudgetModal = function (id, name, amount) {
        document.getElementById('budgetModal').classList.remove('hidden');
        document.getElementById('modalCatId').value = id;
        document.getElementById('modalCatName').innerText = name;

        // Format amount
        let formatted = amount;
        if (!amount || amount == 0) formatted = '';
        else formatted = new Intl.NumberFormat('id-ID').format(amount);

        document.getElementById('modalAmount').value = formatted;
    };

    const form = document.getElementById('budgetForm');
    const input = document.getElementById('modalAmount');

    // Input Formatting (Rupiah)
    if (input) {
        input.addEventListener('keyup', function (e) {
            let value = this.value.replace(/\D/g, '');
            if (value === '') {
                this.value = '';
            } else {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            }
        });
    }

    // AJAX Submit
    if (form) {
        form.onsubmit = async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Menyimpan...';
            btn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch('api/update_budget.php', {
                    method: 'POST', body: formData
                });
                const result = await response.json();

                if (result.success) {
                    document.getElementById('budgetModal').classList.add('hidden');
                    loadPage(window.location.href, false); // Reload content

                    Swal.fire({
                        icon: 'success', title: 'Anggaran Disimpan',
                        toast: true, position: 'top-end',
                        showConfirmButton: false, timer: 1500
                    });
                } else {
                    throw new Error(result.error);
                }
            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        };
    }
}

// Dashboard Logic
function initDashboard() {
    // Budget Rings
    const charts = document.querySelectorAll('.budget-chart');
    charts.forEach(canvas => {
        const percent = parseFloat(canvas.dataset.percent);
        const color = canvas.dataset.color;

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percent, 100 - percent],
                    backgroundColor: [color, '#E5E7EB'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                events: []
            }
        });
    });
}

// Add/Edit Modal Logic
function initAddModal() {
    const form = document.getElementById('addExpenseForm');
    if (!form) return;

    // Formatting Logic
    const amountInput = document.getElementById('transAmount');
    if (amountInput) {
        amountInput.addEventListener('keyup', function (e) {
            let value = this.value.replace(/\D/g, '');
            this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
    }

    form.onsubmit = async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = 'Menyimpan...';

        try {
            const formData = new FormData(form);
            const response = await fetch('api/manage_transaction.php', {
                method: 'POST', body: formData
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success', title: 'Berhasil Disimpan', toast: true,
                    position: 'top-end', showConfirmButton: false, timer: 1500
                });
                closeAddModal();
                // Reload current page to see updates
                loadPage(window.location.href, false);
            } else {
                throw new Error(result.error);
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        }
    };
}

// Global: Open Add Modal (Reset)
window.openAddModal = function () {
    const m = document.getElementById('addModal');
    const c = document.getElementById('addModalContent');
    m.classList.remove('hidden');
    setTimeout(() => c.classList.remove('translate-y-full'), 10);

    // Reset Form
    document.getElementById('addExpenseForm').reset();
    document.getElementById('transAction').value = 'add';
    document.getElementById('transId').value = '';
    document.getElementById('transModalTitle').innerText = 'Tambah Transaksi';
    document.getElementById('btnDeleteTrans').classList.add('hidden');
    document.getElementById('transDate').value = new Date().toISOString().split('T')[0];

    // Fetch latest categories
    fetchCategories();
};

// Global: Open Edit Modal
window.openEditTransaction = function (id, amount, date, catId, desc, type) {
    const m = document.getElementById('addModal');
    const c = document.getElementById('addModalContent');

    // Fetch categories first to ensure select is populated
    fetchCategories();

    // Small delay to allow categories to load (or use promise if refactored, but timeout is simpler for now)
    setTimeout(() => {
        // Populate
        document.getElementById('transAction').value = 'update';
        document.getElementById('transId').value = id;
        document.getElementById('transModalTitle').innerText = 'Edit Transaksi';
        document.getElementById('transAmount').value = new Intl.NumberFormat('id-ID').format(amount);
        document.getElementById('transDate').value = date;
        document.getElementById('transDesc').value = desc;
        document.getElementById('btnDeleteTrans').classList.remove('hidden');

        // Set Type & Category
        window.setType(type); // This switches the toggle UI
        const select = document.getElementById('category_select');
        select.value = catId; // Set selected category
    }, 100);

    m.classList.remove('hidden');
    setTimeout(() => c.classList.remove('translate-y-full'), 10);
};

// Global: Delete Transaction
window.deleteTransaction = function () {
    const id = document.getElementById('transId').value;
    Swal.fire({
        title: 'Hapus Transaksi?',
        text: "Data tidak bisa dikembalikan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/manage_transaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id=' + id
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeAddModal();
                        loadPage(window.location.href, false);
                        Swal.fire('Terhapus!', '', 'success');
                    } else {
                        Swal.fire('Error', data.error, 'error');
                    }
                });
        }
    });
};

window.closeAddModal = function () {
    const m = document.getElementById('addModal');
    const c = document.getElementById('addModalContent');
    c.classList.add('translate-y-full');
    setTimeout(() => m.classList.add('hidden'), 300);
}

// Helper: Fetch and Populate Categories
function fetchCategories() {
    fetch('api/manage_category.php?action=list')
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Store globally for toggle logic
                window.incomeCats = res.data.filter(c => c.type === 'income');
                window.expenseCats = res.data.filter(c => c.type === 'expense');

                // Re-init Type Logic
                initTypeToggle();
            }
        });
}

function initTypeToggle() {
    window.setType = function (type) {
        document.getElementById('type-input').value = type;

        const btnExpense = document.getElementById('btn-expense');
        const btnIncome = document.getElementById('btn-income');

        if (type === 'expense') {
            btnExpense.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
            btnExpense.classList.remove('text-gray-500');
            btnIncome.classList.remove('bg-white', 'text-gray-800', 'shadow-sm');
            btnIncome.classList.add('text-gray-500');
            populateCats(window.expenseCats || []);
        } else {
            btnIncome.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
            btnIncome.classList.remove('text-gray-500');
            btnExpense.classList.remove('bg-white', 'text-gray-800', 'shadow-sm');
            btnExpense.classList.add('text-gray-500');
            populateCats(window.incomeCats || []);
        }
    };

    // Check current selected type or default to expense
    const currentType = document.getElementById('type-input').value || 'expense';
    window.setType(currentType);
}

// Helper: Populate Categories (needs data)
function populateCats(cats) {
    const select = document.getElementById('category_select');
    if (!select) return;
    select.innerHTML = '';
    cats.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.name;
        select.appendChild(opt);
    });
}

// Category Management
window.openManageCategoryModal = function () {
    const m = document.getElementById('manageCatModal');
    const f = document.getElementById('manageCatForm');
    f.reset();
    document.getElementById('catAction').value = 'add';
    document.getElementById('catId').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Kategori';
    m.classList.remove('hidden');
};

window.openEditCategory = function (id, name, type, icon) {
    const m = document.getElementById('manageCatModal');

    document.getElementById('catAction').value = 'update';
    document.getElementById('catId').value = id;
    document.getElementById('catName').value = name;
    document.getElementById('modalTitle').innerText = 'Edit Kategori';

    // Set Type
    if (type === 'expense') document.getElementById('typeExpense').checked = true;
    else document.getElementById('typeIncome').checked = true;

    // Set Icon
    // Uncheck all first
    document.querySelectorAll('input[name="icon"]').forEach(i => i.checked = false);
    const iconInput = document.querySelector(`input[name="icon"][value="${icon}"]`);
    if (iconInput) iconInput.checked = true;

    m.classList.remove('hidden');
};

window.closeManageModal = function () {
    const m = document.getElementById('manageCatModal');
    m.classList.add('hidden');
};

window.deleteCategory = function (id) {
    Swal.fire({
        title: 'Hapus Kategori?',
        text: "Kategori akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/manage_category.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id=' + id
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Terhapus!', '', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Swal.fire('Error', data.error, 'error');
                    }
                });
        }
    });
};

// Handle Category Form Submit
const catForm = document.getElementById('manageCatForm');
if (catForm) {
    catForm.onsubmit = async (e) => {
        e.preventDefault();
        const btn = catForm.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        try {
            const formData = new FormData(catForm);
            const response = await fetch('api/manage_category.php', {
                method: 'POST', body: formData
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1000 });
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(result.error);
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    };
}
