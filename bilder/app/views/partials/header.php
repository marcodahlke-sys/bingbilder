<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= e(BASE_PATH) ?>/assets/app.css">
  <link rel="icon" href="<?= e(BASE_PATH) ?>/favicon.ico" sizes="any">
  <link rel="shortcut icon" href="<?= e(BASE_PATH) ?>/favicon.ico">
  <style>
    :root {
      --radius: 10px;
      --shadow-dark: 0 12px 28px rgba(0,0,0,.30);
      --shadow-light: 0 12px 28px rgba(0,0,0,.08);
      --body-bg-image: url('<?= e($bgImage) ?>');
    }
  </style>
</head>
<body class="<?= $isDark ? 'theme-dark' : 'theme-light' ?>">
<div class="page-wrap">
  <header class="topbar">
    <div class="inner topbar-inner">
      <div class="topbar-head">
        <div class="site-brand">
          <h1 class="site-title">
            <a href="index.php" style="text-decoration: none;">
            <span class="site-title-main">
              <span class="site-title-main-white">Bing</span><span class="site-title-main-green">bilder</span>
            </span>
            <span class="site-title-sub">Bing Hintergründe</span></a>
          </h1>
        </div>

        <div class="topbar-actions">
          <span class="meta-pill"><?= countAllImages() ?> Bilder</span>

          <button
            type="button"
            class="meta-pill theme-toggle"
            id="themeToggle"
            data-theme-toggle
            data-current-theme="<?= $isDark ? 'dark' : 'light' ?>"
            aria-label="<?= $isDark ? 'Heller Modus' : 'Dunkler Modus' ?>"
            title="<?= $isDark ? 'Heller Modus' : 'Dunkler Modus' ?>"
          >
            <span id="themeToggleIcon"><?= $isDark ? '☀' : '☾' ?></span>
          </button>

          <button
            type="button"
            class="meta-pill mobile-nav-toggle"
            id="mobileNavToggle"
            aria-label="Menü öffnen"
            aria-expanded="false"
            aria-controls="mobileNavArea"
          >
            ☰
          </button>
        </div>
      </div>

      <div class="meta-top desktop-only">
        <span class="meta-pill"><?= countAllImages() ?> Bilder</span>
      </div>

      <div class="nav-area" id="mobileNavArea">
        <nav class="nav-search-row">
          <div class="navline">
            <a class="<?= getPage() === 'home' ? 'active' : '' ?>" href="?page=home">Home</a>
            <a class="<?= getPage() === 'blog' ? 'active' : '' ?>" href="?page=blog">Blogstyle</a>
            <a class="<?= getPage() === 'archive' ? 'active' : '' ?>" href="?page=archive">Kalenderblatt</a>
            <a class="<?= getPage() === 'timeline' ? 'active' : '' ?>" href="?page=timeline">Timeline</a>
            <a class="<?= getPage() === 'slideshow' ? 'active' : '' ?>" href="?page=slideshow">Slideshow</a>

            <?php foreach ($topCategories as $category): ?>
              <a class="<?= getPage() === 'category' && (int)($_GET['id'] ?? 0) === (int)$category['id'] ? 'active' : '' ?>" href="?page=category&id=<?= (int)$category['id'] ?>">
                <?= displayText($category['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>

          <form class="searchbar" method="get">
            <input type="hidden" name="page" value="search">
            <input type="search" name="q" placeholder="Suche ...">
            <button type="submit">Suchen</button>
          </form>
        </nav>
      </div>
    </div>
  </header>

  <main class="content">
    <div class="inner">