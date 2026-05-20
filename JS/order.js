document.addEventListener("DOMContentLoaded", function () {
  const promoCheckbox = document.getElementById("free_promo");
  const domainHostingSection = document.getElementById(
    "domainHostingWrap"
  );

  const domainYes = document.getElementById("domain-yes");
  const domainNo = document.getElementById("domain-no");
  const hostingYes = document.getElementById("hosting-yes");
  const hostingNo = document.getElementById("hosting-no");

  promoCheckbox.addEventListener("change", function () {
    if (this.checked) {
      // Sembunyikan input domain & hosting
      domainHostingSection.style.display = "none";

      // Set value otomatis ke "No"
      domainNo.checked = true;
      hostingNo.checked = true;
    } else {
      // Tampilkan kembali input domain & hosting
      domainHostingSection.style.display = "block";

      // Reset ke default (misal domain Yes & hosting Yes)
      domainYes.checked = true;
      hostingYes.checked = true;
    }
  });

  const promoLabel = document.querySelector(".promo-check label");

  // Teks versi desktop & mobile
  const fullText = "🎉 Claim Free Hosting & .COM Domain (Promo)";
  const shortText = "🎉 Claim Free Hosting + .COM";

  function updatePromoText() {
    if (window.innerWidth <= 480) {
      promoLabel.textContent = shortText;
    } else {
      promoLabel.textContent = fullText;
    }
  }

  // Jalankan saat halaman dimuat
  updatePromoText();

  // Jalankan juga saat window di-resize
  window.addEventListener("resize", updatePromoText);

  // Submit feedback — disable button + spinner + label swap so user knows it took.
  const orderForm = document.getElementById("orderForm");
  if (orderForm) {
    orderForm.addEventListener("submit", function () {
      const btn = orderForm.querySelector('button[type="submit"][name="submit"]');
      if (!btn || btn.dataset.busy === "1") return;
      btn.dataset.busy = "1";
      btn.dataset.origLabel = btn.innerHTML;
      // Defer disable until after navigation starts — synchronous disable mid-submit
      // triggers Navigation API InvalidStateError and strips btn from POST body.
      setTimeout(() => {
        btn.disabled = true;
        btn.innerHTML = '<span class="rc-spinner" aria-hidden="true"></span>&nbsp;Sending…';
      }, 0);
    });
  }
});
