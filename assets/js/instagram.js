/**
 * FF Addons — Instagram gallery polish
 * Lightbox (Esc / backdrop / close / arrows) + scroll fade-in via IntersectionObserver.
 */
(function () {
  'use strict';

  var ROOT_SEL = '.instagram-section-wrapper';
  var ITEM_SEL = '.instagram-item[data-instagram-src]';
  var lightbox = null;
  var lastFocus = null;
  var currentIndex = -1;
  var fadeObserver = null;

  function qs(el, sel) {
    return el.querySelector(sel);
  }

  function preferReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function getItems() {
    return Array.prototype.slice.call(document.querySelectorAll(ITEM_SEL));
  }

  function itemData(el) {
    var img = el.querySelector('img');
    return {
      el: el,
      src: el.getAttribute('data-instagram-src') || '',
      caption: el.getAttribute('data-instagram-caption') || '',
      link: el.getAttribute('data-instagram-link') || '',
      alt: img ? img.getAttribute('alt') || '' : '',
    };
  }

  function buildLightbox() {
    if (lightbox) {
      return lightbox;
    }

    var root = document.createElement('div');
    root.className = 'ff-ig-lightbox';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-hidden', 'true');
    root.setAttribute('hidden', '');
    root.innerHTML =
      '<div class="ff-ig-lightbox__backdrop" data-ig-close tabindex="-1"></div>' +
      '<div class="ff-ig-lightbox__dialog" role="document">' +
      '  <button type="button" class="ff-ig-lightbox__close" data-ig-close aria-label="Close">' +
      '    <span aria-hidden="true">&times;</span>' +
      '  </button>' +
      '  <button type="button" class="ff-ig-lightbox__nav ff-ig-lightbox__nav--prev" data-ig-prev aria-label="Previous image">' +
      '    <span aria-hidden="true">&#10094;</span>' +
      '  </button>' +
      '  <button type="button" class="ff-ig-lightbox__nav ff-ig-lightbox__nav--next" data-ig-next aria-label="Next image">' +
      '    <span aria-hidden="true">&#10095;</span>' +
      '  </button>' +
      '  <figure class="ff-ig-lightbox__figure">' +
      '    <img class="ff-ig-lightbox__img" alt="" />' +
      '    <figcaption class="ff-ig-lightbox__caption" hidden></figcaption>' +
      '  </figure>' +
      '  <a class="ff-ig-lightbox__link" href="#" target="_blank" rel="noopener noreferrer" hidden>View post</a>' +
      '</div>';

    document.body.appendChild(root);
    lightbox = root;

    root.addEventListener('click', function (e) {
      var t = e.target;
      if (!(t instanceof Element)) {
        return;
      }
      if (t.closest('[data-ig-close]')) {
        closeLightbox();
        return;
      }
      if (t.closest('[data-ig-prev]')) {
        showAt(currentIndex - 1);
        return;
      }
      if (t.closest('[data-ig-next]')) {
        showAt(currentIndex + 1);
      }
    });

    return lightbox;
  }

  function showAt(index) {
    var nodes = getItems();
    if (!nodes.length || !lightbox) {
      return;
    }
    var len = nodes.length;
    currentIndex = ((index % len) + len) % len;
    var item = itemData(nodes[currentIndex]);
    var img = qs(lightbox, '.ff-ig-lightbox__img');
    var cap = qs(lightbox, '.ff-ig-lightbox__caption');
    var link = qs(lightbox, '.ff-ig-lightbox__link');
    var prev = qs(lightbox, '[data-ig-prev]');
    var next = qs(lightbox, '[data-ig-next]');

    img.src = item.src;
    img.alt = item.caption || item.alt || '';

    if (item.caption) {
      cap.textContent = item.caption;
      cap.hidden = false;
    } else {
      cap.textContent = '';
      cap.hidden = true;
    }

    if (item.link) {
      link.href = item.link;
      link.hidden = false;
    } else {
      link.removeAttribute('href');
      link.hidden = true;
    }

    var multi = nodes.length > 1;
    prev.hidden = !multi;
    next.hidden = !multi;
    prev.disabled = !multi;
    next.disabled = !multi;
  }

  function openLightbox(index) {
    buildLightbox();
    showAt(index);
    lastFocus = document.activeElement;
    lightbox.removeAttribute('hidden');
    lightbox.setAttribute('aria-hidden', 'false');
    lightbox.classList.add('is-open');
    document.documentElement.classList.add('ff-ig-lightbox-open');
    var closeBtn = qs(lightbox, '.ff-ig-lightbox__close');
    if (closeBtn) {
      closeBtn.focus();
    }
  }

  function closeLightbox() {
    if (!lightbox || lightbox.getAttribute('aria-hidden') === 'true') {
      return;
    }
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    lightbox.setAttribute('hidden', '');
    document.documentElement.classList.remove('ff-ig-lightbox-open');
    var img = qs(lightbox, '.ff-ig-lightbox__img');
    if (img) {
      img.removeAttribute('src');
      img.alt = '';
    }
    currentIndex = -1;
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
    lastFocus = null;
  }

  function indexOfItem(el) {
    return getItems().indexOf(el);
  }

  function observeFade(targets) {
    if (!targets.length) {
      return;
    }

    if (preferReducedMotion() || !('IntersectionObserver' in window)) {
      targets.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    if (!fadeObserver) {
      fadeObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
              return;
            }
            entry.target.classList.add('is-visible');
            fadeObserver.unobserve(entry.target);
          });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
      );
    }

    targets.forEach(function (el) {
      if (el.classList.contains('is-visible')) {
        return;
      }
      el.classList.add('instagram-item--reveal');
      fadeObserver.observe(el);
    });
  }

  function enhanceSection(root) {
    var items = root.querySelectorAll('.instagram-item');
    observeFade(Array.prototype.slice.call(items));
  }

  function boot(scope) {
    var roots = scope
      ? scope.querySelectorAll(ROOT_SEL)
      : document.querySelectorAll(ROOT_SEL);
    roots.forEach(enhanceSection);
  }

  document.addEventListener(
    'click',
    function (e) {
      var t = e.target;
      if (!(t instanceof Element)) {
        return;
      }
      var item = t.closest(ITEM_SEL);
      if (!item) {
        return;
      }
      e.preventDefault();
      var idx = indexOfItem(item);
      if (idx < 0) {
        return;
      }
      openLightbox(idx);
    },
    false
  );

  document.addEventListener(
    'keydown',
    function (e) {
      if (lightbox && lightbox.getAttribute('aria-hidden') !== 'true') {
        if (e.key === 'Escape') {
          e.preventDefault();
          closeLightbox();
          return;
        }
        if (e.key === 'ArrowLeft') {
          e.preventDefault();
          showAt(currentIndex - 1);
          return;
        }
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          showAt(currentIndex + 1);
          return;
        }
      }

      var t = e.target;
      if (!(t instanceof Element)) {
        return;
      }
      var item = t.closest(ITEM_SEL);
      if (!item) {
        return;
      }
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        var idx = indexOfItem(item);
        if (idx >= 0) {
          openLightbox(idx);
        }
      }
    },
    false
  );

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      boot();
    });
  } else {
    boot();
  }

  // Elementor frontend / preview re-renders (jQuery optional).
  function bindElementor() {
    if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
      return;
    }
    window.elementorFrontend.hooks.addAction(
      'frontend/element_ready/ff_addons_instagram.default',
      function (scope) {
        var el = scope && scope.jquery ? scope[0] : scope;
        if (el && el.querySelector) {
          boot(el);
        } else {
          boot();
        }
      }
    );
  }

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    bindElementor();
  } else if (typeof window.jQuery !== 'undefined') {
    window.jQuery(window).on('elementor/frontend/init', bindElementor);
  } else {
    window.addEventListener('elementor/frontend/init', bindElementor);
  }
})();
