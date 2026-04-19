<style>
  .header {
    background-color: #ffffff;
    box-shadow: 0px 2px 20px rgba(1, 41, 112, 0.05);
    height: 70px;
    padding: 0 20px;

    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .header .logo span {
    font-size: 24px;
    font-weight: 700;
    color: #1d1145; 
    font-family: "Poppins", sans-serif;
  }

  .header-nav .nav-profile {
    color: #1d1145;
    font-weight: 600;
    transition: 0.3s;
  }

  .header-nav .nav-profile:hover {
    color: #e66c8a;
  }

  .dropdown-menu-arrow.profile {
    min-width: 200px;
    padding: 10px 0;
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 30px rgba(70, 79, 181, 0.15);
  }

  .dropdown-header h6 {
    font-size: 16px;
    margin-bottom: 2px;
    font-weight: 700;
    color: #444;
  }

  .dropdown-item {
    padding: 10px 20px;
    font-size: 14px;
    transition: 0.3s;
  }

  .dropdown-item i {
    font-size: 18px;
    margin-right: 10px;
    color: #777;
  }

  .dropdown-item:hover {
    background-color: #f6f9ff;
    color: #e66c8a;
  }

  .dropdown-item:hover i {
    color: #e66c8a;
  }

  .header-nav .dropdown-menu {
    display: none;
    position: absolute;
    inset: 0px 0px auto auto;
    margin: 0px;
    z-index: 1001;
    border: none;
    box-shadow: 0 5px 30px rgba(70, 79, 181, 0.15);
    transition: all 0.3s;
  }

  .header-nav .dropdown-menu.show {
    display: block;
  }

  .dropdown-menu-end {
    right: 0;
    left: auto;
  }

  .left-area {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .header-nav {
    margin-left: auto;
  }

  .header-nav ul {
    display: flex;
    align-items: center;
    margin: 0;
  }

/* HEADER MOBILE FIX */
@media (max-width: 768px) {

  .header {
    height: 60px;
    padding: 0 12px;

    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  /* LEFT AREA (hamburger + logo) */
  .header .left-area {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* HAMBURGER */
  .header .btn {
    padding: 4px 6px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .header .btn i {
    font-size: 22px;
  }

  /* LOGO */
  .header .logo span {
    font-size: 18px;
    line-height: 1;
  }

  /* RIGHT AREA (PROFILE) */
  .header-nav {
    display: flex;
    align-items: center;
    margin-left: auto;
  }

  .header-nav ul {
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
  }

  .header-nav .nav-profile span {
    display: none; /* hide nama */
  }

  .header-nav .nav-link {
    padding: 0;
    display: flex;
    align-items: center;
  }

  .rounded-circle {
    width: 32px !important;
    height: 32px !important;
  }

}
</style>

<!-- HEADER UNTUK SEMUA HALAMAN, MENAMPILKAN NAMA USER DAN ROLE DARI SESSION, SERTA NAVIGASI LOGOUT -->
<?php
if(!isset($_SESSION)){
  session_start();
}
?>

<header id="header" class="header fixed-top">

  <!-- LEFT -->
  <div class="left-area">

    <!-- HAMBURGER -->
    <button onclick="toggleSidebar()" class="btn d-md-none">
      <i class="bi bi-list"></i>
    </button>

    <!-- LOGO -->
    <a href="index.php" class="logo text-decoration-none">
      <span class="fw-bold text-dark">
        Event<span style="color:#E66C8A;">Ku</span>
      </span>
    </a>

  </div>

  <!-- RIGHT -->
  <nav class="header-nav">
      <ul class="d-flex align-items-center mb-0">

          <li class="nav-item dropdown">
              <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" 
                data-bs-toggle="dropdown" 
                data-bs-offset="0,15"
                aria-expanded="false">
                  
                  <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                      style="width: 35px; height: 35px; border: 1px solid #ddd;">
                      <i class="bi bi-person-fill text-dark"></i>
                  </div>

                  <span class="d-none d-md-block dropdown-toggle ps-2">
                      <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?>
                  </span>
              </a>

              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                  <li class="dropdown-header">
                      <h6><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?></h6>
                      <span class="badge bg-info-light text-primary">
                          <?= ucfirst($_SESSION['user']['role'] ?? 'User'); ?>
                      </span>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                      <a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php">
                          <i class="bi bi-box-arrow-right text-danger"></i>
                          <span>Sign Out</span>
                      </a>
                  </li>
              </ul>
          </li>

      </ul>
  </nav>

</header>