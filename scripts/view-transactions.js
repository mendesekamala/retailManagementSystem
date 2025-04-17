document.addEventListener('DOMContentLoaded', function() {
    // Animate summary tiles
    const tiles = document.querySelectorAll('.summary-tile');
    tiles.forEach((tile, index) => {
        tile.style.setProperty('--order', index);
        
        const data = tile.querySelector('.tile-data');
        const countElement = tile.querySelector('.count');
        const amountElement = tile.querySelector('.amount');
        
        const targetCount = parseInt(data.getAttribute('data-count')) || 0;
        const targetAmount = parseFloat(data.getAttribute('data-amount')) || 0;
        
        animateValue(countElement, 0, targetCount, 1000);
        animateValue(amountElement, 0, targetAmount, 1000, true);
    });

    // Initialize charts
    initializeCharts();

    // Filter event listeners
    document.getElementById('period-filter').addEventListener('change', function() {
        const period = this.value;
        window.location.href = `view-transactions.php?period=${period}`;
    });

    document.getElementById('type-filter').addEventListener('change', function() {
        const type = this.value;
        const period = document.getElementById('period-filter').value;
        
        if (type) {
            window.location.href = `view-transactions.php?period=${period}&type_filter=${type}`;
        } else {
            window.location.href = `view-transactions.php?period=${period}`;
        }
    });
});

function animateValue(element, start, end, duration, isCurrency = false) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        
        if (isCurrency) {
            element.textContent = value.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else {
            element.textContent = value;
        }
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function initializeCharts() {
    // Line Chart - Profit, Sales, Purchases
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    
    // Get data from PHP (simplified for example)
    const profitData = JSON.parse('<?= json_encode($profit_data) ?>');
    
    const dates = profitData.map(item => item.date);
    const sales = profitData.map(item => parseFloat(item.sales) || 0);
    const purchases = profitData.map(item => parseFloat(item.cost) || 0);
    const profits = profitData.map(item => parseFloat(item.profit) || 0);
    
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Sales',
                    data: sales,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Purchases',
                    data: purchases,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Profit',
                    data: profits,
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
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'USD'
                            });
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Pie Chart - Transaction Types
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    
    // Get data from PHP (simplified for example)
    const summaryData = JSON.parse('<?= json_encode($summary) ?>');
    
    const labels = summaryData.map(item => item.transaction_type);
    const counts = summaryData.map(item => item.count);
    const colors = labels.map(label => {
        switch(label) {
            case 'sale': return '#3498db';
            case 'purchase': return '#f39c12';
            case 'expenses': return '#e74c3c';
            case 'drawings': return '#9b59b6';
            case 'add_capitals': return '#34495e';
            default: return '#95a5a6';
        }
    });
    
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: labels,
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
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        },
        plugins: [ChartDataLabels]
    });
}