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

    if (!trigger || !panel) {
      return;
    }

    function setOpen(open) {
      trigger.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.classList.toggle('is-open', open);
      panel.hidden = !open;
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');

      if (open) {
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
  });
})();
