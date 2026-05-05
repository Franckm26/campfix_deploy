// Analytics Modal Functions with SweetAlert2 and Charts

// Global variable to store current modal filter state
window.modalFilterState = {
    period: '',
    month: '',
    month_from: '',
    month_to: '',
    year: ''
};

// Show Location Details Modal
function showLocationDetailsModal(filterType = null) {
    const locations = window.chartLocations || [];
    const counts = window.chartCounts || [];
    const costs = window.chartCosts || [];
    const detailedStats = window.locationDetailedStats || [];
    
    let totalRepairs = 0;
    let totalCost = 0;
    
    // Sort detailed stats by count (number of times that specific item was repaired)
    const sortedStats = [...detailedStats].sort((a, b) => b.count - a.count);
    
    sortedStats.forEach((item) => {
        totalRepairs += parseInt(item.count) || 0;
        totalCost += parseFloat(item.total_cost) || 0;
    });
    
    let tableRows = '';
    sortedStats.forEach((item) => {
        const itemCost = parseFloat(item.total_cost) || 0;
        tableRows += `
            <tr>
                <td><strong>${item.title}</strong></td>
                <td><strong>${item.location}</strong></td>
                <td class="text-center"><span class="badge bg-primary">${item.count}</span></td>
                <td class="text-end">₱${itemCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `;
    });
    
    const filterLabel = getFilterLabel(filterType);
    
    Swal.fire({
        title: '<i class="fas fa-map-marker-alt me-2"></i>Repairs by Location - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex gap-2 align-items-end flex-wrap" style="flex: 1;">
                        <div style="min-width: 120px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Period</label>
                            <select id="modal_period" class="form-select form-select-sm" onchange="updateModalFilters('location')">
                                <option value="">Custom</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div id="modal_month_group" style="min-width: 120px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Month</label>
                            <select id="modal_month" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('location')">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div id="modal_month_from_group" style="min-width: 120px; display: none;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">From Month</label>
                            <select id="modal_month_from" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('location')">
                                <option value="">Select</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div id="modal_month_to_group" style="min-width: 120px; display: none;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">To Month</label>
                            <select id="modal_month_to" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('location')">
                                <option value="">Select</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div style="min-width: 100px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Year</label>
                            <select id="modal_year" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('location')">
                                <option value="">All Years</option>
                                ${generateYearOptions()}
                            </select>
                        </div>
                        <button class="btn btn-sm btn-secondary" onclick="resetModalFilters('location')" style="padding: 6px 12px;">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                    <button onclick="exportLocationReportToPDF()" class="btn btn-danger btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalLocationChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="locationReportTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Fixed</th>
                                <th>Location</th>
                                <th class="text-center">Total Repairs</th>
                                <th class="text-end">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="text-center">${totalRepairs}</td>
                                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `,
        width: '85%',
        heightAuto: false,
        padding: '0',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-analytics-modal',
            popup: 'swal-wide-popup'
        },
        didOpen: () => {
            const ctx = document.getElementById('modalLocationChart');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: locations,
                    datasets: [{
                        data: counts,
                        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
}

// Show Cost Details Modal
function showCostDetailsModal(filterType = null) {
    const locations = window.chartLocations || [];
    const counts = window.chartCounts || [];
    const costs = window.chartCosts || [];
    
    // Combine data and sort by cost
    const data = locations.map((loc, idx) => ({
        location: loc,
        count: counts[idx] || 0,
        cost: costs[idx] || 0
    })).sort((a, b) => b.cost - a.cost);
    
    let highestCost = data[0] || { location: 'N/A', cost: 0 };
    let lowestCost = data[data.length - 1] || { location: 'N/A', cost: 0 };
    const avgCost = data.reduce((sum, item) => sum + item.cost, 0) / (data.length || 1);
    
    let tableRows = '';
    data.forEach((item) => {
        const avgPerRepair = item.count > 0 ? item.cost / item.count : 0;
        
        let costLevel = '';
        let badgeClass = '';
        if (item.cost > avgCost * 1.5) {
            costLevel = 'Very High';
            badgeClass = 'bg-danger';
        } else if (item.cost > avgCost) {
            costLevel = 'High';
            badgeClass = 'bg-warning';
        } else if (item.cost > avgCost * 0.5) {
            costLevel = 'Medium';
            badgeClass = 'bg-info';
        } else {
            costLevel = 'Low';
            badgeClass = 'bg-success';
        }
        
        tableRows += `
            <tr>
                <td><strong>${item.location}</strong></td>
                <td class="text-center">${item.count}</td>
                <td class="text-end">₱${item.cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${avgPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${costLevel}</span></td>
            </tr>
        `;
    });
    
    // Get current filter state
    const state = window.modalFilterState;
    
    Swal.fire({
        title: '<i class="fas fa-dollar-sign me-2"></i>Cost by Location - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex gap-2 align-items-end flex-wrap" style="flex: 1;">
                        <div style="min-width: 120px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Period</label>
                            <select id="modal_period" class="form-select form-select-sm" onchange="updateModalFilters('cost')">
                                <option value="" ${state.period === '' ? 'selected' : ''}>Custom</option>
                                <option value="monthly" ${state.period === 'monthly' ? 'selected' : ''}>Monthly</option>
                                <option value="quarterly" ${state.period === 'quarterly' ? 'selected' : ''}>Quarterly</option>
                                <option value="yearly" ${state.period === 'yearly' ? 'selected' : ''}>Yearly</option>
                            </select>
                        </div>
                        <div id="modal_month_group" style="min-width: 120px; ${state.period === 'quarterly' || state.period === 'yearly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Month</label>
                            <select id="modal_month" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('cost')">
                                <option value="">All Months</option>
                                ${generateMonthOptions(state.month)}
                            </select>
                        </div>
                        <div id="modal_month_from_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">From Month</label>
                            <select id="modal_month_from" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('cost')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_from)}
                            </select>
                        </div>
                        <div id="modal_month_to_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">To Month</label>
                            <select id="modal_month_to" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('cost')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_to)}
                            </select>
                        </div>
                        <div style="min-width: 100px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Year</label>
                            <select id="modal_year" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('cost')">
                                <option value="">All Years</option>
                                ${generateYearOptions(state.year)}
                            </select>
                        </div>
                        <button class="btn btn-sm btn-secondary" onclick="resetModalFilters('cost')" style="padding: 6px 12px;">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                    <button onclick="exportCostReportToPDF()" class="btn btn-danger btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalCostChart"></canvas>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Highest Cost Location</h6>
                                <h4 class="text-danger mb-2">${highestCost.location}</h4>
                                <h5 class="mb-0">₱${highestCost.cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Lowest Cost Location</h6>
                                <h4 class="text-success mb-2">${lowestCost.location}</h4>
                                <h5 class="mb-0">₱${lowestCost.cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Average Cost</h6>
                                <h4 class="text-primary mb-2">₱${avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h4>
                                <h5 class="mb-0">per location</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Location</th>
                                <th class="text-center">Repairs</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Avg per Repair</th>
                                <th class="text-center">Cost Level</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                    </table>
                </div>
            </div>
        `,
        width: '85%',
        heightAuto: false,
        padding: '0',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-analytics-modal',
            popup: 'swal-wide-popup'
        },
        didOpen: () => {
            const ctx = document.getElementById('modalCostChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: locations,
                    datasets: [{
                        label: 'Total Cost (₱)',
                        data: costs,
                        backgroundColor: '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return '₱' + v.toLocaleString(); }
                            }
                        }
                    }
                }
            });
        }
    });
}

