<?php
session_start();
$cartItemCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); 
?>
<?php require __DIR__ . '/lunax-data.php'; $LUNAX = lunax_load_all(); ?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Online Electrics | LUNAX</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../CSS/layout.css">
<link rel="stylesheet" href="../CSS/components.css">
<link rel="stylesheet" href="../CSS/panels.css">
</head>
<body>

<!-- Left Category Sidebar: hover to reveal, peeks a sliver when idle -->
<div class="cat-sidebar-backdrop" id="catSidebarBackdrop"></div>
<aside class="cat-sidebar" id="catSidebar" aria-hidden="true">
  <div class="cat-sidebar-glow" aria-hidden="true"></div>
  <div class="cat-sidebar-glow cat-sidebar-glow-2" aria-hidden="true"></div>
  <div class="cat-sidebar-star-layer-1" aria-hidden="true"></div>
  <div class="cat-sidebar-star-layer-2" aria-hidden="true"></div>

  <button class="cat-sidebar-peek" id="catSidebarPeek" aria-expanded="false" aria-controls="catSidebar">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
    <span>Markets</span>
  </button>

  <div class="cat-sidebar-inner">
<div class="cat-sidebar-head">
  <h3>Market Intelligence</h3>
  <button class="cat-sidebar-close" id="catSidebarClose" aria-label="Close categories" style="display:none;">
      <img src="../image/l.png">
  </button>
