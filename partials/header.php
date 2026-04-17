<style>
  .header {
    background-color: #ffffff;
    box-shadow: 0px 2px 20px rgba(1, 41, 112, 0.05); 
    padding-left: 20px;
    height: 70px;
    transition: all 0.5s;
    z-index: 997;
  }

  .header .logo img {
    max-height: 35px;
    margin-right: 10px;
  }

  .header .logo span {
    font-size: 24px;
    font-weight: 700;
    color: #1d1145; 
    font-family: "Poppins", sans-serif;
  }

  .search-bar {
    min-width: 360px;
    padding: 0 20px;
  }

  .search-form {
    width: 100%;
  }

  .search-form input {
    border: 0;
    font-size: 14px;
    color: #012970;
    border: 1px solid rgba(1, 41, 112, 0.1);
    padding: 8px 15px;
    border-radius: 50px 0 0 50px;
    width: 100%;
    transition: 0.3s;
  }

  .search-form input:focus {
    box-shadow: none;
    border: 1px solid #e66c8a; 
    outline: none;
  }

  .search-form button {
    border: 0;
    padding: 8px 15px;
    background: #1d1145;
    color: #fff;
    border-radius: 0 50px 50px 0;
    transition: 0.3s;
  }

  .search-form button:hover {
    background: #e66c8a;
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
</style>

<!-- HEADER UNTUK SEMUA HALAMAN, MENAMPILKAN NAMA USER DAN ROLE DARI SESSION, SERTA NAVIGASI LOGOUT -->
<?php
if(!isset($_SESSION)){
  session_start();
}
?>

<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">EventKu</span>
      </a>
    </div>

    <button onclick="toggleSidebar()" class="btn btn-primary d-md-none">
        <i class="bi bi-list"></i>
    </button>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle" href="#">
            <i class="bi bi-search" style="font-size: 1.2rem; color: #1d1145;"></i>
          </a>
        </li>

        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; border: 1px solid #ddd;">
              <i class="bi bi-person-fill" style="color: #1d1145;"></i>
            </div>
            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?php echo isset($_SESSION['user']['nama']) ? htmlspecialchars($_SESSION['user']['nama']) : 'User'; ?>
            </span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo isset($_SESSION['user']['nama']) ? htmlspecialchars($_SESSION['user']['nama']) : 'User'; ?></h6>
              <span class="badge bg-info-light text-primary"><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars(ucfirst($_SESSION['user']['role'])) : 'User'; ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>

            <hr class="dropdown-divider"></li>

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