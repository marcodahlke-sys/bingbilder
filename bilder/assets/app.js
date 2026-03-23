document.addEventListener('DOMContentLoaded', function () {
  const detailImage = document.getElementById('detailImage');
  const lightbox = document.getElementById('lightbox');
  const lightboxClose = document.getElementById('lightboxClose');
  const likeButton = document.getElementById('detailLikeButton');
  const likeCount = document.getElementById('detailLikeCount');
  const likeCountText = document.getElementById('detailLikeCountText');
  const likeIcon = document.getElementById('detailLikeIcon');

  function postLike(imageId) {
    const body = new URLSearchParams();
    body.append('action', 'like_toggle');
    body.append('image_id', imageId);

    return fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    });
  }

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

      postLike(imageId)
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

  const archiveTriggers = document.querySelectorAll('.js-lightbox-trigger');
  const archiveLightbox = document.getElementById('archiveLightbox');
  const archiveLightboxImage = document.getElementById('archiveLightboxImage');
  const archiveLightboxCaption = document.getElementById('archiveLightboxCaption');
  const archiveLightboxClose = document.getElementById('archiveLightboxClose');

  if (archiveLightbox && archiveLightboxImage && archiveLightboxCaption && archiveLightboxClose) {
    archiveTriggers.forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        const full = trigger.getAttribute('data-full') || '';
        const title = trigger.getAttribute('data-title') || '';

        archiveLightboxImage.src = full;
        archiveLightboxImage.alt = title;
        archiveLightboxCaption.textContent = title;
        archiveLightbox.classList.add('open');
        archiveLightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      });
    });

    function closeArchiveLightbox() {
      archiveLightbox.classList.remove('open');
      archiveLightbox.setAttribute('aria-hidden', 'true');
      archiveLightboxImage.src = '';
      document.body.style.overflow = '';
    }

    archiveLightboxClose.addEventListener('click', closeArchiveLightbox);

    archiveLightbox.addEventListener('click', function (event) {
      if (event.target === archiveLightbox) {
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

      postLike(imageId)
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

  const themeToggle = document.querySelector('[data-theme-toggle]');
  const themeToggleIcon = document.getElementById('themeToggleIcon');

  if (themeToggle) {
    themeToggle.addEventListener('click', function () {
      const body = document.body;
      const isDark = body.classList.contains('theme-dark');
      const nextTheme = isDark ? 'light' : 'dark';

      body.classList.remove('theme-dark', 'theme-light');
      body.classList.add('theme-' + nextTheme);

      if (themeToggleIcon) {
        themeToggleIcon.textContent = nextTheme === 'dark' ? '☀' : '☾';
      }

      themeToggle.setAttribute('data-current-theme', nextTheme);
      themeToggle.setAttribute('aria-label', nextTheme === 'dark' ? 'Heller Modus' : 'Dunkler Modus');
      themeToggle.setAttribute('title', nextTheme === 'dark' ? 'Heller Modus' : 'Dunkler Modus');

      const formData = new URLSearchParams();
      formData.append('action', 'theme_toggle');
      formData.append('theme', nextTheme);

      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: formData.toString()
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok) {
          throw new Error('Theme konnte nicht gespeichert werden.');
        }
      })
      .catch(function () {
        const rollbackTheme = nextTheme === 'dark' ? 'light' : 'dark';
        body.classList.remove('theme-dark', 'theme-light');
        body.classList.add('theme-' + rollbackTheme);

        if (themeToggleIcon) {
          themeToggleIcon.textContent = rollbackTheme === 'dark' ? '☀' : '☾';
        }

        themeToggle.setAttribute('data-current-theme', rollbackTheme);
        themeToggle.setAttribute('aria-label', rollbackTheme === 'dark' ? 'Heller Modus' : 'Dunkler Modus');
        themeToggle.setAttribute('title', rollbackTheme === 'dark' ? 'Heller Modus' : 'Dunkler Modus');
      });
    });
  }
});

  const slides = document.querySelectorAll('.slide');
  const slideThumbs = document.querySelectorAll('[data-slide-thumb]');
  const slideCurrent = document.getElementById('slideCurrent');
  const slidePrev = document.getElementById('slidePrev');
  const slideNext = document.getElementById('slideNext');
  const slidePlayPause = document.getElementById('slidePlayPause');

  if (slides.length > 0) {
    let currentSlide = 0;
    let isPlaying = true;
    let slideTimer = null;

    function showSlide(index) {
      if (slides.length === 0) {
        return;
      }

      if (index < 0) {
        index = slides.length - 1;
      }

      if (index >= slides.length) {
        index = 0;
      }

      slides.forEach(function (slide, i) {
        slide.classList.toggle('active', i === index);
      });

      slideThumbs.forEach(function (thumb, i) {
        thumb.classList.toggle('active', i === index);
      });

      currentSlide = index;

      if (slideCurrent) {
        slideCurrent.textContent = String(index + 1);
      }
    }

    function nextSlide() {
      showSlide(currentSlide + 1);
    }

    function prevSlideFn() {
      showSlide(currentSlide - 1);
    }

    function startSlideshow() {
      stopSlideshow();
      slideTimer = window.setInterval(function () {
        nextSlide();
      }, 4000);
      isPlaying = true;

      if (slidePlayPause) {
        slidePlayPause.textContent = 'Pause';
        slidePlayPause.setAttribute('data-playing', '1');
      }
    }

    function stopSlideshow() {
      if (slideTimer !== null) {
        window.clearInterval(slideTimer);
        slideTimer = null;
      }
      isPlaying = false;

      if (slidePlayPause) {
        slidePlayPause.textContent = 'Play';
        slidePlayPause.setAttribute('data-playing', '0');
      }
    }

    if (slideNext) {
      slideNext.addEventListener('click', function () {
        nextSlide();
      });
    }

    if (slidePrev) {
      slidePrev.addEventListener('click', function () {
        prevSlideFn();
      });
    }

    slideThumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const index = Number(thumb.getAttribute('data-slide-thumb') || 0);
        showSlide(index);
      });
    });

    if (slidePlayPause) {
      slidePlayPause.addEventListener('click', function () {
        if (isPlaying) {
          stopSlideshow();
        } else {
          startSlideshow();
        }
      });
    }

    document.addEventListener('keydown', function (event) {
      if (!slides.length) {
        return;
      }

      if (event.key === 'ArrowRight') {
        nextSlide();
      }

      if (event.key === 'ArrowLeft') {
        prevSlideFn();
      }
    });

    showSlide(0);
    startSlideshow();
  }
  
  const mobileNavToggle = document.getElementById('mobileNavToggle');
  const mobileNavArea = document.getElementById('mobileNavArea');

  if (mobileNavToggle && mobileNavArea) {
    mobileNavToggle.addEventListener('click', function () {
      const isOpen = mobileNavArea.classList.toggle('open');

      mobileNavToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      mobileNavToggle.setAttribute('aria-label', isOpen ? 'Menü schließen' : 'Menü öffnen');
      mobileNavToggle.textContent = isOpen ? '✕' : '☰';
    });
  }