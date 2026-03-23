<?php

declare(strict_types=1);

use App\Core\Auth;

$user = Auth::user();
$userName = trim((string)($user['name'] ?? 'Admin'));
$userLevel = (int)($user['level'] ?? 0);

$counterValue = $counter ?? null;

if ($counterValue === null) {
    global $pdo;

    try {
        $stmt = $pdo->query('SELECT count FROM counter LIMIT 1');
        $counterValue = number_format((int)($stmt->fetchColumn() ?: 0), 0, ',', '.');
    } catch (\Throwable $e) {
        $counterValue = '0';
    }
}
?>
<header class="topbar px-3 px-lg-4 py-3 border-bottom border-white border-opacity-10">
    <div class="d-flex flex-column flex-xl-row gap-3 align-items-xl-center justify-content-between">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-link text-white d-lg-none p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <div class="d-none d-lg-block fw-semibold fs-5">Bingbilder Admin Dashboard</div>
            </div>
            <div class="fw-semibold text-center d-xl-none">Counter: <?= e((string)$counterValue) ?></div>
        </div>

        <div class="fw-semibold d-none d-xl-block">Counter: <?= e((string)$counterValue) ?></div>

        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
            <form action="index.php" method="get" class="d-flex gap-2">
                <input type="hidden" name="page" value="edit">
                <input type="number" name="pid" class="form-control" placeholder="ID Aufrufen" required>
                <button class="btn btn-dark" type="submit">Suchen</button>
            </form>

            <div class="rounded px-3 py-2 small fw-semibold" style="background: rgba(9,20,37,.55);">
                Willkommen, <?= e($userName) ?>! - Sie haben <?= $userLevel >= 10 ? 'Admin-Rechte' : 'Zugriff' ?>
            </div>
        </div>
    </div>
</header>