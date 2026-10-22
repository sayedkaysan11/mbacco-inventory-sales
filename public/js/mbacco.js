// Angka dashboard menghitung naik satu kali saat halaman dibuka.
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseInt(el.dataset.count, 10);
    if (!isFinite(target) || target <= 0) return;
    var start = null, dur = 1100;
    function tick(t) {
      if (start === null) start = t;
      var p = Math.min((t - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3); // melambat di akhir
      el.textContent = Math.round(target * eased).toLocaleString('id-ID');
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });
})();
