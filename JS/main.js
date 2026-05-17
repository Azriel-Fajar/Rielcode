// ===== Navbar (rc- design contract) =====
const rcNav      = document.getElementById("rc-nav");
const rcBurger   = document.getElementById("rc-burger");
const rcMnav     = document.getElementById("rc-mnav");
const rcMnavScrim= document.getElementById("rc-mnav-scrim");
const rcMnavClose= document.getElementById("rc-mnav-close");
const rcNavLinks = document.querySelectorAll(".rc-nav__link, .rc-mnav__list a");

const openMnav  = () => { rcMnav.classList.add("is-open"); rcMnavScrim.classList.add("is-open"); rcMnav.setAttribute("aria-hidden","false"); document.body.style.overflow = "hidden"; };
const closeMnav = () => { rcMnav.classList.remove("is-open"); rcMnavScrim.classList.remove("is-open"); rcMnav.setAttribute("aria-hidden","true"); document.body.style.overflow = ""; };

if (rcBurger)    rcBurger.addEventListener("click", openMnav);
if (rcMnavClose) rcMnavClose.addEventListener("click", closeMnav);
if (rcMnavScrim) rcMnavScrim.addEventListener("click", closeMnav);
rcNavLinks.forEach(a => a.addEventListener("click", closeMnav));

// Scrolled state
window.addEventListener("scroll", () => {
    if (rcNav) rcNav.classList.toggle("rc-nav--scrolled", window.scrollY > 8);
}, { passive: true });

// Scroll-spy active link
const rcSections = ["hero","packages","about","projects","requirements","contact"]
    .map(id => document.getElementById(id)).filter(Boolean);
const rcDesktopLinks = document.querySelectorAll(".rc-nav__link");
window.addEventListener("scroll", () => {
    let current = "hero";
    const y = window.scrollY + 120;
    rcSections.forEach(s => { if (s.offsetTop <= y) current = s.id; });
    rcDesktopLinks.forEach(l => l.classList.toggle("is-active", l.dataset.target === current));
}, { passive: true });

// ===== ScrollReveal animations =====
// Emil: keep stagger short (30–80ms). Distance reduced — large slides feel sluggish.
ScrollReveal({
    distance: "28px",
    duration: 700,
    delay: 80,
    easing: "cubic-bezier(0.23, 1, 0.32, 1)",
    reset: false
});

// Hero
ScrollReveal().reveal(".rc-hero__copy",  { origin: "left" });
ScrollReveal().reveal(".rc-hero__art",   { delay: 160, origin: "right" });

// Packages — stagger cards
ScrollReveal().reveal(".pricing-section h1",           { origin: "top" });
ScrollReveal().reveal(".pricing-section .pricing-card",{ origin: "bottom", interval: 60 });

// About
ScrollReveal().reveal(".rc-about__head",   { origin: "left" });
ScrollReveal().reveal(".rc-about__body p", { interval: 60, origin: "right" });

// Projects — stagger tiles
ScrollReveal().reveal(".rc-prj__head", { origin: "top" });
ScrollReveal().reveal(".rc-prj__feat", { origin: "left" });
ScrollReveal().reveal(".rc-prj__tile", { interval: 60, origin: "bottom" });

// Testimonials — stagger cards
ScrollReveal().reveal(".rc-testi__card, .testimonial-card", { interval: 60, origin: "bottom" });

// Requirements
ScrollReveal().reveal("#requirements h2",              { origin: "top" });
ScrollReveal().reveal("#requirements .lead",           { origin: "top" });
ScrollReveal().reveal("#requirements .req-intro-card", { origin: "top", delay: 120 });
ScrollReveal().reveal("#requirements .req-card",       { origin: "bottom", interval: 60 });