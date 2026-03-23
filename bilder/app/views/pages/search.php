<?php

$q = trim((string)($_GET['q'] ?? ''));
$page = pageParam();

if ($q === '') {
    header('Location: ?page=home');
    exit;
}

if (mb_strlen($q) < 2) {
    renderHeader(title('Suche'));
    ?>
    <section class="section">
      <div class="box box-pad">
        Bitte mindestens 2 Zeichen eingeben.
      </div>
    </section>
    <?php
    renderFooter();
    return;
}

if (ctype_digit($q) && imageIdExists((int)$q)) {
    header('Location: ?page=detail&id=' . (int)$q);
    exit;
}

$images = searchImages($q, $page, PER_PAGE);
$total = countSearchResults($q);

renderHeader(title('Suche'));
?>
<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Suche</h1>
    <p class="muted" style="margin-bottom:14px;"><?= $total ?> Treffer für „<?= e(html_entity_decode($q)) ?>“ · neueste zuerst.</p>

    <?php if (empty($images)): ?>
      <p>Keine Treffer gefunden.</p>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($images as $image): renderImageCard($image); endforeach; ?>
      </div>

      <?php renderPager($page, $total, PER_PAGE, ['q' => $q]); ?>
    <?php endif; ?>
  </div>
</section>
<?php
renderFooter();