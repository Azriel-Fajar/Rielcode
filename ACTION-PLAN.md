# Rielcode.com — SEO Action Plan
**Generated:** 2026-04-29
**Based on:** FULL-AUDIT-REPORT.md

Priority order: Critical → High → Medium → Low

---

## CRITICAL — Fix Immediately

### C1. HTTP → HTTPS Redirect
**Issue:** `http://rielcode.com` returns 200 OK instead of 301 redirect.
**Impact:** Duplicate content, lost link equity, security risk.
**Fix:** Add to `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### C2. Fix 500 Error on Unknown Paths
**Issue:** Unknown paths (e.g. `/llms.txt`) return 500 Internal Server Error.
**Impact:** Crawl errors, Google sees 500s as crawl failures — can suppress indexing.
**Fix:** Check `.htaccess` for misconfigured `ErrorDocument`. Add:
```apache
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html
```
Then create simple `404.html` and `500.html` pages.

### C3. Fix Duplicate `<title>` Tags
**Issue:** Each section injects its own `<title>` tag outside `<head>`. Page has 2–5 `<title>` elements.
**Impact:** Google uses first title found — section titles override the well-crafted `<head>` title.
**Fix:** Remove `<title>` tags from all section-level meta injection blocks. Keep only the one in `<head>`. Section-specific OG/Twitter meta is fine to keep.

### C4. Fix Hash-Fragment Canonicals
**Issue:** Canonicals like `<link rel="canonical" href="https://rielcode.com/#packages">` are invalid — Google ignores hash fragments for canonicalization.
**Impact:** Canonical signal wasted; could confuse crawlers.
**Fix:** All section-level canonicals should point to `https://rielcode.com/`:
```html
<link rel="canonical" href="https://rielcode.com/">
```

### C5. Fix Hash-Fragment URLs in Sitemap
**Issue:** `sitemap.xml` contains `/#about` and `/#package` — not real indexable URLs.
**Impact:** Google ignores these; wastes crawl budget.
**Fix — new sitemap.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://rielcode.com/</loc>
    <lastmod>2026-04-29</lastmod>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://rielcode.com/order-form/</loc>
    <lastmod>2026-04-29</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
