/* admin-ui.js — popup confirms + auto-dismiss toasts.
   Replaces native confirm() and renders flash success messages as toasts. */

(function () {
  "use strict";

  /* ---------------- Modal confirm ---------------- */

  function buildModal() {
    const wrap = document.createElement("div");
    wrap.className = "rc-modal";
    wrap.setAttribute("role", "dialog");
    wrap.setAttribute("aria-modal", "true");
    wrap.innerHTML = `
      <div class="rc-modal__backdrop" data-rc-cancel></div>
      <div class="rc-modal__panel" role="document">
        <div class="rc-modal__title"></div>
        <div class="rc-modal__body"></div>
        <div class="rc-modal__actions">
          <button type="button" class="rc-modal__btn rc-modal__btn--ghost" data-rc-cancel>Cancel</button>
          <button type="button" class="rc-modal__btn rc-modal__btn--confirm">Confirm</button>
        </div>
      </div>`;
    return wrap;
  }

  function confirmDialog(message, opts) {
    opts = opts || {};
    const variant = opts.variant === "danger" ? "danger" : "primary";
    const title = opts.title || (variant === "danger" ? "Confirm action" : "Are you sure?");
    const confirmLabel = opts.confirmLabel || (variant === "danger" ? "Delete" : "Confirm");
    const cancelLabel = opts.cancelLabel || "Cancel";

    return new Promise(function (resolve) {
      const modal = buildModal();
      modal.classList.add("rc-modal--" + variant);
      modal.querySelector(".rc-modal__title").textContent = title;
      modal.querySelector(".rc-modal__body").textContent = message;
      const cancelBtn = modal.querySelector(".rc-modal__btn--ghost");
      const okBtn = modal.querySelector(".rc-modal__btn--confirm");
      cancelBtn.textContent = cancelLabel;
      okBtn.textContent = confirmLabel;
      document.documentElement.appendChild(modal);

      requestAnimationFrame(function () {
        modal.classList.add("is-open");
        okBtn.focus();
      });

      function close(result) {
        modal.classList.remove("is-open");
        document.removeEventListener("keydown", onKey);
        setTimeout(function () {
          modal.remove();
          resolve(result);
        }, 180);
      }
      function onKey(e) {
        if (e.key === "Escape") close(false);
        else if (e.key === "Enter") close(true);
      }

      modal.addEventListener("click", function (e) {
        if (e.target.matches("[data-rc-cancel]")) close(false);
      });
      okBtn.addEventListener("click", function () { close(true); });
      document.addEventListener("keydown", onKey);
    });
  }

  /* ---------------- Intercept [data-confirm] ---------------- */

  document.addEventListener("click", function (e) {
    const trigger = e.target.closest("[data-confirm]");
    if (!trigger) return;
    if (trigger.dataset.rcConfirmed === "1") return; // already resolved, let it through

    e.preventDefault();
    e.stopPropagation();

    const msg = trigger.dataset.confirm;
    const variant = trigger.dataset.confirmVariant || "primary";
    const title = trigger.dataset.confirmTitle || undefined;
    const confirmLabel = trigger.dataset.confirmLabel || undefined;

    confirmDialog(msg, { variant: variant, title: title, confirmLabel: confirmLabel }).then(function (ok) {
      if (!ok) return;
      trigger.dataset.rcConfirmed = "1";
      // Re-fire the original action: <a> -> follow href, <button form> -> submit form
      if (trigger.tagName === "A" && trigger.href) {
        window.location.href = trigger.href;
      } else if (trigger.tagName === "BUTTON" || trigger.tagName === "INPUT") {
        const form = trigger.form || trigger.closest("form");
        if (form) form.submit();
        else trigger.click();
      } else {
        trigger.click();
      }
    });
  }, true);

  /* ---------------- Toasts ---------------- */

  function ensureToastHost() {
    let host = document.getElementById("rc-toast-host");
    if (!host) {
      host = document.createElement("div");
      host.id = "rc-toast-host";
      host.className = "rc-toast-host";
      document.documentElement.appendChild(host);
    }
    return host;
  }

  function showToast(message, opts) {
    opts = opts || {};
    const variant = opts.variant || "success";
    const duration = opts.duration || 3000;
    const host = ensureToastHost();
    const el = document.createElement("div");
    el.className = "rc-toast rc-toast--" + variant;
    el.setAttribute("role", "status");
    el.innerHTML = `
      <span class="rc-toast__icon" aria-hidden="true"></span>
      <span class="rc-toast__msg"></span>`;
    el.querySelector(".rc-toast__msg").textContent = message;
    host.appendChild(el);

    requestAnimationFrame(function () { el.classList.add("is-open"); });

    setTimeout(function () {
      el.classList.remove("is-open");
      el.classList.add("is-leaving");
      setTimeout(function () { el.remove(); }, 220);
    }, duration);
  }

  // Auto-dismiss elements rendered with [data-auto-dismiss]
  document.querySelectorAll("[data-auto-dismiss]").forEach(function (el) {
    const ms = parseInt(el.dataset.autoDismiss, 10) || 3000;
    // If element is itself the toast, leave it; otherwise hoist its text into a toast.
    if (el.classList.contains("rc-toast")) {
      requestAnimationFrame(function () { el.classList.add("is-open"); });
      setTimeout(function () {
        el.classList.remove("is-open");
        el.classList.add("is-leaving");
        setTimeout(function () { el.remove(); }, 220);
      }, ms);
    } else {
      const msg = el.textContent.trim();
      const variant = el.dataset.toastVariant || "success";
      el.remove();
      showToast(msg, { variant: variant, duration: ms });
    }
  });

  /* ---------------- Theme toggle (system → light → dark → system) ---------------- */

  const THEME_KEY = "rc_admin_theme";
  const THEME_ORDER = ["system", "light", "dark"];
  const THEME_LABELS = { system: "System", light: "Light", dark: "Dark" };

  function currentTheme() {
    try {
      const t = localStorage.getItem(THEME_KEY);
      return t === "light" || t === "dark" ? t : "system";
    } catch (e) { return "system"; }
  }

  function applyTheme(theme) {
    if (theme === "light" || theme === "dark") {
      document.documentElement.setAttribute("data-theme", theme);
    } else {
      document.documentElement.removeAttribute("data-theme");
    }
    try {
      if (theme === "system") localStorage.removeItem(THEME_KEY);
      else localStorage.setItem(THEME_KEY, theme);
    } catch (e) {}
  }

  function paintThemeButton(btn, theme) {
    const label = btn.querySelector(".theme-toggle__label");
    if (label) label.textContent = THEME_LABELS[theme] || "System";
  }

  function nextTheme(t) {
    const i = THEME_ORDER.indexOf(t);
    return THEME_ORDER[(i + 1) % THEME_ORDER.length];
  }

  function bindThemeToggle() {
    const btn = document.getElementById("rc-theme-toggle");
    if (!btn) return;
    paintThemeButton(btn, currentTheme());
    btn.addEventListener("click", function () {
      const next = nextTheme(currentTheme());
      applyTheme(next);
      paintThemeButton(btn, next);
    });
  }

  /* ---------------- Sidebar toggle (mobile) ---------------- */

  function bindSidebarToggle() {
    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("rc-sidebar") || document.querySelector(".sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    if (!toggle || !sidebar) return;
    function open() { sidebar.classList.add("active"); if (overlay) overlay.classList.add("active"); }
    function close() { sidebar.classList.remove("active"); if (overlay) overlay.classList.remove("active"); }
    toggle.addEventListener("click", function () {
      sidebar.classList.contains("active") ? close() : open();
    });
    if (overlay) overlay.addEventListener("click", close);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      bindThemeToggle();
      bindSidebarToggle();
    });
  } else {
    bindThemeToggle();
    bindSidebarToggle();
  }

  /* ---------------- Loading overlay ---------------- */

  let _overlayEl = null;
  let _overlayTimer = null;

  function buildOverlay() {
    if (_overlayEl) return _overlayEl;
    _overlayEl = document.createElement("div");
    _overlayEl.className = "rc-loading-overlay";
    _overlayEl.setAttribute("role", "status");
    _overlayEl.setAttribute("aria-live", "polite");
    _overlayEl.innerHTML = `
      <div class="rc-loading-overlay__card">
        <div class="rc-loading-overlay__spinner"></div>
        <div class="rc-loading-overlay__label" id="rc-loading-label">Processing…</div>
      </div>`;
    document.documentElement.appendChild(_overlayEl);
    return _overlayEl;
  }

  function showLoading(label) {
    const el = buildOverlay();
    const lbl = el.querySelector("#rc-loading-label");
    if (lbl) lbl.textContent = label || "Processing…";
    requestAnimationFrame(function () { el.classList.add("is-visible"); });
    // Safety fallback — hide after 15s in case navigation never happens
    if (_overlayTimer) clearTimeout(_overlayTimer);
    _overlayTimer = setTimeout(function () { hideLoading(); }, 15000);
  }

  function hideLoading() {
    if (!_overlayEl) return;
    _overlayEl.classList.remove("is-visible");
    if (_overlayTimer) { clearTimeout(_overlayTimer); _overlayTimer = null; }
  }

  // Show overlay on any form submit
  document.addEventListener("submit", function (e) {
    const form = e.target;
    if (!form || form.tagName !== "FORM") return;
    // Detect file upload to show a more specific label
    const hasFile = form.querySelector('input[type="file"]');
    const hasFileSelected = hasFile && hasFile.files && hasFile.files.length > 0;
    showLoading(hasFileSelected ? "Uploading image…" : "Saving…");
  }, true);

  // Show overlay when a confirmed [data-confirm] action navigates
  // (patch into the confirm flow — after user clicks OK, show overlay before navigation)
  const _origConfirm = confirmDialog;
  window.rcConfirm = function (message, opts) {
    return _origConfirm(message, opts).then(function (ok) {
      if (ok) showLoading("Processing…");
      return ok;
    });
  };

  // Also intercept direct action links (non-data-confirm ones) that navigate
  document.addEventListener("click", function (e) {
    const link = e.target.closest("a.button, a.btn-tint");
    if (!link) return;
    // Skip links that open a modal (data-confirm handles those above)
    if (link.dataset.confirm) return;
    // Skip anchors, javascript:, external links
    const href = link.getAttribute("href") || "";
    if (!href || href.startsWith("#") || href.startsWith("javascript")) return;
    if (link.target === "_blank") return;
    // Only show for same-origin action links (admin.php?action=...)
    if (href.includes("action=") || href.includes("admin.php")) {
      showLoading("Processing…");
    }
  });

  // Expose
  window.rcShowLoading = showLoading;
  window.rcHideLoading = hideLoading;
  window.rcToast = showToast;
})();
