<nav class="navbar navbar-expand-lg topbar px-4">
    <button id="sidebarToggle" class="btn btn-link text-dark">
        <i class="bi bi-list fs-4"></i>
    </button>
    <span class="fw-semibold text-dark ms-2"><?= $pageTitle ?? '' ?></span>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-muted small"><i class="bi bi-clock me-1"></i><?= date('D, d M Y') ?></span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</nav>
