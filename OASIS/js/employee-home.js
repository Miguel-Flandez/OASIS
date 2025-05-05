// Global Variables
const apiBaseUrl = 'http://localhost/OASIS/html/'; // Corrected path

// Utility Functions
const showLoading = () => document.getElementById('loading-overlay')?.classList.remove('hidden');
const hideLoading = () => document.getElementById('loading-overlay')?.classList.add('hidden');
const showError = (message) => {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
};

// Excel Export Function
function exportToExcel(fullData) {
    const wb = XLSX.utils.book_new();

    function formatSheet(sheet, data) {
        const colWidths = data[0].map((_, colIndex) =>
            Math.max(...data.map(row => String(row[colIndex] || '').length)) + 2
        );
        sheet['!cols'] = colWidths.map(w => ({ wch: w }));
        sheet['!rows'] = data.map(() => ({ hpt: 20, wrapText: true }));
    }

    const statsData = [
        ['Dashboard Stats'],
        ['Total Students', fullData.stats.totalStudents],
        ['Overdue Accounts', fullData.stats.overdueAccounts],
        ['Outstanding Balance', `₱${fullData.stats.outstandingBalance.toLocaleString()}`],
        ['Revenue', `₱${fullData.stats.revenue.toLocaleString()}`]
    ];
    const statsSheet = XLSX.utils.aoa_to_sheet(statsData);
    formatSheet(statsSheet, statsData);
    XLSX.utils.book_append_sheet(wb, statsSheet, 'Stats');

    const distData = [['Unpaid Fees Distribution'], ...Object.entries(fullData.distributionData).map(([type, amount]) => [type, `₱${amount.toLocaleString()}`])];
    const distSheet = XLSX.utils.aoa_to_sheet(distData);
    formatSheet(distSheet, distData);
    XLSX.utils.book_append_sheet(wb, distSheet, 'Unpaid Fees');

    const trendData = [['Payment Trend'], ['Date', 'Amount'], ...fullData.trendData.dates.map((date, i) => [date, `₱${fullData.trendData.amounts[i].toLocaleString()}`])];
    const trendSheet = XLSX.utils.aoa_to_sheet(trendData);
    formatSheet(trendSheet, trendData);
    XLSX.utils.book_append_sheet(wb, trendSheet, 'Payment Trend');

    const historyData = [['History'], ['Transaction Date', 'Amount Paid', 'Fees'], ...fullData.history.map(h => [h.transactiondate, `₱${Number(h.amountpaid).toLocaleString()}`, h.fees])];
    const historySheet = XLSX.utils.aoa_to_sheet(historyData);
    formatSheet(historySheet, historyData);
    XLSX.utils.book_append_sheet(wb, historySheet, 'History');

    const miscData = [['Miscellaneous Fees'], ['ID', 'Fee', 'Due Date', 'Amount', 'Status'], ...fullData.misc.map(m => [m.id, m.fee, m.duedate, `₱${Number(m.amount).toLocaleString()}`, m.status])];
    const miscSheet = XLSX.utils.aoa_to_sheet(miscData);
    formatSheet(miscSheet, miscData);
    XLSX.utils.book_append_sheet(wb, miscSheet, 'Misc');

    const studentsData = [['Students'], ['Student Number', 'First Name', 'Last Name', 'Middle Name', 'Level', 'Username', 'Account Name', 'Payment Plan'], ...fullData.students.map(s => [s.studentnumber, s.firstname, s.lastname, s.middlename, s.level, s.username, s.accountname, s.paymentplan])];
    const studentsSheet = XLSX.utils.aoa_to_sheet(studentsData);
    formatSheet(studentsSheet, studentsData);
    XLSX.utils.book_append_sheet(wb, studentsSheet, 'Students');

    const tuitionData = [['Tuition Fees'], ['ID', 'Fee', 'Due Date', 'Amount', 'Status'], ...fullData.tuition.map(t => [t.id, t.fee, t.duedate, `₱${Number(t.amount).toLocaleString()}`, t.status])];
    const tuitionSheet = XLSX.utils.aoa_to_sheet(tuitionData);
    formatSheet(tuitionSheet, tuitionData);
    XLSX.utils.book_append_sheet(wb, tuitionSheet, 'Tuition');

    XLSX.writeFile(wb, `OASIS_Database_${new Date().toISOString().split('T')[0]}.xlsx`);
}

