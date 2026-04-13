// ===== Navbar =====
const navbar         = document.querySelector(".navbar");
const burger         = document.querySelector("#burger");
const navMenuMobile  = document.querySelector(".navbar-mobile-container");
const ctaNavbar      = document.querySelector(".cta-navbar");
const navLinks       = document.querySelectorAll(".navbar-link");

burger.addEventListener("click", () => {
    navMenuMobile.classList.toggle("active");
});

// Close mobile menu when clicking outside
document.addEventListener("click", (e) => {
    if (!burger.contains(e.target) && !navMenuMobile.contains(e.target)) {
        navMenuMobile.classList.remove("active");
    }
});

// Close mobile menu when a nav link is clicked
navLinks.forEach((link) => {
    link.addEventListener("click", () => {
        navMenuMobile.classList.remove("active");
    });
});

// Sticky navbar on scroll
window.addEventListener("scroll", () => {
    const scrolled = window.pageYOffset > 80;
    navbar.classList.toggle("active", scrolled);
});

// ===== ScrollReveal animations =====
ScrollReveal({ distance: "50px", duration: 1000, delay: 100 });

// Hero
ScrollReveal().reveal("#hero .text-container", { origin: "top" });
ScrollReveal().reveal("#hero .cta-container",  { delay: 300, origin: "top" });

// Packages
ScrollReveal().reveal(".pricing-section h1",          { origin: "top" });
ScrollReveal().reveal(".pricing-section .pricing-card",{ origin: "top" });

// About
ScrollReveal().reveal(".about img", { origin: "top" });
ScrollReveal().reveal(".about h2",  { origin: "top" });
ScrollReveal().reveal(".about p",   { origin: "top" });

// Projects
ScrollReveal().reveal("#projects h2",   { origin: "top" });
ScrollReveal().reveal("#projects .lead",{ origin: "top" });
ScrollReveal().reveal("#projects .row", { origin: "top" });

// Requirements (new section)
ScrollReveal().reveal("#requirements h2",           { origin: "top" });
ScrollReveal().reveal("#requirements .lead",        { origin: "top" });
ScrollReveal().reveal("#requirements .req-intro-card", { origin: "top", delay: 150 });
ScrollReveal().reveal("#requirements .req-card",    { origin: "bottom", interval: 80 });