// Show Status Details Modal
function showStatusDetailsModal(filterType = null) {
    const statuses = window.chartStatuses || [];
    const statusCounts = window.chartStatusCounts || [];
    
    const totalCount = statusCounts.reduce((sum, count) => sum + count, 0);
    
    let tableRows = '';
    statuses.forEach((status, idx) => {
        const count = statusCounts[idx] || 0;
        const percentage = totalCount > 0 ? ((count / totalCount) * 100).toFixed(1) : 0;
        
        let badgeClass = 'bg-secondary';
        if (status === 'Resolved' || status === 'Completed') badgeClass = 'bg-success';
        else if (status === 'Pending' || status === 'In Progress') badgeClass = 'bg-warning';
        else if (status === 'Rejected' || status === 'Cancelled') badgeClass = 'bg-danger';
        
        tableRows += `
            <tr>
                <td><span class="badge ${badgeClass}">${status}</span></td>
                <td class="text-center"><strong>${count}</strong></td>
                <td class="text-center">${percentage}%</td>
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar ${badgeClass}" style="width: ${percentage}%">${percentage}%</div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    // Get current filter state
    const state = window.modalFilterState;
    
    Swal.fire({
        title: '<i class="fas fa-tasks me-2"></i>Status Distribution - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex gap-2 align-items-end flex-wrap" style="flex: 1;">
                        <div style="min-width: 120px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Period</label>
                            <select id="modal_period" class="form-select form-select-sm" onchange="updateModalFilters('status')">
                                <option value="" ${state.period === '' ? 'selected' : ''}>Custom</option>
                                <option value="monthly" ${state.period === 'monthly' ? 'selected' : ''}>Monthly</option>
                                <option value="quarterly" ${state.period === 'quarterly' ? 'selected' : ''}>Quarterly</option>
                                <option value="yearly" ${state.period === 'yearly' ? 'selected' : ''}>Yearly</option>
                            </select>
                        </div>
                        <div id="modal_month_group" style="min-width: 120px; ${state.period === 'quarterly' || state.period === 'yearly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Month</label>
                            <select id="modal_month" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('status')">
                                <option value="">All Months</option>
                                ${generateMonthOptions(state.month)}
                            </select>
                        </div>
                        <div id="modal_month_from_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">From Month</label>
                            <select id="modal_month_from" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('status')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_from)}
                            </select>
                        </div>
                        <div id="modal_month_to_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">To Month</label>
                            <select id="modal_month_to" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('status')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_to)}
                            </select>
                        </div>
                        <div style="min-width: 100px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Year</label>
                            <select id="modal_year" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('status')">
                                <option value="">All Years</option>
                                ${generateYearOptions(state.year)}
                            </select>
                        </div>
                        <button class="btn btn-sm btn-secondary" onclick="resetModalFilters('status')" style="padding: 6px 12px;">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                    <button onclick="exportStatusReportToPDF()" class="btn btn-danger btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalStatusChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Status</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Percentage</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center">${totalCount}</td>
                                <td class="text-center">100%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `,
        width: '85%',
        heightAuto: false,
        padding: '0',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-analytics-modal',
            popup: 'swal-wide-popup'
        },
        didOpen: () => {
            const ctx = document.getElementById('modalStatusChart');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: statuses,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
}

// Show Monthly Trend Modal
function showMonthlyTrendModal(filterType = null) {
    const monthly = window.monthlyStats || [];
    
    // Build 6-month labels
    const monthLabels = [];
    for (let i = 5; i >= 0; i--) {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - i);
        const key = d.toISOString().slice(0, 7);
        const lbl = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
        monthLabels.push({ key: key, label: lbl });
    }
    
    // Group by month
    const monthData = {};
    monthLabels.forEach(m => {
        monthData[m.key] = { label: m.label, issues: {}, total: 0 };
    });
    
    monthly.forEach(item => {
        if (monthData[item.month]) {
            if (!monthData[item.month].issues[item.title]) {
                monthData[item.month].issues[item.title] = 0;
            }
            monthData[item.month].issues[item.title] += item.count;
            monthData[item.month].total += item.count;
        }
    });
    
    // Find peak and lowest
    let peakMonth = null, peakCount = 0;
    let lowestMonth = null, lowestCount = Infinity;
    let totalCount = 0;
    
    Object.entries(monthData).forEach(([key, data]) => {
        totalCount += data.total;
        if (data.total > peakCount) {
            peakCount = data.total;
            peakMonth = data.label;
        }
        if (data.total < lowestCount) {
            lowestCount = data.total;
            lowestMonth = data.label;
        }
    });
    
    // Build table rows
    let tableRows = '';
    Object.entries(monthData).forEach(([key, data]) => {
        if (Object.keys(data.issues).length === 0) {
            tableRows += `
                <tr>
                    <td><strong>${data.label}</strong></td>
                    <td colspan="3" class="text-center text-muted">No repairs recorded</td>
                </tr>
            `;
        } else {
            Object.entries(data.issues).forEach(([issue, count], idx) => {
                const trend = count > 5 ? '<i class="fas fa-arrow-up text-danger"></i> High' : 
                             count > 2 ? '<i class="fas fa-minus text-warning"></i> Medium' : 
                             '<i class="fas fa-arrow-down text-success"></i> Low';
                
                tableRows += `
                    <tr>
                        ${idx === 0 ? `<td rowspan="${Object.keys(data.issues).length}"><strong>${data.label}</strong></td>` : ''}
                        <td>${issue}</td>
                        <td class="text-center"><span class="badge bg-primary">${count}</span></td>
                        <td class="text-center">${trend}</td>
                    </tr>
                `;
            });
        }
    });
    
    // Prepare chart data
    const issueMap = {};
    monthly.forEach(function(item) {
        if (!issueMap[item.title]) issueMap[item.title] = {};
        issueMap[item.title][item.month] = item.count;
    });
    
    const palette = ['#36A2EB','#FF6384','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#22C55E','#F97316'];
    const datasets = Object.entries(issueMap).map(function([title, monthDataMap], idx) {
        return {
            label: title,
            data: monthLabels.map(function(m) { return monthDataMap[m.key] || 0; }),
            borderColor: palette[idx % palette.length],
            backgroundColor: palette[idx % palette.length] + '22',
            borderWidth: 2.5,
            pointRadius: 5,
            tension: 0.3,
            fill: false
        };
    });
    
    // Get current filter state
    const state = window.modalFilterState;
    
    Swal.fire({
        title: '<i class="fas fa-chart-line me-2"></i>Monthly Trend - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex gap-2 align-items-end flex-wrap" style="flex: 1;">
                        <div style="min-width: 120px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Period</label>
                            <select id="modal_period" class="form-select form-select-sm" onchange="updateModalFilters('trend')">
                                <option value="" ${state.period === '' ? 'selected' : ''}>Custom</option>
                                <option value="monthly" ${state.period === 'monthly' ? 'selected' : ''}>Monthly</option>
                                <option value="quarterly" ${state.period === 'quarterly' ? 'selected' : ''}>Quarterly</option>
                                <option value="yearly" ${state.period === 'yearly' ? 'selected' : ''}>Yearly</option>
                            </select>
                        </div>
                        <div id="modal_month_group" style="min-width: 120px; ${state.period === 'quarterly' || state.period === 'yearly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Month</label>
                            <select id="modal_month" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('trend')">
                                <option value="">All Months</option>
                                ${generateMonthOptions(state.month)}
                            </select>
                        </div>
                        <div id="modal_month_from_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">From Month</label>
                            <select id="modal_month_from" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('trend')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_from)}
                            </select>
                        </div>
                        <div id="modal_month_to_group" style="min-width: 120px; ${state.period !== 'quarterly' ? 'display: none;' : ''}">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">To Month</label>
                            <select id="modal_month_to" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('trend')">
                                <option value="">Select</option>
                                ${generateMonthOptions(state.month_to)}
                            </select>
                        </div>
                        <div style="min-width: 100px;">
                            <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block;">Year</label>
                            <select id="modal_year" class="form-select form-select-sm" onchange="applyModalFilterFromInputs('trend')">
                                <option value="">All Years</option>
                                ${generateYearOptions(state.year)}
                            </select>
                        </div>
                        <button class="btn btn-sm btn-secondary" onclick="resetModalFilters('trend')" style="padding: 6px 12px;">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                    <button onclick="exportTrendReportToPDF()" class="btn btn-danger btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalTrendChart"></canvas>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Total Months</h6>
                                <h3 class="text-primary mb-0">6</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Peak Month</h6>
                                <h5 class="text-danger mb-1">${peakMonth || 'N/A'}</h5>
                                <small class="mb-0">${peakCount} repairs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Lowest Month</h6>
                                <h5 class="text-success mb-1">${lowestMonth || 'N/A'}</h5>
                                <small class="mb-0">${lowestCount === Infinity ? 0 : lowestCount} repairs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Avg per Month</h6>
                                <h3 class="text-info mb-0">${(totalCount / 6).toFixed(1)}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Month</th>
                                <th>Issue Type</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Trend</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                    </table>
                </div>
            </div>
        `,
        width: '85%',
        heightAuto: false,
        padding: '0',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-analytics-modal',
            popup: 'swal-wide-popup'
        },
        didOpen: () => {
            const ctx = document.getElementById('modalTrendChart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthLabels.map(m => m.label),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
}


// Export Location Report to PDF
function exportLocationReportToPDF() {
    const params = new URLSearchParams();
    
    // Get filter values from modal state (not from main page)
    const state = window.modalFilterState || {};
    
    console.log('Export PDF - Modal Filter State:', state); // Debug log
    
    // Handle different period types
    if (state.period && state.period !== '') {
        params.append('period', state.period);
        
        // Add period-specific parameters
        if (state.period === 'monthly') {
            if (state.month && state.month !== '') params.append('month', state.month);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'quarterly') {
            if (state.month_from && state.month_from !== '') params.append('month_from', state.month_from);
            if (state.month_to && state.month_to !== '') params.append('month_to', state.month_to);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'yearly') {
            if (state.year && state.year !== '') params.append('year', state.year);
        }
    } else {
        // Custom period - send individual filter values
        if (state.month && state.month !== '') params.append('month', state.month);
        if (state.year && state.year !== '') params.append('year', state.year);
    }
    
    // Open PDF in new window
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/location-report-pdf?' + queryString : '/admin/analytics/location-report-pdf';
    console.log('Export PDF URL:', url); // Debug log
    window.open(url, '_blank');
}

// Helper function to generate year options
function generateYearOptions(selectedYear = '') {
    const currentYear = new Date().getFullYear();
    let options = '';
    for (let y = currentYear; y >= currentYear - 5; y--) {
        const selected = (selectedYear == y) ? 'selected' : '';
        options += `<option value="${y}" ${selected}>${y}</option>`;
    }
    return options;
}

// Helper function to generate month options
function generateMonthOptions(selectedMonth = '') {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    let options = '';
    for (let i = 1; i <= 12; i++) {
        const selected = (selectedMonth == i) ? 'selected' : '';
        options += `<option value="${i}" ${selected}>${months[i-1]}</option>`;
    }
    return options;
}

// Update modal filter visibility based on period selection
function updateModalFilters(modalType) {
    const period = document.getElementById('modal_period').value;
    const monthGroup = document.getElementById('modal_month_group');
    const monthFromGroup = document.getElementById('modal_month_from_group');
    const monthToGroup = document.getElementById('modal_month_to_group');
    
    // Save period to state
    window.modalFilterState.period = period;
    
    if (period === 'monthly') {
        monthGroup.style.display = 'block';
        monthFromGroup.style.display = 'none';
        monthToGroup.style.display = 'none';
    } else if (period === 'quarterly') {
        monthGroup.style.display = 'none';
        monthFromGroup.style.display = 'block';
        monthToGroup.style.display = 'block';
    } else if (period === 'yearly') {
        monthGroup.style.display = 'none';
        monthFromGroup.style.display = 'none';
        monthToGroup.style.display = 'none';
    } else {
        monthGroup.style.display = 'block';
        monthFromGroup.style.display = 'none';
        monthToGroup.style.display = 'none';
    }
    
    // Apply filter after changing period
    applyModalFilterFromInputs(modalType);
}

// Apply modal filter from input values
function applyModalFilterFromInputs(modalType) {
    const period = document.getElementById('modal_period')?.value || '';
    const month = document.getElementById('modal_month')?.value || '';
    const monthFrom = document.getElementById('modal_month_from')?.value || '';
    const monthTo = document.getElementById('modal_month_to')?.value || '';
    const year = document.getElementById('modal_year')?.value || '';
    
    // Save all values to state
    window.modalFilterState = {
        period: period,
        month: month,
        month_from: monthFrom,
        month_to: monthTo,
        year: year
    };
    
    let params = new URLSearchParams();
    params.append('ajax', '1');
    
    if (period) params.append('period', period);
    if (month) params.append('month', month);
    if (monthFrom) params.append('month_from', monthFrom);
    if (monthTo) params.append('month_to', monthTo);
    if (year) params.append('year', year);
    
    // Show loading overlay on chart area only
    const chartContainer = document.querySelector('.swal2-html-container canvas')?.parentElement;
    if (chartContainer) {
        chartContainer.style.position = 'relative';
        chartContainer.style.opacity = '0.5';
    }
    
    // Fetch filtered data
    fetch('/admin/analytics?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update global variables with filtered data
        window.chartLocations = data.chartLocations || [];
        window.chartCounts = data.chartCounts || [];
        window.chartCosts = data.chartCosts || [];
        window.chartStatuses = data.chartStatuses || [];
        window.chartStatusCounts = data.chartStatusCounts || [];
        window.monthlyStats = data.monthlyStats || [];
        window.locationDetailedStats = data.locationStats || [];
        
        // Remove loading overlay
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        // Update the modal content without closing it
        if (modalType === 'location') {
            updateLocationModalContent();
        } else if (modalType === 'cost') {
            updateCostModalContent();
        } else if (modalType === 'status') {
            updateStatusModalContent();
        } else if (modalType === 'trend') {
            updateTrendModalContent();
        }
    })
    .catch(error => {
        console.error('Error fetching filtered data:', error);
        
        // Remove loading overlay
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        alert('Failed to load filtered data. Please try again.');
    });
}

// Reset modal filters
function resetModalFilters(modalType) {
    // Clear state
    window.modalFilterState = {
        period: '',
        month: '',
        month_from: '',
        month_to: '',
        year: ''
    };
    
    if (document.getElementById('modal_period')) document.getElementById('modal_period').value = '';
    if (document.getElementById('modal_month')) document.getElementById('modal_month').value = '';
    if (document.getElementById('modal_month_from')) document.getElementById('modal_month_from').value = '';
    if (document.getElementById('modal_month_to')) document.getElementById('modal_month_to').value = '';
    if (document.getElementById('modal_year')) document.getElementById('modal_year').value = '';
    
    applyModalFilterFromInputs(modalType);
}

// Apply modal filter - fetches filtered data via AJAX and updates modal
function applyModalFilter(modalType, filterType) {
    // Determine filter parameters
    const now = new Date();
    const currentMonth = now.getMonth() + 1; // 1-12
    const currentYear = now.getFullYear();
    
    let params = new URLSearchParams();
    params.append('ajax', '1');
    
    if (filterType === 'monthly') {
        params.append('period', 'monthly');
        params.append('month', currentMonth);
        params.append('year', currentYear);
    } else if (filterType === 'quarterly') {
        const quarterStartMonth = Math.floor((currentMonth - 1) / 3) * 3 + 1;
        const quarterEndMonth = quarterStartMonth + 2;
        params.append('period', 'quarterly');
        params.append('month_from', quarterStartMonth);
        params.append('month_to', quarterEndMonth);
        params.append('year', currentYear);
    } else if (filterType === 'yearly') {
        params.append('period', 'yearly');
        params.append('year', currentYear);
    }
    // Reset = no params (all data)
    
    // Show loading indicator
    Swal.showLoading();
    
    // Fetch filtered data
    fetch('/admin/analytics?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update global variables with filtered data
        window.chartLocations = data.chartLocations || [];
        window.chartCounts = data.chartCounts || [];
        window.chartCosts = data.chartCosts || [];
        window.chartStatuses = data.chartStatuses || [];
        window.chartStatusCounts = data.chartStatusCounts || [];
        window.monthlyStats = data.monthlyStats || [];
        window.locationDetailedStats = data.locationStats || [];
        
        // Re-render the appropriate modal with new data
        if (modalType === 'location') {
            showLocationDetailsModal(filterType);
        } else if (modalType === 'cost') {
            showCostDetailsModal(filterType);
        } else if (modalType === 'status') {
            showStatusDetailsModal(filterType);
        } else if (modalType === 'trend') {
            showMonthlyTrendModal(filterType);
        }
    })
    .catch(error => {
        console.error('Error fetching filtered data:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load filtered data. Please try again.'
        });
    });
}

// Helper function to get filter label
function getFilterLabel(filterType) {
    if (!filterType) return '';
    
    const now = new Date();
    const currentMonth = now.toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
    const currentYear = now.getFullYear();
    const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
    const quarterStart = new Date(currentYear, quarterStartMonth, 1).toLocaleDateString('en-PH', { month: 'short' });
    const quarterEnd = new Date(currentYear, quarterStartMonth + 2, 1).toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
    
    if (filterType === 'monthly') return ` - ${currentMonth}`;
    if (filterType === 'quarterly') return ` - ${quarterStart} to ${quarterEnd}`;
    if (filterType === 'yearly') return ` - ${currentYear}`;
    return '';
}

// Update Location Modal Content (without closing modal)
function updateLocationModalContent() {
    const locations = window.chartLocations || [];
    const counts = window.chartCounts || [];
    const detailedStats = window.locationDetailedStats || [];
    
    let totalRepairs = 0;
    let totalCost = 0;
    
    const sortedStats = [...detailedStats].sort((a, b) => b.count - a.count);
    
    sortedStats.forEach((item) => {
        totalRepairs += parseInt(item.count) || 0;
        totalCost += parseFloat(item.total_cost) || 0;
    });
    
    let tableRows = '';
    sortedStats.forEach((item) => {
        const itemCost = parseFloat(item.total_cost) || 0;
        tableRows += `
            <tr>
                <td><strong>${item.title}</strong></td>
                <td><strong>${item.location}</strong></td>
                <td class="text-center"><span class="badge bg-primary">${item.count}</span></td>
                <td class="text-end">₱${itemCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `;
    });
    
    // Update table
    const tbody = document.querySelector('#locationReportTable tbody');
    const tfoot = document.querySelector('#locationReportTable tfoot');
    if (tbody) tbody.innerHTML = tableRows;
    if (tfoot) {
        tfoot.innerHTML = `
            <tr>
                <td colspan="2">TOTAL</td>
                <td class="text-center">${totalRepairs}</td>
                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `;
    }
    
    // Update chart
    const canvas = document.getElementById('modalLocationChart');
    if (canvas && window.Chart) {
        // Destroy existing chart
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();
        
        // Create new chart
        new Chart(canvas, {
            type: 'pie',
            data: {
                labels: locations,
                datasets: [{
                    data: counts,
                    backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// Update Cost Modal Content (without closing modal)
function updateCostModalContent() {
    const locations = window.chartLocations || [];
    const counts = window.chartCounts || [];
    const costs = window.chartCosts || [];
    
    const data = locations.map((loc, idx) => ({
        location: loc,
        count: counts[idx] || 0,
        cost: costs[idx] || 0
    })).sort((a, b) => b.cost - a.cost);
    
    let highestCost = data[0] || { location: 'N/A', cost: 0 };
    let lowestCost = data[data.length - 1] || { location: 'N/A', cost: 0 };
    const avgCost = data.reduce((sum, item) => sum + item.cost, 0) / (data.length || 1);
    
    // Update summary cards - use more specific selector within the modal
    const modalContainer = document.querySelector('.swal2-html-container');
    if (modalContainer) {
        const summaryCards = modalContainer.querySelectorAll('.row.mb-4 .col-md-4');
        summaryCards.forEach((col, idx) => {
            const h4 = col.querySelector('h4');
            const h5 = col.querySelector('h5');
            
            if (idx === 0 && highestCost && h4 && h5) {
                h4.textContent = highestCost.location;
                h5.textContent = '₱' + highestCost.cost.toLocaleString('en-PH', {minimumFractionDigits: 2});
            } else if (idx === 1 && lowestCost && h4 && h5) {
                h4.textContent = lowestCost.location;
                h5.textContent = '₱' + lowestCost.cost.toLocaleString('en-PH', {minimumFractionDigits: 2});
            } else if (idx === 2 && h4) {
                h4.textContent = '₱' + avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2});
            }
        });
    }
    
    // Update table
    let tableRows = '';
    data.forEach((item) => {
        const avgPerRepair = item.count > 0 ? item.cost / item.count : 0;
        let costLevel = '', badgeClass = '';
        if (item.cost > avgCost * 1.5) {
            costLevel = 'Very High';
            badgeClass = 'bg-danger';
        } else if (item.cost > avgCost) {
            costLevel = 'High';
            badgeClass = 'bg-warning';
        } else if (item.cost > avgCost * 0.5) {
            costLevel = 'Medium';
            badgeClass = 'bg-info';
        } else {
            costLevel = 'Low';
            badgeClass = 'bg-success';
        }
        
        tableRows += `
            <tr>
                <td><strong>${item.location}</strong></td>
                <td class="text-center">${item.count}</td>
                <td class="text-end">₱${item.cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${avgPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${costLevel}</span></td>
            </tr>
        `;
    });
    
    const tbody = modalContainer ? modalContainer.querySelector('.table-responsive .table tbody') : null;
    if (tbody) tbody.innerHTML = tableRows;
    
    // Update chart
    const canvas = document.getElementById('modalCostChart');
    if (canvas && window.Chart) {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();
        
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: locations,
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: costs,
                    backgroundColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return '₱' + v.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }
}

// Update Status Modal Content (without closing modal)
function updateStatusModalContent() {
    const statuses = window.chartStatuses || [];
    const statusCounts = window.chartStatusCounts || [];
    const totalCount = statusCounts.reduce((sum, count) => sum + count, 0);
    
    let tableRows = '';
    statuses.forEach((status, idx) => {
        const count = statusCounts[idx] || 0;
        const percentage = totalCount > 0 ? ((count / totalCount) * 100).toFixed(1) : 0;
        
        let badgeClass = 'bg-secondary';
        if (status === 'Resolved' || status === 'Completed') badgeClass = 'bg-success';
        else if (status === 'Pending' || status === 'In Progress') badgeClass = 'bg-warning';
        else if (status === 'Rejected' || status === 'Cancelled') badgeClass = 'bg-danger';
        
        tableRows += `
            <tr>
                <td><span class="badge ${badgeClass}">${status}</span></td>
                <td class="text-center"><strong>${count}</strong></td>
                <td class="text-center">${percentage}%</td>
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar ${badgeClass}" style="width: ${percentage}%">${percentage}%</div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    // Update table - use more specific selector within the modal
    const modalContainer = document.querySelector('.swal2-html-container');
    const tbody = modalContainer ? modalContainer.querySelector('.table-responsive .table tbody') : null;
    const tfoot = modalContainer ? modalContainer.querySelector('.table-responsive .table tfoot') : null;
    
    if (tbody) tbody.innerHTML = tableRows;
    if (tfoot) {
        tfoot.innerHTML = `
            <tr>
                <td>TOTAL</td>
                <td class="text-center">${totalCount}</td>
                <td class="text-center">100%</td>
                <td></td>
            </tr>
        `;
    }
    
    // Update chart
    const canvas = document.getElementById('modalStatusChart');
    if (canvas && window.Chart) {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();
        
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: statuses,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// Update Trend Modal Content (without closing modal)
function updateTrendModalContent() {
    const monthly = window.monthlyStats || [];
    
    // Build 6-month labels
    const monthLabels = [];
    for (let i = 5; i >= 0; i--) {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - i);
        const key = d.toISOString().slice(0, 7);
        const lbl = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
        monthLabels.push({ key: key, label: lbl });
    }
    
    // Group by month
    const monthData = {};
    monthLabels.forEach(m => {
        monthData[m.key] = { label: m.label, issues: {}, total: 0 };
    });
    
    monthly.forEach(item => {
        if (monthData[item.month]) {
            if (!monthData[item.month].issues[item.title]) {
                monthData[item.month].issues[item.title] = 0;
            }
            monthData[item.month].issues[item.title] += item.count;
            monthData[item.month].total += item.count;
        }
    });
    
    // Find peak and lowest
    let peakMonth = null, peakCount = 0;
    let lowestMonth = null, lowestCount = Infinity;
    let totalCount = 0;
    
    Object.entries(monthData).forEach(([key, data]) => {
        totalCount += data.total;
        if (data.total > peakCount) {
            peakCount = data.total;
            peakMonth = data.label;
        }
        if (data.total < lowestCount) {
            lowestCount = data.total;
            lowestMonth = data.label;
        }
    });
    
    // Update summary cards - use more specific selector within the modal
    const modalContainer = document.querySelector('.swal2-html-container');
    if (modalContainer) {
        const summaryCards = modalContainer.querySelectorAll('.row.mb-4 .col-md-3');
        summaryCards.forEach((col, idx) => {
            const h5 = col.querySelector('h5');
            const h3 = col.querySelector('h3');
            const small = col.querySelector('small');
            
            if (idx === 1 && peakMonth && h5 && small) {
                h5.textContent = peakMonth;
                small.textContent = peakCount + ' repairs';
            } else if (idx === 2 && lowestMonth && h5 && small) {
                h5.textContent = lowestMonth;
                small.textContent = (lowestCount === Infinity ? 0 : lowestCount) + ' repairs';
            } else if (idx === 3 && h3) {
                h3.textContent = (totalCount / 6).toFixed(1);
            }
        });
    }
    
    // Update table
    let tableRows = '';
    Object.entries(monthData).forEach(([key, data]) => {
        if (Object.keys(data.issues).length === 0) {
            tableRows += `
                <tr>
                    <td><strong>${data.label}</strong></td>
                    <td colspan="3" class="text-center text-muted">No repairs recorded</td>
                </tr>
            `;
        } else {
            Object.entries(data.issues).forEach(([issue, count], idx) => {
                const trend = count > 5 ? '<i class="fas fa-arrow-up text-danger"></i> High' : 
                             count > 2 ? '<i class="fas fa-minus text-warning"></i> Medium' : 
                             '<i class="fas fa-arrow-down text-success"></i> Low';
                
                tableRows += `
                    <tr>
                        ${idx === 0 ? `<td rowspan="${Object.keys(data.issues).length}"><strong>${data.label}</strong></td>` : ''}
                        <td>${issue}</td>
                        <td class="text-center"><span class="badge bg-primary">${count}</span></td>
                        <td class="text-center">${trend}</td>
                    </tr>
                `;
            });
        }
    });
    
    const tbody = modalContainer ? modalContainer.querySelector('.table tbody') : null;
    if (tbody) tbody.innerHTML = tableRows;
    
    // Update chart
    const canvas = document.getElementById('modalTrendChart');
    if (canvas && window.Chart) {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();
        
        const issueMap = {};
        monthly.forEach(function(item) {
            if (!issueMap[item.title]) issueMap[item.title] = {};
            issueMap[item.title][item.month] = item.count;
        });
        
        const palette = ['#36A2EB','#FF6384','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#22C55E','#F97316'];
        const datasets = Object.entries(issueMap).map(function([title, monthDataMap], idx) {
            return {
                label: title,
                data: monthLabels.map(function(m) { return monthDataMap[m.key] || 0; }),
                borderColor: palette[idx % palette.length],
                backgroundColor: palette[idx % palette.length] + '22',
                borderWidth: 2.5,
                pointRadius: 5,
                tension: 0.3,
                fill: false
            };
        });
        
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: monthLabels.map(m => m.label),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}


// Export Cost Report to PDF
function exportCostReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    if (state.period && state.period !== '') {
        params.append('period', state.period);
        
        if (state.period === 'monthly') {
            if (state.month && state.month !== '') params.append('month', state.month);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'quarterly') {
            if (state.month_from && state.month_from !== '') params.append('month_from', state.month_from);
            if (state.month_to && state.month_to !== '') params.append('month_to', state.month_to);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'yearly') {
            if (state.year && state.year !== '') params.append('year', state.year);
        }
    } else {
        if (state.month && state.month !== '') params.append('month', state.month);
        if (state.year && state.year !== '') params.append('year', state.year);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/cost-report-pdf?' + queryString : '/admin/analytics/cost-report-pdf';
    window.open(url, '_blank');
}

// Export Status Report to PDF
function exportStatusReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    if (state.period && state.period !== '') {
        params.append('period', state.period);
        
        if (state.period === 'monthly') {
            if (state.month && state.month !== '') params.append('month', state.month);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'quarterly') {
            if (state.month_from && state.month_from !== '') params.append('month_from', state.month_from);
            if (state.month_to && state.month_to !== '') params.append('month_to', state.month_to);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'yearly') {
            if (state.year && state.year !== '') params.append('year', state.year);
        }
    } else {
        if (state.month && state.month !== '') params.append('month', state.month);
        if (state.year && state.year !== '') params.append('year', state.year);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/status-report-pdf?' + queryString : '/admin/analytics/status-report-pdf';
    window.open(url, '_blank');
}

// Export Trend Report to PDF
function exportTrendReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    if (state.period && state.period !== '') {
        params.append('period', state.period);
        
        if (state.period === 'monthly') {
            if (state.month && state.month !== '') params.append('month', state.month);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'quarterly') {
            if (state.month_from && state.month_from !== '') params.append('month_from', state.month_from);
            if (state.month_to && state.month_to !== '') params.append('month_to', state.month_to);
            if (state.year && state.year !== '') params.append('year', state.year);
        } else if (state.period === 'yearly') {
            if (state.year && state.year !== '') params.append('year', state.year);
        }
    } else {
        if (state.month && state.month !== '') params.append('month', state.month);
        if (state.year && state.year !== '') params.append('year', state.year);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/trend-report-pdf?' + queryString : '/admin/analytics/trend-report-pdf';
    window.open(url, '_blank');
}
