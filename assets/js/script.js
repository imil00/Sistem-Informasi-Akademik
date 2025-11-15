// Custom JavaScript untuk Sistem Informasi Akademik

// Konfirmasi sebelum menghapus data
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
}

// Format tanggal
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

// Format angka
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Validasi form
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Auto-hide alert
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alert setelah 5 detik
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Tooltip
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggler = document.querySelector('.navbar-toggler[data-bs-target="#sidebarMenu"]');
    const sidebar = document.getElementById('sidebarMenu');
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }

    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', toggleSidebar);
    }

    backdrop.addEventListener('click', toggleSidebar);

    // Close sidebar when clicking on nav links in mobile
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
        });
    });

    // Adjust main content margin when sidebar is open
    function adjustMainContent() {
        const main = document.querySelector('main');
        if (window.innerWidth < 768 && sidebar.classList.contains('show')) {
            main.style.marginLeft = '250px';
        } else {
            main.style.marginLeft = '0';
        }
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
        adjustMainContent();
    });

    adjustMainContent();
});

// Search functionality
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toLowerCase();
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}