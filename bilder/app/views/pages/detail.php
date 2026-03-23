<?php

$id = (int)($_GET['id'] ?? 0);
$image = getImageDetail($id);

renderHeader(title('Detailansicht'));

if (!$image) {
    echo '<section class="section"><div class="box box-pad">Bild nicht gefunden.</div></section>';
    renderFooter();
    return;
}

$tagsDetailed = getImageTagsDetailed($id);
$tags = array_map(static fn ($row) => (string)($row['tag'] ?? ''), $tagsDetailed);
$likes = getImageLikesCount($id);
$related = getRelatedImages((int)$image['to_kat'], $id, 4);
?>

<section class="section">
  <div class="detail">
    <article class="box detail-main">
      <img
        src="<?= e(imagePath($image)) ?>"
        alt="<?= displayText($image['name']) ?>"
        id="detailImage"
      >
    </article>

    <div class="lightbox" id="lightbox" aria-hidden="true">
      <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Lightbox schließen">×</button>
      <img src="<?= e(imagePath($image)) ?>" alt="<?= displayText($image['name']) ?>">
    </div>

    <aside class="box box-pad">
      <div class="date-badge"><?= e(formatDate($image['entrytime'] ?? null)) ?></div>
      <h1 style="margin-bottom:6px;"><?= displayText($image['name']) ?></h1>
      <p class="muted"><?= displayText($image['category_name'] ?? 'Ohne Kategorie') ?></p>

      <div class="rows">
        <div class="row"><span class="muted">Dateigröße</span><span><?= e(formatBytesValue($image['size'] ?? null)) ?></span></div>
        <div class="row"><span class="muted">Downloads</span><span><?= (int)($image['downloads'] ?? 0) ?></span></div>
        <div class="row"><span class="muted">Likes</span><span id="detailLikeCountText"><?= $likes ?></span></div>
      </div>

      <?php if (!empty($image['beschreibung'])): ?>
        <div>
          <h3 style="margin-bottom:8px;">Beschreibung</h3>
          <p class="muted"><?= nl2br(displayText($image['beschreibung'])) ?></p>
        </div>
      <?php endif; ?>

      <div style="margin-top:14px;">
        <h3 style="margin-bottom:8px;">Tags</h3>

        <?php if (!empty($tags)): ?>
          <div class="tag-row">
            <?php foreach ($tags as $tag): ?>
              <a class="tag" href="?page=search&q=<?= urlencode((string)$tag) ?>"><?= displayText((string)$tag) ?></a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="muted">Keine Tags vorhanden.</p>
        <?php endif; ?>
      </div>

      <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <a class="button primary" href="dl.php?id=<?= (int)$image['id'] ?>">Download</a>

        <button
          type="button"
          class="button"
          id="detailLikeButton"
          data-id="<?= (int)$image['id'] ?>"
          aria-label="Like umschalten"
        >
          <span id="detailLikeIcon"><?= hasUserLikedImageByIp((int)$image['id']) ? '❤' : '♡' ?></span>
          <span id="detailLikeCount"><?= $likes ?></span>
        </button>

        <a class="button" href="?page=category&id=<?= (int)$image['to_kat'] ?>">Zur Kategorie</a>
      </div>
    </aside>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section">
  <div class="box box-pad">
    <h2 style="margin-bottom:6px;">Ähnliche Bilder</h2>
    <p class="muted" style="margin-bottom:14px;">Mehr aus derselben Kategorie.</p>

    <div class="grid">
      <?php foreach ($related as $rel): renderImageCard($rel); endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const detailImage = document.getElementById('detailImage');
  const lightbox = document.getElementById('lightbox');
  const lightboxClose = document.getElementById('lightboxClose');
  const likeButton = document.getElementById('detailLikeButton');
  const likeCount = document.getElementById('detailLikeCount');
  const likeCountText = document.getElementById('detailLikeCountText');
  const likeIcon = document.getElementById('detailLikeIcon');

  function closeLightbox() {
    if (!lightbox) {
      return;
    }
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  if (detailImage && lightbox && lightboxClose) {
    detailImage.addEventListener('click', function () {
      lightbox.classList.add('open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    });

    lightboxClose.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeLightbox();
      }
    });
  }

  if (likeButton && likeCount && likeIcon) {
    likeButton.addEventListener('click', function () {
      const imageId = likeButton.getAttribute('data-id');
      if (!imageId) {
        return;
      }

      const body = new URLSearchParams();
      body.append('action', 'like_toggle');
      body.append('image_id', imageId);

      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok) {
          return;
        }

        likeIcon.textContent = data.liked ? '❤' : '♡';
        likeCount.textContent = data.count;
        if (likeCountText) {
          likeCountText.textContent = data.count;
        }
      })
      .catch(function () {});
    });
  }
});
</script>

<?php
renderFooter();