</div>


    <!-- Refresh live data, then a live mini-ticker -->
    <div class="cat-ai-bar">
      <button class="cat-refresh" id="catRefreshBtn" type="button" aria-label="Refresh live data" title="Refresh live data">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 11a8 8 0 1 0-.5 4"/><path d="M20 4v6h-6"/></svg>
      </button>
    </div>
    <div class="cat-ticker" id="catTicker" aria-hidden="true"></div>
    <nav class="cat-sidebar-list">
      <a href="#" data-center-open="world"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>WORLD</a>
      <a href="#" data-center-open="news"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>NEWS</a>
      <div class="center-panel news-panel" data-center-panel="news" role="dialog" aria-modal="true" aria-label="Lunax news" aria-hidden="true">
        <button class="center-panel-close" type="button" aria-label="Close panel">✕</button>
          <div class="center-panel-glow" aria-hidden="true"></div>
          <div class="center-panel-glow center-panel-glow-2" aria-hidden="true"></div>
          <div class="center-panel-star-layer-1" aria-hidden="true"></div>
          <div class="center-panel-star-layer-2" aria-hidden="true"></div>
          <div class="center-panel-inner">
               <img class="ainews-mask-logo" src="/image/l.png">
            <div class="ainews-scroll-wrap">
            <div class="ainews-scroll">
              <?php foreach ($LUNAX['news'] as $n): $nlive = ($LUNAX['sources']['news'] === 'live'); ?>
              <div class="ainews-card">
                <div class="ainews-top">
                  <div class="ainews-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.8"><path d="M4 5h13v14H6a2 2 0 0 1-2-2z"/><path d="M17 8h3v9a2 2 0 0 1-2 2"/><path d="M7 8h7M7 11h7M7 14h5"/></svg>
                  </div>
                  <div class="ainews-id">
                    <span class="ainews-name"><?= htmlspecialchars($n['source'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <button class="ainews-chevron" aria-label="More">⌄</button>
                </div>
                <div class="ainews-body"><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($n['summary'])): ?> <span class="ainews-summary">— <?= htmlspecialchars($n['summary'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></div>
                <div class="ainews-meta"><span class="ainews-live-dot<?= $nlive ? '' : ' is-sim' ?>" aria-hidden="true"></span><?= $nlive ? 'Live' : 'Simulated' ?> · <?= htmlspecialchars(!empty($n['ago']) ? $n['ago'] : 'moments ago', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="ainews-divider"></div>
<?php if (!empty($n['url'])): ?>
                <a href="<?= htmlspecialchars($n['url'], ENT_QUOTES, 'UTF-8') ?>" class="ainews-cta" target="_blank" rel="noopener noreferrer">Read the full story →</a>
<?php else: ?>
                <a href="#" class="ainews-cta">Read more →</a>
<?php endif; ?>
              </div>
<?php endforeach; ?>
            </div>
            </div>
          </div>
        </div>
      <a href="#" data-center-open="crypto"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9 9h.01M15 15h.01M15 9l-6 6"/></svg>CRYPTO</a>
      <div class="center-panel news-panel" data-center-panel="crypto" role="dialog" aria-modal="true" aria-label="Lunax crypto" aria-hidden="true">
        <button class="center-panel-close" type="button" aria-label="Close panel">✕</button>
        <div class="center-panel-glow" aria-hidden="true"></div>
        <div class="center-panel-glow center-panel-glow-2" aria-hidden="true"></div>
        <div class="center-panel-star-layer-1" aria-hidden="true"></div>
        <div class="center-panel-star-layer-2" aria-hidden="true"></div>
        <div class="center-panel-inner">
          <div class="center-panel-head">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9 9h.01M15 15h.01M15 9l-6 6"/></svg>
            Crypto
          </div>
          <div class="market-list" id="mlist-crypto">
            <div class="market-row">
              <span class="market-name">Bitcoin <span class="market-ticker">BTC</span></span>
              <span class="market-price">$64,210.50</span>
              <span class="market-change up">+2.14%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Ethereum <span class="market-ticker">ETH</span></span>
              <span class="market-price">$3,142.80</span>
              <span class="market-change up">+1.08%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Solana <span class="market-ticker">SOL</span></span>
              <span class="market-price">$146.32</span>
              <span class="market-change down">-0.62%</span>
            </div>
            <div class="market-row">
              <span class="market-name">XRP <span class="market-ticker">XRP</span></span>
              <span class="market-price">$0.612</span>
              <span class="market-change down">-1.35%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Dogecoin <span class="market-ticker">DOGE</span></span>
              <span class="market-price">$0.148</span>
              <span class="market-change up">+4.27%</span>
            </div>
          </div>
          <div class="ainews-meta market-updated"><span class="ainews-live-dot" aria-hidden="true"></span>Live · Auto-refreshed every few minutes</div>
        </div>
      </div>
      <a href="#" data-center-open="metals"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M9 9.5c0-1.4 1.3-2.5 3-2.5s3 1.1 3 2.5-1.3 2-3 2.5-3 1.1-3 2.5 1.3 2.5 3 2.5 3-1.1 3-2.5"/></svg>GOLD &amp; SILVER</a>
      <div class="center-panel news-panel" data-center-panel="metals" role="dialog" aria-modal="true" aria-label="Lunax gold and silver" aria-hidden="true">
        <button class="center-panel-close" type="button" aria-label="Close panel">✕</button>
        <div class="center-panel-glow" aria-hidden="true"></div>
        <div class="center-panel-glow center-panel-glow-2" aria-hidden="true"></div>
        <div class="center-panel-star-layer-1" aria-hidden="true"></div>
        <div class="center-panel-star-layer-2" aria-hidden="true"></div>
        <div class="center-panel-inner">
          <div class="center-panel-head">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M9 9.5c0-1.4 1.3-2.5 3-2.5s3 1.1 3 2.5-1.3 2-3 2.5-3 1.1-3 2.5 1.3 2.5 3 2.5 3-1.1 3-2.5"/></svg>
            Gold &amp; Silver
          </div>
          <div class="market-list" id="mlist-metals">
            <div class="market-row">
              <span class="market-name">Gold <span class="market-ticker">XAU/oz</span></span>
              <span class="market-price">$2,384.20</span>
              <span class="market-change up">+0.42%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Silver <span class="market-ticker">XAG/oz</span></span>
              <span class="market-price">$28.61</span>
              <span class="market-change up">+0.85%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Platinum <span class="market-ticker">XPT/oz</span></span>
              <span class="market-price">$968.40</span>
              <span class="market-change down">-0.19%</span>
            </div>
            <div class="market-row">
              <span class="market-name">Palladium <span class="market-ticker">XPD/oz</span></span>
              <span class="market-price">$1,012.75</span>
              <span class="market-change down">-1.02%</span>
            </div>
          </div>
          <div class="ainews-meta market-updated"><span class="ainews-live-dot" aria-hidden="true"></span>Live · Auto-refreshed every few minutes</div>
        </div> 
      </div>
      <a href="#" data-center-open="currency"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/></svg>CURRENCY</a>
      <div class="center-panel news-panel" data-center-panel="currency" role="dialog" aria-modal="true" aria-label="Lunax currency" aria-hidden="true">
        <button class="center-panel-close" type="button" aria-label="Close panel">✕</button>
        <div class="center-panel-glow" aria-hidden="true"></div>
        <div class="center-panel-glow center-panel-glow-2" aria-hidden="true"></div>
        <div class="center-panel-star-layer-1" aria-hidden="true"></div>
        <div class="center-panel-star-layer-2" aria-hidden="true"></div>
        <div class="center-panel-inner">
          <div class="center-panel-head">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/></svg>
            Currency
          </div>
          <div class="market-list" id="mlist-currency">
            <div class="market-row">
              <span class="market-name">EUR / USD</span>
              <span class="market-price">1.0842</span>
              <span class="market-change up">+0.12%</span>
            </div>
            <div class="market-row">
              <span class="market-name">GBP / USD</span>
              <span class="market-price">1.2716</span>
              <span class="market-change down">-0.08%</span>
            </div>
            <div class="market-row">
              <span class="market-name">USD / JPY</span>
              <span class="market-price">151.64</span>
              <span class="market-change up">+0.31%</span>
            </div>
            <div class="market-row">
              <span class="market-name">USD / CNY</span>
              <span class="market-price">7.238</span>
              <span class="market-change down">-0.04%</span>
            </div>
            <div class="market-row">
              <span class="market-name">AUD / USD</span>
              <span class="market-price">0.6591</span>
              <span class="market-change up">+0.19%</span>
            </div>
          </div>
          <div class="ainews-meta market-updated"><span class="ainews-live-dot" aria-hidden="true"></span>Live · Auto-refreshed every few minutes</div>
        </div>
      </div>


      <!-- WORLD: interactive live-data world + dashboard (filled by home.js) -->
      <div class="center-panel world-panel" data-center-panel="world" role="dialog" aria-modal="true" aria-label="Lunax live data world" aria-hidden="true">
        <button class="center-panel-close" type="button" aria-label="Close panel">✕</button>
        <div class="center-panel-glow" aria-hidden="true"></div>
        <div class="center-panel-glow center-panel-glow-2" aria-hidden="true"></div>
        <div class="center-panel-star-layer-1" aria-hidden="true"></div>
        <div class="center-panel-star-layer-2" aria-hidden="true"></div>
        <div class="center-panel-inner">
          <div class="center-panel-head">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
            Live Data World
          </div>
          <div class="world-panel-body" id="worldPanelBody">
            <div class="world-loading">Loading live markets…</div>
          </div>
        </div>
      </div>
    </nav>
    <div class="cat-sidebar-foot">
      <a href="/PHP/info-center.php#about">About Lunax</a>
      <a href="/PHP/info-center.php#careers">Careers</a>
      <a href="/PHP/info-center.php#journal">Lunax Journal</a>
    </div>
  </div>
</aside>


<!-- Ticker -->
  <div class="ticker-bar">
    <div class="ticker-track">
      <?php foreach ($LUNAX['news'] as $n): ?>
        <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg><?= strtoupper(htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8')) ?></span>
      <?php endforeach; ?>
    </div>
  </div>



  <!-- Live market data (server-rendered by lunax-data.php): gold panel above the search box -->
  <section class="lunax-ticker-panel" aria-label="Live market data">
    <div class="ltp-head">
      <span class="ltp-title"><span class="ltp-dot"></span>Live Markets</span>
      <span class="ltp-meta">Updated <?= htmlspecialchars($LUNAX['updatedLabel'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="ltp-scroll">
<?php foreach ($LUNAX['order'] as $k): $a = $LUNAX['assets'][$k]; $up = ($a['change'] >= 0); ?>
      <button class="ltp-pill" type="button" data-asset="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($a['name'] . ' ' . lunax_price_of($a) . ' ' . lunax_pct($a['change']), ENT_QUOTES, 'UTF-8') ?>. Open details.">
        <span class="ltp-name"><?= htmlspecialchars(!empty($a['ticker']) ? $a['ticker'] : $a['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <span class="ltp-px"><?= htmlspecialchars(lunax_price_of($a), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="ltp-chg <?= $up ? 'up' : 'down' ?>"><?= htmlspecialchars(lunax_pct($a['change']), ENT_QUOTES, 'UTF-8') ?></span>
      </button>
<?php endforeach; ?>
    </div>
  </section>




<!-- Header -->
<header>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
    const countEl = document.querySelector('.wish-icon-count');
    if (countEl) countEl.textContent = <?= count($_SESSION['wishlist'] ?? []) ?>;
    });
  </script>
  <div class="nav-main">
    <a href="home.php" class="logo" id="siteLogo">
      <span class="logo-text">LUNAX</span>
      <img class="logo-mark" src="../image/l.png" alt="Lunax logo">
    </a>
<form class="lnx-search" action="search.php" method="get" role="search" autocomplete="off">
  <div class="lnx-sbox">
    <input type="search" name="q" placeholder="Search phones, laptops, audio, drones..." aria-label="Search products">
    <button type="submit" aria-label="Search">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    </button>
  </div>
</form>
    <div class="nav-icons">
      <div class="account-trigger">
        <a href="personal-centre.php" class="nav-icon" aria-label="Account">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>
        <a href="#" class="account-tooltip">personal center</a>
        <div class="account-panel">
          <a href="signin.php" class="account-panel-title">Sign In / Register</a>
          <div class="account-panel-divider"></div>
          <a href="personal-centre.php?active=all-orders" class="account-panel-link">My Orders</a>
          <a href="personal-centre.php?active=inbox" class="account-panel-link">My Message</a>
          <a href="wishlist.php" class="account-panel-link">My Wishlist</a>
          <a href="personal-centre.php?active=my-vouchers" class="account-panel-link">My Vouchers</a>
          <a href="personal-centre.php?active=my-points" class="account-panel-link">My Points</a>
          <a href="personal-centre.php?active=recently-viewed" class="account-panel-link">Recently Viewed</a>
        </div>
      </div>
      <div class="account-trigger">
<a href="wishlist.php" class="nav-icon nav-icon-count" aria-label="Wishlist">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 21s-7.5-4.65-9.5-8.7C1.2 9.77 2.76 5 7.08 5c2.1 0 3.47 1.2 4.92 3.1C13.45 6.2 14.82 5 16.92 5c4.32 0 5.88 4.77 4.58 7.3C19.5 16.35 12 21 12 21Z"/></svg>
  <span class="wish-icon-count icon-count"><?= count($_SESSION['wishlist'] ?? []) ?></span>
</a>
        <a href="wishlist.php" class="account-tooltip">wishlist</a>
      </div>
      <div class="account-trigger">
        <a href="cart.php" class="nav-icon nav-icon-count" aria-label="Cart">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 2H2"/><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
          <span class="icon-count"><?= (int) $cartItemCount ?></span>
        </a>
        <div class="cart-panel">
          <svg class="cart-panel-icon" viewBox="0 0 64 64" fill="none" stroke="var(--ink-dim)" stroke-width="1.6">
            <path d="M18 20h4l4 26h20l4-18H24"/>
            <circle cx="46" cy="19" r="1.4" fill="var(--ink-dim)" stroke="none"/>
            <circle cx="35" cy="10" r="1" fill="var(--ink-dim)" stroke="none"/>
            <path d="M40 12l3 3M43 12l-3 3" stroke-linecap="round"/>
            <line x1="14" y1="52" x2="52" y2="52" stroke-dasharray="2 3"/>
            <circle cx="24" cy="52" r="2.4"/>
            <circle cx="40" cy="52" r="2.4"/>
          </svg>
          <div class="cart-panel-title">Shopping cart is Empty</div>
          <div class="cart-panel-copy">Welcome back! If you had items in your shopping cart, we saved them for you. <a href="#">SIGN IN</a> now to see them, or whenever you're ready to check out.</div>
        </div>
      </div>
      <div class="account-trigger">
        <a href="customer-service.php" class="nav-icon" aria-label="Customer Service">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2z"/><path d="M20 13a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2z"/><path d="M18 17.5a4 4 0 0 1-4 3.5h-1.5"/><circle cx="11" cy="21" r="1.2"/></svg>
        </a>
        <div class="cs-panel">
          <div class="cs-panel-title">Customer Service</div>
          <div class="cs-panel-copy">What can we do for you?</div>
        </div>
      </div>
      <div class="account-trigger">
        <a href="#" class="nav-icon nav-icon-locale" aria-label="Currency / Language" data-lunax-locale-trigger="1">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/></svg>
          <span class="locale-badge" data-lunax-currency-badge>USD</span>
        </a>
        <div class="currency-panel">
          <div class="currency-panel-title" data-i18n="Currency &amp; Language">Currency &amp; Language</div>
          <div class="currency-panel-label" data-i18n="Currency">Currency</div>
          <div class="currency-select">
            <select data-lunax-currency aria-label="Select currency">
              <option value="USD">USD / $ — US Dollar</option>
              <option value="GBP">GBP / £ — British Pound</option>
              <option value="EUR">EUR / € — Euro</option>
              <option value="JPY">JPY / ¥ — Japanese Yen</option>
              <option value="CNY">CNY / ¥ — Chinese Yuan</option>
              <option value="AUD">AUD / $ — Australian Dollar</option>
              <option value="CAD">CAD / $ — Canadian Dollar</option>
              <option value="CHF">CHF / Fr — Swiss Franc</option>
              <option value="INR">INR / ₹ — Indian Rupee</option>
              <option value="AED">AED / د.إ — UAE Dirham</option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </div>
          <div class="currency-panel-label" data-i18n="Language">Language</div>
          <div class="language-select">
            <select data-lunax-language aria-label="Select language">
              <option value="en">English</option>
              <option value="es">Español</option>
              <option value="fr">Français</option>
              <option value="de">Deutsch</option>
              <option value="zh">中文</option>
              <option value="ja">日本語</option>
              <option value="ar">العربية</option>
              <option value="pt">Português</option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </div>
          <div class="locale-active-hint">Selected: <strong data-lunax-currency-badge>USD</strong> · <strong data-lunax-lang-badge>EN</strong></div>
          <a href="#" class="currency-panel-link" data-i18n="International Site">International Site</a>
        </div>
      </div>
    </div>
  </div>
  <nav class="nav-cats">
    <div class="nav-cats-track">
      <div class="cat-trigger nav-trigger-word">
        <a href="#" class="cat-trigger-link nav-trigger-link">Shop →</a>
        <div class="cat-mega-menu">
        <div class="cat-mega-menu-inner">
  <div class="cat-star-layer-1"></div>
  <div class="cat-star-layer-2"></div>
          <div class="cat-mega-glow" aria-hidden="true"></div>
          <div class="cat-mega-glow cat-mega-glow-2" aria-hidden="true"></div>
          <ul class="cat-mega-list">
            <li class="active" data-target="phones">Phones & Tablets</li>
            <li data-target="laptops">Laptops & PCs</li>
            <li data-target="audio">Audio</li>
            <li data-target="wearables">Wearables</li>
            <li data-target="gaming">Gaming</li>
            <li data-target="smarthome">Smart Home</li>
            <li data-target="cameras">Cameras</li>
            <li data-target="drones">Drones</li>
            <li data-target="components">Components</li>
            <li data-target="accessories">Accessories</li>
          </ul>
          <div class="cat-mega-detail">
            <div class="cat-detail-panel active" data-panel="phones">
              <h4>Phones & Tablets</h4>
              <div class="cat-detail-links">
                <a href="#">Smartphones</a>
                <a href="#">Tablets</a>
                <a href="#">Phone Cases</a>
                <a href="#">Screen Protectors</a>
                <a href="#">Fast Chargers</a>
                <a href="#">5G Devices</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="laptops">
              <h4>Laptops & PCs</h4>
              <div class="cat-detail-links">
                <a href="#">Ultrabooks</a>
                <a href="#">Gaming Laptops</a>
                <a href="#">Desktop PCs</a>
                <a href="#">Monitors</a>
                <a href="#">Docking Stations</a>
                <a href="#">Laptop Bags</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="audio">
              <h4>Audio</h4>
              <div class="cat-detail-links">
                <a href="#">Wireless Earbuds</a>
                <a href="#">Headphones</a>
                <a href="#">Bluetooth Speakers</a>
                <a href="#">Soundbars</a>
                <a href="#">Turntables</a>
                <a href="#">Microphones</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="wearables">
              <h4>Wearables</h4>
              <div class="cat-detail-links">
                <a href="#">Smartwatches</a>
                <a href="#">Fitness Bands</a>
                <a href="#">Watch Straps</a>
                <a href="#">Smart Rings</a>
                <a href="#">Kids Watches</a>
                <a href="#">Health Trackers</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="gaming">
              <h4>Gaming</h4>
              <div class="cat-detail-links">
                <a href="#">Mechanical Keyboards</a>
                <a href="#">Gaming Mice</a>
                <a href="#">Headsets</a>
                <a href="#">Controllers</a>
                <a href="#">Gaming Chairs</a>
                <a href="#">Consoles</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="smarthome">
              <h4>Smart Home</h4>
              <div class="cat-detail-links">
                <a href="#">Smart Speakers</a>
                <a href="#">Smart Plugs</a>
                <a href="#">Smart Bulbs</a>
                <a href="#">Video Doorbells</a>
                <a href="#">Robot Vacuums</a>
                <a href="#">Security Cameras</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="cameras">
              <h4>Cameras</h4>
              <div class="cat-detail-links">
                <a href="#">Action Cameras</a>
                <a href="#">Webcams</a>
                <a href="#">Dash Cams</a>
                <a href="#">Tripods</a>
                <a href="#">Memory Cards</a>
                <a href="#">Camera Bags</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="drones">
              <h4>Drones</h4>
              <div class="cat-detail-links">
                <a href="#">Camera Drones</a>
                <a href="#">Mini Drones</a>
                <a href="#">FPV Drones</a>
                <a href="#">Batteries</a>
                <a href="#">Propellers</a>
                <a href="#">Carry Cases</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="components">
              <h4>Components</h4>
              <div class="cat-detail-links">
                <a href="#">SSDs & Storage</a>
                <a href="#">Graphics Cards</a>
                <a href="#">RAM</a>
                <a href="#">Motherboards</a>
                <a href="#">Power Supplies</a>
                <a href="#">Cooling</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="accessories">
              <h4>Accessories</h4>
              <div class="cat-detail-links">
                <a href="#">Chargers & Cables</a>
                <a href="#">Cases & Sleeves</a>
                <a href="#">Power Banks</a>
                <a href="#">Cable Organizers</a>
                <a href="#">Screen Protectors</a>
                <a href="#">Mounts & Stands</a>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
      <div class="nav-trigger-word">
        <a href="#" class="nav-trigger-link" data-center-open="news">News</a>
      </div>
      <div class="nav-trigger-word">
        <a href="#" class="nav-trigger-link quick-filter-link" data-filter="bestsellers">Best Sellers</a>
      </div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-center-open="phones">Phones &amp; Tablets</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="laptops">Laptops &amp; PCs</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="audio">Audio</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="drones">Drones</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="gaming">Gaming</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="smarthome">Smart Home</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="cameras">Cameras</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="wearables">Wearables</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="tablets">Tablets</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="laptops2">Laptops</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="components">Components</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="accessories">Accessories</a></div>
    </div>
  </nav>
</header>


<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <defs>
    <linearGradient id="lnxWave" gradientUnits="userSpaceOnUse"
                    x1="0" y1="0" x2="24" y2="8" spreadMethod="repeat">
      <stop offset="0"   stop-color="#ffffff" stop-opacity=".35"/>
      <stop offset=".5"  stop-color="#ffffff" stop-opacity="1"/>
      <stop offset="1"   stop-color="#ffffff" stop-opacity=".35"/>
      <animateTransform attributeName="gradientTransform" type="translate"
                        from="0 0" to="24 8" dur="2.6s" repeatCount="indefinite"/>
    </linearGradient>
  </defs>
</svg>

<!-- Hero -->
<div class="hero-wrap">
  <div class="hero-grid">

    <!-- LEFT -->
    <div class="promo-stack hero-dark-zone">
      <a href="#" class="promo-card" data-center-open="world">
        <svg class="glyph" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <path d="M2 12h20"/>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <span>World</span>
      </a>
      <a href="#" class="promo-card" data-center-open="news">
        <svg class="glyph" viewBox="0 0 24 24">
          <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
          <path d="M18 14h-8M15 18h-5"/>
          <path d="M10 6h8v4h-8V6Z"/>
        </svg>
        <span>News</span>
      </a>
      <a href="#" class="promo-card" data-center-open="currency">
        <svg class="glyph" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
          <path d="M12 18V6"/>
        </svg>
        <span>Currency</span>
      </a>
    </div>

<!-- CENTER -->
<div class="hero-banner" id="lnxHero">
  <div class="lnx-slide active" style="background-image:url('../image/a.jpg')">
    <p class="lnx-cap">Gaming Week — gear from $29</p>
  </div>
  <div class="lnx-slide" style="background-image:url('../image/b.jpg')">
    <p class="lnx-cap">Signal Drop — up to 60% off</p>
  </div>
  <div class="lnx-slide" style="background-image:url('../image/c.jpg')">
    <p class="lnx-cap">Student Offer — 10% off with ID</p>
  </div>
  <div class="lnx-slide" style="background-image:url('../image/d.jpg')">
    <p class="lnx-cap">New arrivals every week</p>
  </div>
  <div class="lnx-dots">
    <button type="button" class="on" aria-label="Slide 1"></button>
    <button type="button" aria-label="Slide 2"></button>
    <button type="button" aria-label="Slide 3"></button>
    <button type="button" aria-label="Slide 4"></button>
  </div>
</div>

    <!-- RIGHT -->
    <div class="brand-stack hero-dark-zone">
      <a href="#" class="brand-card">APPLE</a>
      <a href="#" class="brand-card">SPACEX</a>
      <a href="#" class="brand-card">SAMSUNG</a>
    </div>

  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var box = document.getElementById('lnxHero');
  if (!box) return;
  var slides = box.querySelectorAll('.lnx-slide');
  var dots   = box.querySelectorAll('.lnx-dots button');
  var i = 0, timer = null;

  function go(n) {
    slides[i].classList.remove('active');
    dots[i].classList.remove('on');
    i = (n + slides.length) % slides.length;
    slides[i].classList.add('active');
    dots[i].classList.add('on');
  }
  function start() { stop(); timer = setInterval(function () { go(i + 1); }, 4000); }
  function stop()  { if (timer) clearInterval(timer); }

  dots.forEach(function (d, n) {
    d.addEventListener('click', function () { go(n); start(); });
  });
  box.addEventListener('mouseenter', stop);   // pause on hover
  box.addEventListener('mouseleave', start);
  start();                                    // auto skip every 4s
});
</script>
<script>
(function(){
  var box = document.getElementById('lnxHero');
  if(!box) return;
  var slides = box.querySelectorAll('.lnx-slide');
  var dots = box.querySelectorAll('.lnx-dots button');
  var i = 0, timer;
  function go(n){
    slides[i].classList.remove('active'); dots[i].classList.remove('on');
    i = (n + slides.length) % slides.length;
    slides[i].classList.add('active'); dots[i].classList.add('on');
  }
  function start(){ timer = setInterval(function(){ go(i+1); }, 5000); }
  dots.forEach(function(d, n){
    d.addEventListener('click', function(){ clearInterval(timer); go(n); start(); });
  });
  start();
})();
</script>


<div class="perks-wrap hero-dark-zone">
  <div class="perks-bar">
    <div class="perk-item">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
      <div>
        <div class="perk-title">Secure checkout</div>
        <div class="perk-sub">Encrypted payments</div>
      </div>
    </div>
    <div class="perk-item">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/><path d="M3 3h2l1 3"/></svg>
      <div>
        <div class="perk-title">Easy buy</div>
        <div class="perk-sub">One-click ordering</div>
      </div>
    </div>
    <div class="perk-item">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M21 3 2 10.5l6.5 2.5M21 3l-3.5 17L8.5 13M21 3 8.5 13m0 0v6.5l3-3.5"/></svg>
      <div>
        <div class="perk-title">24/7 support</div>
        <div class="perk-sub">Live chat, always on</div>
      </div>
    </div>
    <div class="perk-item">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M12 3 2 8l10 5 10-5Z"/><path d="M6 10.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-5.5"/></svg>
      <div>
        <div class="perk-title">Student discount</div>
        <div class="perk-sub">10% off with valid ID</div>
      </div>
    </div>
  </div>
</div>
<div class="section-title"><h2>Easy Buy!</h2></div>
<div class="grid-wrap">

<div class="product-grid" id="productGrid"></div>

<div class="view-more-wrap">
  <button class="btn-outline">View more
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
  </button>
</div>


<!-- Footer -->
<footer>
  <div class="footer-grid">
    <div class="footer-col">
      <h3>Company</h3>
      <a href="/PHP/info-center.php#about">About Lunax</a>
      <a href="/PHP/info-center.php#careers">Careers</a>
      <a href="/PHP/info-center.php#journal">Lunax Journal</a>
    </div>
    <div class="footer-col">
      <h3>Help & Support</h3>
      <a href="/PHP/info-center.php#shipping">Shipping Info</a>
      <a href="/PHP/info-center.php#returns">Returns</a>
      <a href="/PHP/info-center.php#warranty">Warranty</a>
      <a href="/PHP/info-center.php#track-order">How to Track</a>
      <a href="/PHP/info-center.php#faq">FAQ</a>
    </div>
    <div class="footer-col">
      <h3>Customer Care</h3>
      <a href="/PHP/customer-service.php">Contact Us</a>
      <a href="/PHP/info-center.php#payment-tax">Payment & Tax</a>
      <a href="/PHP/info-center.php#gift-cards">Gift Cards</a>
    </div>
    <div class="footer-col">
      <h3>Sign up for Lunax News</h3>
      <div class="newsletter-form">
        <input type="email" placeholder="Your email address">
        <button>Subscribe</button>
      </div>
      <span style="font-size:0.7rem; color:rgba(237,233,224,0.5);">Weekly drops, no spam. Unsubscribe anytime.</span>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="copy">© 2026 LUNAX. All rights reserved.</span>
    <div class="footer-links">
      <a href="/PHP/info-center.php#privacy">Privacy Center</a>
      <a href="/PHP/info-center.php#terms">Terms & Conditions</a>
      <a href="/PHP/info-center.php#accessibility">Accessibility</a>
    </div>
    <div class="pay-icons">
      <span>MASTERCARD</span><span>GOOGLE PAY</span>
    </div>
  </div>
</footer>

<script src="../JS/locale.js"></script>
<script src="../JS/products.js"></script>
<script>window.LUNAX_DATA = <?= json_encode($LUNAX, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="../JS/home.js"></script>
</body>
</html>

