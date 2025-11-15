<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('guru');

// Ambil data guru
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM guru WHERE user_id = ?");
$stmt->execute([$user_id]);
$guru = $stmt->fetch();

// Hitung statistik
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT mp.id) as total_mapel 
    FROM mata_pelajaran mp 
    WHERE mp.guru_id = ?
");
$stmt->execute([$guru['id']]);
$total_mapel = $stmt->fetch()['total_mapel'];

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.id) as total_siswa 
    FROM mata_pelajaran mp 
    JOIN siswa s ON mp.kelas = s.kelas 
    WHERE mp.guru_id = ?
");
$stmt->execute([$guru['id']]);
$total_siswa = $stmt->fetch()['total_siswa'];
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard Guru</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">Selamat datang, <?php echo $guru['nama_lengkap']; ?></span>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_mapel; ?></h4>
                                    <p>Mata Pelajaran</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_siswa; ?></h4>
                                    <p>Total Siswa</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>5</h4>
                                    <p>Kelas Diajar</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mata Pelajaran yang Diajar -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Mata Pelajaran yang Diajar</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mata Pelajaran</th>
                                            <th>Kelas</th>
                                            <th>Jumlah Siswa</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo->prepare("
                                            SELECT mp.*, COUNT(s.id) as jumlah_siswa 
                                            FROM mata_pelajaran mp 
                                            LEFT JOIN siswa s ON mp.kelas = s.kelas 
                                            WHERE mp.guru_id = ? 
                                            GROUP BY mp.id, mp.kelas
                                        ");
                                        $stmt->execute([$guru['id']]);
                                        $mapel_list = $stmt->fetchAll();
                                        
                                        foreach ($mapel_list as $mapel):
                                        ?>
                                        <tr>
                                            <td><?php echo $mapel['nama_mapel']; ?></td>
                                            <td><?php echo $mapel['kelas']; ?></td>
                                            <td><?php echo $mapel['jumlah_siswa']; ?> Siswa</td>
                                            <td>
                                                <a href="input_nilai.php?mapel=<?php echo $mapel['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Input Nilai
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item">
                                    <small class="text-muted">Hari ini</small><br>
                                    <span>Input nilai Matematika Kelas VII A</span>
                                </div>
                                <div class="list-group-item">
                                    <small class="text-muted">Kemarin</small><br>
                                    <span>Input absensi Kelas VIII B</span>
                                </div>
                                <div class="list-group-item">
                                    <small class="text-muted">2 hari lalu</small><br>
                                    <span>Update nilai UTS Bahasa Indonesia</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>