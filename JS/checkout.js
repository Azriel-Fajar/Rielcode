document
  .getElementById("checkoutForm")
  .addEventListener("submit", function (e) {
    const btn = document.getElementById("checkoutBtn");

    // ubah tombol jadi loading
    btn.innerHTML = '<span class="loader"></span> &nbsp; Processing...';
    btn.disabled = true;

    // tambahkan overlay
    const overlay = document.createElement("div");
    overlay.classList.add("loading-overlay");
    overlay.innerHTML = '<div class="loader"></div><div>Processing your order…</div>';
    document.body.appendChild(overlay);
  });

// kalau user klik back → reset halaman
window.addEventListener("pageshow", function (event) {
  if (
    event.persisted ||
    performance.getEntriesByType("navigation")[0].type === "back_forward"
  ) {
    window.location.reload();
  }
});
