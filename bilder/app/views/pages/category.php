<?php

$id = (int)($_GET['id'] ?? 0);
$page = pageParam();
$category = getCategory($id);

renderHeader(title((string)($category['name'] ?? 'Kategorie')));

if (!$category): ?>
    <section class="section"><div class="box box-pad">Kategorie nicht gefunden.</div></section>
    <?php renderFooter(); return; ?>
<?php endif;

$total = countImagesInCategory($id);
$images = getImagesInCategory($id, $page, PER_PAGE);
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;"><?= displayText($category['name']) ?></h1>
    <p class="muted" style="margin-bottom:14px;"><?= $total ?> Bilder · neueste zuerst.</p>

    <div class="grid">
      <?php foreach ($images as $image): renderImageCard($image); endforeach; ?>
    </div>

    <?php renderPager($page, $total, PER_PAGE, ['id' => $id]); ?>
  </div>
</section>

<?php renderFooter(); ?>