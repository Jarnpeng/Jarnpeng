(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
      return;
    }
    document.addEventListener('DOMContentLoaded', fn);
  }

  function setVisible(element, visible) {
    if (!element) {
      return;
    }
    element.style.display = visible ? 'grid' : 'none';
    element.setAttribute('aria-hidden', visible ? 'false' : 'true');
  }

  function setButtonStatus(button, text, timeout) {
    if (!button) {
      return;
    }
    var original = button.getAttribute('data-original-label') || button.textContent;
    button.setAttribute('data-original-label', original);
    button.textContent = text;
    if (timeout) {
      window.setTimeout(function () {
        button.textContent = original;
      }, timeout);
    }
  }

  function copyText(text) {
    if (window.navigator && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      return navigator.clipboard.writeText(text);
    }

    return new Promise(function (resolve, reject) {
      var textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', 'readonly');
      textarea.style.position = 'fixed';
      textarea.style.top = '-9999px';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();

      try {
        var ok = document.execCommand && document.execCommand('copy');
        document.body.removeChild(textarea);
        ok ? resolve() : reject(new Error('Copy command failed'));
      } catch (error) {
        document.body.removeChild(textarea);
        reject(error);
      }
    });
  }

  function updateViewportVar() {
    var height = window.innerHeight || document.documentElement.clientHeight || 0;
    if (height && document.documentElement && document.documentElement.style) {
      document.documentElement.style.setProperty('--tbpbi-vh', (height * 0.01) + 'px');
    }
  }

  ready(function () {
    updateViewportVar();
    window.addEventListener('resize', updateViewportVar, false);
    window.addEventListener('orientationchange', updateViewportVar, false);

    var source = document.getElementById('tbpbi-source');
    var installed = document.querySelector('.tbpbi-installed-field');
    var upload = document.querySelector('.tbpbi-upload-field');

    function syncSource() {
      if (!source) {
        return;
      }
      var isUpload = source.value === 'upload';
      setVisible(installed, !isUpload);
      setVisible(upload, isUpload);
    }

    if (source) {
      source.addEventListener('change', syncSource, false);
      syncSource();
    }

    var copyButton = document.getElementById('tbpbi-copy-report');
    var reportText = document.getElementById('tbpbi-report-text');
    if (copyButton && reportText) {
      copyButton.addEventListener('click', function () {
        var value = reportText.value || '';
        reportText.focus();
        reportText.select();
        if (typeof reportText.setSelectionRange === 'function') {
          reportText.setSelectionRange(0, value.length);
        }

        copyText(value).then(function () {
          setButtonStatus(copyButton, 'Copied', 1600);
        }).catch(function () {
          setButtonStatus(copyButton, 'Copy manually', 1800);
        });
      }, false);
    }
  });
})();
