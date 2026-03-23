<?php

declare(strict_types=1);

$currentPage = $_GET['page'] ?? 'home';

function isActivePage(string $page, array $aliases = []): bool
{
    global $currentPage;
    return $currentPage === $page || in_array($currentPage, $aliases, true);
}
?>
<div class="app-shell d-flex">
    <aside class="sidebar d-none d-lg-flex flex-column p-3">
        <div class="px-2 pb-2 small text-white-50">Menü</div>

        <nav class="nav flex-column gap-1">
            <a class="nav-link <?= isActivePage('home') ? 'active' : '' ?>" href="index.php?page=home">
                <i class="bi bi-house-door me-2"></i>Startseite
            </a>

            <a class="nav-link <?= isActivePage('upload') ? 'active' : '' ?>" href="index.php?page=upload">
                <i class="bi bi-cloud-arrow-up me-2"></i>Upload
            </a>

            <a class="nav-link d-flex justify-content-between align-items-center <?= isActivePage('rename', ['files']) ? 'active' : '' ?>"
               data-bs-toggle="collapse"
               href="#filesSubmenu"
               role="button"
               aria-expanded="false"
               aria-controls="filesSubmenu">
                <span><i class="bi bi-images me-2"></i>Dateien</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse ps-3" id="filesSubmenu">
                <a class="submenu-link nav-link <?= isActivePage('home') ? 'active' : '' ?>" href="index.php?page=home">Startseite</a>
                <a class="submenu-link nav-link <?= isActivePage('rename') ? 'active' : '' ?>" href="index.php?page=rename">Datei umbenennen</a>
                <a class="submenu-link nav-link <?= isActivePage('files') ? 'active' : '' ?>" href="index.php?page=files">Dateienliste</a>
            </div>

            <a class="nav-link d-flex justify-content-between align-items-center <?= isActivePage('tags', ['empty-tags', 'tagcloud']) ? 'active' : '' ?>"
               data-bs-toggle="collapse"
               href="#tagsSubmenu"
               role="button"
               aria-expanded="false"
               aria-controls="tagsSubmenu">
                <span><i class="bi bi-tags me-2"></i>Tags</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse ps-3" id="tagsSubmenu">
                <a class="submenu-link nav-link <?= isActivePage('tags') ? 'active' : '' ?>" href="index.php?page=tags">Tags verwalten</a>
                <a class="submenu-link nav-link <?= isActivePage('tagcloud') ? 'active' : '' ?>" href="index.php?page=tagcloud">Tag-Cloud</a>
                <a class="submenu-link nav-link <?= isActivePage('empty-tags') ? 'active' : '' ?>" href="index.php?page=empty-tags">Leere Tags</a>
            </div>

            <a class="nav-link <?= isActivePage('downloads') ? 'active' : '' ?>" href="index.php?page=downloads">
                <i class="bi bi-download me-2"></i>Top Downloads
            </a>

            <a class="nav-link <?= isActivePage('likes') ? 'active' : '' ?>" href="index.php?page=likes">
                <i class="bi bi-heart me-2"></i>Top Likes
            </a>

            <a class="nav-link <?= isActivePage('bing-download') ? 'active' : '' ?>" href="index.php?page=bing-download">
                <i class="bi bi-image me-2"></i>Bing-Download
            </a>

            <div class="pt-2">
                <a class="btn btn-danger w-100" href="index.php?page=logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </nav>
    </aside>

    <div class="flex-grow-1 min-vh-100">
        <div class="offcanvas offcanvas-start text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="background: rgba(7,17,30,.95);">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="mobileSidebarLabel">Bingbilder Admin Dashboard</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <nav class="nav flex-column gap-1">
                    <a class="nav-link text-white" href="index.php?page=home">Startseite</a>
                    <a class="nav-link text-white" href="index.php?page=upload">Upload</a>
                    <a class="nav-link text-white" href="index.php?page=rename">Datei umbenennen</a>
                    <a class="nav-link text-white" href="index.php?page=files">Dateienliste</a>
                    <a class="nav-link text-white" href="index.php?page=tags">Tags verwalten</a>
                    <a class="nav-link text-white" href="index.php?page=tagcloud">Tag-Cloud</a>
                    <a class="nav-link text-white" href="index.php?page=empty-tags">Leere Tags</a>
                    <a class="nav-link text-white" href="index.php?page=downloads">Top Downloads</a>
                    <a class="nav-link text-white" href="index.php?page=likes">Top Likes</a>
                    <a class="nav-link text-white" href="index.php?page=bing-download">Bing-Download</a>

                    <div class="pt-2">
                        <a class="btn btn-danger w-100" href="index.php?page=logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </nav>
            </div>
        </div>