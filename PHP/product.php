<?php
/* ============================================================================
   LUNAX — Full marketplace product detail
   Inspired by Amazon / Best Buy / Apple product pages
   URL: PHP/product.php?id=pulse-wireless-earbuds-pro-anc-30h-battery
   ========================================================================== */
session_start(); 

$products = lunax_products();


$reviewTemplates = [
    ['name' => 'Jordan M.', 'title' => 'Exactly as described', 'body' => 'Packaging was solid, arrived fast, and it works exactly like the listing promised. Would buy again from LUNAX.'],
    ['name' => 'Samira K.', 'title' => 'Great value vs big stores', 'body' => 'Compared prices on bigger sites — same quality feel here for less. Setup took minutes.'],
    ['name' => 'Chris P.', 'title' => 'Daily driver now', 'body' => 'Two weeks of daily use. Build and battery hold up. Support answered same-day.'],
    ['name' => 'Alex R.', 'title' => 'Looks premium in person', 'body' => 'Photos undersell it. Feels more expensive than what I paid.'],
    ['name' => 'Taylor B.', 'title' => 'Smooth delivery', 'body' => 'Ordered Sunday, doorstep Wednesday. Tracking was clear. Specs matched perfectly.'],
    ['name' => 'Morgan L.', 'title' => 'Gift-ready packaging', 'body' => 'Bought as a gift — box looked clean, no damage, included everything listed.'],
];

$id = isset($_GET['id']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['id'])) : null;
$product = ($id && isset($products[$id])) ? $products[$id] : reset($products);

if (!$product) {
    http_response_code(404);
    echo 'Product not found.';
    exit;
}

$savePct = (!empty($product['was']) && $product['was'] > $product['price'])
    ? (int) round((1 - $product['price'] / $product['was']) * 100)
    : 0;

$payMonthly = max(5.99, round($product['price'] / 6, 2));
$ratingDist = [5 => 58, 4 => 24, 3 => 10, 2 => 5, 1 => 3];

$productReviews = [];
for ($r = 0; $r < 5; $r++) {
    $t = $reviewTemplates[($product['index'] + $r) % count($reviewTemplates)];
    $productReviews[] = [
        'name' => $t['name'], 'title' => $t['title'], 'body' => $t['body'],
        'stars' => max(3, min(5, (int) round($product['rating']) - ($r === 4 ? 1 : 0))),
        'date' => date('M j, Y', strtotime('-' . (5 + $r * 9 + $product['index']) . ' days')),
        'verified' => true,
        'helpful' => 3 + (($product['index'] + $r) * 7) % 40,
    ];
}

