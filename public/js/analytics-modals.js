// Analytics Modal Functions with SweetAlert2 and Charts

// Global variable to store current modal filter state
window.modalFilterState = {
    period: '',
    month: '',
    month_from: '',
    month_to: '',
    year: '',
    date_from: '',
    date_to: ''
};

// Helper function to generate YouTube-style date range filter HTML
function generateYouTubeStyleFilter(modalType, exportFunction) {
    // Get locations from window variable
    const locations = window.chartLocations || [];
    
    // Build location dropdown items
    let locationItems = '<li><a class="dropdown-item" href="#" onclick="setModalRoom(\'' + modalType + '\', \'all\', event)">All Rooms</a></li>';
    if (locations.length > 0) {
        locationItems += '<li><hr class="dropdown-divider"></li>';
        locations.forEach(function(location) {
            const escapedLocation = location.replace(/'/g, "\\'");
            locationItems += '<li><a class="dropdown-item" href="#" onclick="setModalRoom(\'' + modalType + '\', \'' + escapedLocation + '\', event)">' + location + '</a></li>';
        });
    }
    
    return `
        <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="${modalType}ModalRoomDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                        <i class="fas fa-door-open me-1"></i>
                        <span id="${modalType}ModalRoomLabel">All Rooms</span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="${modalType}ModalRoomDropdown" style="max-height: 300px; overflow-y: auto; min-width: 200px;">
                        ${locationItems}
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="${modalType}ModalRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <span id="${modalType}ModalRangeLabel">Last 6 months</span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="${modalType}ModalRangeDropdown" style="min-width: 220px;">
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'last7days', event)">Last 7 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'last28days', event)">Last 28 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'last90days', event)">Last 90 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'last6months', event)">Last 6 months</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'last12months', event)">Last 12 months</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'thisyear', event)">This year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'lastyear', event)">Last year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setModalRange('${modalType}', 'allyears', event)">All years</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-2">
                            <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                            <div class="mb-2">
                                <input type="date" id="${modalType}ModalCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                            </div>
                            <div class="mb-2">
                                <input type="date" id="${modalType}ModalCustomDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                            </div>
                            <button class="btn btn-primary btn-sm w-100" onclick="applyModalCustomRange('${modalType}', event)" style="font-size: 0.75rem;">Apply</button>
                        </li>
                    </ul>
                </div>
            </div>
            <button onclick="${exportFunction}()" class="btn btn-danger btn-sm" style="white-space: nowrap; font-size: 0.9rem;">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
            </button>
        </div>
    `;
}

// Set room filter for modal
window.setModalRoom = function(modalType, room, event) {
    if (event) event.preventDefault();
    
    // Update label
    const label = room === 'all' ? 'All Rooms' : room;
    document.getElementById(modalType + 'ModalRoomLabel').textContent = label;
    
    // Store in global state
    if (!window.modalFilterState) {
        window.modalFilterState = {};
    }
    window.modalFilterState.room = room;
    
    // Get current date range
    const dateFrom = window.modalFilterState.date_from ? new Date(window.modalFilterState.date_from) : new Date(new Date().setMonth(new Date().getMonth() - 6));
    const dateTo = window.modalFilterState.date_to ? new Date(window.modalFilterState.date_to) : new Date();
    
    // Fetch data with room filter
    fetchModalData(modalType, dateFrom, dateTo);
};

// Set room filter for status modal (alias for setModalRoom)
window.setModalRoomFilter = function(modalType, room, event) {
    window.setModalRoom(modalType, room, event);
};

// Universal modal date range setter
window.setModalRange = function(modalType, range, event) {
    if (event) event.preventDefault();
    
    var labels = {
        'last7days': 'Last 7 days',
        'last28days': 'Last 28 days',
        'last90days': 'Last 90 days',
        'last6months': 'Last 6 months',
        'last12months': 'Last 12 months',
        'thisyear': 'This year',
        'lastyear': 'Last year',
        'allyears': 'All years',
        'alltime': 'All time'
    };
    
    document.getElementById(modalType + 'ModalRangeLabel').textContent = labels[range] || range;
    
    var today = new Date();
    var dateFrom, dateTo;
    
    switch(range) {
        case 'last7days':
            dateFrom = new Date(today);
            dateFrom.setDate(today.getDate() - 7);
            dateTo = today;
            break;
        case 'last28days':
            dateFrom = new Date(today);
            dateFrom.setDate(today.getDate() - 28);
            dateTo = today;
            break;
        case 'last90days':
            dateFrom = new Date(today);
            dateFrom.setDate(today.getDate() - 90);
            dateTo = today;
            break;
        case 'last6months':
            dateFrom = new Date(today);
            dateFrom.setMonth(today.getMonth() - 6);
            dateTo = today;
            break;
        case 'last12months':
            dateFrom = new Date(today);
            dateFrom.setMonth(today.getMonth() - 12);
            dateTo = today;
            break;
        case 'thisyear':
            dateFrom = new Date(today.getFullYear(), 0, 1);
            dateTo = today;
            break;
        case 'lastyear':
            dateFrom = new Date(today.getFullYear() - 1, 0, 1);
            dateTo = new Date(today.getFullYear() - 1, 11, 31);
            break;
        case 'allyears':
            // Get all data from the beginning (2020 or earliest data)
            dateFrom = new Date(2020, 0, 1);
            dateTo = today;
            break;
        case 'alltime':
            // Get all data from the beginning (2020 or earliest data)
            dateFrom = new Date(2020, 0, 1);
            dateTo = today;
            break;
    }
    
    // Store in global state for export functions
    window.modalFilterState.date_from = dateFrom.toISOString().split('T')[0];
    window.modalFilterState.date_to = dateTo.toISOString().split('T')[0];
    
    fetchModalData(modalType, dateFrom, dateTo);
};

// Apply custom date range for modal
window.applyModalCustomRange = function(modalType, event) {
    if (event) event.preventDefault();
    
    var dateFromInput = document.getElementById(modalType + 'ModalCustomDateFrom').value;
    var dateToInput = document.getElementById(modalType + 'ModalCustomDateTo').value;
    
    if (!dateFromInput || !dateToInput) {
        alert('Please select both start and end dates');
        return;
    }
    
    var dateFrom = new Date(dateFromInput);
    var dateTo = new Date(dateToInput);
    
    if (dateFrom > dateTo) {
        alert('Start date must be before end date');
        return;
    }
    
    var fromStr = dateFrom.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    var toStr = dateTo.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById(modalType + 'ModalRangeLabel').textContent = fromStr + ' - ' + toStr;
    
    var dropdownEl = document.getElementById(modalType + 'ModalRangeDropdown');
    var dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
    if (dropdown) dropdown.hide();
    
    // Store in global state for export functions
    window.modalFilterState.date_from = dateFromInput;
    window.modalFilterState.date_to = dateToInput;
    
    fetchModalData(modalType, dateFrom, dateTo);
};

// Fetch data for modal based on date range
function fetchModalData(modalType, dateFrom, dateTo) {
    var dateFromStr = dateFrom.toISOString().split('T')[0];
    var dateToStr = dateTo.toISOString().split('T')[0];
    
    var params = new URLSearchParams();
    params.append('ajax', '1');
    params.append('date_from', dateFromStr);
    params.append('date_to', dateToStr);
    
    // Add room filter if set
    if (window.modalFilterState && window.modalFilterState.room && window.modalFilterState.room !== 'all') {
        params.append('room_filter', window.modalFilterState.room);
    }
    
    // Show loading overlay on chart area
    const chartContainer = document.querySelector('.swal2-html-container canvas')?.parentElement;
    if (chartContainer) {
        chartContainer.style.position = 'relative';
        chartContainer.style.opacity = '0.5';
    }
    
    fetch('/admin/analytics?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        window.chartLocations = data.chartLocations || [];
        window.chartCounts = data.chartCounts || [];
        window.chartCosts = data.chartCosts || [];
        window.chartStatuses = data.chartStatuses || [];
        window.chartStatusCounts = data.chartStatusCounts || [];
        window.statusReportIds = data.statusReportIds || {};
        window.monthlyStats = data.monthlyStats || [];
        window.monthlyCostData = data.monthlyCostData || [];
        window.locationDetailedStats = data.locationStatsDetailed || [];
        window.responseTimeStats = data.responseTimeStats || [];
        window.avgSubmittedToAssigned = data.avgSubmittedToAssigned || 0;
        window.avgAssignedToResolved = data.avgAssignedToResolved || 0;
        window.avgTotalTime = data.avgTotalTime || 0;
        
        // Update category data
        if (data.costByCategory) {
            window.categoryData = {
                categories: data.costByCategory.map(item => item.category),
                counts: data.costByCategory.map(item => item.count),
                costs: data.costByCategory.map(item => item.total_cost),
                avgCosts: data.costByCategory.map(item => item.avg_cost),
                percentages: data.costByCategory.map(item => item.percentage)
            };
        }
        
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
        } else if (modalType === 'period') {
            updatePeriodComparisonModalContent();
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        
        // Remove loading overlay
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        alert('Failed to load filtered data. Please try again.');
    });
}

