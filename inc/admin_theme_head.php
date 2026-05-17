<?php /* Inline theme bootstrap — prevents FOUC. Must run before stylesheet paints. */ ?>
<script>
(function () {
  try {
    var t = localStorage.getItem('rc_admin_theme');
    if (t === 'light' || t === 'dark') {
      document.documentElement.setAttribute('data-theme', t);
    }
  } catch (e) {}
})();
</script>
