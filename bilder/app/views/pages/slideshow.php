<?php

$images = getRandomSlideshowImages(50);

renderHeader(title('Slideshow'));
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Slideshow</h1>

    <?php if (empty($images)): ?>
      <p>Keine Bilder gefunden.</p>
    <?php else: ?>
      <div class="slideshow-wrap">
        <div class="slideshow-stage">
          <?php foreach ($images as $index => $image): ?>
            <article
              class="slide<?= $index === 0 ? ' active' : '' ?>"
              data-slide-index="<?= $index ?>"
            >
              <a href="?page=detail&id=<?= (int)$image['id'] ?>" class="slide-image-link">
                <img
                  src="<?= e(imagePath($image)) ?>"
                  alt="<?= displayText($image['name']) ?>"
                  class="slide-image"
                >
              </a>

              <div class="slide-caption">
                <div class="date-badge"><?= e(formatDate($image['entrytime'] ?? null)) ?></div>
                <h2><?= displayText($image['name']) ?></h2>
                <p class="muted" style="margin-top:8px;"><?= displayText($image['category_name'] ?? 'Ohne Kategorie') ?></p>

                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
                  <a class="button primary" href="?page=detail&id=<?= (int)$image['id'] ?>">Details</a>
                  <a class="button" href="dl.php?id=<?= (int)$image['id'] ?>">Download</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>

          <button type="button" class="slide-nav prev" id="slidePrev" aria-label="Vorheriges Bild">‹</button>
          <button type="button" class="slide-nav next" id="slideNext" aria-label="Nächstes Bild">›</button>
        </div>

        <div class="slideshow-toolbar">
          <button type="button" class="button" id="slidePlayPause" data-playing="1">Pause</button>
          <span class="meta-pill"><span id="slideCurrent">1</span> / <?= count($images) ?></span>
        </div>

        <div class="slideshow-thumbs">
          <?php foreach ($images as $index => $image): ?>
            <button
              type="button"
              class="slide-thumb<?= $index === 0 ? ' active' : '' ?>"
              data-slide-thumb="<?= $index ?>"
              aria-label="Bild <?= $index + 1 ?>"
            >
              <img src="<?= e(imagePath($image, true)) ?>" alt="<?= displayText($image['name']) ?>">
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php renderFooter(); ?>