<?php

$categories = getCategoriesLite();
renderHeader(title('Kategorien'));
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Kategorien</h1>
    <p class="muted" style="margin-bottom:14px;">Die bekannten Themenbereiche.</p>

    <div class="cat-grid">
      <?php foreach ($categories as $category): $bg = !empty($category['preview_image']) ? imagePath($category['preview_image'], true) : ''; ?>
        <article class="cat-card"<?= $bg !== '' ? ' style="background-image:url(' . e($bg) . ')"' : '' ?>>
          <div class="cat-card-content">
            <h3><?= displayText($category['name']) ?></h3>
            <p style="margin-top:8px;color:rgba(255,255,255,.85);">
              ID <?= (int)$category['id'] ?> · <?= (int)$category['image_count'] ?> Bilder
            </p>
            <div style="margin-top:10px;">
              <a class="button" href="?page=category&id=<?= (int)$category['id'] ?>">Bilder ansehen</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php renderFooter(); ?>