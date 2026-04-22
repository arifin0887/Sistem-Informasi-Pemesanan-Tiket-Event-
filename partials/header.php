<?php
if(!isset($_SESSION)){
  session_start();
}
?>

<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between px-3" style="min-width: 280px;">
    <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
      <span class="fw-bold fs-4" style="color: var(--primary-color); letter-spacing: -0.5px;">
        Event<span style="color: var(--secondary-color);">Ku</span>
      </span>
    </a>
    
    <button onclick="toggleSidebar()" class="btn toggle-sidebar-btn d-md-none border-0">
      <i class="bi bi-list fs-3" style="color: var(--primary-color);"></i>
    </button>
  </div>

  <nav class="header-nav ms-auto pe-4">
    <ul class="d-flex align-items-center mb-0 list-unstyled">

      <li class="nav-item dropdown">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" 
           data-bs-toggle="dropdown">
          
          <div class="avatar-circle d-flex align-items-center justify-content-center shadow-sm">
            <i class="bi bi-person-fill"></i>
          </div>

          <span class="d-none d-md-block dropdown-toggle ps-2 fw-semibold text-dark">
            <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?>
          </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow-lg border-0 mt-3">
          <li class="dropdown-header text-center py-3">
            <h6 class="mb-1 fw-bold" style="color: var(--primary-color);">
                <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?>
            </h6>
            <span class="badge rounded-pill" style="background-color: rgba(13, 181, 187, 0.1); color: var(--secondary-color);">
                <?= ucfirst($_SESSION['user']['role'] ?? 'User'); ?>
            </span>
          </li>
          <li><hr class="dropdown-divider"></li>
          
          <li>
            <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="../logout.php">
              <i class="bi bi-box-arrow-right me-2"></i>
              <span>Sign Out</span>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </nav>

</header>