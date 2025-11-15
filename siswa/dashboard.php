<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('siswa');

// Ambil data siswa
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE user_id = ?");
$stmt->execute([$user_id]);
$siswa = $stmt->fetch();

// Ambil nilai rata-rata
$stmt = $pdo->prepare("
    SELECT AVG(nilai_akhir) as rata_rata 
    FROM nilai 
    WHERE siswa_id = ? AND semester = '1' AND tahun_ajaran = '2024/2025'
");
$stmt->execute([$siswa['id']]);
$rata_rata = $stmt->fetch()['rata_rata'];

// Ambil data absensi bulan ini
$bulan_ini = date('m');
$tahun_ini = date('Y');
$stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'Hadir' THEN 1 END) as hadir,
        COUNT(CASE WHEN status = 'Izin' THEN 1 END) as izin,
        COUNT(CASE WHEN status = 'Sakit' THEN 1 END) as sakit,
        COUNT(CASE WHEN status = 'Alpha' THEN 1 END) as alpha
    FROM absensi 
    WHERE siswa_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
");
$stmt->execute([$siswa['id'], $bulan_ini, $tahun_ini]);
$absensi = $stmt->fetch();

// Ambil jumlah mata pelajaran
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT mata_pelajaran_id) as jumlah_mapel 
    FROM nilai 
    WHERE siswa_id = ? AND semester = '1' AND tahun_ajaran = '2024/2025'
");
$stmt->execute([$siswa['id']]);
$jumlah_mapel = $stmt->fetch()['jumlah_mapel'];

// Ambil pengumuman terbaru
$pengumuman = [
    ['title' => 'Ujian Semester 1', 'date' => '2025-01-15', 'content' => 'Ujian semester 1 akan dilaksanakan mulai 15 Januari 2025.'],
    ['title' => 'Pembagian Raport', 'date' => '2025-01-20', 'content' => 'Pembagian raport semester 1 akan dilakukan pada 20 Januari 2025.'],
    ['title' => 'Libur Semester', 'date' => '2025-01-25', 'content' => 'Libur semester 1 dimulai dari 25 Januari hingga 10 Februari 2025.']
];
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard Siswa</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">Selamat datang, <?php echo $siswa['nama_lengkap']; ?></span>
                </div>
            </div>

            <!-- Informasi Siswa -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi Pribadi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">NIS</th>
                                            <td><?php echo $siswa['nis']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <td><?php echo $siswa['nama_lengkap']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td><?php echo $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Kelas</th>
                                            <td><?php echo $siswa['kelas']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Lahir</th>
                                            <td><?php echo date('d/m/Y', strtotime($siswa['tanggal_lahir'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?php echo $siswa['alamat']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Statistik Nilai -->
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body text-center">
                            <h3><?php echo number_format($rata_rata, 1); ?></h3>
                            <p>Rata-rata Nilai Semester 1</p>
                        </div>
                    </div>
                    
                    <!-- Statistik Absensi -->
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h3><?php echo $absensi['hadir']; ?></h3>
                            <p>Hari Hadir Bulan Ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Nilai Terbaru -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Nilai Terbaru</h5>
                            <span class="badge bg-primary"><?php echo $jumlah_mapel; ?> Mapel</span>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT n.*, mp.nama_mapel 
                                FROM nilai n 
                                JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id 
                                WHERE n.siswa_id = ? AND n.semester = '1' AND n.tahun_ajaran = '2024/2025'
                                ORDER BY n.nilai_akhir DESC 
                                LIMIT 5
                            ");
                            $stmt->execute([$siswa['id']]);
                            $nilai_terbaru = $stmt->fetchAll();
                            
                            if ($nilai_terbaru):
                            ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($nilai_terbaru as $nilai): 
                                    $predikat = getPredikat($nilai['nilai_akhir']);
                                    $predikat_color = getPredikatColor($nilai['nilai_akhir']);
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo $nilai['nama_mapel']; ?></h6>
                                        <small class="text-muted">Nilai Akhir: <?php echo number_format($nilai['nilai_akhir'], 1); ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo $predikat_color; ?>"><?php echo $predikat; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                    <p>Belum ada data nilai.</p>
                                </div>
                            <?php endif; ?>
                            <div class="text-center mt-3">
                                <a href="nilai.php" class="btn btn-sm btn-outline-primary">Lihat Semua Nilai</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Absensi Bulan Ini -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Absensi Bulan <?php echo date('F'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-3">
                                    <div class="text-success">
                                        <h4><?php echo $absensi['hadir']; ?></h4>
                                        <small>Hadir</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-info">
                                        <h4><?php echo $absensi['izin']; ?></h4>
                                        <small>Izin</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-warning">
                                        <h4><?php echo $absensi['sakit']; ?></h4>
                                        <small>Sakit</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-danger">
                                        <h4><?php echo $absensi['alpha']; ?></h4>
                                        <small>Alpha</small>
                                    </div>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: <?php echo ($absensi['hadir']/20)*100; ?>%"></div>
                                <div class="progress-bar bg-info" style="width: <?php echo ($absensi['izin']/20)*100; ?>%"></div>
                                <div class="progress-bar bg-warning" style="width: <?php echo ($absensi['sakit']/20)*100; ?>%"></div>
                                <div class="progress-bar bg-danger" style="width: <?php echo ($absensi['alpha']/20)*100; ?>%"></div>
                            </div>
                            <div class="text-center">
                                <a href="absensi.php" class="btn btn-sm btn-outline-primary">Lihat Detail Absensi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengumuman Terbaru -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengumuman Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($pengumuman as $item): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $item['title']; ?></h6>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($item['date'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo $item['content']; ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Helper functions
function getPredikat($nilai) {
    if ($nilai >= 90) return 'A';
    if ($nilai >= 80) return 'B';
    if ($nilai >= 70) return 'C';
    if ($nilai >= 60) return 'D';
    return 'E';
}

function getPredikatColor($nilai) {
    if ($nilai >= 90) return 'success';
    if ($nilai >= 80) return 'primary';
    if ($nilai >= 70) return 'warning';
    if ($nilai >= 60) return 'info';
    return 'danger';
}
?>

<?php include '../includes/footer.php'; ?>