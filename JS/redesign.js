// Rielcode — redesign layer (terminal hero typing)
(function () {
  const code = document.getElementById("rc-term-code");
  if (!code) return;

  const LINES = [
    { t: "// real code, real results", c: "c-mute" },
    { t: '$ rielcode init "your-brand"', c: "c-prompt",
      parts: [
        { t: "$ ",            c: "c-mute" },
        { t: "rielcode init ", c: "c-fn" },
        { t: '"your-brand"',   c: "c-str" },
      ] },
    { t: "→ designing layout",       c: "c-mute" },
    { t: "→ writing clean code",     c: "c-mute" },
    { t: "→ optimizing for speed",   c: "c-mute" },
    { t: "✓ shipped in 3 days",      c: "c-ok" },
  ];

  const reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const renderFinal = (line) => {
    if (!line.parts) {
      const div = document.createElement("div");
      div.className = "rc-term__line " + line.c;
      div.textContent = line.t;
      return div;
    }
    const div = document.createElement("div");
    div.className = "rc-term__line " + line.c;
    line.parts.forEach(p => {
      const span = document.createElement("span");
      span.className = p.c;
      span.textContent = p.t;
      div.appendChild(span);
    });
    return div;
  };

  if (reduce) {
    LINES.forEach(l => code.appendChild(renderFinal(l)));
    return;
  }

  let cancelled = false;
  let idx = 0;
  let activeDiv = null;
  let cursor = null;

  const ensureCursor = () => {
    if (!cursor) {
      cursor = document.createElement("span");
      cursor.className = "rc-term__cursor";
      cursor.textContent = "▍";
    }
  };

  const startTypingLine = (i) => {
    if (cancelled) return;
    if (i >= LINES.length) {
      setTimeout(() => {
        if (cancelled) return;
        code.innerHTML = "";
        activeDiv = null; cursor = null;
        startTypingLine(0);
      }, 2400);
      return;
    }
    const line = LINES[i];
    activeDiv = document.createElement("div");
    activeDiv.className = "rc-term__line " + line.c;
    code.appendChild(activeDiv);
    ensureCursor();
    activeDiv.appendChild(cursor);

    let p = 0;
    const full = line.t;
    const tick = () => {
      if (cancelled) return;
      p++;
      const slice = full.slice(0, p);
      // text node before cursor
      activeDiv.removeChild(cursor);
      activeDiv.textContent = slice;
      activeDiv.appendChild(cursor);
      if (p >= full.length) {
        setTimeout(() => {
          if (cancelled) return;
          // finalize line with parts coloring if any
          activeDiv.removeChild(cursor);
          if (line.parts) {
            activeDiv.textContent = "";
            line.parts.forEach(pp => {
              const s = document.createElement("span");
              s.className = pp.c;
              s.textContent = pp.t;
              activeDiv.appendChild(s);
            });
          }
          setTimeout(() => startTypingLine(i + 1), 220);
        }, 280);
      } else {
        setTimeout(tick, 22 + Math.random() * 28);
      }
    };
    tick();
  };

  setTimeout(() => startTypingLine(0), 400);
  window.addEventListener("beforeunload", () => { cancelled = true; });
})();
