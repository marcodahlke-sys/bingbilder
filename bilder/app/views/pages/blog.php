<?php

$page = pageParam();
$total = countAllImages();
$images = getExploreImages($page, PER_PAGE);

renderHeader(title('Blogstyle'));
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Blogstyle</h1>
    <p class="muted" style="margin-bottom:14px;">Der bekannte chronologische Bereich.</p>

    <div class="grid">
      <?php foreach ($images as $image): renderImageCard($image); endforeach; ?>
    </div>

    <?php renderPager($page, $total, PER_PAGE); ?>
  </div>
</section>

<?php renderFooter(); ?>