<?php

declare(strict_types=1);

function renderHeader(string $pageTitle): void
{
    $bgImage = randomBodyBackground();
    $theme = currentTheme();
    $isDark = $theme === 'dark';
    $topCategories = getCategoriesLite();

    require __DIR__ . '/views/partials/header.php';
}

function renderFooter(): void
{
    require __DIR__ . '/views/partials/footer.php';
}

function renderImageCard(array $image): void
{
    ?>
<article class="card">
  <a href="?page=detail&id=<?= (int)$image['id'] ?>">
    <img src="<?= e(imagePath($image, true)) ?>" alt="<?= displayText($image['name']) ?>" loading="lazy">
  </a>
  <div class="card-body">
    <div class="date-badge"><?= e(formatDate($image['entrytime'] ?? null)) ?></div>
    <h3><?= displayText($image['name']) ?></h3>
    <p class="muted" style="margin-top:6px;"><?= displayText($image['category_name'] ?? 'Ohne Kategorie') ?></p>
    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
      <a class="button primary" href="?page=detail&id=<?= (int)$image['id'] ?>">Details</a>
      <a class="button" href="dl.php?id=<?= (int)$image['id'] ?>">Download</a>
    </div>
  </div>
</article>
<?php
}

function renderPager(int $currentPage, int $totalItems, int $perPage, array $extra = []): void
{
    $totalPages = (int)ceil($totalItems / $perPage);

    if ($totalPages <= 1) {
        return;
    }

    $params = array_merge(['page' => getPage()], $extra);

    $buildLink = function (int $page, ?string $label = null, string $class = 'button') use ($params, $currentPage): string {
        $params['p'] = $page;
        $href = '?' . http_build_query($params);
        $label = $label ?? (string)$page;

        if ($page === $currentPage && $label === (string)$page) {
            $class = 'button primary';
        }

        return '<a class="' . $class . '" href="' . e($href) . '">' . e($label) . '</a>';
    };

    $pages = [];
    $pages[] = 1;

    $start = max(2, $currentPage - 2);
    $end = min($totalPages - 1, $currentPage + 2);

    if ($currentPage <= 4) {
        $end = min($totalPages - 1, 5);
    }

    if ($currentPage >= $totalPages - 3) {
        $start = max(2, $totalPages - 4);
    }

    if ($start > 2) {
        $pages[] = '...';
    }

    for ($i = $start; $i <= $end; $i++) {
        $pages[] = $i;
    }

    if ($end < $totalPages - 1) {
        $pages[] = '...';
    }

    if ($totalPages > 1) {
        $pages[] = $totalPages;
    }

    echo '<div class="pager">';

    if ($currentPage > 1) {
        echo $buildLink($currentPage - 1, '«');
    }

    foreach ($pages as $item) {
        if ($item === '...') {
            echo '<span class="button" style="pointer-events:none;opacity:.75;">…</span>';
        } else {
            echo $buildLink((int)$item);
        }
    }

    if ($currentPage < $totalPages) {
        echo $buildLink($currentPage + 1, '»');
    }

    echo '</div>';
}