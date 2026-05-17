<?php
if (!isset($meta_title))       $meta_title       = "Rielcode | Modern Web Development Studio";
if (!isset($meta_description)) $meta_description = "Rielcode is a modern web development studio creating fast, beautiful websites for businesses and creators across Indonesia. Real code, real results.";
if (!isset($meta_keywords))    $meta_keywords    = "Rielcode, web development Indonesia, jasa pembuatan website, web design, digital agency, Surakarta";
if (!isset($meta_image))       $meta_image       = "https://rielcode.com/IMG/Rielcode Logo Square.png";
if (!isset($meta_url))         $meta_url         = "https://rielcode.com" . ($_SERVER['REQUEST_URI'] ?? '/');
if (!isset($meta_type))        $meta_type        = "website";
if (!isset($meta_robots))      $meta_robots      = "index, follow";
if (!isset($canonical))        $canonical        = $meta_url;
if (!isset($twitter_card))     $twitter_card     = "summary_large_image";

if (isset($og_type)) $meta_type = $og_type;

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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://unpkg.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<link rel="dns-prefetch" href="https://www.googletagmanager.com">

<title><?= $safe_title ?></title>
<meta name="title" content="<?= $safe_title ?>">
<meta name="description" content="<?= $safe_description ?>">
<meta name="keywords" content="<?= $safe_keywords ?>">
<meta name="author" content="Rielcode">
<meta name="robots" content="<?= $safe_robots ?>">
<link rel="canonical" href="<?= $safe_canonical ?>">

<meta property="og:type" content="<?= $safe_type ?>">
<meta property="og:url" content="<?= $safe_url ?>">
<meta property="og:title" content="<?= $safe_title ?>">
<meta property="og:description" content="<?= $safe_description ?>">
<meta property="og:image" content="<?= $safe_image ?>">
<meta property="og:locale" content="en_US">
<meta property="og:site_name" content="Rielcode">

<meta name="twitter:card" content="<?= $safe_card ?>">
<meta name="twitter:url" content="<?= $safe_url ?>">
<meta name="twitter:title" content="<?= $safe_title ?>">
<meta name="twitter:description" content="<?= $safe_description ?>">
<meta name="twitter:image" content="<?= $safe_image ?>">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "name": <?= json_encode($meta_title,       JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      "description": <?= json_encode($meta_description, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      "url": <?= json_encode($meta_url,          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      "image": <?= json_encode($meta_image,      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      "publisher": { "@id": "https://rielcode.com/#organization" }
    },
    {
      "@type": "LocalBusiness",
      "@id": "https://rielcode.com/#organization",
      "name": "Rielcode",
      "url": "https://rielcode.com",
      "logo": "https://rielcode.com/IMG/Rielcode Logo Square Transparent.png",
      "image": "https://rielcode.com/IMG/Rielcode Logo Square.png",
      "description": "Modern web development studio creating fast, beautiful websites for businesses and creators across Indonesia.",
      "email": "info@rielcode.com",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Jl. Dipomenggolo, RT.01/RW.04, Ngentaksari, Pulutan",
        "addressLocality": "Salatiga",
        "addressRegion": "Central Java",
        "addressCountry": "ID"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer support",
        "email": "info@rielcode.com",
        "availableLanguage": ["English", "Indonesian"]
      },
      "sameAs": [
        "https://www.instagram.com/rielcode",
        "https://github.com/Azriel-Fajar"
      ],
      "priceRange": "$$",
      "currenciesAccepted": "IDR",
      "paymentAccepted": "Bank Transfer"
    }
  ]
}
</script>
