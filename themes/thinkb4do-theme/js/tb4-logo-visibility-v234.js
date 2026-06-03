/* Thinkb4do Logo Visibility v2.3.4 */
(function () {
  'use strict';

  function parseRGB(value) {
    if (!value || value === 'transparent') return null;
    var match = value.match(/rgba?\(([^)]+)\)/i);
    if (!match) return null;
    var parts = match[1].split(',').map(function (part) { return parseFloat(part.trim()); });
    if (parts.length < 3) return null;
    return {
      r: parts[0],
      g: parts[1],
      b: parts[2],
      a: parts.length > 3 ? parts[3] : 1
    };
  }

  function luminance(color) {
    function channel(v) {
      v = v / 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    }
    return (0.2126 * channel(color.r)) + (0.7152 * channel(color.g)) + (0.0722 * channel(color.b));
  }

  function readBackground(el) {
    var current = el;
    var depth = 0;
    while (current && current !== document.documentElement && depth < 8) {
      var style = window.getComputedStyle(current);
      var bg = parseRGB(style.backgroundColor);
      if (bg && bg.a > 0.12) {
        // Blend semi-transparent backgrounds against white for a safe fallback.
        if (bg.a < 1) {
          bg = {
            r: (bg.r * bg.a) + (255 * (1 - bg.a)),
            g: (bg.g * bg.a) + (255 * (1 - bg.a)),
            b: (bg.b * bg.a) + (255 * (1 - bg.a)),
            a: 1
          };
        }
        return bg;
      }
      current = current.parentElement;
      depth += 1;
    }
    return { r: 255, g: 255, b: 255, a: 1 };
  }

  function updateLogo(logo) {
    if (!logo || logo.getAttribute('data-logo-mode') !== 'auto') return;
    var surface = logo.closest('#site-header, #site-footer, header, footer, .site-header, .site-footer') || logo.parentElement || document.body;
    var bg = readBackground(surface);
    var isDark = luminance(bg) < 0.42;
    logo.classList.toggle('tb4-logo-bg-dark', isDark);
    logo.classList.toggle('tb4-logo-bg-light', !isDark);
  }

  var ticking = false;
  function updateAll() {
    ticking = false;
    var logos = document.querySelectorAll('.site-logo[data-logo-mode="auto"]');
    for (var i = 0; i < logos.length; i += 1) updateLogo(logos[i]);
  }

  function requestUpdate() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame ? window.requestAnimationFrame(updateAll) : setTimeout(updateAll, 16);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateAll);
  } else {
    updateAll();
  }
  window.addEventListener('load', updateAll, { passive: true });
  window.addEventListener('resize', requestUpdate, { passive: true });
  window.addEventListener('orientationchange', requestUpdate, { passive: true });
  window.addEventListener('scroll', requestUpdate, { passive: true });
})();
