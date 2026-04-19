<style>
    .sidebar {
        background-color: #fff;
        box-shadow: 0px 0px 20px rgba(1, 41, 112, 0.1);
    }
    
    .sidebar-nav .nav-link {
        color: #1d1145;
        background: #fff;
        font-weight: 600;
        transition: 0.3s;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    /* State saat menu aktif */
    .sidebar-nav .nav-link:not(.collapsed) {
        color: #e66c8a;
        background: #fef1f4;
    }

    .sidebar-nav .nav-link:not(.collapsed) i {
        color: #e66c8a;
    }

    .sidebar-nav .nav-link:hover {
        color: #e66c8a;
        background: #fef1f4;
    }

    .sidebar-nav .nav-link i {
        font-size: 1.1rem;
        margin-right: 10px;
        color: #899bbd;
    }

    .nav-heading {
        font-size: 11px;
        text-transform: uppercase;
        color: #899bbd;
        font-weight: 700;
        margin: 10px 0 5px 15px;
    }

    .sidebar {
        position: fixed;
        top: 60px; /* Sesuaikan dengan tinggi navbar kamu */
        left: 0;
        bottom: 0;
        width: 260px;
        z-index: 996;
        transition: all 0.3s;
        padding: 20px;
        overflow-y: auto;
        background-color: #fff;
        border-right: 1px solid #f0f0f0;
    }

    /* OVERLAY: Latar gelap saat sidebar terbuka di mobile */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 995;
        display: none;
        backdrop-filter: blur(2px);
    }

    /* RESPONSIVE LOGIC */
    @media (max-width: 768px) {
        .sidebar {
            left: -260px; /* Sembunyikan ke kiri */
        }

        .sidebar.active {
            left: 0; /* Munculkan saat class active ditambah */
        }

        .sidebar-overlay.active {
            display: block; /* Tampilkan overlay */
        }
    }

    /* Pengaturan konten utama agar tidak tertutup di Desktop */
    #main-content {
        transition: all 0.3s;
        padding: 20px;
    }

    @media (min-width: 769px) {
        #main-content {
            margin-left: 260px;
        }
    }
</style>

<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <?php $is_dashboard = (!isset($_GET['page']) || $_GET['page'] == 'user'); ?>
            <a class="nav-link <?= $is_dashboard ? '' : 'collapsed'; ?>" href="index.php?page=user">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-heading">Layanan Tiket</li>

        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'event') ? '' : 'collapsed'; ?>" href="index.php?page=event">
                <i class="bi bi-ticket-perforated"></i>
                <span>Cari Tiket</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'riwayat') ? '' : 'collapsed'; ?>" href="index.php?page=riwayat">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Saya</span>
            </a>
        </li>
    </ul>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Mencegah body agar tidak bisa discroll saat sidebar aktif di mobile
        if (sidebar.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }

    // Otomatis tutup sidebar jika user memperlebar layar ke Desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('sidebarOverlay').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
</script>