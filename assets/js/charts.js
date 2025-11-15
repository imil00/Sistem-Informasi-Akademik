// Chart functions for dashboard
function initCharts() {
    // Chart for student distribution
    const ctxKelas = document.getElementById('chartKelas');
    if (ctxKelas) {
        new Chart(ctxKelas, {
            type: 'bar',
            data: {
                labels: ['VII A', 'VII B', 'VIII A', 'VIII B', 'IX A', 'IX B'],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: [25, 28, 30, 27, 26, 29],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ],
                    borderColor: [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
                        'rgb(201, 203, 207)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribusi Siswa per Kelas'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Chart for attendance
    const ctxAbsensi = document.getElementById('chartAbsensi');
    if (ctxAbsensi) {
        new Chart(ctxAbsensi, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                datasets: [{
                    data: [18, 2, 1, 1],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 99, 132)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Statistik Absensi Bulan Ini'
                    }
                }
            }
        });
    }
}

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});