</urlset>
```

---

## HIGH — Fix Within 1 Week

### H1. Add Security Headers
**Issue:** No `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`.
**Impact:** Security vulnerabilities; Google uses HTTPS/security as ranking signal.
**Fix — add to `.htaccess`:**
```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set X-XSS-Protection "1; mode=block"
```

### H2. Replace WebPage Schemas with Organization + LocalBusiness
**Issue:** All 5 schemas are generic `WebPage` type — no business entity, no service, no pricing data.
**Impact:** Misses rich result eligibility; weak E-E-A-T signal to Google.
**Fix — add to `<head>` (single schema block):**
```json
{
  "@context": "https://schema.org",
  "@type": ["Organization", "LocalBusiness", "ProfessionalService"],
  "name": "Rielcode",
  "url": "https://rielcode.com",
  "logo": "https://rielcode.com/IMG/Rielcode%20Logo%20Square%20Transparent.png",
  "description": "Rielcode is a web development studio based in Salatiga, Indonesia, building custom websites for businesses, startups, and creators.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Dipomenggolo, RT.01/RW.04, Ngentaksari, Pulutan",
    "addressLocality": "Salatiga",
    "addressRegion": "Central Java",
    "addressCountry": "ID"
  },
  "areaServed": ["Salatiga", "Surakarta", "Central Java", "Indonesia"],
  "telephone": "+6281295536876",
  "email": "info@rielcode.com",
  "sameAs": [
    "https://www.instagram.com/rielcode",
    "https://github.com/Azriel-Fajar"
  ],
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Website Development Packages",
    "itemListElement": [
      {"@type": "Offer", "name": "Student Plan", "price": "29.99", "priceCurrency": "USD"},
      {"@type": "Offer", "name": "Starter Plan", "price": "59.99", "priceCurrency": "USD"},
      {"@type": "Offer", "name": "Pro Plan", "price": "119.99", "priceCurrency": "USD"},
      {"@type": "Offer", "name": "Premium Plan", "price": "239.99", "priceCurrency": "USD"}
    ]
  }
}
```

### H3. Fix NAP Inconsistency — Remove "Surakarta" from Keywords
**Issue:** `<meta name="keywords">` contains "website Surakarta" but address is Salatiga.
**Impact:** NAP (Name/Address/Phone) inconsistency — local SEO ranking factor.
**Fix:** Replace `website Surakarta` with `website Salatiga` in homepage keywords meta. Add Surakarta only as `areaServed` in schema (service area, not address).

### H4. Fix H1 — Add Keyword to Hero Heading
**Issue:** H1 is `"Real Code. Real Results."` — zero search volume tagline, not a keyword.
**Impact:** H1 is a top on-page ranking signal. Currently wasted.
**Fix:** Either change H1 or add a visible subtitle:
- Option A: `<h1>Web Development Studio in Salatiga | Real Code. Real Results.</h1>`
- Option B: Keep tagline as H1, add `<h2>Professional Website Development for Businesses in Indonesia</h2>` below hero text

### H5. Fix Multiple H1 Tags
**Issue:** Two `<h1>` elements on page (`<h1>Real Code. Real Results.</h1>` + `<h1 class="pricing-title">Website Development Packages</h1>`).
**Impact:** Dilutes H1 signal.
**Fix:** Change pricing section H1 to H2:
```html
<h2 class="pricing-title">Website Development Packages</h2>
```

### H6. Fix OG Image Filename Space
**Issue:** `og:image` points to `IMG/Rielcode Logo.png` — space in filename.
**Impact:** OG image may fail to load on Facebook/WhatsApp preview (URL encoding issues).
**Fix:** Rename file to `rielcode-logo.png` (no spaces, lowercase) and update all references.

### H7. Add Order Form Meta Description + Schema
**Issue:** Order form page has no meta description, no schema, generic title.
**Impact:** Order form may appear in search with no snippet — hurts CTR.
**Fix:**
```html
<meta name="description" content="Place your website order with Rielcode. Choose from Student, Starter, Pro, or Premium packages — fast delivery, responsive design, and free hosting included.">
```

---

## MEDIUM — Fix Within 1 Month

### M1. Create Local SEO Landing Page
**Opportunity:** No `/jasa-website-salatiga/` or `/web-development-salatiga/` page.
**Impact:** High-value local keyword page — captures "jasa website Salatiga" searches.
**Action:** Create a dedicated page targeting local keywords with:
- Local address, map embed
- Local testimonials (once available)
- `LocalBusiness` schema
- Indonesian-language content

### M2. Add `llms.txt` for AI Search Readiness
**Issue:** `/llms.txt` returns 500. AI crawlers (ChatGPT, Perplexity, Claude) look for this.
**Action:** Create `/llms.txt`:
```
# Rielcode — Web Development Studio

> Rielcode is a website development studio based in Salatiga, Indonesia.
> We build custom websites for businesses, startups, and creators.
> Packages from $29.99. Contact: info@rielcode.com | wa.me/6281295536876

## Services
- Custom websites
- Landing pages (Student Plan: $29.99)
- Business websites (Starter: $59.99, Pro: $119.99)
- E-commerce (Premium: $239.99)

## Portfolio
- Parallaxnet (parallaxnet.id)
- DAAM (daam.co.id)
- 3s-tech (3s-tech.co.id)
```

### M3. Defer/Async All Non-Critical Scripts
**Issue:** `scrollreveal` loaded synchronously blocks render.
**Fix:**
```html
<!-- Change this: -->
<script src="https://unpkg.com/scrollreveal"></script>
<!-- To this: -->
<script defer src="https://unpkg.com/scrollreveal"></script>
```
Also add `defer` to `chatbot.js`.

### M4. Add Static Asset Caching
**Issue:** `Cache-Control: no-store, no-cache` applies to all responses including static assets.
**Impact:** CSS/JS/images re-downloaded on every visit — hurts repeat visit performance.
**Fix — add to `.htaccess`:**
```apache
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ico)$">
  Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>
