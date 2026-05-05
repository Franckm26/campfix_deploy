// Show Location Details Modal
function showLocationDetailsModal() {
    const locations = {!! json_encode($chartLocations ?? []) !!};
    const counts = {!! json_encode($chartCounts ?? []) !!};
    const costs = {!! json_encode($chartCosts ?? []) !!};
    
    let totalRepairs = 0;
    let totalCost = 0;
    
    // Combine data and sort by count
    const data = locations.map((loc, idx) => ({
        location: loc,
        count: counts[idx] || 0,
        cost: costs[idx] || 0
    })).sort((a, b) => b.count - a.count);
    
    data.forEach((item) => {
        totalRepairs += item.count;
        totalCost += item.cost;
    });
    
    let tableRows = '';
    data.forEach((item, idx) => {
        const avgCost = item.count > 0 ? item.cost / item.count : 0;
        const percentage = totalRepairs > 0 ? ((item.count / totalRepairs) * 100).toFixed(1) : 0;
        
        tableRows += `
            <tr>
                <td><span class="badge bg-secondary">#${idx + 1}</span></td>
                <td><strong>${item.location}</strong></td>
                <td class="text-center"><span class="badge bg-primary">${item.count}</span></td>
                <td class="text-end">₱${item.cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                <td class="text-center">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" style="width: ${percentage}%">${percentage}%</div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    Swal.fire({
        title: '<i class="fas fa-map-marker-alt me-2"></i>Repairs by Location - Detailed Report',
        html: `
            <div style="max-height: 500px; overflow-y: auto;">
                <div style="height: 250px; margin-bottom: 20px;">
                    <canvas id="modalLocationChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Location</th>
                                <th class="text-center">Total Repairs</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Avg Cost</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="text-center">${totalRepairs}</td>
                                <td class="text-end">₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `,
        width: '900px',
        showCloseButton: true,
        showConfirmButton: false,
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
