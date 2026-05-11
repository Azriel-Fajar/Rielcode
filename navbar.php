<?php if (!isset($base)) $base = ''; ?>
<nav class="rc-nav rc-nav--blur" id="rc-nav">
    <div class="rc-nav__inner rc-container">
        <a class="rc-nav__logo" href="<?= $base ?>">
            <img src="<?= $base ?>IMG/Rielcode Logo Transparent.png" alt="Rielcode">
        </a>
        <ul class="rc-nav__links">
            <li><a href="<?= $base ?>#hero"         class="rc-nav__link" data-target="hero">Home</a></li>
            <li><a href="<?= $base ?>#packages"     class="rc-nav__link" data-target="packages">Packages</a></li>
            <li><a href="<?= $base ?>#about"        class="rc-nav__link" data-target="about">About</a></li>
            <li><a href="<?= $base ?>#projects"     class="rc-nav__link" data-target="projects">Projects</a></li>
            <li><a href="<?= $base ?>#requirements" class="rc-nav__link" data-target="requirements">Requirements</a></li>
            <li><a href="<?= $base ?>#contact"      class="rc-nav__link" data-target="contact">Contact</a></li>
        </ul>
        <div class="rc-nav__cta">
            <a class="rc-btn rc-btn-sm" href="<?= $base ?>order-form/">
                Start a Project
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <button class="rc-nav__burger" id="rc-burger" aria-label="Open menu" type="button">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="13" x2="20" y2="13"/><line x1="4" y1="19" x2="20" y2="19"/></svg>
            </button>
        </div>
    </div>
</nav>

<div class="rc-mnav" id="rc-mnav" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="rc-mnav__head">
        <a class="rc-nav__logo" href="<?= $base ?>">
            <img src="<?= $base ?>IMG/Rielcode Logo Transparent.png" alt="Rielcode">
        </a>
        <button class="rc-mnav__close" id="rc-mnav-close" aria-label="Close menu" type="button">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/></svg>
        </button>
    </div>
    <ul class="rc-mnav__list">
        <li><span class="rc-mnav__num">01</span><a href="<?= $base ?>#hero">Home</a></li>
        <li><span class="rc-mnav__num">02</span><a href="<?= $base ?>#packages">Packages</a></li>
        <li><span class="rc-mnav__num">03</span><a href="<?= $base ?>#about">About</a></li>
        <li><span class="rc-mnav__num">04</span><a href="<?= $base ?>#projects">Projects</a></li>
        <li><span class="rc-mnav__num">05</span><a href="<?= $base ?>#requirements">Requirements</a></li>
        <li><span class="rc-mnav__num">06</span><a href="<?= $base ?>#contact">Contact</a></li>
    </ul>
    <div class="rc-mnav__foot">
        <a class="rc-btn" href="<?= $base ?>order-form/">
            Start a Project
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <p class="rc-body-sm" style="margin:0">info@rielcode.com</p>
    </div>
</div>
<button class="rc-mnav__scrim" id="rc-mnav-scrim" aria-label="Close menu" type="button" tabindex="-1"></button>
