document.addEventListener("DOMContentLoaded", function () {
  const chatbotHTML = `
<div id="chatbot-icon" class="chatbot-icon" title="Chat with RielBot">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
  </svg>
  <span id="chatbot-badge" class="chatbot-badge">1</span>
</div>

<!-- Greeting bubble above chatbot icon -->
<div id="chatbot-greeting" class="chatbot-greeting">
  👋 Hi! Anything I can help with?
  <button id="chatbot-greeting-close" class="chatbot-greeting-close">×</button>
</div>

<!-- Chatbot Window -->
<div id="chatbot-container" class="chatbot-container hidden">

  <!-- Header -->
  <div class="chat-header">
    <div class="chat-header-info">
      <div class="chat-header-title">
        <span class="chat-header-name">RielBot</span>
        <span class="chat-header-status">online</span>
      </div>
    </div>
    <button id="close-chat" class="close-btn" title="Close">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>

  <!-- Messages -->
  <div id="chat-messages" class="chat-messages"></div>

  <!-- Quick Reply Dropup -->
  <div id="quick-replies" class="quick-replies-dropup">
    <div id="quick-menu" class="quick-menu">
      <button class="quick-chip" data-msg="What packages do you offer?">📦 View Packages</button>
      <button class="quick-chip" data-msg="How much does a Rielcode package cost?">💰 Check Pricing</button>
      <button class="quick-chip" data-msg="Tell me about the Custom Plan presets (Copy Website and E-Commerce)">⚡ Custom Plan</button>
      <button class="quick-chip" data-msg="How do I order a website?">🚀 How to Order</button>
    </div>
    <button id="quick-toggle" class="quick-toggle" title="Quick questions">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
      Quick Ask
    </button>
  </div>

  <!-- Input -->
  <div class="chat-input">
    <input type="text" id="user-input" placeholder="Ask me anything…" autocomplete="off" />
    <button id="send-btn" title="Send">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>

</div>
`;

  document.body.insertAdjacentHTML("beforeend", chatbotHTML);

  const promo = document.querySelector(".rc-promo--bottom-bar");
  const chatIcon = document.getElementById("chatbot-icon");
  const chatContainer = document.getElementById("chatbot-container");
  const closeBtn = document.getElementById("close-chat");
  const sendBtn = document.getElementById("send-btn");
  const input = document.getElementById("user-input");
  const messages = document.getElementById("chat-messages");
  const badge = document.getElementById("chatbot-badge");
  const greeting = document.getElementById("chatbot-greeting");
  const greetingClose = document.getElementById("chatbot-greeting-close");
  const quickReplies = document.getElementById("quick-replies");

  // ─── Resolve proxy URL ──────────────────────────────────────────────────────
  // BUG FIX: the old code hardcoded "/Rielcode" as the localhost subdirectory.
  // If the folder was renamed (or nested differently), the URL broke.
  //
  // New approach:
  //  • On PRODUCTION the site lives at the domain root  → use /proxy  (clean URL via .htaccess)
  //  • On LOCALHOST  the site lives in a subdirectory   → derive the folder from
  //    the current page URL and call proxy.php directly, bypassing .htaccess rewrites
  //    which do not work on localhost because RewriteBase is set to / (production).
  //
  // Example results:
  //   https://rielcode.com/          → /proxy
  //   http://localhost/Rielcode/     → /Rielcode/proxy.php
  //   http://127.0.0.1/my-project/  → /my-project/proxy.php
  // ─────────────────────────────────────────────────────────────────────────────
  const isLocal = ["localhost", "127.0.0.1"].includes(window.location.hostname);

  function getProxyUrl() {
    if (!isLocal) {
      // Production: clean URL handled by .htaccess RewriteRule ^proxy$ proxy.php
      return window.location.origin + "/proxy";
    }
    // Localhost: .htaccess rewrites don't fire reliably in subdirectories.
    // Derive the app root from the current pathname so we can call proxy.php directly.
    const path = window.location.pathname; // e.g. "/Rielcode/" or "/Rielcode/index.php"
    const parts = path.split("/").filter(Boolean); // ["Rielcode"] or ["Rielcode", "index.php"]
    // Keep only the first segment (the project folder) — drop any filename
    const appRoot = parts.length > 0 ? "/" + parts[0] : "";
    return window.location.origin + appRoot + "/proxy.php";
  }

  const PROXY_URL = getProxyUrl();
  // ─────────────────────────────────────────────────────────────────────────────

  // ─── Greeting bubble auto-show (after 3 s) ───────────────────────────────
  let greetingSent = false;
  setTimeout(() => {
    if (chatContainer.classList.contains("hidden")) {
      greeting.classList.add("visible");
    }
  }, 3000);

  greetingClose.addEventListener("click", (e) => {
    e.stopPropagation();
    greeting.classList.remove("visible");
    badge.style.display = "none";
  });

  // ─── UI event listeners ───────────────────────────────────────────────────
  chatIcon.addEventListener("click", () => {
    chatContainer.classList.toggle("hidden");
    greeting.classList.remove("visible");
    badge.style.display = "none";

    if (promo) promo.style.transform = "translateY(100%)";

    if (!chatContainer.classList.contains("hidden")) {
      // Send bot greeting on first open
      if (!greetingSent) {
        greetingSent = true;
        setTimeout(() => {
          const typingDiv = addMessage("bot", "");
          typingDiv.classList.add("typing");
          typingDiv.innerHTML = "<span></span><span></span><span></span>";
          setTimeout(() => {
            typingDiv.classList.remove("typing");
            typingDiv.innerHTML =
              "👋 Hi! I'm <strong>RielBot</strong>, Rielcode's virtual assistant.<br><br>Want to ask about packages, pricing, or how to order? Just ask — or click one of the buttons below! 😊";
            messages.scrollTop = messages.scrollHeight;
          }, 1200);
        }, 300);
      }
      setTimeout(() => input.focus(), 100);
    }

    if (typeof gtag === "function") {
      gtag("event", "chat_open", {
        event_category: "Chatbot",
        event_label: "User opened chatbot",
      });
    }
  });

  closeBtn.addEventListener("click", () => {
    chatContainer.classList.add("hidden");
    if (typeof gtag === "function") {
      gtag("event", "chat_close", {
        event_category: "Chatbot",
        event_label: "User closed chatbot",
      });
    }
  });

  // ─── Click-outside-to-close ────────────────────────────────────────────────
  document.addEventListener("click", (e) => {
    if (chatContainer.classList.contains("hidden")) return;
    // Ignore clicks inside the chatbot container or on the icon
    if (chatContainer.contains(e.target) || chatIcon.contains(e.target)) return;
    chatContainer.classList.add("hidden");
  });

  // ─── Quick reply dropup ────────────────────────────────────────────────────
  const quickToggle = document.getElementById("quick-toggle");
  const quickMenu = document.getElementById("quick-menu");

  quickToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    quickMenu.classList.toggle("open");
    quickToggle.classList.toggle("open");
  });

  quickReplies.addEventListener("click", (e) => {
    const chip = e.target.closest(".quick-chip");
    if (!chip) return;
    input.value = chip.dataset.msg;
    sendMessage();
    // Close the dropup but keep it accessible
    quickMenu.classList.remove("open");
    quickToggle.classList.remove("open");
  });

  sendBtn.addEventListener("click", sendMessage);
  input.addEventListener("keypress", (e) => {
    if (e.key === "Enter") sendMessage();
  });

  // ─── Message rendering ────────────────────────────────────────────────────
  function addMessage(sender, text) {
    const div = document.createElement("div");
    div.className = `message ${sender}`;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;

    // Resize container to fit content (capped at max dimensions)
    const maxWidth = window.innerWidth > 768 ? 500 : window.innerWidth * 0.9;
    const maxHeight = window.innerWidth > 768 ? 600 : 400;
    chatContainer.style.height =
      Math.min(messages.scrollHeight + 120, maxHeight) + "px";
    chatContainer.style.width =
      Math.min(messages.scrollWidth + 40, maxWidth) + "px";

    return div;
  }

  // ─── Markdown parser ──────────────────────────────────────────────────────
  function parseMarkdown(text) {
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
      .replace(/\*(.+?)\*/g, "<strong>$1</strong>")
      .replace(/\n/g, "<br>");
  }

  // ─── Send & fetch ─────────────────────────────────────────────────────────
  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;
    input.value = "";

    if (typeof gtag === "function") {
      gtag("event", "chat_message", {
        event_category: "Chatbot",
        event_label: text,
      });
    }

    // Local identity shortcut (no API call needed)
    if (text.toLowerCase().includes("rielbot")) {
      addMessage(
        "bot",
        "Hi! I'm RielBot, Rielcode's virtual assistant. How can I help?",
      );
      return;
    }

    addMessage("user", text);
    const botMsgDiv = addMessage("bot", "");
    botMsgDiv.classList.add("typing");
    botMsgDiv.innerHTML = "<span></span><span></span><span></span>";

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 15000);

      const res = await fetch(PROXY_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: text, source: "chatbot" }),
        signal: controller.signal,
      });
      clearTimeout(timeoutId);

      if (!res.ok) {
        // Surface the HTTP status to make debugging easier
        throw new Error(
          `Server error (HTTP ${res.status}). URL tried: ${PROXY_URL}`,
        );
      }

      const data = await res.json();
      let replyText = data.reply || "⚠️ No response.";

      // Highlight any active discount mention in pricing replies
      const priceKeywords = ["price", "pricing", "cost", "package", "plan", "how much"];
      if (priceKeywords.some((kw) => text.toLowerCase().includes(kw))) {
        replyText = replyText.replace(/(Grand Opening.*?OFF)/gi, "🔥 $1 🔥");
      }

      botMsgDiv.classList.remove("typing");
      botMsgDiv.innerHTML = parseMarkdown(replyText);
    } catch (err) {
      botMsgDiv.classList.remove("typing");
      if (err.name === "AbortError") {
        botMsgDiv.textContent = "⚠️ Request timed out. Please try again.";
      } else {
        botMsgDiv.textContent = "⚠️ " + err.message;
        // Show URL in console to aid localhost debugging
        console.warn(
          "[RielBot] Proxy URL used:",
          PROXY_URL,
          "\nError:",
          err.message,
        );
      }
    }
  }

  // ─── Promo height helper ──────────────────────────────────────────────────
  function getPromoHeight() {
    return promo ? promo.offsetHeight : 0;
  }

  function updateChatbotOffset(promoVisible) {
    const isMobile = window.innerWidth <= 500;
    if (!isMobile) return;
    const offset = promoVisible ? getPromoHeight() + 12 : 20;
    chatIcon.style.bottom = offset + "px";
    if (!chatContainer.classList.contains("hidden")) {
      chatContainer.style.bottom = (offset + 70) + "px";
    }
  }

  // ─── Scroll-based promo hide/show ────────────────────────────────────────
  let lastScrollTop = 0;
  let promoVisible = true;
  window.addEventListener("scroll", () => {
    const scrollTop = window.scrollY;
    const scrollingDown = scrollTop > lastScrollTop;

    if (promo && promo.classList.contains("is-mounted")) {
      if (scrollingDown && promoVisible) {
        promo.classList.add("is-hidden-scroll");
        promoVisible = false;
        updateChatbotOffset(false);
      } else if (!scrollingDown && !promoVisible) {
        promo.classList.remove("is-hidden-scroll");
        promoVisible = true;
        updateChatbotOffset(true);
      }
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
  });

  // Set initial offset once promo mounts
  const promoObserver = new MutationObserver(() => {
    if (promo && promo.classList.contains("is-mounted")) {
      updateChatbotOffset(true);
    }
  });
  if (promo) promoObserver.observe(promo, { attributes: true, attributeFilter: ["class"] });
});

