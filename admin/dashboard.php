<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

$stmt = $pdo->query("SELECT COUNT(*) as total FROM siswa");
$total_siswa = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM guru");
$total_guru = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM mata_pelajaran");
$total_mapel = $stmt->fetch()['total'];

$stmt = $pdo->query("
    SELECT kelas, COUNT(*) as jumlah 
    FROM siswa 
    GROUP BY kelas
");
$data_kelas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Informasi Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

<div class="row">
    <div class="col-md-3 mb-4">
        <a href="data_siswa.php" class="dashboard-card text-decoration-none">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-stats"><?php echo $total_siswa; ?></div>
                    <div class="card-title">Total Siswa</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-4">
        <a href="data_guru.php" class="dashboard-card text-decoration-none">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="card-stats"><?php echo $total_guru; ?></div>
                    <div class="card-title">Total Guru</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-4">
        <a href="data_mata_pelajaran.php" class="dashboard-card text-decoration-none">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="card-stats"><?php echo $total_mapel; ?></div>
                    <div class="card-title">Mata Pelajaran</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-4">
        <a href="data_siswa.php" class="dashboard-card text-decoration-none">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="card-stats">6</div>
                    <div class="card-title">Kelas</div>
                </div>
            </div>
        </a>
    </div>
</div>

                <!-- Chart dan Data Terbaru -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Distribusi Siswa per Kelas</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartKelas" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Input nilai Matematika kelas VII A</li>
                                    <li class="list-group-item">Update data siswa baru</li>
                                    <li class="list-group-item">Input absensi hari ini</li>
                                    <li class="list-group-item">Generate laporan semester</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/chart.min.js"></script>
    <script>
        // Chart untuk distribusi siswa
        const ctx = document.getElementById('chartKelas').getContext('2d');
        const chartKelas = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . $item['kelas'] . "'"; }, $data_kelas)); ?>],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: [<?php echo implode(',', array_column($data_kelas, 'jumlah')); ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>