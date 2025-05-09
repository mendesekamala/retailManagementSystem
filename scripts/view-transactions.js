document.addEventListener('DOMContentLoaded', function() {
    // Register the Chart.js plugins
    Chart.register(ChartDataLabels);

    // Initialize Line Chart
    const initLineChart = () => {
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        return new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: chartData.dates,
                datasets: [
                    {
                        label: 'Sales',
                        data: chartData.sales,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Purchases',
                        data: chartData.purchases,
                        borderColor: '#e67e22',
                        backgroundColor: 'rgba(230, 126, 34, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Profit',
                        data: chartData.profits,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    };

    // Initialize Pie Chart
    const initPieChart = () => {
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const labels = summaryData.map(item => item.transaction_type);
        const counts = summaryData.map(item => item.count);
        
        const colors = labels.map(label => {
            switch(label) {
                case 'sale': return '#4361ee';
                case 'purchase': return '#e67e22';
                case 'expenses': return '#e74c3c';
                case 'drawings': return '#9b59b6';
                case 'add_capitals': return '#34495e';
                case 'profit': return '#2ecc71';
                default: return '#95a5a6';
            }
        });
        
        return new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: labels.map(label => label.replace('_', ' ')),
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return percentage > 5 ? `${percentage}%` : '';
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    };

    // Initialize both charts
    const lineChart = initLineChart();
    const pieChart = initPieChart();

    // Filter event listeners
    document.getElementById('period-filter').addEventListener('change', function() {
        const period = this.value;
        const type = document.getElementById('type-filter').value;
        const page = 1;
        
        if (type) {
            window.location.href = `view-transactions.php?period=${period}&type_filter=${type}&page=${page}`;
        } else {
            window.location.href = `view-transactions.php?period=${period}&page=${page}`;
        }
    });

    document.getElementById('type-filter').addEventListener('change', function() {
        const type = this.value;
        const period = document.getElementById('period-filter').value;
        const page = 1;
        
        if (type) {
            window.location.href = `view-transactions.php?period=${period}&type_filter=${type}&page=${page}`;
        } else {
            window.location.href = `view-transactions.php?period=${period}&page=${page}`;
        }
    });

    // Pagination buttons
    document.getElementById('prev-page').addEventListener('click', function() {
        const currentPage = parseInt(document.getElementById('page-info').textContent.match(/Page (\d+)/)[1]);
        if (currentPage > 1) {
            const period = document.getElementById('period-filter').value;
            const type = document.getElementById('type-filter').value;
            const newPage = currentPage - 1;
            
            if (type) {
                window.location.href = `view-transactions.php?period=${period}&type_filter=${type}&page=${newPage}`;
            } else {
                window.location.href = `view-transactions.php?period=${period}&page=${newPage}`;
            }
        }
    });

    document.getElementById('next-page').addEventListener('click', function() {
        const pageInfo = document.getElementById('page-info').textContent;
        const currentPage = parseInt(pageInfo.match(/Page (\d+)/)[1]);
        const totalPages = parseInt(pageInfo.match(/of (\d+)/)[1]);
        
        if (currentPage < totalPages) {
            const period = document.getElementById('period-filter').value;
            const type = document.getElementById('type-filter').value;
            const newPage = currentPage + 1;
            
            if (type) {
                window.location.href = `view-transactions.php?period=${period}&type_filter=${type}&page=${newPage}`;
            } else {
                window.location.href = `view-transactions.php?period=${period}&page=${newPage}`;
            }
        }
    });

    // Responsive handling
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            lineChart.resize();
            pieChart.resize();
        }, 250);
    });
});