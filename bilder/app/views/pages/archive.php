<?php

$monate = monthOptionsGerman();
$aktuellerMonat = !empty($_GET['amonat']) ? (int)$_GET['amonat'] : (int)date('n');
$aktuellesJahr = !empty($_GET['ajahr']) ? (int)$_GET['ajahr'] : (int)date('Y');

if ($aktuellerMonat < 1 || $aktuellerMonat > 12) {
    $aktuellerMonat = (int)date('n');
}

$jahrStart = 2009;
$jahrEnde = (int)date('Y');

$calendarHtml = getArchiveCalendarHtml($aktuellerMonat, $aktuellesJahr);

renderHeader(title('Kalenderblatt'));
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Kalenderblatt</h1>
    <p class="muted" style="margin-bottom:14px;">Monatsansicht mit Bildern, Infos, Likes und Tags direkt im Kalendertag.</p>

    <form method="get" class="archive-filter">
      <input type="hidden" name="page" value="archive">

      <select name="amonat" id="amonat">
        <?php foreach ($monate as $num => $name): ?>
          <option value="<?= $num ?>"<?= $num === $aktuellerMonat ? ' selected' : '' ?>><?= e($name) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="ajahr" id="ajahr">
        <?php for ($jahr = $jahrEnde; $jahr >= $jahrStart; $jahr--): ?>
          <option value="<?= $jahr ?>"<?= $jahr === $aktuellesJahr ? ' selected' : '' ?>><?= $jahr ?></option>
        <?php endfor; ?>
      </select>

      <button type="submit" class="button primary">Go</button>
    </form>

    <?= $calendarHtml ?>
  </div>
</section>

<div class="archive-lightbox" id="archiveLightbox" aria-hidden="true">
  <div class="archive-lightbox-inner">
    <button type="button" class="archive-lightbox-close" id="archiveLightboxClose" aria-label="Schließen">×</button>
    <img src="" alt="" id="archiveLightboxImage">
    <div class="archive-lightbox-caption" id="archiveLightboxCaption"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const triggers = document.querySelectorAll('.js-lightbox-trigger');
  const lightbox = document.getElementById('archiveLightbox');
  const lightboxImage = document.getElementById('archiveLightboxImage');
  const lightboxCaption = document.getElementById('archiveLightboxCaption');
  const lightboxClose = document.getElementById('archiveLightboxClose');

  if (lightbox && lightboxImage && lightboxCaption && lightboxClose) {
    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        const full = trigger.getAttribute('data-full') || '';
        const title = trigger.getAttribute('data-title') || '';

        lightboxImage.src = full;
        lightboxImage.alt = title;
        lightboxCaption.textContent = title;
        lightbox.classList.add('open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      });
    });

    function closeArchiveLightbox() {
      lightbox.classList.remove('open');
      lightbox.setAttribute('aria-hidden', 'true');
      lightboxImage.src = '';
      lightboxImage.alt = '';
      lightboxCaption.textContent = '';
      document.body.style.overflow = '';
    }

    lightboxClose.addEventListener('click', closeArchiveLightbox);

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) {
        closeArchiveLightbox();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeArchiveLightbox();
      }
    });
  }

  document.querySelectorAll('.archive-like-btn').forEach(function (button) {
    button.addEventListener('click', function () {
      const imageId = button.getAttribute('data-id');
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

        button.textContent = data.liked ? '❤' : '♡';

        const counter = document.getElementById('archive-like-count-' + imageId);
        if (counter) {
          counter.textContent = data.count;
        }
      })
      .catch(function () {});
    });
  });
});
</script>

<?php renderFooter(); ?>