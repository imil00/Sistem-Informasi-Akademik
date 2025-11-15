<!-- Sidebar -->
<div class="collapse d-md-block col-md-3 col-lg-2 sidebar-collapse" id="sidebarMenu">
    <nav class="sidebar bg-dark">
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                       href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'data_siswa.php' ? 'active' : ''; ?>" 
                       href="data_siswa.php">
                        <i class="fas fa-users me-2"></i>
                        Data Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'data_guru.php' ? 'active' : ''; ?>" 
                       href="data_guru.php">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Data Guru
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'data_mata_pelajaran.php' ? 'active' : ''; ?>" 
                       href="data_mata_pelajaran.php">
                        <i class="fas fa-book me-2"></i>
                        Mata Pelajaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nilai.php' ? 'active' : ''; ?>" 
                       href="nilai.php">
                        <i class="fas fa-chart-bar me-2"></i>
                        Nilai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'absensi.php' ? 'active' : ''; ?>" 
                       href="absensi.php">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Absensi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" 
                       href="laporan.php">
                        <i class="fas fa-file-alt me-2"></i>
                        Laporan
                    </a>
                </li>
                <?php elseif ($_SESSION['role'] == 'guru'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'input_nilai.php' ? 'active' : ''; ?>" 
                       href="input_nilai.php">
                        <i class="fas fa-edit me-2"></i>
                        Input Nilai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'input_absensi.php' ? 'active' : ''; ?>" 
                       href="input_absensi.php">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Input Absensi
                    </a>
                </li>
                <?php elseif ($_SESSION['role'] == 'siswa'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nilai.php' ? 'active' : ''; ?>" 
                       href="nilai.php">
                        <i class="fas fa-chart-bar me-2"></i>
                        Lihat Nilai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'absensi.php' ? 'active' : ''; ?>" 
                       href="absensi.php">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Lihat Absensi
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</div>