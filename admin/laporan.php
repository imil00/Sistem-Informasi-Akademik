<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

// Default values
$kelas = $_GET['kelas'] ?? 'VII A';
$semester = $_GET['semester'] ?? '1';
$tahun_ajaran = $_GET['tahun_ajaran'] ?? '2024/2025';
$jenis_laporan = $_GET['jenis_laporan'] ?? 'nilai';

// Ambil data untuk laporan
if ($jenis_laporan == 'nilai') {
    $stmt = $pdo->prepare("
        SELECT s.nis, s.nama_lengkap, mp.nama_mapel, 
               n.nilai_uh1, n.nilai_uh2, n.nilai_uts, n.nilai_uas, n.nilai_akhir,
               n.semester, n.tahun_ajaran
        FROM nilai n
        JOIN siswa s ON n.siswa_id = s.id
        JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id
        WHERE s.kelas = ? AND n.semester = ? AND n.tahun_ajaran = ?
        ORDER BY s.nama_lengkap, mp.nama_mapel
    ");
    $stmt->execute([$kelas, $semester, $tahun_ajaran]);
    $laporan_data = $stmt->fetchAll();
} else {
    // Laporan absensi
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    
    $stmt = $pdo->prepare("
        SELECT s.nis, s.nama_lengkap,
               COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as hadir,
               COUNT(CASE WHEN a.status = 'Izin' THEN 1 END) as izin,
               COUNT(CASE WHEN a.status = 'Sakit' THEN 1 END) as sakit,
               COUNT(CASE WHEN a.status = 'Alpha' THEN 1 END) as alpha,
               COUNT(*) as total
        FROM siswa s
        LEFT JOIN absensi a ON s.id = a.siswa_id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
        WHERE s.kelas = ?
        GROUP BY s.id, s.nis, s.nama_lengkap
        ORDER BY s.nama_lengkap
    ");
    $stmt->execute([$bulan, $tahun, $kelas]);
    $laporan_data = $stmt->fetchAll();
}
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Akademik</h1>
                <div>
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Jenis Laporan</label>
                            <select class="form-select" name="jenis_laporan" id="jenisLaporan">
                                <option value="nilai" <?php echo $jenis_laporan == 'nilai' ? 'selected' : ''; ?>>Laporan Nilai</option>
                                <option value="absensi" <?php echo $jenis_laporan == 'absensi' ? 'selected' : ''; ?>>Laporan Absensi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kelas</label>
                            <select class="form-select" name="kelas">
                                <option value="VII A" <?php echo $kelas == 'VII A' ? 'selected' : ''; ?>>VII A</option>
                                <option value="VII B" <?php echo $kelas == 'VII B' ? 'selected' : ''; ?>>VII B</option>
                                <option value="VIII A" <?php echo $kelas == 'VIII A' ? 'selected' : ''; ?>>VIII A</option>
                                <option value="VIII B" <?php echo $kelas == 'VIII B' ? 'selected' : ''; ?>>VIII B</option>
                                <option value="IX A" <?php echo $kelas == 'IX A' ? 'selected' : ''; ?>>IX A</option>
                                <option value="IX B" <?php echo $kelas == 'IX B' ? 'selected' : ''; ?>>IX B</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3" id="semesterField">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester">
                                <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>2</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3" id="tahunAjaranField">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                        </div>
                        
                        <div class="col-md-3" id="bulanField" style="display: none;">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="bulan">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $bulan == sprintf('%02d', $i) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3" id="tahunField" style="display: none;">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" value="<?php echo $tahun; ?>" min="2020" max="2030">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Tampilkan Laporan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Laporan Nilai -->
            <?php if ($jenis_laporan == 'nilai'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Laporan Nilai Kelas <?php echo $kelas; ?> - Semester <?php echo $semester; ?> - Tahun Ajaran <?php echo $tahun_ajaran; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Mata Pelajaran</th>
                                    <th>UH 1</th>
                                    <th>UH 2</th>
                                    <th>UTS</th>
                                    <th>UAS</th>
                                    <th>Nilai Akhir</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporan_data as $data): 
                                    $predikat = getPredikat($data['nilai_akhir']);
                                ?>
                                <tr>
                                    <td><?php echo $data['nis']; ?></td>
                                    <td><?php echo $data['nama_lengkap']; ?></td>
                                    <td><?php echo $data['nama_mapel']; ?></td>
                                    <td><?php echo number_format($data['nilai_uh1'], 1); ?></td>
                                    <td><?php echo number_format($data['nilai_uh2'], 1); ?></td>
                                    <td><?php echo number_format($data['nilai_uts'], 1); ?></td>
                                    <td><?php echo number_format($data['nilai_uas'], 1); ?></td>
                                    <td><strong><?php echo number_format($data['nilai_akhir'], 1); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo getPredikatColor($data['nilai_akhir']); ?>">
                                            <?php echo $predikat; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Laporan Absensi -->
            <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Laporan Absensi Kelas <?php echo $kelas; ?> - Bulan <?php echo date('F', mktime(0, 0, 0, $bulan, 1)); ?> <?php echo $tahun; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Hadir</th>
                                    <th>Izin</th>
                                    <th>Sakit</th>
                                    <th>Alpha</th>
                                    <th>Total</th>
                                    <th>Persentase Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporan_data as $data): 
                                    $persentase = $data['total'] > 0 ? ($data['hadir'] / $data['total']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo $data['nis']; ?></td>
                                    <td><?php echo $data['nama_lengkap']; ?></td>
                                    <td><?php echo $data['hadir']; ?></td>
                                    <td><?php echo $data['izin']; ?></td>
                                    <td><?php echo $data['sakit']; ?></td>
                                    <td><?php echo $data['alpha']; ?></td>
                                    <td><?php echo $data['total']; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $persentase >= 80 ? 'bg-success' : ($persentase >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 style="width: <?php echo $persentase; ?>%">
                                                <?php echo number_format($persentase, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    // Toggle fields berdasarkan jenis laporan
    document.getElementById('jenisLaporan').addEventListener('change', function() {
        const jenisLaporan = this.value;
        const semesterField = document.getElementById('semesterField');
        const tahunAjaranField = document.getElementById('tahunAjaranField');
        const bulanField = document.getElementById('bulanField');
        const tahunField = document.getElementById('tahunField');
        
        if (jenisLaporan === 'nilai') {
            semesterField.style.display = 'block';
            tahunAjaranField.style.display = 'block';
            bulanField.style.display = 'none';
            tahunField.style.display = 'none';
        } else {
            semesterField.style.display = 'none';
            tahunAjaranField.style.display = 'none';
            bulanField.style.display = 'block';
            tahunField.style.display = 'block';
        }
    });

    // Trigger change event on page load
    document.getElementById('jenisLaporan').dispatchEvent(new Event('change'));
</script>

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