// ─── Injected styles ───────────────────────────────────────────────────────
const style = document.createElement("style");
style.textContent = `
@import url("https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap");

.chatbot-icon {
  position: fixed;
  bottom: 25px;
  right: 25px;
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(135deg, #3a7cff 0%, #5b52f5 100%);
  color: #fff;
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1001;
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow:
    0 8px 24px rgba(58, 124, 255, 0.45),
    0 0 0 1px rgba(58, 124, 255, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  animation: chatIconSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.chatbot-icon::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 60%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.chatbot-icon:hover {
  transform: translateY(-3px) scale(1.04);
  box-shadow:
    0 12px 32px rgba(58, 124, 255, 0.6),
    0 0 0 1px rgba(58, 124, 255, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.chatbot-icon:hover::before { opacity: 1; }
.chatbot-icon:active { transform: translateY(0) scale(1); }

@keyframes chatIconSlideIn {
  from { transform: translateX(80px); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}

.chatbot-container {
  position: fixed;
  bottom: 25px;
  right: 96px;
  width: 360px;
  max-width: 90vw;
  min-height: 70dvh;
  height: 70dvh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1002;

  /* Glassmorphism card */
  background: rgba(10, 15, 25, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 22px;
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  box-shadow:
    0 0 0 1px rgba(58, 124, 255, 0.08),
    0 32px 64px rgba(0, 0, 0, 0.65),
    inset 0 1px 0 rgba(255, 255, 255, 0.07);

  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1),
              transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hidden {
  opacity: 0;
  pointer-events: none;
  transform: translateY(16px) scale(0.97);
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  flex-shrink: 0;
  position: relative;
}

/* Top accent line */
.chat-header::before {
  content: "";
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 55%;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(58, 124, 255, 0.7), transparent);
  border-radius: 1px;
}

.chat-header-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Avatar dot */
.chat-header-info::before {
  content: "⬡";
  font-size: 1.1rem;
  color: #3a7cff;
  line-height: 1;
}

.chat-header-title {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.chat-header-name {
  font-family: "Outfit", sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #e2e8f0;
  line-height: 1;
}

.chat-header-status {
  font-family: "JetBrains Mono", monospace;
  font-size: 0.65rem;
  color: #3ecf8e;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.chat-header-status::before {
  content: "";
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: #3ecf8e;
  box-shadow: 0 0 6px #3ecf8e;
  display: inline-block;
  animation: status-pulse 2s ease-in-out infinite;
}

@keyframes status-pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

/* Fallback for plain header text (no sub-elements) */
.chat-header > span,
.chat-header > div:not(.chat-header-info) {
  font-family: "Outfit", sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #e2e8f0;
}

.close-btn {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #64748b;
  font-size: 16px;
  font-weight: 400;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  line-height: 1;
  flex-shrink: 0;
}

.close-btn:hover {
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.3);
  color: #f87171;
  transform: rotate(90deg);
}

.chat-messages {
  flex: 1;
  padding: 16px 14px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: transparent;

  /* Subtle scrollbar */
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.08) transparent;
}

.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 4px;
}
.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.15);
}

.message {
  max-width: 82%;
  padding: 10px 14px;
  border-radius: 14px;
  font-family: "Outfit", sans-serif;
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.55;
  word-wrap: break-word;
  white-space: pre-wrap;
  margin: 3px 0;
  animation: msgIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes msgIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* User message */
.message.user {
  background: linear-gradient(135deg, #3a7cff, #5b52f5);
  color: #fff;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
  box-shadow: 0 4px 12px rgba(58, 124, 255, 0.3);
}

/* Bot message */
.message.bot {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.07);
  color: #cbd5e1;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
}

/* Typing indicator */
.message.bot.typing {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 12px 16px;
}

.message.bot.typing span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #475569;
  animation: typing-bounce 1.2s ease-in-out infinite;
}

.message.bot.typing span:nth-child(2) { animation-delay: 0.15s; }
.message.bot.typing span:nth-child(3) { animation-delay: 0.3s; }

@keyframes typing-bounce {
  0%, 60%, 100% { transform: translateY(0); background: #475569; }
  30%            { transform: translateY(-5px); background: #3a7cff; }
}

.chat-input {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.03);
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  flex-shrink: 0;
}

.chat-input input {
  flex: 1 1 auto;
  min-width: 0;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 12px;
  padding: 10px 14px;
  font-family: "Outfit", sans-serif;
  font-size: 0.875rem;
  font-weight: 400;
  color: #e2e8f0;
  outline: none;
  transition: all 0.2s ease;
  -webkit-appearance: none;
}

.chat-input input::placeholder { color: #334155; }

.chat-input input:focus {
  border-color: rgba(58, 124, 255, 0.5);
  background: rgba(58, 124, 255, 0.05);
  box-shadow: 0 0 0 3px rgba(58, 124, 255, 0.1);
}

.chat-input button {
  flex: 0 0 auto;
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: linear-gradient(135deg, #3a7cff 0%, #5b52f5 100%);
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(58, 124, 255, 0.35);
  position: relative;
  overflow: hidden;
}

.chat-input button::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.1);
  opacity: 0;
  transition: opacity 0.15s ease;
}

.chat-input button:hover {
  transform: translateY(-1px) scale(1.05);
  box-shadow: 0 6px 18px rgba(58, 124, 255, 0.5);
}

.chat-input button:hover::before { opacity: 1; }
.chat-input button:active { transform: scale(0.96); }

@media (max-width: 500px) {
  .chatbot-container {
    width: calc(100vw - 24px);
    height: 75vh;
    right: 12px;
    bottom: 90px;
    border-radius: 18px;
  }

  .chatbot-icon {
    bottom: 76px;
    right: 16px;
    transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s;
  }

  .chatbot-greeting {
    right: 12px;
    max-width: calc(100vw - 80px);
    bottom: 148px;
  }
}

@media (max-width: 300px) {
  .chat-input {
    flex-direction: column;
  }
  .chat-input input,
  .chat-input button {
    width: 100%;
    flex: none;
  }
  .chat-input button {
    height: 40px;
    width: 100%;
    border-radius: 11px;
  }
}

/* ── Notification badge on icon ── */
.chatbot-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #ef4444;
  color: #fff;
  font-family: "Outfit", sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 0 2px #080c14, 0 2px 8px rgba(239,68,68,0.6);
  animation: badgePop 0.4s cubic-bezier(0.16, 1, 0.3, 1) 3.1s both;
}

@keyframes badgePop {
  from { transform: scale(0); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

/* ── Greeting bubble above icon ── */
.chatbot-greeting {
  position: fixed;
  bottom: 92px;
  right: 18px;
  background: rgba(10, 15, 25, 0.95);
  border: 1px solid rgba(58, 124, 255, 0.3);
  border-radius: 16px 16px 4px 16px;
  padding: 12px 36px 12px 16px;
  font-family: "Outfit", sans-serif;
  font-size: 0.85rem;
  color: #e2e8f0;
  max-width: 220px;
  line-height: 1.4;
  z-index: 1001;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 0 1px rgba(58,124,255,0.1);
  backdrop-filter: blur(12px);
  opacity: 0;
  transform: translateY(10px) scale(0.95);
  pointer-events: none;
  transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.16,1,0.3,1);
}

.chatbot-greeting.visible {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.chatbot-greeting-close {
  position: absolute;
  top: 6px;
  right: 8px;
  background: none;
  border: none;
  color: #475569;
  font-size: 1rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  transition: color 0.15s;
}
.chatbot-greeting-close:hover { color: #e2e8f0; }

/* ── Quick reply dropup ── */
.quick-replies-dropup {
  position: relative;
  flex-shrink: 0;
  padding: 6px 14px;
  border-top: 1px solid rgba(255,255,255,0.05);
}

.quick-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  padding: 7px 14px;
  border-radius: 10px;
  background: rgba(58, 124, 255, 0.06);
  border: 1px solid rgba(58, 124, 255, 0.18);
  color: #93b4ff;
  font-family: "Outfit", sans-serif;
  font-size: 0.72rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  justify-content: center;
}

.quick-toggle svg {
  transition: transform 0.25s ease;
}

.quick-toggle.open svg {
  transform: rotate(180deg);
}

.quick-toggle:hover {
  background: rgba(58, 124, 255, 0.12);
  border-color: rgba(58, 124, 255, 0.35);
  color: #c0d4ff;
}

.quick-menu {
  position: absolute;
  bottom: 100%;
  left: 14px;
  right: 14px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 10px;
  background: rgba(10, 15, 25, 0.96);
  border: 1px solid rgba(255,255,255,0.09);
  border-radius: 14px;
  box-shadow: 0 -8px 24px rgba(0,0,0,0.4);
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
  z-index: 10;
}

.quick-menu.open {
  opacity: 1;
  visibility: visible;
  transform: translateY(-4px);
}

.quick-chip {
  background: rgba(58, 124, 255, 0.08);
  border: 1px solid rgba(58, 124, 255, 0.22);
  border-radius: 20px;
  color: #93b4ff;
  font-family: "Outfit", sans-serif;
  font-size: 0.72rem;
  font-weight: 500;
  padding: 5px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.quick-chip:hover {
  background: rgba(58, 124, 255, 0.18);
  border-color: rgba(58, 124, 255, 0.5);
  color: #c0d4ff;
  transform: translateY(-1px);
}
`;
document.head.appendChild(style);
