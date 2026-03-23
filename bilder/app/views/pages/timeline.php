<?php

$total = countTimelineImages();

renderHeader(title('Timeline'));
?>

<section class="section">
  <div class="box box-pad">
    <h1 style="margin-bottom:6px;">Timeline</h1>
    <p class="muted" style="margin-bottom:14px;">Klassische vertikale Timeline mit allen Bildern. Es werden zuerst nur die sichtbaren Einträge geladen.</p>

    <div
      class="timeline timeline-lazy"
      id="timeline"
      data-total="<?= (int)$total ?>"
      data-feed-url="<?= e(BASE_PATH) ?>/timeline-feed.php"
    ></div>

    <div class="timeline-status muted" id="timelineStatus">Lade Timeline …</div>
    <div id="timelineSentinel" aria-hidden="true"></div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const timeline = document.getElementById('timeline');
  const status = document.getElementById('timelineStatus');
  const sentinel = document.getElementById('timelineSentinel');

  if (!timeline || !status || !sentinel) {
    return;
  }

  const feedUrl = timeline.getAttribute('data-feed-url') || '';
  const total = Number(timeline.getAttribute('data-total') || '0');

  let offset = 0;
  let loading = false;
  let hasMore = total > 0;
  let estimatedItemHeight = 320;
  let observer = null;

  function getScreenBatchSize() {
    const firstItem = timeline.querySelector('.timeline-item');

    if (firstItem) {
      const rect = firstItem.getBoundingClientRect();
      if (rect.height > 120) {
        estimatedItemHeight = rect.height + 24;
      }
    }

    const viewportHeight = window.innerHeight || 900;
    return Math.max(4, Math.ceil(viewportHeight / estimatedItemHeight) + 1);
  }

  function updateStatus() {
    if (total <= 0) {
      status.textContent = 'Keine Bilder gefunden.';
      return;
    }

    if (offset >= total) {
      status.textContent = 'Alle ' + total + ' Bilder geladen.';
      return;
    }

    status.textContent = offset + ' von ' + total + ' Bildern geladen.';
  }

  function shouldLoadMoreByViewport() {
    if (!hasMore || loading) {
      return false;
    }

    const sentinelRect = sentinel.getBoundingClientRect();
    return sentinelRect.top <= window.innerHeight + 300;
  }

  function loadMore(limit) {
    if (loading || !hasMore || !feedUrl) {
      return Promise.resolve();
    }

    loading = true;
    status.textContent = 'Lade weitere Bilder …';

    return fetch(feedUrl + '?offset=' + encodeURIComponent(offset) + '&limit=' + encodeURIComponent(limit), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      cache: 'no-store'
    })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('HTTP ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      if (!data || !data.ok) {
        throw new Error('Timeline konnte nicht geladen werden.');
      }

      if (data.html) {
        timeline.insertAdjacentHTML('beforeend', data.html);
      }

      offset = Number(data.next_offset || offset);
      hasMore = Boolean(data.has_more);
      updateStatus();
    })
    .catch(function (error) {
      console.error('Timeline-Feed-Fehler:', error);
      status.textContent = 'Fehler beim Laden der Timeline.';
    })
    .finally(function () {
      loading = false;
    });
  }

  function fillViewportInitially() {
    function step() {
      if (!hasMore || loading) {
        return;
      }

      if (document.documentElement.scrollHeight <= window.innerHeight + 120) {
        loadMore(getScreenBatchSize()).then(function () {
          if (document.documentElement.scrollHeight <= window.innerHeight + 120 && hasMore) {
            step();
          }
        });
      }
    }

    step();
  }

  function maybeLoadMore() {
    if (shouldLoadMoreByViewport()) {
      loadMore(getScreenBatchSize());
    }
  }

  observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        maybeLoadMore();
      }
    });
  }, {
    root: null,
    rootMargin: '400px 0px',
    threshold: 0
  });

  observer.observe(sentinel);

  window.addEventListener('scroll', maybeLoadMore, { passive: true });
  window.addEventListener('resize', function () {
    maybeLoadMore();
    fillViewportInitially();
  });

  loadMore(getScreenBatchSize()).then(function () {
    fillViewportInitially();
    maybeLoadMore();
  });
});
</script>

<?php renderFooter(); ?>