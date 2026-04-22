
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="sidebar" class="sidebar shadow-sm">
    <ul class="sidebar-nav" id="sidebar-nav">
        
        <li class="nav-item">
            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'admin' || $_GET['page'] == 'dashboard') ? '' : 'collapsed'; ?>" href="index.php?page=dashboard">
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
            <a class="nav-link <?php echo (isset($_GET['page']) && ($_GET['page'] == 'transaksi' || $_GET['page'] == 'detail')) ? '' : 'collapsed'; ?>" href="index.php?page=transaksi">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Riwayat Transaksi</span>
            </a>
        </li>

        <li class="nav-heading">Analisis</li>

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