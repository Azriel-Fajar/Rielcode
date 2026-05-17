// Lightweight hover/touch prefetch — instant.page style, no deps.
// On mouseover (with 65ms intent debounce) and touchstart, inject
// <link rel="prefetch"> for same-origin anchors so the browser warms
// the next page before the user clicks.
(function rielPrefetch() {
  if (!('connection' in navigator) || navigator.connection.saveData) {
    // Respect Data Saver — skip prefetch entirely.
  }
  if (navigator.connection && /2g/.test(navigator.connection.effectiveType || '')) {
    return;
  }

  const prefetched = new Set();
  const HOVER_DELAY = 65;
  let hoverTimer = null;

  function isEligible(a) {
    if (!a || !a.href) return false;
    if (a.target === '_blank') return false;
    if (a.hasAttribute('data-no-prefetch')) return false;
    if (a.rel && /external|nofollow/.test(a.rel)) return false;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin) return false;
    if (url.pathname === location.pathname && url.search === location.search) return false;
    if (a.href.startsWith('mailto:') || a.href.startsWith('tel:')) return false;
    if (prefetched.has(url.href)) return false;
    return url.href;
  }

  function prefetch(href) {
    if (prefetched.has(href)) return;
    prefetched.add(href);
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = href;
    link.as = 'document';
    document.head.appendChild(link);
  }

  function onHover(e) {
    const a = e.target.closest && e.target.closest('a');
    const href = isEligible(a);
    if (!href) return;
    clearTimeout(hoverTimer);
    hoverTimer = setTimeout(() => prefetch(href), HOVER_DELAY);
  }

  function onTouch(e) {
    const a = e.target.closest && e.target.closest('a');
    const href = isEligible(a);
    if (href) prefetch(href);
  }

  function onCancel() { clearTimeout(hoverTimer); }

  document.addEventListener('mouseover', onHover, { passive: true, capture: true });
  document.addEventListener('mouseout',  onCancel, { passive: true, capture: true });
  document.addEventListener('touchstart', onTouch, { passive: true, capture: true });
})();
