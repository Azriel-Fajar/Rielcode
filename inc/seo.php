<?php
/**
 *
 * BUG FIX: $base_url was declared but never used — removed.
 * BUG FIX: JSON-LD was hardcoded to the Projects page — now fully dynamic.
 * BUG FIX: $meta_robots and $canonical were read from calling files but never
 *           actually output — now they are.
 */

// --- Defaults ---
if (!isset($meta_title))       $meta_title       = "Rielcode | Modern Web Development Studio";
if (!isset($meta_description)) $meta_description = "Rielcode is a modern web development studio that creates engaging and efficient digital experiences for businesses and creators.";
if (!isset($meta_keywords))    $meta_keywords    = "Rielcode, web development, web design, digital agency, Surakarta, Indonesia";
if (!isset($meta_image))       $meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
if (!isset($meta_url))         $meta_url         = "https://rielcode.com" . ($_SERVER['REQUEST_URI'] ?? '/');
if (!isset($meta_type))        $meta_type        = "website";
if (!isset($meta_robots))      $meta_robots      = "index, follow";
if (!isset($canonical))        $canonical        = $meta_url;
if (!isset($twitter_card))     $twitter_card     = "summary_large_image";

// $og_type overrides $meta_type if set
if (isset($og_type))           $meta_type        = $og_type;

// Sanitise for output
$safe_title       = htmlspecialchars($meta_title,       ENT_QUOTES, 'UTF-8');
$safe_description = htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8');
$safe_keywords    = htmlspecialchars($meta_keywords,    ENT_QUOTES, 'UTF-8');
$safe_image       = htmlspecialchars($meta_image,       ENT_QUOTES, 'UTF-8');
$safe_url         = htmlspecialchars($meta_url,         ENT_QUOTES, 'UTF-8');
$safe_canonical   = htmlspecialchars($canonical,        ENT_QUOTES, 'UTF-8');
$safe_type        = htmlspecialchars($meta_type,        ENT_QUOTES, 'UTF-8');
$safe_robots      = htmlspecialchars($meta_robots,      ENT_QUOTES, 'UTF-8');
$safe_card        = htmlspecialchars($twitter_card,     ENT_QUOTES, 'UTF-8');
?>
<!-- Primary Meta Tags -->
<title><?= $safe_title ?></title>
<meta name="title"       content="<?= $safe_title ?>">
<meta name="description" content="<?= $safe_description ?>">
<meta name="keywords"    content="<?= $safe_keywords ?>">
<meta name="author"      content="Rielcode">
<meta name="robots"      content="<?= $safe_robots ?>">
<link rel="canonical"    href="<?= $safe_canonical ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type"        content="<?= $safe_type ?>">
<meta property="og:url"         content="<?= $safe_url ?>">
<meta property="og:title"       content="<?= $safe_title ?>">
<meta property="og:description" content="<?= $safe_description ?>">
<meta property="og:image"       content="<?= $safe_image ?>">

<!-- Twitter Card -->
<meta name="twitter:card"        content="<?= $safe_card ?>">
<meta name="twitter:url"         content="<?= $safe_url ?>">
<meta name="twitter:title"       content="<?= $safe_title ?>">
<meta name="twitter:description" content="<?= $safe_description ?>">
<meta name="twitter:image"       content="<?= $safe_image ?>">

<!-- JSON-LD Structured Data (dynamic) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": <?= json_encode($meta_title,       JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "description": <?= json_encode($meta_description, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "url": <?= json_encode($meta_url,          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "image": <?= json_encode($meta_image,      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "publisher": {
    "@type": "Organization",
    "name": "Rielcode",
    "url": "https://rielcode.com",
    "logo": {
      "@type": "ImageObject",
      "url": "https://rielcode.com/IMG/Rielcode Logo Square Transparent.png"
    }
  }
}
</script>