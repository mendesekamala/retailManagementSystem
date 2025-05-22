document.addEventListener('DOMContentLoaded', function() {
    // State variables
    let currentPage = 1;
    const itemsPerPage = 10;
    let currentStartDate = '';
    let currentEndDate = '';
    let currentSearchTerm = '';
    let currentStatusFilter = '';
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
        document.getElementById('order-search').addEventListener('input', function() {
            currentSearchTerm = this.value;
            currentPage = 1;
            loadOrders();
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            currentStatusFilter = this.value;
            currentPage = 1;
            loadOrders();
        });

        // Pagination
        document.getElementById('prev-page').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadOrders();
            }
        });

        document.getElementById('next-page').addEventListener('click', function() {
            currentPage++;
            loadOrders();
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
                    
                    // Load both summary data and orders
                    Promise.all([
                        fetchSummaryData(currentStartDate, currentEndDate),
                        loadOrders()
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
        
        // Load both summary data and orders
        Promise.all([
            fetchSummaryData(currentStartDate, currentEndDate),
            loadOrders()
        ]).then(() => {
            showLoading(false);
        }).catch(error => {
            console.error('Error loading data:', error);
            alert('Failed to load data. Check console for details.');
            showLoading(false);
        });
    }

    function fetchSummaryData(startDate, endDate) {
        return fetch(`api/get_orders.php?start_date=${startDate}&end_date=${endDate}&page=1&per_page=${itemsPerPage}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API returned unsuccessful response');
                updateSummaryCards(data.summary);
                prepareChartData(data.orders);
            });
    }

    function loadOrders() {
        showLoading(true);
        
        let url = `api/get_orders.php?start_date=${currentStartDate}&end_date=${currentEndDate}`;
        url += `&page=${currentPage}&per_page=${itemsPerPage}`;
        
        if (currentSearchTerm) {
            url += `&search=${encodeURIComponent(currentSearchTerm)}`;
        }
        
        if (currentStatusFilter) {
            url += `&status=${encodeURIComponent(currentStatusFilter)}`;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API returned unsuccessful response');
                
                renderOrdersTable(data.orders);
                updatePagination(data.pagination);
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading orders:', error);
                alert('Failed to load orders. Check console for details.');
                showLoading(false);
            });
    }

    function updateSummaryCards(summary) {
        const formatMoney = val =>
            `Tsh${parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        document.getElementById('total-orders').textContent = summary.total_orders || 0;
        document.getElementById('total-revenue').textContent = formatMoney(summary.total_revenue);
        document.getElementById('avg-order-value').textContent = formatMoney(summary.avg_order_value);
        document.getElementById('total-profit').textContent = formatMoney(summary.total_profit);
    }

    function prepareChartData(orders) {
        const dateGroups = {};
    
        if (!Array.isArray(orders)) return;
    
        orders.forEach(order => {
            // Skip cancelled orders for chart calculations only
            if (order.status === 'cancelled') return;
            
            let date;
            try {
                date = order.time.split(' ')[0];
            } catch (e) {
                console.error('Error parsing order date', order);
                return;
            }
    
            if (!dateGroups[date]) {
                dateGroups[date] = { revenue: 0, profit: 0, count: 0 };
            }
    
            const revenue = parseFloat(order.total) || 0;
            const profit = parseFloat(order.profit) || 0;
    
            dateGroups[date].revenue += revenue;
            dateGroups[date].profit += profit;
            dateGroups[date].count++;
        });
    
        const dates = Object.keys(dateGroups).sort();
        const revenueData = dates.map(date => dateGroups[date].revenue);
        const profitData = dates.map(date => dateGroups[date].profit);
    
        initCharts(dates, revenueData, profitData);
    }

    function initCharts(dates, revenueData, profitData) {
        const revenueCtx = document.getElementById('revenue-chart').getContext('2d');
        if (window.revenueChart) window.revenueChart.destroy();
        window.revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
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

        const profitCtx = document.getElementById('profit-chart').getContext('2d');
        if (window.profitChart) window.profitChart.destroy();
        window.profitChart = new Chart(profitCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Profit',
                    data: profitData,
                    borderColor: '#4cc9f0',
                    backgroundColor: 'rgba(76, 201, 240, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
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
    }

    function renderOrdersTable(orders) {
        const tableBody = document.querySelector('#orders-table tbody');
        tableBody.innerHTML = '';

        if (orders.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
        } else {
            orders.forEach(order => {
                const formattedDate = order.time ? new Date(order.time).toLocaleDateString() : 'N/A';
                const formattedTotal = order.total ? parseFloat(order.total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00';
                const formattedProfit = order.profit ? parseFloat(order.profit).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.orderNo || 'N/A'}</td>
                    <td>${order.customer_name || 'N/A'}</td>
                    <td>${formattedDate}</td>
                    <td>Tsh${formattedTotal}</td>
                    <td>Tsh${formattedProfit}</td>
                    <td><span class="status-badge status-${order.status || 'unknown'}">${order.status || 'unknown'}</span></td>
                    <td>
                        <a href="view-order.php?order_id=${order.order_id}" class="action-btn view" title="View Order">
                            <i class='bx bx-show'></i>
                        </a>
                        <a href="cancel-order.php?order_id=${order.order_id}" class="action-btn delete" title="Cancel Order" 
                           onclick="return confirm('Are you sure you want to cancel order ${order.orderNo || order.order_id}?')">
                            <i class='bx bx-x'></i>
                        </a>
                    </td>
                `;
                tableBody.appendChild(row);
            });
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