(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  ready(function () {
    var trigger = document.getElementById('tb4AppGridTrigger');
    var panel = document.getElementById('tb4AppGridPanel');
    var closeBtn = document.getElementById('tb4AppGridClose');
    var root = document.documentElement;

    if (!trigger || !panel) {
      return;
    }

    function getPanelWidth() {
      var previousHidden = panel.hidden;
      var previousVisibility = panel.style.visibility;
      var previousDisplay = panel.style.display;

      if (previousHidden) {
        panel.hidden = false;
        panel.style.visibility = 'hidden';
        panel.style.display = 'block';
      }

      var width = Math.ceil(panel.getBoundingClientRect().width || 390);

      if (previousHidden) {
        panel.hidden = true;
        panel.style.visibility = previousVisibility;
        panel.style.display = previousDisplay;
      }

      return width;
    }

    function positionPanel() {
      var viewportW = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      var rect = trigger.getBoundingClientRect();
      var gap = viewportW <= 782 ? 8 : 10;
      var edge = viewportW <= 430 ? 8 : 12;
      var panelW = getPanelWidth();

      if (viewportW <= 782) {
        root.style.setProperty('--tb4-app-grid-panel-left', Math.max(edge, 8) + 'px');
        root.style.setProperty('--tb4-app-grid-panel-top', Math.round(rect.bottom + gap) + 'px');
        return;
      }

      // Prefer right-aligning the panel to the 9-square button so the popup
      // stays visually connected to the trigger even when account/notice
      // controls exist on the right side of the header.
      var preferredLeft = Math.round(rect.right - panelW);
      var maxLeft = viewportW - panelW - edge;
      var left = Math.min(Math.max(preferredLeft, edge), maxLeft);
      var top = Math.round(rect.bottom + gap);

      root.style.setProperty('--tb4-app-grid-panel-left', left + 'px');
      root.style.setProperty('--tb4-app-grid-panel-top', top + 'px');
    }

    function closeOtherPanels() {
      var notification = document.getElementById('tb4NotificationPanel');
      var notificationTrigger = document.getElementById('tb4NotificationTrigger');
      var message = document.getElementById('tb4MessagePanel');
      var messageTrigger = document.getElementById('tb4MessageTrigger');

      if (notification) {
        notification.hidden = true;
        notification.classList.remove('is-open');
        notification.setAttribute('aria-hidden', 'true');
      }
      if (notificationTrigger) {
        notificationTrigger.setAttribute('aria-expanded', 'false');
      }
      if (message) {
        message.hidden = true;
        message.classList.remove('is-open');
        message.setAttribute('aria-hidden', 'true');
      }
      if (messageTrigger) {
        messageTrigger.setAttribute('aria-expanded', 'false');
      }
    }

    function setOpen(open) {
      if (open) {
        closeOtherPanels();
        positionPanel();
      }

      trigger.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.classList.toggle('is-open', open);
      panel.hidden = !open;
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');

      if (open) {
        // Run one more time after the panel becomes visible so browser zoom,
        // Thai font metrics and scrollbar width do not offset the popup.
        window.requestAnimationFrame(positionPanel);
      }
    }

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      setOpen(!panel.classList.contains('is-open'));
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setOpen(false);
        trigger.focus();
      });
    }

    document.addEventListener('click', function (event) {
      if (!panel.classList.contains('is-open')) {
        return;
      }
      if (panel.contains(event.target) || trigger.contains(event.target)) {
        return;
      }
      setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && panel.classList.contains('is-open')) {
        setOpen(false);
        trigger.focus();
      }
    });

    window.addEventListener('resize', function () {
      if (panel.classList.contains('is-open')) {
        positionPanel();
      }
    }, { passive: true });

    window.addEventListener('orientationchange', function () {
      if (panel.classList.contains('is-open')) {
        window.setTimeout(positionPanel, 80);
      }
    }, { passive: true });
  });
})();
