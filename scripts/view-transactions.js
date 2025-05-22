document.addEventListener('DOMContentLoaded', function() {
    // State variables
    let currentPage = 1;
    const itemsPerPage = 10;
    let currentStartDate = '';
    let currentEndDate = '';
    let currentSearchTerm = '';
    let currentTypeFilter = '';
    let dateRangePicker;

    // Initialize the dashboard
    initDashboard();

    // Event listeners
    setupEventListeners();

    // Load initial data (last 7 days by default)
    loadDashboardData('7');

    function initDashboard() {
        initCharts([], [], []);
    }

    function setupEventListeners() {
        // Date filter buttons (excluding custom range)
        document.querySelectorAll('.date-filter .btn:not(#custom-range)').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.date-filter .btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const days = this.dataset.days;
                loadDashboardData(days);
            });
        });

        // Custom range button
        document.getElementById('custom-range').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCustomDateRangePicker();
        });

        // Search input
        document.getElementById('transaction-search').addEventListener('input', function() {
            currentSearchTerm = this.value;
            currentPage = 1;
            loadTransactions();
        });

        // Type filter
        document.getElementById('type-filter').addEventListener('change', function() {
            currentTypeFilter = this.value;
            currentPage = 1;
            loadTransactions();
        });

        // Pagination
        document.getElementById('prev-page').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadTransactions();
            }
        });

        document.getElementById('next-page').addEventListener('click', function() {
            currentPage++;
            loadTransactions();
        });
    }

    function showCustomDateRangePicker() {
        // Create or show the date picker container
        let pickerContainer = document.getElementById('date-range-picker');
        if (!pickerContainer) {
            pickerContainer = document.createElement('div');
            pickerContainer.id = 'date-range-picker';
            pickerContainer.className = 'date-range-picker';
            pickerContainer.innerHTML = `
                <div class="date-range-inputs">
                    <input type="text" id="start-date-input" placeholder="Start Date" readonly>
                    <span>to</span>
                    <input type="text" id="end-date-input" placeholder="End Date" readonly>
                </div>
                <div class="date-range-buttons">
                    <button id="cancel-date-range" class="btn">Cancel</button>
                    <button id="apply-date-range" class="btn">Apply</button>
                </div>
            `;
            document.body.appendChild(pickerContainer);
            
            // Close picker when clicking outside
            document.addEventListener('click', function outsideClickListener(e) {
                if (!pickerContainer.contains(e.target) && e.target.id !== 'custom-range') {
                    pickerContainer.style.display = 'none';
                }
            });
            
            // Initialize flatpickr
            const startDateInput = document.getElementById('start-date-input');
            const endDateInput = document.getElementById('end-date-input');
            
            flatpickr(startDateInput, {
                dateFormat: "Y-m-d",
                maxDate: new Date(),
                defaultDate: currentStartDate || new Date(),
                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        endDateInput._flatpickr.set('minDate', dateStr);
                    }
                }
            });
            
            flatpickr(endDateInput, {
                dateFormat: "Y-m-d",
                maxDate: new Date(),
                defaultDate: currentEndDate || new Date()
            });
            
            // Handle apply button click
            document.getElementById('apply-date-range').addEventListener('click', function() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                
                if (startDate && endDate) {
                    // Remove active class from all date filter buttons
                    document.querySelectorAll('.date-filter .btn').forEach(b => b.classList.remove('active'));
                    
                    // Set custom dates and load data
                    currentStartDate = startDate;
                    currentEndDate = endDate;
                    currentPage = 1;
                    
                    // Load both summary data and transactions
                    Promise.all([
                        fetchSummaryData(currentStartDate, currentEndDate),
                        loadTransactions()
                    ]).then(() => {
                        pickerContainer.style.display = 'none';
                    }).catch(error => {
                        console.error('Error loading data:', error);
                        pickerContainer.style.display = 'none';
                    });
                }
            });
            
            // Handle cancel button click
            document.getElementById('cancel-date-range').addEventListener('click', function() {
                pickerContainer.style.display = 'none';
            });
        }
        
        // Toggle visibility
        pickerContainer.style.display = pickerContainer.style.display === 'none' ? 'block' : 'none';
    }

    function loadDashboardData(days) {
        showLoading(true);

        // Only set dates if it's a predefined range (not custom)
        if (days && days !== 'custom') {
            const endDate = new Date();
            endDate.setHours(23, 59, 59, 999); // Include full current day
            
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - parseInt(days) + 1); // Add 1 to include start day
            startDate.setHours(0, 0, 0, 0);

            currentStartDate = startDate.toISOString().split('T')[0];
            currentEndDate = endDate.toISOString().split('T')[0];
        }
        
        // Make sure we have valid dates
        if (!currentStartDate || !currentEndDate) {
            // Default to last 7 days if no dates are set
            const endDate = new Date();
            endDate.setHours(23, 59, 59, 999);
            
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 6); // 7 days total (6 days + current day)
            startDate.setHours(0, 0, 0, 0);

            currentStartDate = startDate.toISOString().split('T')[0];
            currentEndDate = endDate.toISOString().split('T')[0];
        }
        
        // Load both summary data and transactions
        Promise.all([
            fetchSummaryData(currentStartDate, currentEndDate),
            loadTransactions()
        ]).then(() => {
            showLoading(false);
        }).catch(error => {
            console.error('Error loading data:', error);
            alert('Failed to load data. Check console for details.');
            showLoading(false);
        });
    }

    function fetchSummaryData(startDate, endDate) {
        return fetch(`api/get_transactions.php?start_date=${startDate}&end_date=${endDate}&page=1&per_page=${itemsPerPage}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API returned unsuccessful response');
                updateSummaryCards(data.summary);
                prepareChartData(data.transactions, data.transactionTypes);
            });
    }

    function loadTransactions() {
        showLoading(true);
        
        let url = `api/get_transactions.php?start_date=${currentStartDate}&end_date=${currentEndDate}`;
        url += `&page=${currentPage}&per_page=${itemsPerPage}`;
        
        if (currentSearchTerm) {
            url += `&search=${encodeURIComponent(currentSearchTerm)}`;
        }
        
        if (currentTypeFilter) {
            url += `&type=${encodeURIComponent(currentTypeFilter)}`;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API returned unsuccessful response');
                
                renderTransactionsTable(data.transactions);
                updatePagination(data.pagination);
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading transactions:', error);
                alert('Failed to load transactions. Check console for details.');
                showLoading(false);
            });
    }

    function updateSummaryCards(summary) {
        function formatSummary(count, amount) {
            const formattedAmount = amount ? parseFloat(amount).toLocaleString('en-US') : '0';
            return `${formattedAmount} Tsh`;
        }

        document.getElementById('sales-count').textContent = formatSummary(summary.sales_count, summary.sales_amount);
        document.getElementById('purchases-count').textContent = formatSummary(summary.purchases_count, summary.purchases_amount);
        document.getElementById('drawings-count').textContent = formatSummary(summary.drawings_count, summary.drawings_amount);
        document.getElementById('expenses-count').textContent = formatSummary(summary.expenses_count, summary.expenses_amount);
        document.getElementById('capital-count').textContent = formatSummary(summary.capital_count, summary.capital_amount);
        document.getElementById('debtors-count').textContent = formatSummary(summary.debtors_count, summary.debtors_amount);
        document.getElementById('creditors-count').textContent = formatSummary(summary.creditors_count, summary.creditors_amount);
        document.getElementById('destructions-count').textContent = formatSummary(summary.destructions_count, summary.destructions_amount);
        document.getElementById('refund-count').textContent = formatSummary(summary.refund_count, summary.refund_amount);
    }

    function prepareChartData(transactions, transactionTypes) {
        const dateGroups = {};

        if (!Array.isArray(transactions)) return;

        transactions.forEach(transaction => {
            let date;
            try {
                date = transaction.date_made.split(' ')[0];
            } catch (e) {
                console.error('Error parsing transaction date', transaction);
                return;
            }

            if (!dateGroups[date]) {
                dateGroups[date] = { sales: 0, purchases: 0 };
            }

            const amount = parseFloat(transaction.amount) || 0;
            
            if (transaction.transaction_type === 'sale') {
                dateGroups[date].sales += amount;
            } else if (transaction.transaction_type === 'purchase') {
                dateGroups[date].purchases += amount;
            }
        });

        const dates = Object.keys(dateGroups).sort();
        const salesData = dates.map(date => dateGroups[date].sales);
        const purchasesData = dates.map(date => dateGroups[date].purchases);

        initCharts(dates, salesData, purchasesData, transactionTypes);
    }

    function initCharts(dates, salesData, purchasesData, transactionTypes) {
        // Sales vs Purchases Chart (Line Graph)
        const salesPurchasesCtx = document.getElementById('sales-purchases-chart').getContext('2d');
        if (window.salesPurchasesChart) window.salesPurchasesChart.destroy();
        window.salesPurchasesChart = new Chart(salesPurchasesCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Sales',
                        data: salesData,
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        borderColor: '#2ecc71',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: 'Purchases',
                        data: purchasesData,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: '#3498db',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { 
                    legend: { 
                        position: 'top' 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Tsh' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => 'Tsh' + value.toLocaleString()
                        }
                    }
                }
            }
        });

        // Transaction Types Chart
        const transactionTypesCtx = document.getElementById('transaction-types-chart').getContext('2d');
        if (window.transactionTypesChart) window.transactionTypesChart.destroy();
        
        if (transactionTypes && transactionTypes.length > 0) {
            const typeLabels = transactionTypes.map(t => t.transaction_type);
            const typeData = transactionTypes.map(t => t.total_amount);
            const typeCounts = transactionTypes.map(t => t.count);
            
            const backgroundColors = [
                '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#d35400'
            ];
            
            window.transactionTypesChart = new Chart(transactionTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeData,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const count = typeCounts[context.dataIndex] || 0;
                                    return `${label}: Tsh${value.toLocaleString()} (${count} transactions)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Show empty state if no data
            window.transactionTypesChart = new Chart(transactionTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['No data'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#e0e0e0']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                }
            });
        }
    }

    function renderTransactionsTable(transactions) {
        const tableBody = document.querySelector('#transactions-table tbody');
        tableBody.innerHTML = '';

        if (transactions.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No transactions found</td></tr>';
        } else {
            transactions.forEach(transaction => {
                const formattedDate = transaction.date_made ? new Date(transaction.date_made).toLocaleDateString() : 'N/A';
                const formattedAmount = transaction.amount ? parseFloat(transaction.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00';
                const typeClass = getTypeClass(transaction.transaction_type);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${transaction.transaction_id || 'N/A'}</td>
                    <td><span class="type-badge ${typeClass}">${transaction.transaction_type || 'N/A'}</span></td>
                    <td>${formattedDate}</td>
                    <td class="${transaction.transaction_type === 'sale' || transaction.transaction_type === 'add_capital' ? 'text-success' : 'text-danger'}">
                        Tsh${formattedAmount}
                    </td>
                    <td>${transaction.description || 'N/A'}</td>
                    <td>
                        <a href="view-transaction.php?transaction_id=${transaction.transaction_id}" class="action-btn view" title="View Transaction">
                            <i class='bx bx-show'></i>
                        </a>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
    }

    function getTypeClass(type) {
        switch(type) {
            case 'sale': return 'type-sale';
            case 'purchase': return 'type-purchase';
            case 'expenses': return 'type-expenses';
            case 'drawings': return 'type-drawings';
            case 'add_capital': return 'type-capital';
            case 'destruction': return 'type-destruction';
            default: return '';
        }
    }

    function updatePagination(pagination) {
        document.getElementById('page-info').textContent = `Page ${pagination.current_page} of ${pagination.total_pages}`;
        document.getElementById('prev-page').disabled = pagination.current_page <= 1;
        document.getElementById('next-page').disabled = pagination.current_page >= pagination.total_pages;
    }

    function showLoading(show) {
        const loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.innerHTML = '<div class="spinner"></div>';

        if (show) {
            document.body.appendChild(loader);
        } else {
            const existingLoader = document.querySelector('.loading-overlay');
            if (existingLoader) existingLoader.remove();
        }
    }
});