$related = $fbt = [];
foreach ($products as $p) {
    if ($p['id'] === $product['id']) continue;
    if ($p['category'] === $product['category'] && count($related) < 4) $related[] = $p;
    if ($p['price'] < $product['price'] * 0.55 && count($fbt) < 2) $fbt[] = $p;
}
if (count($related) < 4) {
    foreach ($products as $p) {
        if ($p['id'] === $product['id'] || in_array($p, $related, true)) continue;
        $related[] = $p;
        if (count($related) >= 4) break;
    }
}
if (count($fbt) < 2) {
    foreach ($products as $p) {
        if ($p['id'] === $product['id'] || in_array($p, $fbt, true)) continue;
        $fbt[] = $p;
        if (count($fbt) >= 2) break;
    }
}
$fbtTotal = $product['price'];
foreach ($fbt as $f) $fbtTotal += $f['price'];

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
$pageTitle = $product['name'] . ' — Buy Online | LUNAX';
$galleryShifts = [0, 1, 2, 3, 4];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= htmlspecialchars($product['desc'], ENT_QUOTES, 'UTF-8') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#140F0C; --gold:#F2C94C; --gold-dark:#D1A83B; --gold-light:#F6DC7D;
    --cream:#FBF6EC; --line:rgba(20,15,12,0.12); --muted:rgba(20,15,12,0.55);
    --ok:#0d5c37; --warn:#8a5a05; --link:#1a5f9e;
  }
  *{box-sizing:border-box} body{margin:0;background:var(--cream);color:var(--ink);font-family:'JetBrains Mono',monospace}
  a{color:inherit;text-decoration:none} button{font-family:inherit} img{max-width:100%;display:block}

  .deal-bar{background:var(--gold);color:var(--ink);text-align:center;font-size:0.72rem;font-weight:600;padding:0.45rem 1rem;letter-spacing:0.04em}
  .lnx-topbar{background:var(--ink);color:#fff;padding:0.85rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;position:sticky;top:0;z-index:40}
  .lnx-logo{font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:0.14em;font-size:1.15rem}
  .lnx-nav{display:flex;align-items:center;gap:1.1rem;font-size:0.75rem}
  .lnx-nav a{color:rgba(255,255,255,.85)}.lnx-nav a:hover{color:var(--gold)}
  .lnx-cart-link{color:var(--gold)!important;font-weight:600}

  .breadcrumb{max-width:1200px;margin:0 auto;padding:1rem 1.5rem 0;font-size:0.72rem;color:var(--muted)}
  .breadcrumb a:hover{color:var(--ink);text-decoration:underline}

  .product-layout{max-width:1200px;margin:0 auto;padding:1rem 1.5rem 2rem;display:grid;grid-template-columns:1.1fr 0.95fr 280px;gap:1.75rem;align-items:start}
  .gallery{display:flex;gap:0.75rem;position:sticky;top:4.2rem}
  .thumbs{display:flex;flex-direction:column;gap:0.45rem;width:58px;flex-shrink:0}
  .thumb{width:58px;height:58px;border-radius:10px;border:1.5px solid var(--line);background:#F1ECDD;cursor:pointer;overflow:hidden;display:flex;align-items:center;justify-content:center;padding:0}
  .thumb.active{border-color:var(--gold-dark);box-shadow:0 0 0 1px var(--gold)}
  .thumb svg{width:58%;height:58%;stroke:rgba(20,15,12,.5)}
  .thumb img{width:100%;height:100%;object-fit:cover}
  .main-shot{flex:1;aspect-ratio:1;border-radius:16px;border:1px solid var(--line);background:radial-gradient(circle at 28% 22%,#FFFEF8,#F1ECDD 75%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
  .main-shot img{width:100%;height:100%;object-fit:cover}
  .main-shot svg{width:46%;height:46%;stroke:rgba(20,15,12,.5);transition:transform .35s}
  .main-shot:hover svg{transform:scale(1.06)}
  .pd-badge{position:absolute;top:1rem;left:1rem;z-index:2;font-size:0.68rem;font-weight:700;padding:0.3rem 0.7rem;border-radius:999px}
  .pd-badge.new{background:var(--gold);color:var(--ink)}.pd-badge.sale{background:var(--ink);color:var(--gold-light)}
  .zoom-hint{position:absolute;bottom:0.75rem;right:0.75rem;font-size:0.62rem;color:var(--muted);background:rgba(255,255,255,.85);padding:0.25rem 0.5rem;border-radius:6px}

  .buy-mid{min-width:0}
  .pd-brand{font-size:0.72rem;color:var(--muted);margin-bottom:0.35rem}
  .pd-brand a{color:var(--link)}.pd-brand a:hover{text-decoration:underline}
  .pd-name{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(1.2rem,2.2vw,1.55rem);line-height:1.25;margin:0 0 0.55rem}
  .pd-rating{display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.55rem;font-size:0.8rem}
  .pd-rating .stars{color:var(--gold-dark)}.pd-rating a{color:var(--link)}.pd-rating a:hover{text-decoration:underline}
  .sold-line{font-size:0.72rem;color:var(--muted);margin-bottom:0.75rem}
  .pd-divider{height:1px;background:var(--line);margin:0.85rem 0}
  .pd-price-row{display:flex;align-items:baseline;gap:0.6rem;flex-wrap:wrap}
  .pd-price{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.7rem}
  .pd-was{font-size:0.92rem;color:var(--muted);text-decoration:line-through}
  .pd-save{font-size:0.7rem;font-weight:700;color:var(--ok);background:rgba(26,156,92,.12);padding:0.2rem 0.5rem;border-radius:6px}
  .pd-install{font-size:0.75rem;color:rgba(20,15,12,.75);margin:0.35rem 0 0.5rem}
  .pd-install strong{font-weight:700}
  .coupon{display:inline-flex;align-items:center;gap:0.35rem;font-size:0.7rem;font-weight:700;color:#7a1b21;background:rgba(180,40,50,.1);border:1px dashed rgba(180,40,50,.35);padding:0.35rem 0.6rem;border-radius:6px;margin-bottom:0.75rem}
  .pd-stock{font-size:0.8rem;font-weight:600;margin:0.5rem 0 0.25rem}.pd-stock.in{color:var(--ok)}.pd-stock.low{color:var(--warn)}
  .pd-ship{font-size:0.78rem;line-height:1.55;color:rgba(20,15,12,.8);margin-bottom:0.75rem}
  .pd-sku{font-size:0.66rem;color:var(--muted);margin-bottom:1rem}
  .pd-label{font-size:0.72rem;font-weight:600;margin-bottom:0.4rem;display:block}
  .color-swatches,.variant-row{display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:1rem}
  .swatch,.variant-btn{min-width:70px;padding:0.42rem 0.6rem;border:1.5px solid var(--line);border-radius:8px;background:#fff;cursor:pointer;font-size:0.7rem;text-align:center}
  .swatch.active,.variant-btn.active{border-color:var(--ink);background:#FAF7EE;font-weight:600}
  .pd-qty-row{display:flex;align-items:center;gap:0.75rem;margin-bottom:0.85rem}
  .qty-control{display:flex;align-items:center;border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#fff}
  .qty-btn{width:34px;height:34px;border:none;background:#fff;cursor:pointer;font-size:1rem;font-weight:700}
  .qty-btn:hover{background:#FAF7EE}.qty-value{width:38px;text-align:center;font-weight:600;font-size:0.88rem}

  .buy-side{background:#fff;border:1px solid var(--line);border-radius:14px;padding:1.1rem;position:sticky;top:4.2rem}
  .side-price{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.35rem;margin-bottom:0.35rem}
  .side-ship{font-size:0.72rem;line-height:1.5;margin-bottom:0.85rem;color:rgba(20,15,12,.78)}
  .btn-row{display:flex;flex-direction:column;gap:0.5rem;margin-bottom:0.7rem}
  .pd-add-btn,.pd-buy-btn{width:100%;padding:0.9rem;border:none;border-radius:999px;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:0.9rem;cursor:pointer}
  .pd-add-btn{background:var(--gold);color:var(--ink)}.pd-add-btn:hover{background:var(--gold-dark)}
  .pd-buy-btn{background:var(--ink);color:#fff}.pd-buy-btn:hover{opacity:.92}
  .wish-btn{width:100%;padding:0.65rem;border:1px solid var(--line);border-radius:999px;background:#fff;font-size:0.75rem;cursor:pointer;margin-bottom:0.7rem}
  .wish-btn:hover{background:#FAF7EE}
  .pd-add-confirm{display:none;font-size:0.75rem;color:var(--ok);background:rgba(26,156,92,.12);border-radius:8px;padding:0.5rem 0.7rem;margin-bottom:0.7rem}
  .pd-add-confirm.show{display:block}
  .seller-card{border-top:1px solid var(--line);padding-top:0.85rem;font-size:0.72rem;line-height:1.55}
  .seller-card strong{display:block;font-size:0.78rem;margin-bottom:0.25rem}
  .prime-pill{display:inline-block;background:#140F0C;color:var(--gold);font-size:0.62rem;font-weight:700;padding:0.15rem 0.4rem;border-radius:4px;letter-spacing:0.06em;margin-right:0.3rem}

  .detail-sections{max-width:1200px;margin:0 auto;padding:0 1.5rem 3.5rem}
  .fbt{background:#fff;border:1px solid var(--line);border-radius:14px;padding:1.2rem 1.3rem;margin-bottom:1.75rem}
  .fbt h2{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;margin:0 0 1rem}
  .fbt-row{display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap}
  .fbt-item{display:flex;align-items:center;gap:0.65rem;background:var(--cream);border-radius:10px;padding:0.55rem 0.7rem;max-width:260px}
  .fbt-thumb{width:48px;height:48px;border-radius:8px;background:#EDE6D6;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
  .fbt-thumb img{width:100%;height:100%;object-fit:cover}
  .fbt-thumb svg{width:55%;height:55%;stroke:rgba(20,15,12,.45)}
  .fbt-meta{font-size:0.68rem;line-height:1.35}.fbt-meta strong{display:block;font-size:0.72rem}
  .fbt-plus{font-size:1.2rem;font-weight:700;color:var(--muted)}
  .fbt-total{margin-left:auto;font-size:0.8rem}.fbt-total strong{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;display:block}
  .fbt-add{margin-top:0.45rem;padding:0.45rem 0.9rem;border:none;border-radius:8px;background:var(--ink);color:#fff;font-size:0.72rem;font-weight:700;cursor:pointer}

  .tabs{display:flex;border-bottom:1px solid var(--line);margin-bottom:1.3rem;overflow-x:auto}
  .tab{appearance:none;background:none;border:none;border-bottom:2px solid transparent;padding:0.75rem 1rem;font-size:0.76rem;font-weight:600;cursor:pointer;color:var(--muted);white-space:nowrap;font-family:'Space Grotesk',sans-serif}
  .tab.active{color:var(--ink);border-bottom-color:var(--gold-dark)}
  .tab-panel{display:none}.tab-panel.active{display:block;animation:fadeIn .25s ease}
  @keyframes fadeIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
  .pd-section-title{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.05rem;margin:0 0 0.75rem}
  .pd-desc{font-size:0.85rem;line-height:1.7;max-width:72ch;margin:0 0 1rem;color:rgba(20,15,12,.82)}
  .pd-bullets{margin:0;padding-left:1.2rem;font-size:0.85rem;line-height:1.9}
  .feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin:1.2rem 0}
  .feature-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:0.9rem;font-size:0.75rem;line-height:1.45}
  .feature-card strong{display:block;font-family:'Space Grotesk',sans-serif;font-size:0.82rem;margin-bottom:0.25rem}
  .spec-table{width:100%;max-width:680px;border-collapse:collapse;font-size:0.8rem}
  .spec-table tr{border-bottom:1px solid var(--line)}
  .spec-table th{text-align:left;width:36%;padding:0.7rem 0.8rem 0.7rem 0;color:var(--muted);font-weight:500}
  .spec-table td{padding:0.7rem 0;font-weight:600}
  .box-list{margin:0;padding-left:1.2rem;font-size:0.85rem;line-height:1.9}

  .reviews-layout{display:grid;grid-template-columns:220px 1fr;gap:2rem}
  .avg-big{font-family:'Space Grotesk',sans-serif;font-size:2.4rem;font-weight:700;line-height:1}
  .avg-stars{color:var(--gold-dark);margin:0.3rem 0}.avg-count{font-size:0.72rem;color:var(--muted);margin-bottom:1rem}
  .bar-row{display:flex;align-items:center;gap:0.45rem;font-size:0.68rem;margin-bottom:0.3rem}
  .bar-track{flex:1;height:8px;background:#E8E2D4;border-radius:99px;overflow:hidden}
  .bar-fill{height:100%;background:var(--gold-dark)}
  .review-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:1rem;margin-bottom:0.7rem}
  .review-top{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:0.3rem}
  .review-name{font-weight:700;font-size:0.8rem}.review-date{font-size:0.66rem;color:var(--muted)}
  .review-stars{color:var(--gold-dark);margin-bottom:0.2rem}
  .review-title{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:0.88rem;margin-bottom:0.3rem}
  .review-body{font-size:0.8rem;line-height:1.6;color:rgba(20,15,12,.8)}
  .verified{font-size:0.64rem;color:var(--ok);font-weight:600;margin-left:0.35rem}
  .helpful{font-size:0.68rem;color:var(--muted);margin-top:0.5rem}

  .related-head{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.15rem;margin:2.2rem 0 1rem;padding-top:1.4rem;border-top:1px solid var(--line)}
  .related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
  .rel-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;transition:border-color .2s,transform .2s}
  .rel-card:hover{border-color:var(--gold-dark);transform:translateY(-2px)}
  .rel-thumb{aspect-ratio:1;background:#F1ECDD;display:flex;align-items:center;justify-content:center}
  .rel-thumb img{width:100%;height:100%;object-fit:cover}.rel-thumb svg{width:42%;height:42%;stroke:rgba(20,15,12,.45)}
  .rel-body{padding:0.75rem}.rel-name{font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:0.76rem;line-height:1.3;margin-bottom:0.35rem}
  .rel-price{font-weight:700;font-size:0.85rem}.rel-stars{color:var(--gold-dark);font-size:0.7rem;margin-bottom:0.25rem}

  .compare{overflow-x:auto;margin:1rem 0 2rem}
  .compare table{width:100%;border-collapse:collapse;font-size:0.75rem;background:#fff;border-radius:12px;overflow:hidden;border:1px solid var(--line)}
  .compare th,.compare td{padding:0.75rem;border-bottom:1px solid var(--line);text-align:left}
  .compare th{background:#F7F2E6;font-family:'Space Grotesk',sans-serif}

  .site-footer{background:var(--ink);color:rgba(255,255,255,.65);font-size:0.72rem;padding:1.4rem;text-align:center}
  .site-footer a{color:var(--gold)}

  @media(max-width:1100px){.product-layout{grid-template-columns:1fr 1fr}.buy-side{grid-column:1/-1;position:static}}
  @media(max-width:760px){
    .product-layout{grid-template-columns:1fr}.gallery{position:static}
    .reviews-layout,.feature-grid,.related-grid{grid-template-columns:1fr 1fr}
    .fbt-total{margin-left:0}
  }
  @media(max-width:520px){
    .thumbs{flex-direction:row;width:auto}.gallery{flex-direction:column-reverse}
    .related-grid,.feature-grid{grid-template-columns:1fr}
  }
</style>
</head>
<body>

<?php if ($product['coupon'] || $savePct): ?>
<div class="deal-bar">
  <?php if ($savePct): ?>Limited-time deal — save <?= $savePct ?>%<?php endif; ?>
  <?php if ($product['coupon']): ?><?= $savePct ? ' · ' : '' ?>Clip coupon: save an extra $<?= (int) $product['coupon'] ?><?php endif; ?>
  · Free returns · Ships from LUNAX
</div>
<?php endif; ?>

<div class="lnx-topbar">
  <a class="lnx-logo" href="PHP/home.php">LUNAX</a>
  <nav class="lnx-nav">
    <a href="PHP/home.php">&larr; Continue shopping</a>
    <a class="lnx-cart-link" href="PHP/cart.php">Cart (<span id="navCartCount"><?= (int) $cartCount ?></span>)</a>
  </nav>
</div>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="PHP/home.php">Shop</a> &rsaquo;
  <a href="PHP/home.php"><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></a> &rsaquo;
  <span><?= htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8') ?></span>
</nav>

<div class="product-layout"
     id="productRoot"
     data-index="<?= (int) $product['index'] ?>"
     data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
     data-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>"
     data-image="<?= htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
     data-icon="<?= htmlspecialchars($product['icon'], ENT_QUOTES, 'UTF-8') ?>">

  <div class="gallery">
    <div class="thumbs" role="tablist">
      <?php foreach ($galleryShifts as $gi => $shift): ?>
      <button type="button" class="thumb<?= $gi === 0 ? ' active' : '' ?>" data-g="<?= $gi ?>" aria-label="Image <?= $gi + 1 ?>">
        <?php if (!empty($product['image'])): ?>
          <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="filter:hue-rotate(<?= $shift * 14 ?>deg) saturate(<?= 1 - $shift * 0.06 ?>)">
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" style="transform:rotate(<?= $shift * 5 ?>deg)"><?= $product['icon'] ?></svg>
        <?php endif; ?>
      </button>
      <?php endforeach; ?>
</div>
    <div class="main-shot" id="mainShot">
    <?php if (!empty($product['badge'])): ?>
        <span class="pd-badge <?= htmlspecialchars($product['badge'], ENT_QUOTES, 'UTF-8') ?>"><?= $product['badge'] === 'new' ? 'New' : 'Sale' ?></span>
      <?php endif; ?>
      <?php if (!empty($product['image'])): ?>
        <img id="mainImg" src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
      <?php else: ?>
        <svg id="mainSvg" viewBox="0 0 24 24" fill="none" stroke-width="1.5"><?= $product['icon'] ?></svg>
    <?php endif; ?>
      <span class="zoom-hint">Hover to preview</span>
    </div>
  </div>

  <div class="buy-mid">
    <div class="pd-brand">Brand: <a href="PHP/home.php"><?= htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8') ?></a> · Visit the LUNAX Store</div>
    <h1 class="pd-name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="pd-rating">
      <span class="stars"><?= lnx_stars_html($product['rating']) ?></span>
      <span><?= number_format($product['rating'], 1) ?></span>
      <a href="#reviews"><?= number_format($product['reviews']) ?> ratings</a>
      <span style="color:var(--line)">|</span>
      <a href="#qa">+<?= 12 + ($product['index'] % 30) ?> answered questions</a>
    </div>
    <div class="sold-line"><?= number_format($product['sold']) ?>+ bought in past month</div>

    <div class="pd-divider"></div>

    <div class="pd-price-row">
      <span class="pd-price"><?= lnx_money($product['price']) ?></span>
      <?php if (!empty($product['was'])): ?>
        <span class="pd-was"><?= lnx_money($product['was']) ?></span>
        <?php if ($savePct): ?><span class="pd-save">−<?= $savePct ?>%</span><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="pd-install">or <strong><?= lnx_money($payMonthly) ?>/mo</strong> for 6 months · 0% APR with Lunax Pay</div>
    <?php if ($product['coupon']): ?>
      <div class="coupon">Save $<?= (int) $product['coupon'] ?> with coupon · Apply at checkout</div>
    <?php endif; ?>

    <?php if ($product['stock'] <= 5): ?>
      <div class="pd-stock low">Only <?= (int) $product['stock'] ?> left in stock — order soon</div>
    <?php else: ?>
      <div class="pd-stock in">In Stock</div>
    <?php endif; ?>
    <div class="pd-ship">
      <?php if ($product['prime']): ?><span class="prime-pill">LUNAX+</span><?php endif; ?>
      <strong><?= htmlspecialchars($product['ship'], ENT_QUOTES, 'UTF-8') ?></strong>
      · Arrives <strong><?= htmlspecialchars($product['arrive'], ENT_QUOTES, 'UTF-8') ?></strong><br>
      Free returns within 30 days · Order within 4 hrs for same-day dispatch
    </div>
    <div class="pd-sku">SKU <?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?> · ASIN-style ID <?= strtoupper(substr(md5($product['id']), 0, 10)) ?></div>

    <span class="pd-label">Color: <span id="colorLabel"><?= htmlspecialchars($product['colors'][0], ENT_QUOTES, 'UTF-8') ?></span></span>
    <div class="color-swatches">
      <?php foreach ($product['colors'] as $ci => $color): ?>
        <button type="button" class="swatch<?= $ci === 0 ? ' active' : '' ?>" data-color="<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?></button>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($product['variants'])): ?>
    <span class="pd-label">Options</span>
    <div class="variant-row">
      <?php foreach ($product['variants'] as $vi => $v): ?>
        <button type="button" class="variant-btn<?= $vi === 0 ? ' active' : '' ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <span class="pd-label">Quantity</span>
    <div class="pd-qty-row">
      <div class="qty-control">
        <button type="button" class="qty-btn qty-minus" aria-label="Decrease">&minus;</button>
        <span class="qty-value" id="pdQtyValue">1</span>
        <button type="button" class="qty-btn qty-plus" aria-label="Increase">+</button>
      </div>
    </div>

    <div class="feature-grid">
      <?php foreach (array_slice($product['bullets'], 0, 3) as $b): ?>
      <div class="feature-card"><strong>Highlight</strong><?= htmlspecialchars($b, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endforeach; ?>
    </div>
  </div>

  <aside class="buy-side">
    <div class="side-price"><?= lnx_money($product['price']) ?></div>
    <div class="side-ship">
      <?= $product['prime'] ? '<span class="prime-pill">LUNAX+</span> ' : '' ?>
      <?= htmlspecialchars($product['ship'], ENT_QUOTES, 'UTF-8') ?><br>
      Get it by <strong><?= htmlspecialchars($product['arrive'], ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
    <div class="btn-row">
    <button type="button" class="pd-add-btn" id="pdAddBtn">Add to Cart</button>
      <button type="button" class="pd-buy-btn" id="pdBuyBtn">Buy Now</button>
    </div>
    <button type="button" class="wish-btn" id="wishBtn">♡ Add to Wish List</button>
    <div class="pd-add-confirm" id="pdAddConfirm">Added to your cart.</div>
    <div class="seller-card">
      <strong>Sold by <?= htmlspecialchars($product['seller'], ENT_QUOTES, 'UTF-8') ?></strong>
      Ships from LUNAX warehouse<br>
      Returns: Eligible · 30 days<br>
      Payment: Cards · Google Pay · Lunax Pay<br>
      <a href="PHP/customer-service.php" style="color:var(--link)">Report an issue with this product</a>
    </div>
  </aside>
</div>

<div class="detail-sections">
  <div class="fbt">
    <h2>Frequently bought together</h2>
    <div class="fbt-row">
      <div class="fbt-item">
        <div class="fbt-thumb">
          <?php if (!empty($product['image'])): ?><img src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
          <?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><?= $product['icon'] ?></svg><?php endif; ?>
        </div>
        <div class="fbt-meta"><strong>This item</strong><?= lnx_money($product['price']) ?></div>
      </div>
      <?php foreach ($fbt as $f): ?>
      <span class="fbt-plus">+</span>
      <a class="fbt-item" href="PHP/product.php?id=<?= htmlspecialchars($f['id'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="fbt-thumb">
          <?php if (!empty($f['image'])): ?><img src="<?= htmlspecialchars($f['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
          <?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><?= $f['icon'] ?></svg><?php endif; ?>
        </div>
        <div class="fbt-meta"><strong><?= htmlspecialchars(mb_strimwidth($f['name'], 0, 42, '…'), ENT_QUOTES, 'UTF-8') ?></strong><?= lnx_money($f['price']) ?></div>
      </a>
      <?php endforeach; ?>
      <div class="fbt-total">
        Total: <strong><?= lnx_money($fbtTotal) ?></strong>
        <button type="button" class="fbt-add" id="fbtAdd">Add all 3 to Cart</button>
      </div>
    </div>
  </div>

  <div class="tabs" role="tablist">
    <button type="button" class="tab active" data-tab="overview">Overview</button>
    <button type="button" class="tab" data-tab="specs">Specifications</button>
    <button type="button" class="tab" data-tab="box">What's in the box</button>
    <button type="button" class="tab" data-tab="compare">Compare</button>
    <button type="button" class="tab" data-tab="reviews">Customer reviews</button>
    <button type="button" class="tab" data-tab="qa" id="qa">Q&amp;A</button>
  </div>

  <div class="tab-panel active" id="panel-overview">
    <h2 class="pd-section-title">About this item</h2>
      <p class="pd-desc"><?= htmlspecialchars($product['desc'], ENT_QUOTES, 'UTF-8') ?></p>
      <ul class="pd-bullets">
      <?php foreach ($product['bullets'] as $b): ?><li><?= htmlspecialchars($b, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
    </ul>
  </div>

  <div class="tab-panel" id="panel-specs">
    <h2 class="pd-section-title">Technical details</h2>
    <table class="spec-table">
      <?php foreach ($product['specs'] as $k => $v): ?>
      <tr><th><?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?></th><td><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></td></tr>
      <?php endforeach; ?>
      <tr><th>SKU</th><td><?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?></td></tr>
    </table>
  </div>

  <div class="tab-panel" id="panel-box">
    <h2 class="pd-section-title">What's in the box</h2>
    <ul class="box-list">
      <?php foreach ($product['box'] as $item): ?><li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
    </ul>
  </div>

  <div class="tab-panel" id="panel-compare">
    <h2 class="pd-section-title">Compare with similar items</h2>
    <div class="compare">
      <table>
        <thead>
          <tr>
            <th></th>
            <th>This item</th>
            <?php foreach (array_slice($related, 0, 2) as $c): ?>
            <th><a href="PHP/product.php?id=<?= htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(mb_strimwidth($c['name'], 0, 36, '…'), ENT_QUOTES, 'UTF-8') ?></a></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <tr><th>Price</th><td><?= lnx_money($product['price']) ?></td><?php foreach (array_slice($related, 0, 2) as $c): ?><td><?= lnx_money($c['price']) ?></td><?php endforeach; ?></tr>
          <tr><th>Rating</th><td><?= number_format($product['rating'], 1) ?> ★</td><?php foreach (array_slice($related, 0, 2) as $c): ?><td><?= number_format($c['rating'], 1) ?> ★</td><?php endforeach; ?></tr>
          <tr><th>Category</th><td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></td><?php foreach (array_slice($related, 0, 2) as $c): ?><td><?= htmlspecialchars($c['category'], ENT_QUOTES, 'UTF-8') ?></td><?php endforeach; ?></tr>
          <tr><th>Brand</th><td><?= htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8') ?></td><?php foreach (array_slice($related, 0, 2) as $c): ?><td><?= htmlspecialchars($c['brand'], ENT_QUOTES, 'UTF-8') ?></td><?php endforeach; ?></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-panel" id="panel-reviews">
    <div id="reviews" class="reviews-layout">
      <div>
        <div class="avg-big"><?= number_format($product['rating'], 1) ?></div>
        <div class="avg-stars"><?= lnx_stars_html($product['rating']) ?></div>
        <div class="avg-count"><?= number_format($product['reviews']) ?> global ratings</div>
        <?php foreach ($ratingDist as $star => $pct): ?>
        <div class="bar-row"><span><?= $star ?>★</span><div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div><span><?= $pct ?>%</span></div>
        <?php endforeach; ?>
      </div>
      <div>
        <?php foreach ($productReviews as $rev): ?>
        <article class="review-card">
          <div class="review-top">
            <span class="review-name"><?= htmlspecialchars($rev['name'], ENT_QUOTES, 'UTF-8') ?><?php if ($rev['verified']): ?><span class="verified">Verified purchase</span><?php endif; ?></span>
            <span class="review-date"><?= htmlspecialchars($rev['date'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="review-stars"><?= lnx_stars_html($rev['stars']) ?></div>
          <div class="review-title"><?= htmlspecialchars($rev['title'], ENT_QUOTES, 'UTF-8') ?></div>
          <p class="review-body"><?= htmlspecialchars($rev['body'], ENT_QUOTES, 'UTF-8') ?></p>
          <div class="helpful">Helpful (<?= (int) $rev['helpful'] ?>)</div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="tab-panel" id="panel-qa">
    <h2 class="pd-section-title">Customer questions &amp; answers</h2>
    <article class="review-card">
      <div class="review-title">Q: Is this covered by warranty?</div>
      <p class="review-body"><strong>A:</strong> Yes — 12-month limited warranty from LUNAX Official. Extend at checkout if needed.</p>
    </article>
    <article class="review-card">
      <div class="review-title">Q: How fast does it ship?</div>
      <p class="review-body"><strong>A:</strong> Most orders leave within 24 hours. Estimated arrival: <strong><?= htmlspecialchars($product['arrive'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </article>
    <article class="review-card">
      <div class="review-title">Q: Can I return it?</div>
      <p class="review-body"><strong>A:</strong> Yes — 30-day returns on unused items in original packaging via your account.</p>
    </article>
    <article class="review-card">
      <div class="review-title">Q: Does the color match the photos?</div>
      <p class="review-body"><strong>A:</strong> Colors are studio-accurate under daylight. Slight variance can occur by screen calibration.</p>
    </article>
  </div>

  <h2 class="related-head">Customers also viewed</h2>
  <div class="related-grid">
    <?php foreach ($related as $rel): ?>
    <a class="rel-card" href="PHP/product.php?id=<?= htmlspecialchars($rel['id'], ENT_QUOTES, 'UTF-8') ?>">
      <div class="rel-thumb">
        <?php if (!empty($rel['image'])): ?><img src="<?= htmlspecialchars($rel['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
        <?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><?= $rel['icon'] ?></svg><?php endif; ?>
      </div>
      <div class="rel-body">
        <div class="rel-name"><?= htmlspecialchars($rel['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="rel-stars"><?= lnx_stars_html($rel['rating']) ?></div>
        <div class="rel-price"><?= lnx_money($rel['price']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<footer class="site-footer">
  © 2026 LUNAX · <a href="PHP/info-center.php#returns">Returns</a> · <a href="PHP/info-center.php#shipping">Shipping</a> · <a href="PHP/customer-service.php">Support</a>
</footer>

<script>
(function(){
  var root = document.getElementById('productRoot');
  var qtyEl = document.getElementById('pdQtyValue');
  var confirmEl = document.getElementById('pdAddConfirm');
  var navCount = document.getElementById('navCartCount');
  var colorLabel = document.getElementById('colorLabel');
  var fbtPayload = <?= json_encode(array_map(function ($f) {
      return ['index' => $f['index'], 'name' => $f['name'], 'price' => $f['price'], 'image' => $f['image'] ?? '', 'icon' => $f['icon']];
  }, array_merge([$product], $fbt)), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  document.querySelector('.qty-minus').onclick = function(){ qtyEl.textContent = Math.max(1, +qtyEl.textContent - 1); };
  document.querySelector('.qty-plus').onclick = function(){ qtyEl.textContent = +qtyEl.textContent + 1; };

  document.querySelectorAll('.swatch').forEach(function(btn){
    btn.onclick = function(){
      document.querySelectorAll('.swatch').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      colorLabel.textContent = btn.dataset.color;
    };
  });
  document.querySelectorAll('.variant-btn').forEach(function(btn){
    btn.onclick = function(){
      document.querySelectorAll('.variant-btn').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
    };
  });
  document.querySelectorAll('.thumb').forEach(function(btn){
    btn.onclick = function(){
      document.querySelectorAll('.thumb').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var mainImg = document.getElementById('mainImg');
      var mainSvg = document.getElementById('mainSvg');
      var thumbImg = btn.querySelector('img');
      var thumbSvg = btn.querySelector('svg');
      if (mainImg && thumbImg) { mainImg.src = thumbImg.src; mainImg.style.filter = thumbImg.style.filter || ''; }
      if (mainSvg && thumbSvg) mainSvg.style.transform = thumbSvg.style.transform || '';
    };
  });
  document.querySelectorAll('.tab').forEach(function(tab){
    tab.onclick = function(){
      document.querySelectorAll('.tab').forEach(function(t){ t.classList.remove('active'); });
      document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
      tab.classList.add('active');
      document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
    };
  });
  if (location.hash === '#reviews' || location.hash === '#qa') {
    var t = document.querySelector('.tab[data-tab="' + location.hash.slice(1) + '"]');
    if (t) t.click();
  }

  function postAdd(item, qty){
    var body = new URLSearchParams({
      action:'add', index:String(item.index), name:item.name, price:String(item.price),
      qty:String(qty||1), image:item.image||'', icon:item.icon||''
    });
    return fetch('PHP/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:body.toString() })
      .then(function(r){ return r.json(); });
  }

  function addCurrent(goCheckout){
    postAdd({
      index: root.dataset.index, name: root.dataset.name, price: root.dataset.price,
      image: root.dataset.image, icon: root.dataset.icon
    }, parseInt(qtyEl.textContent,10)||1).then(function(data){
      if (!data.ok) throw new Error('fail');
      if (navCount) navCount.textContent = data.itemCount;
      if (goCheckout) { location.href = 'PHP/checkout.php'; return; }
    confirmEl.classList.add('show');
    setTimeout(function(){ confirmEl.classList.remove('show'); }, 2500);
    }).catch(function(){ alert('Could not add to cart.'); });
  }

  document.getElementById('pdAddBtn').onclick = function(){ addCurrent(false); };
  document.getElementById('pdBuyBtn').onclick = function(){ addCurrent(true); };
  document.getElementById('wishBtn').onclick = function(){
    this.textContent = '✓ Saved to Wish List';
    this.disabled = true;
  };
  document.getElementById('fbtAdd').onclick = function(){
    var btn = this;
    btn.disabled = true;
    var chain = Promise.resolve();
    fbtPayload.forEach(function(item){ chain = chain.then(function(){ return postAdd(item, 1); }); });
    chain.then(function(last){
      if (last && last.itemCount && navCount) navCount.textContent = last.itemCount;
      btn.textContent = 'Added ✓';
      confirmEl.classList.add('show');
    }).catch(function(){ btn.disabled = false; alert('Could not add bundle.'); });
  };
})();
</script>
</body>
</html>
