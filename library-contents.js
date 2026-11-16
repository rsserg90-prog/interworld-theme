/* INTERWORLD: close book contents when the user clicks/taps outside it. */
(function () {
  'use strict';
  function initBookContents() {
    var disclosures = document.querySelectorAll('.iw-book-contents');
    if (!disclosures.length) return;

    document.addEventListener('pointerdown', function (event) {
      disclosures.forEach(function (details) {
        if (details.open && !details.contains(event.target)) {
          details.open = false;
        }
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      disclosures.forEach(function (details) {
        if (details.open) {
          details.open = false;
          var summary = details.querySelector('summary');
          if (summary) summary.focus();
        }
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBookContents);
  } else {
    initBookContents();
  }
})();