// Data Fetching
async function fetchPaymentData() {
    const fetchWithErrorHandling = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            console.log(`Fetched from ${url}:`, data); // Debugging log
            return data;
        } catch (error) {
            console.error(`Fetch error for ${url}:`, error);
            return [];
        }
    };

    try {
        const [tuitionData, miscData, historyData, studentsData] = await Promise.all([
            fetchWithErrorHandling(`${apiBaseUrl}fetch_tuition.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_misc.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_employee_history.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_accounts.php`)
        ]);

        return {
            tuition: tuitionData.map(item => ({
                ...item,
                type: 'tuition',
                date: item.duedate
            })),
            misc: miscData.map(item => ({
                ...item,
                type: 'misc',
                date: item.duedate
            })),
            history: historyData.map(item => ({
                ...item,
                type: 'history',
                date: item.transactiondate
            })),
            students: studentsData
        };
    } catch (error) {
        console.error('Fetch error:', error);
        showError('Failed to load payment data.');
        return { tuition: [], misc: [], history: [], students: [] };
    }
}

// Fetch Dashboard Stats
async function fetchStats() {
    const stats = {
        outstandingBalance: parseFloat(document.getElementById('outstanding-balances')?.textContent.replace('₱', '').replace(',', '')) || 0,
        overdueAccounts: parseInt(document.getElementById('overdue-accounts')?.textContent) || 0,
        totalStudents: parseInt(document.getElementById('total-students')?.textContent) || 0,
        revenue: parseFloat(document.getElementById('revenue')?.textContent.replace('₱', '').replace(',', '')) || 0
    };
    return stats;
}

