<?php
// Projects section — auto-rotating featured + paginated side slider.
// Falls back to hardcoded entries if DB table is missing or empty.
$projects_featured = [];
$projects_side     = [];
if (isset($conn) && $conn instanceof mysqli) {
    $tableExists = @$conn->query("SHOW TABLES LIKE 'projects'");
    if ($tableExists && $tableExists->num_rows > 0) {
        $res = $conn->query("SELECT * FROM projects WHERE is_visible = 1 ORDER BY layout DESC, sort_order ASC, id ASC");
        if ($res) {
            while ($p = $res->fetch_assoc()) {
                if ($p['layout'] === 'featured') {
                    $projects_featured[] = $p;
                } else {
                    $projects_side[] = $p;
                }
            }
        }
    }
}

// If no featured but we have side projects, promote first side to featured so the layout still works.
if (empty($projects_featured) && !empty($projects_side)) {
    $projects_featured[] = array_shift($projects_side);
}

// Group side projects into pages of 2 for the slider.
$side_pages = array_chunk($projects_side, 2);

function rc_render_tags($csv) {
    $tags = array_filter(array_map('trim', explode(',', $csv ?? '')));
    foreach ($tags as $t) {
        echo '<span class="rc-tag">' . htmlspecialchars($t) . '</span>';
    }
}
function rc_host($url) {
    $h = parse_url($url, PHP_URL_HOST);
    return $h ? preg_replace('/^www\./', '', $h) : $url;
}
?>
<section class="rc-prj rc-prj--asymmetric" id="projects">
    <div class="rc-container">
        <div class="rc-prj__head">
            <span class="rc-eyebrow">our work</span>
            <h2 class="rc-h2">Projects</h2>
            <p class="rc-body rc-prj__sub">Every Website is Unique <span class="rc-prj__sep">|</span> Rielcode, Real Code.</p>
        </div>

        <div class="rc-prj__layout">
            <?php if (!empty($projects_featured)): ?>
                <!-- ========= FEATURED CROSS-FADE ========= -->
                <div class="rc-prj__feat-stage" data-rc-feat-count="<?= count($projects_featured) ?>">
                    <?php foreach ($projects_featured as $i => $p): ?>
                        <article class="rc-prj__feat<?= $i === 0 ? ' is-active' : '' ?>" data-rc-feat-index="<?= $i ?>" style="--accent:#3a7bff">
                            <div class="rc-prj__feat-thumb">
                                <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                                <?php if (!empty($p['url'])): ?>
                                    <div class="rc-prj__feat-mock-bar">
                                        <span></span><span></span><span></span>
                                        <div class="rc-prj__feat-url"><?= htmlspecialchars(rc_host($p['url'])) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="rc-prj__feat-meta">
                                <div class="rc-prj__tags"><?php rc_render_tags($p['tags']); ?></div>
                                <h3 class="rc-prj__feat-title"><?= htmlspecialchars($p['title']) ?></h3>
                                <p class="rc-prj__feat-desc rc-body"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                                <?php if (!empty($p['url'])): ?>
                                    <a class="rc-btn rc-prj__feat-cta" href="<?= htmlspecialchars($p['url']) ?>" target="_blank" rel="noopener">
                                        Visit Site
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if (count($projects_featured) > 1): ?>
                        <div class="rc-prj__feat-dots" role="tablist" aria-label="Featured projects">
                            <?php foreach ($projects_featured as $i => $_p): ?>
                                <button type="button"
                                        class="rc-prj__feat-dot<?= $i === 0 ? ' is-active' : '' ?>"
                                        data-rc-feat-goto="<?= $i ?>"
                                        aria-label="Featured project <?= $i + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($side_pages)): ?>
                <!-- ========= SIDE SLIDER (2-per-page) ========= -->
                <div class="rc-prj__side-wrap" data-rc-side-pages="<?= count($side_pages) ?>">
                    <div class="rc-prj__side-viewport">
                        <div class="rc-prj__side-track">
                            <?php foreach ($side_pages as $pageIdx => $page): ?>
                                <div class="rc-prj__side" data-rc-side-page="<?= $pageIdx ?>">
                                    <?php foreach ($page as $p): ?>
                                        <article class="rc-prj__tile rc-prj__tile--md" style="--accent:#3ecf8e">
                                            <div class="rc-prj__thumb"><img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy"></div>
                                            <div class="rc-prj__meta">
                                                <div class="rc-prj__tags"><?php rc_render_tags($p['tags']); ?></div>
                                                <h3 class="rc-prj__title"><?= htmlspecialchars($p['title']) ?></h3>
                                                <p class="rc-prj__tile-desc"><?= htmlspecialchars($p['description']) ?></p>
                                                <?php if (!empty($p['url'])): ?>
                                                    <a class="rc-prj__tile-cta" href="<?= htmlspecialchars($p['url']) ?>" target="_blank" rel="noopener">
                                                        Visit Site
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($side_pages) > 1): ?>
                        <div class="rc-prj__side-nav">
                            <button type="button" class="rc-prj__side-arrow" data-rc-side-dir="-1" aria-label="Previous projects">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18L9 12L15 6"/></svg>
                            </button>
                            <div class="rc-prj__side-dots" role="tablist" aria-label="Side projects pages">
                                <?php foreach ($side_pages as $i => $_pg): ?>
                                    <button type="button"
                                            class="rc-prj__side-dot<?= $i === 0 ? ' is-active' : '' ?>"
                                            data-rc-side-goto="<?= $i ?>"
                                            aria-label="Page <?= $i + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="rc-prj__side-arrow" data-rc-side-dir="1" aria-label="Next projects">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6L15 12L9 18"/></svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($projects_featured) && empty($projects_side)): ?>
                <p class="rc-body" style="opacity:0.6;text-align:center;padding:40px 0;">No projects to display yet — add them from the admin panel.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* === Featured cross-fade === */
    var stage = document.querySelector('.rc-prj__feat-stage');
    if (stage) {
        var slides = stage.querySelectorAll('.rc-prj__feat');
        var dots   = stage.querySelectorAll('.rc-prj__feat-dot');
        var total  = slides.length;
        var idx    = 0;
        var timer  = null;

        function show(n) {
            idx = (n + total) % total;
            slides.forEach(function (s, i) { s.classList.toggle('is-active', i === idx); });
            dots.forEach(function (d, i)   { d.classList.toggle('is-active',  i === idx); });
        }
        function next() { show(idx + 1); }
        function start() { if (reduce || total < 2) return; stop(); timer = setInterval(next, 6000); }
        function stop()  { if (timer) { clearInterval(timer); timer = null; } }

        dots.forEach(function (d) {
            d.addEventListener('click', function () {
                show(parseInt(d.getAttribute('data-rc-feat-goto'), 10) || 0);
                start();
            });
        });

        if (total > 1) {
            stage.addEventListener('mouseenter', stop);
            stage.addEventListener('mouseleave', start);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) stop(); else start();
            });
            start();
        }
    }

    /* === Side slider (translateX, 2 tiles per page) === */
    var sideWrap = document.querySelector('.rc-prj__side-wrap');
    if (sideWrap) {
        var track   = sideWrap.querySelector('.rc-prj__side-track');
        var pages   = sideWrap.querySelectorAll('.rc-prj__side');
        var dotsS   = sideWrap.querySelectorAll('.rc-prj__side-dot');
        var arrows  = sideWrap.querySelectorAll('.rc-prj__side-arrow');
        var totalS  = pages.length;
        var idxS    = 0;
        var timerS  = null;

        function moveTo(n) {
            idxS = (n + totalS) % totalS;
            if (track) track.style.transform = 'translateX(-' + (idxS * 100) + '%)';
            dotsS.forEach(function (d, i) { d.classList.toggle('is-active', i === idxS); });
        }
        function nextS() { moveTo(idxS + 1); }
        function startS() { if (reduce || totalS < 2) return; stopS(); timerS = setInterval(nextS, 7500); }
        function stopS()  { if (timerS) { clearInterval(timerS); timerS = null; } }

        arrows.forEach(function (a) {
            a.addEventListener('click', function () {
                moveTo(idxS + (parseInt(a.getAttribute('data-rc-side-dir'), 10) || 1));
                startS();
            });
        });
        dotsS.forEach(function (d) {
            d.addEventListener('click', function () {
                moveTo(parseInt(d.getAttribute('data-rc-side-goto'), 10) || 0);
                startS();
            });
        });

        if (totalS > 1) {
            sideWrap.addEventListener('mouseenter', stopS);
            sideWrap.addEventListener('mouseleave', startS);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) stopS(); else startS();
            });
            startS();
        }
    }
})();
</script>
