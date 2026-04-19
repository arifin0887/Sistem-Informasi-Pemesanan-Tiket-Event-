<style>
.sidebar {
  position: fixed;
  top: 70px;
  left: 0;
  bottom: 0;
  width: 260px;
  z-index: 996;
  transition: all 0.3s;
  padding: 20px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #aab7cf transparent;
  box-shadow: 0px 0px 20px rgba(1, 41, 112, 0.05);
  background-color: #fff;
  border-right: 1px solid #f0f0f0;
}

.sidebar-nav {
  padding: 0;
  margin: 0;
  list-style: none;
}

.sidebar-nav li {
  padding: 0;
  margin: 0;
  list-style: none;
}

.sidebar-nav .nav-heading {
  font-size: 11px;
  text-transform: uppercase;
  color: #899bbd;
  font-weight: 700;
  margin: 20px 0 10px 15px;
  letter-spacing: 1px;
}

.sidebar-nav .nav-link {
  display: flex;
  align-items: center;
  font-size: 15px;
  font-weight: 600;
  color: #4154f1; 
  background: #f6f9ff; 
  padding: 12px 15px;
  border-radius: 8px;
  transition: 0.3s;
  text-decoration: none;
  margin-bottom: 5px;
}

.sidebar-nav .nav-link i {
  font-size: 18px;
  margin-right: 12px;
  line-height: 0;
}

.sidebar-nav .nav-link.collapsed {
  color: #1d1145; 
  background: transparent;
}

.sidebar-nav .nav-link.collapsed i {
  color: #899bbd;
}

.sidebar-nav .nav-link:hover {
  color: #e66c8a; 
  background: #fef1f4; 
}

.sidebar-nav .nav-link:hover i {
  color: #e66c8a;
}

.sidebar-nav .nav-link::after {
  display: none;
}

/* SIDEBAR RESPONSIVE */
@media (max-width: 768px) {

  .sidebar {
    left: -260px;
    width: 260px;
    transition: 0.3s;
  }

  .sidebar.active {
    left: 0;
  }

  /* OVERLAY */
  .sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 995;
    display: none;
  }

  .sidebar-overlay.active {
    display: block;
  }
}

/* Sesuaikan Konten Utama (Main) */
#main {
  transition: all 0.3s;
  padding: 20px;
}

@media (min-width: 769px) {
  #main {
    margin-left: 260px; /* Jarak sidebar di desktop */
  }
}

@media (max-width: 768px) {
  #main {
    margin-left: 0;
  }
  
  .sidebar {
    top: 0; /* Di mobile, sidebar biasanya menutupi dari atas */
    height: 100vh;
    z-index: 1000;
  }

  .sidebar-overlay {
    z-index: 999;
  }
}

</style>

<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'admin') ? '' : 'collapsed'; ?>" href="index.php?page=admin">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-heading">Manajemen Event</li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'venue') ? '' : 'collapsed'; ?>" href="index.php?page=venue">
                <i class="bi bi-building"></i>
                <span>Venue</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'event') ? '' : 'collapsed'; ?>" href="index.php?page=event">
                <i class="bi bi-calendar-event"></i>
                <span>Event</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'tiket') ? '' : 'collapsed'; ?>" href="index.php?page=tiket">
                <i class="bi bi-ticket-perforated"></i>
                <span>Manajemen Tiket</span>
            </a>
        </li>

        <li class="nav-heading">Keuangan</li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'voucher') ? '' : 'collapsed'; ?>" href="index.php?page=voucher">
                <i class="bi bi-gift"></i>
                <span>Promo & Voucher</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && ($_GET['page'] == 'transaksi' || $_GET['page'] == 'detail_transaksi')) ? '' : 'collapsed'; ?>" href="index.php?page=transaksi">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Riwayat Transaksi</span>
            </a>
        </li>

        <li class="nav-heading">Laporan</li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'laporan') ? '' : 'collapsed'; ?>" href="index.php?page=laporan">
                <i class="bi bi-bar-chart-line"></i>
                <span>Laporan Penjualan</span>
            </a>
        </li>
    </ul>
</aside>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
    
    // Opsional: Mencegah scroll pada body saat sidebar aktif di mobile
    if (sidebar.classList.contains("active")) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "auto";
    }
}

// Menutup sidebar jika layar di-resize ke desktop saat sidebar mobile masih terbuka
window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
        document.getElementById("sidebar").classList.remove("active");
        document.getElementById("sidebarOverlay").classList.remove("active");
        document.body.style.overflow = "auto";
    }

});
</script>