// Chart Rendering
let distributionChart = null;
async function drawCharts() {
    showLoading();
    const data = await fetchPaymentData();
    const stats = await fetchStats();

    // Unpaid Fees Distribution
    const unpaidDistribution = {};
    data.tuition.forEach(entry => {
        if (entry.status && entry.status.toLowerCase() === 'unpaid') {
            unpaidDistribution['Tuition'] = (unpaidDistribution['Tuition'] || 0) + (parseFloat(entry.amount) || 0);
        }
    });
    data.misc.forEach(entry => {
        if (entry.status && entry.status.toLowerCase() === 'unpaid') {
            unpaidDistribution['Miscellaneous'] = (unpaidDistribution['Miscellaneous'] || 0) + (parseFloat(entry.amount) || 0);
        }
    });
    console.log('Unpaid Distribution:', unpaidDistribution); // Debugging log

    const ctx1 = document.getElementById('paymentDistributionChart');
    const distributionNoData = document.getElementById('distribution-no-data');
    const togglePie = document.getElementById('toggle-pie');
    const toggleBar = document.getElementById('toggle-bar');

    // Check if there's data for the distribution chart
    const hasDistributionData = Object.keys(unpaidDistribution).length > 0 && Object.values(unpaidDistribution).some(val => val > 0);

    if (!hasDistributionData) {
        ctx1.style.display = 'none';
        distributionNoData.style.display = 'block';
        togglePie.style.display = 'none';
        toggleBar.style.display = 'none';
    } else {
        ctx1.style.display = 'block';
        distributionNoData.style.display = 'none';
        togglePie.style.display = 'inline-block';
        toggleBar.style.display = 'inline-block';

        function renderChart(type) {
            if (distributionChart) distributionChart.destroy();
            
            const commonOptions = {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 20 } },
                    title: { display: true, text: 'Unpaid Fees Distribution', font: { size: 18, weight: 'bold', family: 'Montserrat' }, color: '#333', padding: 20 },
                    datalabels: { color: type === 'pie' ? '#fff' : '#333', font: { weight: 'bold', size: 12, family: 'Montserrat' }, formatter: value => `₱${value.toLocaleString()}`, anchor: 'end', align: 'end' },
                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', titleFont: { family: 'Montserrat' }, bodyFont: { family: 'Montserrat' }, callbacks: { label: context => `${context.label}: ₱${context.raw.toLocaleString()}` } }
                },
                animation: { duration: 1500, easing: 'easeInOutCubic' }
            };

            distributionChart = new Chart(ctx1, {
                type: type === 'pie' ? 'pie' : 'bar',
                data: {
                    labels: Object.keys(unpaidDistribution),
                    datasets: [{
                        data: Object.values(unpaidDistribution),
                        backgroundColor: type === 'pie' ? ['#00C4CC', '#FF6B6B'] : '#00C4CC',
                        borderColor: type === 'pie' ? '#ffffff' : '#00C4CC',
                        borderWidth: 2,
                        hoverBorderWidth: 3,
                        hoverOffset: type === 'pie' ? 10 : 0
                    }]
                },
                options: type === 'pie' ? {
                    ...commonOptions,
                    cutout: '70%'
                } : {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => `₱${v.toLocaleString()}` } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        renderChart('pie');
        togglePie.addEventListener('click', () => {
            togglePie.classList.add('active');
            toggleBar.classList.remove('active');
            renderChart('pie');
        });
        toggleBar.addEventListener('click', () => {
            toggleBar.classList.add('active');
            togglePie.classList.remove('active');
            renderChart('bar');
        });
    }

    // Payment Trend (using history data only)
    const trendData = {};
    data.history.forEach(entry => {
        if (entry.transactiondate && entry.amountpaid > 0) {
            // Parse the full timestamp and extract date only, handling timezone
            const date = new Date(entry.transactiondate.split(' ')[0]).toISOString().split('T')[0];
            trendData[date] = (trendData[date] || 0) + parseFloat(entry.amountpaid);
        }
    });
    console.log('Trend Data:', trendData); // Debugging log

    const dates = Object.keys(trendData).sort();
    const amounts = dates.map(d => trendData[d]);
    const ctx2 = document.getElementById('paymentTrendChart');
    const trendNoData = document.getElementById('trend-no-data');

    // Check if there's data for the trend chart
    const hasTrendData = dates.length > 0 && amounts.some(val => val > 0);

    if (!hasTrendData) {
        ctx2.style.display = 'none';
        trendNoData.style.display = 'block';
    } else {
        ctx2.style.display = 'block';
        trendNoData.style.display = 'none';

        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Payment Trend (PHP)',
                    data: amounts,
                    borderColor: '#00C4CC',
                    backgroundColor: context => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(0, 196, 204, 0.3)');
                        gradient.addColorStop(1, 'rgba(0, 196, 204, 0)');
                        return gradient;
                    },
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#00C4CC',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 20 } },
                    title: { display: true, text: 'Payment Trends Over Time', font: { size: 18, weight: 'bold', family: 'Montserrat' }, color: '#333', padding: 20 },
                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', titleFont: { family: 'Montserrat' }, bodyFont: { family: 'Montserrat' }, callbacks: { label: context => `₱${context.raw.toLocaleString()}` } }
                },
                scales: {
                    x: { 
                        type: 'time', 
                        time: { unit: 'day' }, 
                        title: { display: true, text: 'Date', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, 
                        grid: { display: false }, 
                        ticks: { color: '#666' } 
                    },
                    y: { 
                        beginAtZero: true, 
                        title: { display: true, text: 'Amount (PHP)', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, 
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }, 
                        ticks: { color: '#666', callback: v => `₱${v.toLocaleString()}` } 
                    }
                },
                animation: { duration: 1500, easing: 'easeInOutCubic' }
            }
        });
    }

    hideLoading();
    return {
        distributionData: unpaidDistribution,
        trendData: { dates, amounts },
        stats: stats,
        history: data.history,
        misc: data.misc,
        students: data.students,
        tuition: data.tuition
    };
}

// Initialization
let chartData = null;
async function getChartData() {
    if (!chartData) chartData = await drawCharts();
    return chartData;
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Ensure Chart is globally available and register plugins
        if (typeof window !== 'undefined' && typeof Chart !== 'undefined') {
            window.Chart = Chart;
            Chart.register(ChartDataLabels);
        } else {
            throw new Error('Chart.js is not loaded properly');
        }

        chartData = await drawCharts();

        document.getElementById('export-excel').addEventListener('click', () => {
            exportToExcel(chartData);
        });
    } catch (error) {
        console.error('Initialization error:', error);
        showError('Failed to initialize dashboard.');
    }
});