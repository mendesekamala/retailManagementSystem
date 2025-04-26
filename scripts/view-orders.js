document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const itemsPerPage = 10;
    let allOrders = [];
    let filteredOrders = [];

    initDashboard();

    document.querySelectorAll('.date-filter .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-filter .btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const days = this.dataset.days;
            loadDashboardData(days);
        });
    });

    document.getElementById('order-search').addEventListener('input', function() {
        filterOrders();
    });

    document.getElementById('status-filter').addEventListener('change', function() {
        filterOrders();
    });

    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderOrdersTable();
        }
    });

    document.getElementById('next-page').addEventListener('click', function() {
        const totalPages = Math.ceil(filteredOrders.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderOrdersTable();
        }
    });

    document.querySelector('.date-filter .btn[data-days="7"]').click();

    function initDashboard() {
        initCharts([], [], []);
    }

    function loadDashboardData(days) {
        showLoading(true);

        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - parseInt(days));

        const startDateStr = startDate.toISOString().split('T')[0];
        const endDateStr = endDate.toISOString().split('T')[0];

        fetch(`api/get_orders.php?start_date=${startDateStr}&end_date=${endDateStr}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API returned unsuccessful response');

                allOrders = data.orders || [];
                filteredOrders = [...allOrders];

                updateSummaryCards(data.summary);
                prepareChartData(allOrders);
                renderOrdersTable();
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading data:', error);
                alert('Failed to load data. Check console for details.');
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

    function filterOrders() {
        const searchTerm = document.getElementById('order-search').value.toLowerCase();
        const statusFilter = document.getElementById('status-filter').value;

        filteredOrders = allOrders.filter(order => {
            const matchesSearch = 
                (order.orderNo && order.orderNo.toLowerCase().includes(searchTerm)) ||
                (order.customer_name && order.customer_name.toLowerCase().includes(searchTerm));
            const matchesStatus = statusFilter === '' || order.status === statusFilter;

            return matchesSearch && matchesStatus;
        });

        currentPage = 1;
        renderOrdersTable();
    }

    function renderOrdersTable() {
        const tableBody = document.querySelector('#orders-table tbody');
        tableBody.innerHTML = '';

        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedOrders = filteredOrders.slice(startIndex, startIndex + itemsPerPage);

        if (paginatedOrders.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
        } else {
            paginatedOrders.forEach(order => {
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

        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredOrders.length / itemsPerPage) || 1;
        document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('prev-page').disabled = currentPage <= 1;
        document.getElementById('next-page').disabled = currentPage >= totalPages;
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