// Show Location Details Modal
function showLocationDetailsModal(filterType = null) {
    const locations = window.chartLocations || [];
    const counts = window.chartCounts || [];
    const costs = window.chartCosts || [];
    const detailedStats = window.locationDetailedStats || [];
    
    // Get category data
    const categoryData = window.categoryData || {};
    const categories = categoryData.categories || [];
    const categoryCounts = categoryData.counts || [];
    const categoryCosts = categoryData.costs || [];
    const categoryAvgCosts = categoryData.avgCosts || [];
    const categoryPercentages = categoryData.percentages || [];
    
    let totalRepairs = 0;
    let totalCost = 0;
    
    // Sort detailed stats by location and cost
    const sortedStats = [...detailedStats].sort((a, b) => {
        if (a.location !== b.location) {
            return a.location.localeCompare(b.location);
        }
        return (b.cost || 0) - (a.cost || 0);
    });
    
    totalRepairs = sortedStats.length;
    sortedStats.forEach((item) => {
        totalCost += parseFloat(item.cost) || 0;
    });
    
    // Build comprehensive table with Location, Category, Ticket, Issue, Damaged Part, Cost, and Date
    let tableRows = '';
    sortedStats.forEach((item) => {
        const itemCost = parseFloat(item.cost) || 0;
        const itemCategory = item.category || 'Uncategorized';
        const ticketNumber = '#' + String(item.id).padStart(4, '0');
        const issue = item.title || 'N/A';
        const damagedPart = item.damaged_part || 'N/A';
        const resolvedDate = item.resolved_at || 'N/A';
        
        tableRows += `
            <tr>
                <td><strong>${item.location}</strong></td>
                <td><span class="badge bg-info">${itemCategory}</span></td>
                <td><span class="badge bg-primary">${ticketNumber}</span></td>
                <td>${issue}</td>
                <td>${damagedPart}</td>
                <td class="text-end">₱${itemCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-center" style="font-size: 0.85rem;">${resolvedDate}</td>
            </tr>
        `;
    });
    
    const filterLabel = getFilterLabel(filterType);
    
    Swal.fire({
        title: '<i class="fas fa-chart-pie me-2"></i>Repairs Breakdown - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                ${generateYouTubeStyleFilter('location', 'exportLocationReportToPDF')}
                
                <!-- Single Pie Chart -->
                <div class="mb-4">
                    <div style="height: 400px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <canvas id="modalLocationChart"></canvas>
                    </div>
                </div>
                
                <!-- Summary Statistics -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background: #f0f4ff; border-radius: 8px; border-left: 4px solid #667eea;">
                            <div style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">Total Locations</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;">${locations.length}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background: #fff8f0; border-radius: 8px; border-left: 4px solid #f39c12;">
                            <div style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">Total Categories</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #f39c12;">${categories.length}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background: #f0fff4; border-radius: 8px; border-left: 4px solid #27ae60;">
                            <div style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">Total Tickets</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">${sortedStats.length}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Comprehensive Breakdown Table -->
                <div class="mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm" id="comprehensiveReportTable">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-map-marker-alt me-1"></i>Location</th>
                                    <th><i class="fas fa-tags me-1"></i>Category</th>
                                    <th><i class="fas fa-ticket-alt me-1"></i>Ticket #</th>
                                    <th><i class="fas fa-exclamation-circle me-1"></i>Issue</th>
                                    <th><i class="fas fa-wrench me-1"></i>Damaged Part</th>
                                    <th class="text-end"><i class="fas fa-peso-sign me-1"></i>Cost</th>
                                    <th class="text-center"><i class="fas fa-calendar-check me-1"></i>Date Fixed</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="5">TOTAL (${totalRepairs} tickets)</td>
                                    <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        `,
        width: '90%',
        heightAuto: false,
        padding: '0',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-analytics-modal',
            popup: 'swal-wide-popup'
        },
        didOpen: () => {
            // Single Location Pie Chart
            const locationCtx = document.getElementById('modalLocationChart');
            new Chart(locationCtx, {
                type: 'pie',
                data: {
                    labels: locations,
                    datasets: [{
                        data: counts,
                        backgroundColor: [
                            '#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF',
                            '#FF9F40','#C9CBCF','#667eea','#764ba2','#f093fb',
                            '#4facfe','#43e97b','#fa709a','#fee140','#30cfd0'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { 
                                font: { size: 11 },
                                padding: 10
                            } 
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const cost = costs[context.dataIndex] || 0;
                                    const percentage = ((value / totalRepairs) * 100).toFixed(1);
                                    
                                    return [
                                        `Repairs: ${value} (${percentage}%)`,
                                        `Total Cost: ₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`,
                                        `Avg Cost: ₱${(cost / value).toLocaleString('en-PH', {minimumFractionDigits: 2})}`
                                    ];
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12
                        },
                        title: {
                            display: true,
                            text: 'Repairs Distribution by Location',
                            font: { size: 16, weight: 'bold' },
                            padding: { bottom: 20 }
                        }
                    }
                }
            });
        }
    });
}

// Show Category Details Modal
function showCategoryDetailsModal(filterType = null) {
    const categoryData = window.categoryData || {};
    const categories = categoryData.categories || [];
    const counts = categoryData.counts || [];
    const costs = categoryData.costs || [];
    const avgCosts = categoryData.avgCosts || [];
    const percentages = categoryData.percentages || [];
    
    let totalTickets = 0;
    let totalCost = 0;
    
    // Combine data and sort by total cost
    const data = categories.map((cat, idx) => ({
        category: cat,
        count: counts[idx] || 0,
        cost: costs[idx] || 0,
        avgCost: avgCosts[idx] || 0,
        percentage: percentages[idx] || 0
    })).sort((a, b) => b.cost - a.cost);
    
    data.forEach((item) => {
        totalTickets += parseInt(item.count) || 0;
        totalCost += parseFloat(item.cost) || 0;
    });
    
    let tableRows = '';
    data.forEach((item) => {
        tableRows += `
            <tr>
                <td><strong>${item.category}</strong></td>
                <td class="text-center"><span class="badge bg-primary">${item.count}</span></td>
                <td class="text-end">₱${item.cost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">₱${item.avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end"><span class="badge bg-info">${item.percentage.toFixed(1)}%</span></td>
            </tr>
        `;
    });
    
    Swal.fire({
        title: '<i class="fas fa-tags me-2"></i>Cost Breakdown by Category - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalCategoryChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="categoryReportTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Category</th>
                                <th class="text-center">Total Tickets</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Avg Cost</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center">${totalTickets}</td>
                                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-end">-</td>
                                <td class="text-end">100%</td>
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
            const ctx = document.getElementById('modalCategoryChart');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categories,
                    datasets: [{
                        data: costs,
                        backgroundColor: [
                            '#667eea', '#764ba2', '#f093fb', '#4facfe',
                            '#43e97b', '#fa709a', '#fee140', '#30cfd0',
                            '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const category = categories[index];
                                    const count = counts[index];
                                    const cost = costs[index];
                                    const avgCost = avgCosts[index];
                                    const percentage = percentages[index];
                                    
                                    return [
                                        `${category}`,
                                        `Tickets: ${count}`,
                                        `Total: ₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`,
                                        `Avg: ₱${avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`,
                                        `${percentage.toFixed(1)}%`
                                    ];
                                }
                            }
                        }
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
                ${generateYouTubeStyleFilter('cost', 'exportCostReportToPDF')}
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
    const statusReportIds = window.statusReportIds || {};
    
    // Define the desired order
    const statusOrder = ['Pending', 'Assigned', 'In Progress', 'Resolved'];
    const itemsPerPage = 5;
    
    let tableRows = '';
    statusOrder.forEach(function(orderedStatus) {
        const index = statuses.indexOf(orderedStatus);
        if (index !== -1) {
            const status = statuses[index];
            const count = statusCounts[index] || 0;
            const issuesString = statusReportIds[status] || 'N/A';
            const issuesArray = issuesString.split(', ');
            
            let badgeClass = 'bg-secondary';
            if (status === 'Resolved' || status === 'Completed') badgeClass = 'bg-success';
            else if (status === 'Pending') badgeClass = 'bg-warning';
            else if (status === 'Assigned') badgeClass = 'bg-info';
            else if (status === 'In Progress') badgeClass = 'bg-primary';
            else if (status === 'Rejected' || status === 'Cancelled') badgeClass = 'bg-danger';
            
            let issuesList = '<div id="issues-' + status.replace(/\s+/g, '-') + '">';
            
            // Show first 5 items
            issuesArray.slice(0, itemsPerPage).forEach(function(issue) {
                issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
            });
            
            // Hidden items
            if (issuesArray.length > itemsPerPage) {
                issuesList += '<div id="hidden-issues-' + status.replace(/\s+/g, '-') + '" style="display: none;">';
                issuesArray.slice(itemsPerPage).forEach(function(issue) {
                    issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
                });
                issuesList += '</div>';
                issuesList += '<button class="btn btn-sm btn-link p-0 mt-1" onclick="toggleStatusIssues(\'' + status.replace(/\s+/g, '-') + '\')" id="toggle-btn-' + status.replace(/\s+/g, '-') + '" style="font-size: 0.8rem;">Show More (' + (issuesArray.length - itemsPerPage) + ')</button>';
            }
            
            issuesList += '</div>';
            
            tableRows += `
                <tr>
                    <td><span class="badge ${badgeClass}">${status}</span></td>
                    <td class="text-center"><strong>${count}</strong></td>
                    <td>${issuesList}</td>
                </tr>
            `;
        }
    });
    
    // Toggle function
    window.toggleStatusIssues = function(statusId) {
        const hiddenDiv = document.getElementById('hidden-issues-' + statusId);
        const toggleBtn = document.getElementById('toggle-btn-' + statusId);
        
        if (hiddenDiv && toggleBtn) {
            if (hiddenDiv.style.display === 'none') {
                hiddenDiv.style.display = 'block';
                toggleBtn.textContent = 'Show Less';
            } else {
                hiddenDiv.style.display = 'none';
                const hiddenCount = hiddenDiv.querySelectorAll('div').length;
                toggleBtn.textContent = 'Show More (' + hiddenCount + ')';
            }
        }
    };
    
    Swal.fire({
        title: '<i class="fas fa-tasks me-2"></i>Status Distribution - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                ${generateYouTubeStyleFilter('status', 'exportStatusPDF')}
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalStatusChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Status</th>
                                <th class="text-center">Count</th>
                                <th>Issue</th>
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

// Show Period Comparison Modal
function showPeriodComparisonModal(filterType = null) {
    // Determine if we should use yearly or monthly grouping
    const state = window.modalFilterState || {};
    const dateFrom = state.date_from ? new Date(state.date_from) : new Date(new Date().setMonth(new Date().getMonth() - 6));
    const dateTo = state.date_to ? new Date(state.date_to) : new Date();
    const yearSpan = dateTo.getFullYear() - dateFrom.getFullYear();
    const isAllYears = yearSpan > 2;
    
    let labels = [];
    let keys = [];
    let costs = [];
    let counts = [];
    
    // Get monthly cost data from window if available
    const monthlyCostData = window.monthlyCostData || [];
    
    if (isAllYears) {
        // Group by year for "All years" view
        const startYear = dateFrom.getFullYear();
        const endYear = dateTo.getFullYear();
        
        for (let year = startYear; year <= endYear; year++) {
            keys.push(year.toString());
            labels.push(year.toString());
            costs.push(0);
            counts.push(0);
        }
        
        // Aggregate monthly data into yearly data
        monthlyCostData.forEach(item => {
            const itemYear = item.month.substring(0, 4); // Extract year from YYYY-MM
            const yearIndex = keys.indexOf(itemYear);
            if (yearIndex !== -1) {
                costs[yearIndex] += parseFloat(item.total_cost) || 0;
                counts[yearIndex] += parseInt(item.count) || 0;
            }
        });
    } else {
        // Group by month for other views (default: last 6 months)
        const currentDate = new Date();
        
        for (let i = 5; i >= 0; i--) {
            const d = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const key = d.toISOString().slice(0, 7); // YYYY-MM format
            const label = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
            keys.push(key);
            labels.push(label);
            costs.push(0);
            counts.push(0);
        }
        
        // Populate data from server
        monthlyCostData.forEach(item => {
            const monthIndex = keys.indexOf(item.month);
            if (monthIndex !== -1) {
                costs[monthIndex] = parseFloat(item.total_cost) || 0;
                counts[monthIndex] = parseInt(item.count) || 0;
            }
        });
    }
    
    // Calculate statistics
    const totalCost = costs.reduce((sum, cost) => sum + cost, 0);
    const totalCount = counts.reduce((sum, count) => sum + count, 0);
    const avgCostPerPeriod = costs.length > 0 ? totalCost / costs.length : 0;
    const avgCostPerRepair = totalCount > 0 ? totalCost / totalCount : 0;
    
    // Find highest and lowest periods
    let highestIdx = 0, lowestIdx = 0;
    for (let i = 1; i < costs.length; i++) {
        if (costs[i] > costs[highestIdx]) highestIdx = i;
        if (costs[i] < costs[lowestIdx]) lowestIdx = i;
    }
    
    // Build table rows
    let tableRows = '';
    labels.forEach((label, idx) => {
        const cost = costs[idx];
        const count = counts[idx];
        const avgPerRepair = count > 0 ? cost / count : 0;
        const percentOfTotal = totalCost > 0 ? ((cost / totalCost) * 100).toFixed(1) : 0;
        const periodKey = keys[idx]; // Store the period key (YYYY-MM or YYYY)
        
        let trendIcon = '';
        if (idx > 0) {
            const prevCost = costs[idx - 1];
            if (cost > prevCost) {
                trendIcon = '<i class="fas fa-arrow-up text-danger"></i>';
            } else if (cost < prevCost) {
                trendIcon = '<i class="fas fa-arrow-down text-success"></i>';
            } else {
                trendIcon = '<i class="fas fa-minus text-secondary"></i>';
            }
        }
        
        // Make row clickable if there are repairs
        const rowClass = count > 0 ? 'cursor-pointer period-breakdown-row' : '';
        const rowClick = count > 0 ? `onclick="showPeriodBreakdownModal('${periodKey}', '${label}')"` : '';
        
        tableRows += `
            <tr class="${rowClass}" ${rowClick} style="${count > 0 ? 'cursor: pointer;' : ''}">
                <td><strong>${label}</strong></td>
                <td class="text-center">${count}</td>
                <td class="text-end">₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${avgPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-center">${percentOfTotal}%</td>
                <td class="text-center">${trendIcon}</td>
            </tr>
        `;
    });
    
    const periodLabel = isAllYears ? 'Years' : (costs.length + ' Months');
    
    // Get current filter state
    const state2 = window.modalFilterState;
    
    Swal.fire({
        title: '<i class="fas fa-chart-line me-2"></i>Period Comparison - Detailed Report',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                ${generateYouTubeStyleFilter('period', 'exportPeriodComparisonToPDF')}
                <div style="height: 400px; margin-bottom: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <canvas id="modalPeriodComparisonChart"></canvas>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Highest Cost ${isAllYears ? 'Year' : 'Month'}</h6>
                                <h4 class="text-danger mb-2">${labels[highestIdx]}</h4>
                                <h5 class="mb-0">₱${costs[highestIdx].toLocaleString('en-PH', {minimumFractionDigits: 2})}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Lowest Cost ${isAllYears ? 'Year' : 'Month'}</h6>
                                <h4 class="text-success mb-2">${labels[lowestIdx]}</h4>
                                <h5 class="mb-0">₱${costs[lowestIdx].toLocaleString('en-PH', {minimumFractionDigits: 2})}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-2">Average Cost/${isAllYears ? 'Year' : 'Month'}</h6>
                                <h4 class="text-primary mb-2">₱${avgCostPerPeriod.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h4>
                                <h5 class="mb-0" style="font-size: 0.9rem;">₱${avgCostPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})} per repair</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Period</th>
                                <th class="text-center">Repairs</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Avg per Repair</th>
                                <th class="text-center">% of Total</th>
                                <th class="text-center">Trend</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr class="cursor-pointer period-breakdown-row" onclick="showAllPeriodsBreakdown()" style="cursor: pointer;">
                                <td>TOTAL (${periodLabel})</td>
                                <td class="text-center">${totalCount}</td>
                                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                <td class="text-end">₱${avgCostPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
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
            const ctx = document.getElementById('modalPeriodComparisonChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
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
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y || 0;
                                    const repairs = counts[context.dataIndex] || 0;
                                    const avgCost = repairs > 0 ? (value / repairs) : 0;
                                    return [
                                        'Total Cost: ₱' + value.toLocaleString('en-PH', {minimumFractionDigits: 2}),
                                        'Total Repairs: ' + repairs,
                                        'Avg Cost: ₱' + avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
    });
}

// Show Period Breakdown Modal - Shows individual repairs for a specific period
function showPeriodBreakdownModal(periodKey, periodLabel) {
    // Get current filter state
    const state = window.modalFilterState || {};
    const location = state.location || '';
    const category = state.category || '';
    
    // Build query parameters
    const params = new URLSearchParams({
        period: periodKey,
        location: location,
        category: category
    });
    
    // Show loading state
    Swal.fire({
        title: `<i class="fas fa-spinner fa-spin me-2"></i>Loading ${periodLabel} Breakdown...`,
        html: '<p class="text-muted">Fetching repair details...</p>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Fetch breakdown data from server
    fetch(`/admin/analytics/period-breakdown?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (!data.repairs || data.repairs.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Repairs Found',
                    text: `No repair data available for ${periodLabel}`,
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Build table rows for individual repairs
            let tableRows = '';
            let totalCost = 0;
            
            data.repairs.forEach((repair, idx) => {
                const cost = parseFloat(repair.cost) || 0;
                totalCost += cost;
                const date = new Date(repair.created_at).toLocaleDateString('en-PH', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                // Format ticket number from id
                const ticketNumber = '#' + String(repair.id).padStart(4, '0');
                
                // Status badge color
                const statusColors = {
                    'Pending': 'warning',
                    'Assigned': 'info',
                    'In Progress': 'primary',
                    'Resolved': 'success'
                };
                const statusColor = statusColors[repair.status] || 'secondary';
                
                tableRows += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${date}</td>
                        <td>${ticketNumber}</td>
                        <td>${repair.title || 'N/A'}</td>
                        <td>${repair.location || 'N/A'}</td>
                        <td><span class="badge bg-${statusColor}">${repair.status}</span></td>
                        <td>${repair.damaged_part || 'N/A'}</td>
                        <td class="text-end">${cost > 0 ? '₱' + cost.toLocaleString('en-PH', {minimumFractionDigits: 2}) : 'N/A'}</td>
                    </tr>
                `;
            });
            
            // Show the breakdown modal
            Swal.fire({
                title: `<i class="fas fa-list-alt me-2"></i>${periodLabel} - Repair Breakdown`,
                html: `
                    <div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-secondary" onclick="Swal.close(); showPeriodComparisonModal();">
                                <i class="fas fa-arrow-left me-2"></i>Back to Period Comparison
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportPeriodBreakdownPDF('${periodKey}', '${periodLabel}');">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                        </div>
                        <div class="alert alert-info mb-3">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Total Repairs</h6>
                                    <h4 class="mb-0">${data.repairs.length}</h4>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Total Cost</h6>
                                    <h4 class="mb-0">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h4>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Average Cost</h6>
                                    <h4 class="mb-0">₱${totalCost > 0 ? (totalCost / data.repairs.filter(r => r.cost > 0).length).toLocaleString('en-PH', {minimumFractionDigits: 2}) : '0.00'}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Ticket</th>
                                        <th>Issue</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Damaged Part</th>
                                        <th class="text-end">Cost</th>
                                    </tr>
                                </thead>
                                <tbody>${tableRows}</tbody>
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td colspan="7" class="text-end">TOTAL:</td>
                                        <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `,
                width: '90%',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'swal-analytics-modal',
                    popup: 'swal-wide-popup'
                }
            });
        })
        .catch(error => {
            console.error('Error fetching period breakdown:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load repair breakdown. Please try again.',
                confirmButtonText: 'OK'
            });
        });
}

// Show All Periods Breakdown - Shows all repairs across all periods
function showAllPeriodsBreakdown() {
    // Get current filter state
    const state = window.modalFilterState || {};
    const location = state.location || '';
    const category = state.category || '';
    const dateFrom = state.date_from || '';
    const dateTo = state.date_to || '';
    
    // Build query parameters
    const params = new URLSearchParams({
        location: location,
        category: category,
        date_from: dateFrom,
        date_to: dateTo
    });
    
    // Show loading state
    Swal.fire({
        title: `<i class="fas fa-spinner fa-spin me-2"></i>Loading All Repairs...`,
        html: '<p class="text-muted">Fetching all repair details...</p>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Fetch breakdown data from server
    fetch(`/admin/analytics/all-periods-breakdown?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (!data.repairs || data.repairs.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Repairs Found',
                    text: 'No repair data available for the selected period',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Build table rows for individual repairs
            let tableRows = '';
            let totalCost = 0;
            
            data.repairs.forEach((repair, idx) => {
                const cost = parseFloat(repair.cost) || 0;
                totalCost += cost;
                const date = new Date(repair.created_at).toLocaleDateString('en-PH', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                // Format ticket number from id
                const ticketNumber = '#' + String(repair.id).padStart(4, '0');
                
                // Status badge color
                const statusColors = {
                    'Pending': 'warning',
                    'Assigned': 'info',
                    'In Progress': 'primary',
                    'Resolved': 'success'
                };
                const statusColor = statusColors[repair.status] || 'secondary';
                
                tableRows += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${date}</td>
                        <td>${ticketNumber}</td>
                        <td>${repair.title || 'N/A'}</td>
                        <td>${repair.location || 'N/A'}</td>
                        <td><span class="badge bg-${statusColor}">${repair.status}</span></td>
                        <td>${repair.damaged_part || 'N/A'}</td>
                        <td class="text-end">${cost > 0 ? '₱' + cost.toLocaleString('en-PH', {minimumFractionDigits: 2}) : 'N/A'}</td>
                    </tr>
                `;
            });
            
            // Show the breakdown modal
            Swal.fire({
                title: `<i class="fas fa-list-alt me-2"></i>All Periods - Complete Repair Breakdown`,
                html: `
                    <div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <button class="btn btn-secondary btn-sm" onclick="Swal.close(); showPeriodComparisonModal();">
                                <i class="fas fa-arrow-left me-1"></i> Back to Period Comparison
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="exportAllPeriodsBreakdownPDF()">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </button>
                        </div>
                        <div class="alert alert-info mb-3">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Total Repairs</h6>
                                    <h4 class="mb-0">${data.repairs.length}</h4>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Total Cost</h6>
                                    <h4 class="mb-0">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</h4>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-1">Average Cost</h6>
                                    <h4 class="mb-0">₱${totalCost > 0 ? (totalCost / data.repairs.filter(r => r.cost > 0).length).toLocaleString('en-PH', {minimumFractionDigits: 2}) : '0.00'}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Ticket</th>
                                        <th>Issue</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Damaged Part</th>
                                        <th class="text-end">Cost</th>
                                    </tr>
                                </thead>
                                <tbody>${tableRows}</tbody>
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td colspan="7" class="text-end">TOTAL:</td>
                                        <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `,
                width: '90%',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'swal-analytics-modal',
                    popup: 'swal-wide-popup'
                }
            });
        })
        .catch(error => {
            console.error('Error fetching all periods breakdown:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load repair breakdown. Please try again.',
                confirmButtonText: 'OK'
            });
        });
}

// Export Period Breakdown to PDF
function exportPeriodBreakdownPDF(periodKey, periodLabel) {
    const state = window.modalFilterState || {};
    const location = state.location || '';
    const category = state.category || '';
    
    const params = new URLSearchParams({
        period: periodKey,
        period_label: periodLabel,
        location: location,
        category: category
    });
    
    window.open(`/admin/analytics/period-breakdown-pdf?${params.toString()}`, '_blank');
}

// Export All Periods Breakdown to PDF
function exportAllPeriodsBreakdownPDF() {
    const state = window.modalFilterState || {};
    const location = state.location || '';
    const category = state.category || '';
    const dateFrom = state.date_from || '';
    const dateTo = state.date_to || '';
    
    const params = new URLSearchParams({
        location: location,
        category: category,
        date_from: dateFrom,
        date_to: dateTo
    });
    
    window.open(`/admin/analytics/all-periods-breakdown-pdf?${params.toString()}`, '_blank');
}

// View Repair Details - Opens the concern/report detail modal
function viewRepairDetails(reportId) {
    // Check if viewConcern function exists (from my.blade.php)
    if (typeof viewConcern === 'function') {
        viewConcern(reportId);
    } else {
        // Fallback: redirect to the concern page
        window.location.href = `/concerns/${reportId}`;
    }
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
    
    // Group by month with status breakdown
    const monthData = {};
    monthLabels.forEach(m => {
        monthData[m.key] = { label: m.label, issues: {}, total: 0 };
    });
    
    monthly.forEach(item => {
        if (monthData[item.month]) {
            if (!monthData[item.month].issues[item.title]) {
                monthData[item.month].issues[item.title] = {
                    total: 0,
                    Pending: { count: 0, tickets: [], damagedParts: [] },
                    Assigned: { count: 0, tickets: [], damagedParts: [] },
                    'In Progress': { count: 0, tickets: [], damagedParts: [] },
                    Resolved: { count: 0, tickets: [], damagedParts: [] }
                };
            }
            const count = parseInt(item.count) || 0;
            const ticketIds = item.ticket_ids ? item.ticket_ids.split(',') : [];
            const damagedParts = item.damaged_parts ? item.damaged_parts.split('|') : [];
            
            monthData[item.month].issues[item.title].total += count;
            
            if (!monthData[item.month].issues[item.title][item.status]) {
                monthData[item.month].issues[item.title][item.status] = { count: 0, tickets: [], damagedParts: [] };
            }
            
            monthData[item.month].issues[item.title][item.status].count += count;
            monthData[item.month].issues[item.title][item.status].tickets.push(...ticketIds);
            monthData[item.month].issues[item.title][item.status].damagedParts.push(...damagedParts);
            monthData[item.month].total += count;
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
    
    // Build table rows with status breakdown
    let tableRows = '';
    Object.entries(monthData).forEach(([key, data]) => {
        if (Object.keys(data.issues).length === 0) {
            tableRows += `
                <tr>
                    <td><strong>${data.label}</strong></td>
                    <td colspan="5" class="text-center text-muted">No repairs recorded</td>
                </tr>
            `;
        } else {
            Object.entries(data.issues).forEach(([issue, statusData], idx) => {
                // Helper function to format ticket list with damage parts
                const formatTicketList = (statusObj) => {
                    if (!statusObj || !statusObj.tickets || statusObj.tickets.length === 0) {
                        return '<span class="badge bg-secondary">0</span>';
                    }
                    
                    const tickets = statusObj.tickets;
                    const parts = statusObj.damagedParts || [];
                    const items = tickets.map((ticketId, i) => {
                        const formattedTicket = '#' + String(ticketId).padStart(4, '0');
                        const part = parts[i] || 'N/A';
                        return `${formattedTicket} ${part}`;
                    });
                    
                    return `<div style="text-align: left; max-width: 200px;">
                        ${items.map(item => `<div style="margin-bottom: 2px; font-size: 0.85em;">${item}</div>`).join('')}
                    </div>`;
                };
                
                tableRows += `
                    <tr>
                        ${idx === 0 ? `<td rowspan="${Object.keys(data.issues).length}"><strong>${data.label}</strong></td>` : ''}
                        <td>${issue}</td>
                        <td class="text-center"><span class="badge bg-primary">${statusData.total}</span></td>
                        <td class="text-center">${formatTicketList(statusData.Resolved)}</td>
                        <td class="text-center">${formatTicketList(statusData['In Progress'])}</td>
                        <td class="text-center">${formatTicketList(statusData.Pending)}</td>
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
                ${generateYouTubeStyleFilter('trend', 'exportTrendReportToPDF')}
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
                                <th class="text-center">Total</th>
                                <th class="text-center">Resolved</th>
                                <th class="text-center">In Progress</th>
                                <th class="text-center">Pending</th>
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
    
    // Get filter values from modal state
    const state = window.modalFilterState || {};
    
    console.log('Export PDF - Modal Filter State:', state); // Debug log
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    // Add room filter if set
    if (state.room && state.room !== 'all') {
        params.append('room_filter', state.room);
    }
    
    // Open PDF in new window
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/location-report-pdf?' + queryString : '/admin/analytics/location-report-pdf';
    console.log('Export PDF URL:', url); // Debug log
    window.open(url, '_blank');
}

// Export Status Distribution & Response Time Report to PDF
function exportStatusPDF() {
    const params = new URLSearchParams();
    
    // Get filter values from modal state
    const state = window.modalFilterState || {};
    
    console.log('Export Status PDF - Modal Filter State:', state); // Debug log
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    // Add room filter if set
    if (state.room && state.room !== 'all') {
        params.append('room_filter', state.room);
    }
    
    // Open PDF in new window
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/status-distribution-pdf?' + queryString : '/admin/analytics/status-distribution-pdf';
    console.log('Export Status PDF URL:', url); // Debug log
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
    
    // Save all values to state (preserve date_from and date_to if they exist)
    window.modalFilterState = {
        period: period,
        month: month,
        month_from: monthFrom,
        month_to: monthTo,
        year: year,
        date_from: window.modalFilterState?.date_from || '',
        date_to: window.modalFilterState?.date_to || ''
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
        window.statusReportIds = data.statusReportIds || {};
        window.monthlyStats = data.monthlyStats || [];
        window.monthlyCostData = data.monthlyCostData || [];
        window.locationDetailedStats = data.locationStats || [];
        window.responseTimeStats = data.responseTimeStats || [];
        window.avgSubmittedToAssigned = data.avgSubmittedToAssigned || 0;
        window.avgAssignedToResolved = data.avgAssignedToResolved || 0;
        window.avgTotalTime = data.avgTotalTime || 0;
        
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
        } else if (modalType === 'period') {
            updatePeriodComparisonModalContent();
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
        year: '',
        date_from: '',
        date_to: ''
    };
    
    if (document.getElementById('modal_period')) document.getElementById('modal_period').value = '';
    if (document.getElementById('modal_month')) document.getElementById('modal_month').value = '';
    if (document.getElementById('modal_month_from')) document.getElementById('modal_month_from').value = '';
    if (document.getElementById('modal_month_to')) document.getElementById('modal_month_to').value = '';
    if (document.getElementById('modal_year')) document.getElementById('modal_year').value = '';
    
    // Reset the label to default
    const labelElement = document.getElementById(modalType + 'ModalRangeLabel');
    if (labelElement) {
        labelElement.textContent = 'Last 6 months';
    }
    
    // Set default range to last 6 months
    setModalRange(modalType, 'last6months', null);
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
        window.statusReportIds = data.statusReportIds || {};
        window.monthlyStats = data.monthlyStats || [];
        window.monthlyCostData = data.monthlyCostData || [];
        window.locationDetailedStats = data.locationStats || [];
        window.responseTimeStats = data.responseTimeStats || [];
        window.avgSubmittedToAssigned = data.avgSubmittedToAssigned || 0;
        window.avgAssignedToResolved = data.avgAssignedToResolved || 0;
        window.avgTotalTime = data.avgTotalTime || 0;
        
        // Re-render the appropriate modal with new data
        if (modalType === 'location') {
            showLocationDetailsModal(filterType);
        } else if (modalType === 'cost') {
            showCostDetailsModal(filterType);
        } else if (modalType === 'status') {
            showStatusDetailsModal(filterType);
        } else if (modalType === 'trend') {
            showMonthlyTrendModal(filterType);
        } else if (modalType === 'period') {
            showPeriodComparisonModal(filterType);
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
    const costs = window.chartCosts || [];
    const detailedStats = window.locationDetailedStats || [];
    
    // Get category data
    const categoryData = window.categoryData || {};
    const categories = categoryData.categories || [];
    
    let totalRepairs = 0;
    let totalCost = 0;
    
    // Sort detailed stats by location and cost
    const sortedStats = [...detailedStats].sort((a, b) => {
        if (a.location !== b.location) {
            return a.location.localeCompare(b.location);
        }
        return (b.cost || 0) - (a.cost || 0);
    });
    
    totalRepairs = sortedStats.length;
    sortedStats.forEach((item) => {
        totalCost += parseFloat(item.cost) || 0;
    });
    
    // Build comprehensive table with Location, Category, Ticket, Issue, Damaged Part, Cost, and Date
    let tableRows = '';
    sortedStats.forEach((item) => {
        const itemCost = parseFloat(item.cost) || 0;
        const itemCategory = item.category || 'Uncategorized';
        const ticketNumber = '#' + String(item.id).padStart(4, '0');
        const issue = item.title || 'N/A';
        const damagedPart = item.damaged_part || 'N/A';
        const resolvedDate = item.resolved_at || 'N/A';
        
        tableRows += `
            <tr>
                <td><strong>${item.location}</strong></td>
                <td><span class="badge bg-info">${itemCategory}</span></td>
                <td><span class="badge bg-primary">${ticketNumber}</span></td>
                <td>${issue}</td>
                <td>${damagedPart}</td>
                <td class="text-end">₱${itemCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-center" style="font-size: 0.85rem;">${resolvedDate}</td>
            </tr>
        `;
    });
    
    // Update summary statistics
    const modalContainer = document.querySelector('.swal2-html-container');
    if (modalContainer) {
        const summaryCards = modalContainer.querySelectorAll('.row.mb-3 .col-md-4');
        if (summaryCards.length >= 3) {
            // Update Total Locations
            const locationsValue = summaryCards[0].querySelector('div[style*="font-size: 1.5rem"]');
            if (locationsValue) locationsValue.textContent = locations.length;
            
            // Update Total Categories
            const categoriesValue = summaryCards[1].querySelector('div[style*="font-size: 1.5rem"]');
            if (categoriesValue) categoriesValue.textContent = categories.length;
            
            // Update Total Tickets
            const ticketsValue = summaryCards[2].querySelector('div[style*="font-size: 1.5rem"]');
            if (ticketsValue) ticketsValue.textContent = sortedStats.length;
        }
    }
    
    // Update table
    const tbody = document.querySelector('#comprehensiveReportTable tbody');
    const tfoot = document.querySelector('#comprehensiveReportTable tfoot');
    if (tbody) tbody.innerHTML = tableRows;
    if (tfoot) {
        tfoot.innerHTML = `
            <tr>
                <td colspan="5">TOTAL (${totalRepairs} tickets)</td>
                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td></td>
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
                    backgroundColor: [
                        '#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF',
                        '#FF9F40','#C9CBCF','#667eea','#764ba2','#f093fb',
                        '#4facfe','#43e97b','#fa709a','#fee140','#30cfd0'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'right',
                        labels: { 
                            font: { size: 11 },
                            padding: 10
                        } 
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const cost = costs[context.dataIndex] || 0;
                                const percentage = ((value / totalRepairs) * 100).toFixed(1);
                                
                                return [
                                    `Repairs: ${value} (${percentage}%)`,
                                    `Total Cost: ₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`,
                                    `Avg Cost: ₱${(cost / value).toLocaleString('en-PH', {minimumFractionDigits: 2})}`
                                ];
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12
                    },
                    title: {
                        display: true,
                        text: 'Repairs Distribution by Location',
                        font: { size: 16, weight: 'bold' },
                        padding: { bottom: 20 }
                    }
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
    const statusReportIds = window.statusReportIds || {};
    
    // Define the desired order
    const statusOrder = ['Pending', 'Assigned', 'In Progress', 'Resolved'];
    const itemsPerPage = 5;
    
    let tableRows = '';
    statusOrder.forEach(function(orderedStatus) {
        const index = statuses.indexOf(orderedStatus);
        if (index !== -1) {
            const status = statuses[index];
            const count = statusCounts[index] || 0;
            const issuesString = statusReportIds[status] || 'N/A';
            const issuesArray = issuesString.split(', ');
            
            let badgeClass = 'bg-secondary';
            if (status === 'Resolved' || status === 'Completed') badgeClass = 'bg-success';
            else if (status === 'Pending') badgeClass = 'bg-warning';
            else if (status === 'Assigned') badgeClass = 'bg-info';
            else if (status === 'In Progress') badgeClass = 'bg-primary';
            else if (status === 'Rejected' || status === 'Cancelled') badgeClass = 'bg-danger';
            
            let issuesList = '<div id="issues-' + status.replace(/\s+/g, '-') + '">';
            
            // Show first 5 items
            issuesArray.slice(0, itemsPerPage).forEach(function(issue) {
                issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
            });
            
            // Hidden items
            if (issuesArray.length > itemsPerPage) {
                issuesList += '<div id="hidden-issues-' + status.replace(/\s+/g, '-') + '" style="display: none;">';
                issuesArray.slice(itemsPerPage).forEach(function(issue) {
                    issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
                });
                issuesList += '</div>';
                issuesList += '<button class="btn btn-sm btn-link p-0 mt-1" onclick="toggleStatusIssues(\'' + status.replace(/\s+/g, '-') + '\')" id="toggle-btn-' + status.replace(/\s+/g, '-') + '" style="font-size: 0.8rem;">Show More (' + (issuesArray.length - itemsPerPage) + ')</button>';
            }
            
            issuesList += '</div>';
            
            tableRows += `
                <tr>
                    <td><span class="badge ${badgeClass}">${status}</span></td>
                    <td class="text-center"><strong>${count}</strong></td>
                    <td>${issuesList}</td>
                </tr>
            `;
        }
    });
    
    // Update table - use more specific selector within the modal
    const modalContainer = document.querySelector('.swal2-html-container');
    const tbody = modalContainer ? modalContainer.querySelector('.table-responsive .table tbody') : null;
    
    if (tbody) tbody.innerHTML = tableRows;
    
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
    
    // Update Response Time Details section
    const responseTimeStats = window.responseTimeStats || [];
    const avgSubmittedToAssigned = window.avgSubmittedToAssigned || 0;
    const avgAssignedToResolved = window.avgAssignedToResolved || 0;
    const avgTotalTime = window.avgTotalTime || 0;
    
    // Convert hours to HH:MM:SS format
    const formatHoursToTime = function(hours) {
        const totalSeconds = Math.floor(hours * 3600);
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    };
    
    // Update average metrics
    const avgMetrics = modalContainer ? modalContainer.querySelectorAll('.row.mb-3 .col-4') : [];
    if (avgMetrics.length >= 3) {
        avgMetrics[0].querySelector('div[style*="font-size: 1.5rem"]').textContent = formatHoursToTime(avgSubmittedToAssigned);
        avgMetrics[1].querySelector('div[style*="font-size: 1.5rem"]').textContent = formatHoursToTime(avgAssignedToResolved);
        avgMetrics[2].querySelector('div[style*="font-size: 1.5rem"]').textContent = formatHoursToTime(avgTotalTime);
    }
    
    // Update response time table
    let responseTimeRows = '';
    responseTimeStats.forEach(function(stat) {
        const ticketNum = '#' + String(stat.id).padStart(4, '0');
        responseTimeRows += `
            <tr>
                <td>${ticketNum} - ${stat.title}</td>
                <td>${stat.location}</td>
                <td style="font-size: 0.85rem;">${stat.created_at}</td>
                <td style="font-size: 0.85rem;">${stat.assigned_at}</td>
                <td style="font-size: 0.85rem;">${stat.resolved_at}</td>
                <td class="text-center">${stat.submitted_to_assigned_formatted}</td>
                <td class="text-center">${stat.assigned_to_resolved_formatted}</td>
                <td class="text-center"><strong>${stat.total_time_formatted}</strong></td>
                <td>${stat.assigned_to_name}</td>
            </tr>
        `;
    });
    
    const responseTimeTbody = modalContainer ? modalContainer.querySelectorAll('.table-responsive .table tbody')[1] : null;
    if (responseTimeTbody) {
        responseTimeTbody.innerHTML = responseTimeRows;
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
    
    // Group by month with status breakdown
    const monthData = {};
    monthLabels.forEach(m => {
        monthData[m.key] = { label: m.label, issues: {}, total: 0 };
    });
    
    monthly.forEach(item => {
        if (monthData[item.month]) {
            if (!monthData[item.month].issues[item.title]) {
                monthData[item.month].issues[item.title] = {
                    total: 0,
                    Pending: { count: 0, tickets: [], damagedParts: [] },
                    Assigned: { count: 0, tickets: [], damagedParts: [] },
                    'In Progress': { count: 0, tickets: [], damagedParts: [] },
                    Resolved: { count: 0, tickets: [], damagedParts: [] }
                };
            }
            const count = parseInt(item.count) || 0;
            const ticketIds = item.ticket_ids ? item.ticket_ids.split(',') : [];
            const damagedParts = item.damaged_parts ? item.damaged_parts.split('|') : [];
            
            monthData[item.month].issues[item.title].total += count;
            
            if (!monthData[item.month].issues[item.title][item.status]) {
                monthData[item.month].issues[item.title][item.status] = { count: 0, tickets: [], damagedParts: [] };
            }
            
            monthData[item.month].issues[item.title][item.status].count += count;
            monthData[item.month].issues[item.title][item.status].tickets.push(...ticketIds);
            monthData[item.month].issues[item.title][item.status].damagedParts.push(...damagedParts);
            monthData[item.month].total += count;
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
    
    // Update table with status breakdown
    let tableRows = '';
    Object.entries(monthData).forEach(([key, data]) => {
        if (Object.keys(data.issues).length === 0) {
            tableRows += `
                <tr>
                    <td><strong>${data.label}</strong></td>
                    <td colspan="5" class="text-center text-muted">No repairs recorded</td>
                </tr>
            `;
        } else {
            Object.entries(data.issues).forEach(([issue, statusData], idx) => {
                // Helper function to format ticket list with damage parts
                const formatTicketList = (statusObj) => {
                    if (!statusObj || !statusObj.tickets || statusObj.tickets.length === 0) {
                        return '<span class="badge bg-secondary">0</span>';
                    }
                    
                    const tickets = statusObj.tickets;
                    const parts = statusObj.damagedParts || [];
                    const items = tickets.map((ticketId, i) => {
                        const formattedTicket = '#' + String(ticketId).padStart(4, '0');
                        const part = parts[i] || 'N/A';
                        return `${formattedTicket} ${part}`;
                    });
                    
                    return `<div style="text-align: left; max-width: 200px;">
                        ${items.map(item => `<div style="margin-bottom: 2px; font-size: 0.85em;">${item}</div>`).join('')}
                    </div>`;
                };
                
                tableRows += `
                    <tr>
                        ${idx === 0 ? `<td rowspan="${Object.keys(data.issues).length}"><strong>${data.label}</strong></td>` : ''}
                        <td>${issue}</td>
                        <td class="text-center"><span class="badge bg-primary">${statusData.total}</span></td>
                        <td class="text-center">${formatTicketList(statusData.Resolved)}</td>
                        <td class="text-center">${formatTicketList(statusData['In Progress'])}</td>
                        <td class="text-center">${formatTicketList(statusData.Pending)}</td>
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

// Update Period Comparison Modal Content (without closing modal)
function updatePeriodComparisonModalContent() {
    // Determine if we should use yearly or monthly grouping
    const state = window.modalFilterState || {};
    const dateFrom = state.date_from ? new Date(state.date_from) : new Date(new Date().setMonth(new Date().getMonth() - 6));
    const dateTo = state.date_to ? new Date(state.date_to) : new Date();
    const yearSpan = dateTo.getFullYear() - dateFrom.getFullYear();
    const isAllYears = yearSpan > 2;
    
    let labels = [];
    let keys = [];
    let costs = [];
    let counts = [];
    
    // Get monthly cost data from window if available
    const monthlyCostData = window.monthlyCostData || [];
    
    if (isAllYears) {
        // Group by year for "All years" view
        const startYear = dateFrom.getFullYear();
        const endYear = dateTo.getFullYear();
        
        for (let year = startYear; year <= endYear; year++) {
            keys.push(year.toString());
            labels.push(year.toString());
            costs.push(0);
            counts.push(0);
        }
        
        // Aggregate monthly data into yearly data
        monthlyCostData.forEach(item => {
            const itemYear = item.month.substring(0, 4); // Extract year from YYYY-MM
            const yearIndex = keys.indexOf(itemYear);
            if (yearIndex !== -1) {
                costs[yearIndex] += parseFloat(item.total_cost) || 0;
                counts[yearIndex] += parseInt(item.count) || 0;
            }
        });
    } else {
        // Group by month for other views (default: last 6 months)
        const currentDate = new Date();
        
        for (let i = 5; i >= 0; i--) {
            const d = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const key = d.toISOString().slice(0, 7); // YYYY-MM format
            const label = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
            keys.push(key);
            labels.push(label);
            costs.push(0);
            counts.push(0);
        }
        
        // Populate data from server
        monthlyCostData.forEach(item => {
            const monthIndex = keys.indexOf(item.month);
            if (monthIndex !== -1) {
                costs[monthIndex] = parseFloat(item.total_cost) || 0;
                counts[monthIndex] = parseInt(item.count) || 0;
            }
        });
    }
    
    // Calculate statistics
    const totalCost = costs.reduce((sum, cost) => sum + cost, 0);
    const totalCount = counts.reduce((sum, count) => sum + count, 0);
    const avgCostPerPeriod = costs.length > 0 ? totalCost / costs.length : 0;
    const avgCostPerRepair = totalCount > 0 ? totalCost / totalCount : 0;
    
    // Find highest and lowest periods
    let highestIdx = 0, lowestIdx = 0;
    for (let i = 1; i < costs.length; i++) {
        if (costs[i] > costs[highestIdx]) highestIdx = i;
        if (costs[i] < costs[lowestIdx]) lowestIdx = i;
    }
    
    // Update summary cards
    const modalContainer = document.querySelector('.swal2-html-container');
    if (modalContainer) {
        const summaryCards = modalContainer.querySelectorAll('.row.mb-4 .col-md-4');
        if (summaryCards[0]) {
            const h6 = summaryCards[0].querySelector('h6');
            const h4 = summaryCards[0].querySelector('h4');
            const h5 = summaryCards[0].querySelector('h5');
            if (h6) h6.textContent = `Highest Cost ${isAllYears ? 'Year' : 'Month'}`;
            if (h4) h4.textContent = labels[highestIdx];
            if (h5) h5.textContent = '₱' + costs[highestIdx].toLocaleString('en-PH', {minimumFractionDigits: 2});
        }
        if (summaryCards[1]) {
            const h6 = summaryCards[1].querySelector('h6');
            const h4 = summaryCards[1].querySelector('h4');
            const h5 = summaryCards[1].querySelector('h5');
            if (h6) h6.textContent = `Lowest Cost ${isAllYears ? 'Year' : 'Month'}`;
            if (h4) h4.textContent = labels[lowestIdx];
            if (h5) h5.textContent = '₱' + costs[lowestIdx].toLocaleString('en-PH', {minimumFractionDigits: 2});
        }
        if (summaryCards[2]) {
            const h6 = summaryCards[2].querySelector('h6');
            const h4 = summaryCards[2].querySelector('h4');
            const h5 = summaryCards[2].querySelector('h5');
            if (h6) h6.textContent = `Average Cost/${isAllYears ? 'Year' : 'Month'}`;
            if (h4) h4.textContent = '₱' + avgCostPerPeriod.toLocaleString('en-PH', {minimumFractionDigits: 2});
            if (h5) h5.textContent = '₱' + avgCostPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2}) + ' per repair';
        }
    }
    
    // Build table rows
    let tableRows = '';
    labels.forEach((label, idx) => {
        const cost = costs[idx];
        const count = counts[idx];
        const avgPerRepair = count > 0 ? cost / count : 0;
        const percentOfTotal = totalCost > 0 ? ((cost / totalCost) * 100).toFixed(1) : 0;
        const periodKey = keys[idx]; // Store the period key (YYYY-MM or YYYY)
        
        let trendIcon = '';
        if (idx > 0) {
            const prevCost = costs[idx - 1];
            if (cost > prevCost) {
                trendIcon = '<i class="fas fa-arrow-up text-danger"></i>';
            } else if (cost < prevCost) {
                trendIcon = '<i class="fas fa-arrow-down text-success"></i>';
            } else {
                trendIcon = '<i class="fas fa-minus text-secondary"></i>';
            }
        }
        
        // Make row clickable if there are repairs
        const rowClass = count > 0 ? 'cursor-pointer period-breakdown-row' : '';
        const rowClick = count > 0 ? `onclick="showPeriodBreakdownModal('${periodKey}', '${label}')"` : '';
        
        tableRows += `
            <tr class="${rowClass}" ${rowClick} style="${count > 0 ? 'cursor: pointer;' : ''}">
                <td><strong>${label}</strong></td>
                <td class="text-center">${count}</td>
                <td class="text-end">₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${avgPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-center">${percentOfTotal}%</td>
                <td class="text-center">${trendIcon}</td>
            </tr>
        `;
    });
    
    const periodLabel = isAllYears ? 'Years' : (costs.length + ' Months');
    
    // Add footer row
    tableRows += `
        <tr class="table-secondary fw-bold cursor-pointer period-breakdown-row" onclick="showAllPeriodsBreakdown()" style="cursor: pointer;">
            <td>TOTAL (${periodLabel})</td>
            <td class="text-center">${totalCount}</td>
            <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
            <td class="text-end">₱${avgCostPerRepair.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
            <td class="text-center">100%</td>
            <td></td>
        </tr>
    `;
    
    const tbody = modalContainer ? modalContainer.querySelector('.table tbody') : null;
    const tfoot = modalContainer ? modalContainer.querySelector('.table tfoot') : null;
    if (tbody) tbody.innerHTML = tableRows.replace(/<tr class="table-secondary.*?<\/tr>/, '');
    if (tfoot) tfoot.innerHTML = tableRows.match(/<tr class="table-secondary.*?<\/tr>/)?.[0] || '';
    
    // Update chart
    const canvas = document.getElementById('modalPeriodComparisonChart');
    if (canvas && window.Chart) {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();
        
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
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
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y || 0;
                                const repairs = counts[context.dataIndex] || 0;
                                const avgCost = repairs > 0 ? (value / repairs) : 0;
                                return [
                                    'Total Cost: ₱' + value.toLocaleString('en-PH', {minimumFractionDigits: 2}),
                                    'Total Repairs: ' + repairs,
                                    'Avg Cost: ₱' + avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
}


// Export Cost Report to PDF
function exportCostReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/cost-report-pdf?' + queryString : '/admin/analytics/cost-report-pdf';
    window.open(url, '_blank');
}

// Export Period Comparison to PDF
function exportPeriodComparisonToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/period-comparison-pdf?' + queryString : '/admin/analytics/period-comparison-pdf';
    window.open(url, '_blank');
}

// Export Status Report to PDF
function exportStatusReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/status-report-pdf?' + queryString : '/admin/analytics/status-report-pdf';
    window.open(url, '_blank');
}

// Export Trend Report to PDF
function exportTrendReportToPDF() {
    const params = new URLSearchParams();
    const state = window.modalFilterState || {};
    
    // Use date_from and date_to if available (from YouTube-style filter)
    if (state.date_from && state.date_to) {
        params.append('date_from', state.date_from);
        params.append('date_to', state.date_to);
    }
    
    const queryString = params.toString();
    const url = queryString ? '/admin/analytics/trend-report-pdf?' + queryString : '/admin/analytics/trend-report-pdf';
    window.open(url, '_blank');
}
