// ====== Gallery lightbox (вставьте в popup.js или отдельный gallery.js) ======
(function() {
  const items = Array.from(document.querySelectorAll('.gallery-item'));
  if (!items.length) return;

  const overlay = document.getElementById('galleryOverlay');
  const modal   = overlay ? overlay.querySelector('.gallery-modal') : null;
  const imgEl   = document.getElementById('galleryImage');
  const capEl   = document.getElementById('galleryCaption');
  const btnClose = document.getElementById('galleryClose');
  const btnPrev  = document.getElementById('galleryPrev');
  const btnNext  = document.getElementById('galleryNext');

  let currentIndex = -1;

  function openAt(index) {
    const btn = items[index];
    if (!btn) return;
    const full = btn.getAttribute('data-full') || btn.querySelector('img')?.src;
    const alt  = btn.querySelector('img')?.alt || '';
    currentIndex = index;
    if (overlay) overlay.hidden = false;
    if (overlay) overlay.classList.add('active');
    if (imgEl) { imgEl.src = full; imgEl.alt = alt; }
    if (capEl) capEl.textContent = alt;
    // focus for keyboard
    setTimeout(() => { (btnClose || overlay).focus(); }, 100);
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove('active');
    setTimeout(() => {
      overlay.hidden = true;
      if (imgEl) imgEl.src = '';
      currentIndex = -1;
    }, 180);
  }

  function showNext() {
    if (currentIndex < 0) return;
    const next = (currentIndex + 1) % items.length;
    openAt(next);
  }
  function showPrev() {
    if (currentIndex < 0) return;
    const prev = (currentIndex - 1 + items.length) % items.length;
    openAt(prev);
  }

  items.forEach((btn, i) => {
    btn.addEventListener('click', (e) => { e.preventDefault(); openAt(i); });
  });

  btnClose && btnClose.addEventListener('click', close);
  overlay && overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });

  btnNext && btnNext.addEventListener('click', (e) => { e.stopPropagation(); showNext(); });
  btnPrev && btnPrev.addEventListener('click', (e) => { e.stopPropagation(); showPrev(); });

  document.addEventListener('keydown', (e) => {
    if (overlay && overlay.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') showNext();
    if (e.key === 'ArrowLeft') showPrev();
  });

  // свайп на мобильных: добавим простую детекцию свайпа
  (function() {
    if (!imgEl) return;
    let sx = 0, sy = 0, ex = 0, ey = 0;
    imgEl.addEventListener('touchstart', (ev) => {
      const t = ev.touches[0]; sx = t.clientX; sy = t.clientY;
    }, {passive:true});
    imgEl.addEventListener('touchend', (ev) => {
      const t = ev.changedTouches[0]; ex = t.clientX; ey = t.clientY;
      const dx = ex - sx, dy = ey - sy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
        if (dx < 0) showNext(); else showPrev();
      }
    }, {passive:true});
  })();
})();