```

### M5. Add `apple-touch-icon` and ICO Favicon
**Issue:** Only PNG favicons defined. No `apple-touch-icon`, no `.ico` fallback.
**Fix:**
```html
<link rel="apple-touch-icon" sizes="180x180" href="IMG/apple-touch-icon.png">
<link rel="icon" href="favicon.ico" type="image/x-icon">
```

### M6. Fix www / non-www Canonical Consolidation
**Issue:** Both `www.rielcode.com` and `rielcode.com` return 200.
**Fix — add to `.htaccess`:**
```apache
RewriteCond %{HTTP_HOST} ^www\.rielcode\.com [NC]
RewriteRule ^(.*)$ https://rielcode.com/$1 [L,R=301]
```

### M7. Add Testimonials Section
**Issue:** No social proof on page — weak E-E-A-T.
**Action:** After Parallaxnet delivery, request a written testimonial. Add a testimonials section with `Review` schema:
```json
{
  "@type": "Review",
  "author": {"@type": "Person", "name": "Ali (Parallaxnet)"},
  "reviewBody": "...",
  "reviewRating": {"@type": "Rating", "ratingValue": "5"}
}
```

### M8. Request Backlinks from Client Sites
**Action:** Ask Parallaxnet, DAAM, and 3s-tech to add "Built by Rielcode" footer link pointing to `rielcode.com`. This creates real do-follow backlinks from live sites you built.

### M9. Convert Images to WebP
**Action:** Convert PNG assets to WebP format. Use `<picture>` tag with WebP + PNG fallback. Priority: hero background (`bg.jpg`), OG images, project logos.

### M10. Add `<meta name="language">` / Review `lang` Attribute
**Issue:** Schema 3 (About) is in Indonesian but `<html lang="en">`.
**Fix:** Standardize. Either translate About schema to English, or add `hreflang` tags if planning a bilingual site. For now: translate all schema descriptions to English to match `lang="en"`.

---

## LOW — Backlog

### L1. Add FAQPage Schema
**Opportunity:** The price estimator section answers common questions. Add `FAQPage` schema for:
- What's included in each plan?
- How long does delivery take?
- Is hosting included?

### L2. Add `rel="me"` for Identity Verification
```html
<link rel="me" href="https://github.com/Azriel-Fajar">
<link rel="me" href="https://www.instagram.com/rielcode">
```

### L3. Create a Blog / Resource Section
**Opportunity:** Even 3–5 articles on "how to build a website for your business" captures informational keywords that convert to leads. Supports E-E-A-T and backlink acquisition.

### L4. Add `BreadcrumbList` Schema to Order Form
```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://rielcode.com/"},
    {"@type": "ListItem", "position": 2, "name": "Order", "item": "https://rielcode.com/order-form/"}
  ]
}
```

### L5. Register Google Business Profile
**Action:** Create/claim GBP listing for Rielcode in Salatiga. Enables Google Maps appearance for local searches.

### L6. Remove `X-Powered-By` Header
```apache
Header unset X-Powered-By
ServerSignature Off
```
Minor security hygiene — prevents server fingerprinting.

### L7. Add SRI Hash to AOS CDN Resources
**Issue:** AOS CSS/JS loaded from `unpkg.com` without Subresource Integrity hashes.
**Risk:** CDN compromise could inject malicious code.
**Fix:** Add `integrity` and `crossorigin` attributes (generate at srihash.com).

---

## Implementation Priority Matrix

| ID | Action | Effort | Impact | Do First |
|---|---|---|---|---|
| C1 | HTTP→HTTPS redirect | 5 min | Critical | YES |
| C2 | Fix 500 errors | 15 min | Critical | YES |
| C3 | Remove duplicate titles | 20 min | High | YES |
| C4 | Fix hash canonicals | 20 min | High | YES |
| C5 | Fix sitemap | 10 min | High | YES |
| H1 | Security headers | 10 min | High | YES |
| H2 | Replace schemas | 30 min | High | YES |
| H3 | Fix Surakarta→Salatiga | 5 min | Medium | YES |
| H4 | H1 keyword fix | 15 min | Medium | Week 1 |
| H5 | Fix multiple H1 | 5 min | Medium | Week 1 |
| H6 | Fix OG image filename | 10 min | Medium | Week 1 |
| H7 | Order form meta | 10 min | Medium | Week 1 |
| M1 | Local landing page | 2 hrs | High | Month 1 |
| M2 | llms.txt | 15 min | Low-Med | Month 1 |
| M3 | Defer scripts | 10 min | Medium | Month 1 |
| M4 | Asset caching | 10 min | Medium | Month 1 |
| M7 | Testimonials | 1 hr | High | After Parallaxnet delivery |
| M8 | Client backlinks | 5 min | High | After delivery |
