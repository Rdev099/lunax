<?php

require_once __DIR__ . '/lunax-data.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$LUNAX = lunax_load_all();
$products = lunax_products();


/* ---- POST actions (add to cart / remove / restore) ---------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $str    = fn(string $k) => strip_tags(trim((string)($_POST[$k] ?? '')));
    $action = $str('action');
    $id     = $str('id');
    $cart   = $_SESSION['cart'] ?? [];
    $isAjax = !empty($_POST['ajax']);

if ($action === 'add_item' && $id !== '') {
    $name  = $str('name');
    $price = isset($_POST['price']) ? (float) filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
    if (!isset($_SESSION['wishlist'][$id])) {
        $image = isset($_POST['image']) ? filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL) : '';
        $icon  = $str('icon');
        $_SESSION['wishlist'][$id] = [
            'id'         => $id,
            'name'       => $name,
            'price'      => $price,
            'oldPrice'   => $price,
            'variant'    => '',
            'rating'     => 4.5,
            'stock'      => 'In stock',
            'stockLevel' => 'in',
            'category'   => 'general',
            'color'      => 'thumb-a',
            'badge'      => '',
            'image'      => $image,
            'icon'       => $icon,
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'wishCount' => count($_SESSION['wishlist'])]);
    exit;
}

if ($action === 'add' && isset($_SESSION['wishlist'][$id])) {
        $w = $_SESSION['wishlist'][$id];
        if (isset($cart[$id])) {
            $cart[$id]['qty']++;
        } else {
            $cart[$id] = ['id' => $id, 'name' => $w['name'], 'price' => $w['price'], 'qty' => 1, 'meta' => $w['variant']];
        }
        $_SESSION['cart'] = $cart;
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => true,
                'cartItemCount' => array_sum(array_column($cart, 'qty')),
            ]);
            exit;
        }
        $_SESSION['flash'] = $w['name'] . ' added to your cart.';
    }

    if ($action === 'add_all') {
        foreach ($_SESSION['wishlist'] as $wid => $w) {
            if (isset($cart[$wid])) {
                $cart[$wid]['qty']++;
            } else {
                $cart[$wid] = ['id' => $wid, 'name' => $w['name'], 'price' => $w['price'], 'qty' => 1, 'meta' => $w['variant']];
            }
        }
        $_SESSION['flash'] = count($_SESSION['wishlist']) . ' items added to your cart.';
    }

if ($action === 'remove' && isset($_SESSION['wishlist'][$id])) {
        unset($_SESSION['wishlist'][$id]);
        if ($isAjax) {
            $wl       = $_SESSION['wishlist'];
            $savedN   = count($wl);
            $valueN   = array_sum(array_column($wl, 'price'));
            $oldN     = array_sum(array_column($wl, 'oldPrice'));
            $savingN  = $oldN > 0 ? round((1 - $valueN / $oldN) * 100) : 0;
            $readyN   = count(array_filter($wl, fn($i) => $i['stockLevel'] === 'in'));

            header('Content-Type: application/json');
            echo json_encode([
                'ok'          => true,
                'savedCount'  => $savedN,
                'totalValue'  => lnx_money($valueN),
                'avgSaving'   => $savingN,
                'readyCount'  => $readyN,
            ]);
            exit;
        }
        $_SESSION['flash'] = 'Item removed from your wishlist.';
    }

    if ($action === 'restore') {
        $_SESSION['wishlist'] = [];
        $_SESSION['flash'] = 'Wishlist restored.';
    }

    $_SESSION['cart'] = $cart;
    header('Location: wishlist.php');
    exit;
}

$wishlistItems = $_SESSION['wishlist'] ??[];
$cartItemCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);


function lnx_stars($rating) {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= round($rating) ? '★' : '☆';
    }
    return $out;
}

/* ---- Summary numbers, calculated instead of hardcoded ------------------- */
$savedCount  = count($wishlistItems);
$totalValue  = array_sum(array_column($wishlistItems, 'price'));
$totalOld    = array_sum(array_column($wishlistItems, 'oldPrice'));
$avgSaving   = $totalOld > 0 ? round((1 - $totalValue / $totalOld) * 100) : 0;
$readyCount  = count(array_filter($wishlistItems, fn($i) => $i['stockLevel'] === 'in'));

// ---- Related items: same categories as current wishlist -------------------
$categoryMap = [
    'audio'   => 'Audio',
    'phones'  => 'Phones & Tablets',
    'laptops' => 'Computers',
];

$wishlistCategories = [];
foreach ($wishlistItems as $item) {
    if (isset($categoryMap[$item['category']])) {
        $wishlistCategories[] = $categoryMap[$item['category']];
    }
}
$wishlistCategories = array_unique($wishlistCategories);
$wishlistIds = array_keys($wishlistItems);

$relatedItems = array_filter($products, function ($p) use ($wishlistCategories, $wishlistIds) {
    return in_array($p['category'], $wishlistCategories, true)
        && !in_array($p['id'], $wishlistIds, true);
});
$relatedItems = array_slice($relatedItems, 0, 10);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Wishlist — LUNAX</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/wishlist.css">
</head>
<body>

  <div class="ticker-bar">
    <div class="ticker-track">
      <?php foreach ($LUNAX['news'] as $n): ?>
        <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg><?= strtoupper(htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8')) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <header>
    <div class="nav-main">
      <a href="home.php" class="logo" aria-label="LUNAX home">
        <span>LUNAX</span>
        <img class="logo-mark" src="../image/l.png" alt="LUNAX logo">
      </a>

      <form class="search-wrap" action="home.php" method="get">
        <input type="text" name="q" placeholder="Search phones, laptops, audio, drones…">
        <button type="submit" aria-label="Search">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        </button>
      </form>

      <div class="nav-icons">
        <div class="account-trigger">
          <a href="personal-centre.php" class="nav-icon" aria-label="Personal Centre">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </a>
          <span class="account-tooltip">Personal Centre</span>
        </div>

        <div class="account-trigger">
          <a href="wishlist.php" class="nav-icon active" aria-label="Wishlist">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 21s-7.5-4.65-9.5-8.7C1.2 9.77 2.76 5 7.08 5c2.1 0 3.47 1.2 4.92 3.1C13.45 6.2 14.82 5 16.92 5c4.32 0 5.88 4.77 4.58 7.3C19.5 16.35 12 21 12 21Z"/></svg>
          </a>
          <span class="account-tooltip">Wishlist</span>
        </div>

        <div class="account-trigger">
          <a href="cart.php" class="nav-icon nav-icon-count" aria-label="Cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 2H2"/><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
            <span class="icon-count"><?= (int) $cartItemCount ?></span>
          </a>
          <span class="account-tooltip">Cart</span>
        </div>
      </div>
    </div>
  </header>

  <main class="page-shell">
    <?php if ($flash): ?>
      <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="page-top">
      <div class="title-wrap">
        <h1>My Wishlist</h1>
        <p>Saved items you want to review, compare, and buy later.</p>
      </div>
      <div class="top-actions">
        <a href="home.php" class="shop-btn">Continue Shopping</a>
        <?php if ($savedCount > 0): ?>
        <?php endif; ?>
      </div>
    </div>

<section class="summary-row" aria-label="Wishlist overview">
  <div class="summary-card">
    <span class="label">Saved items</span>
    <strong id="summarySaved"><?= $savedCount ?></strong>
    <small>Across devices, audio, and accessories</small>
  </div>
  <div class="summary-card">
    <span class="label">Estimated value</span>
    <strong id="summaryValue"><?= lnx_money($totalValue) ?></strong>
    <small>Before discounts and bundle offers</small>
  </div>
  <div class="summary-card">
    <span class="label">Average saving</span>
    <strong id="summarySaving"><?= $avgSaving ?>%</strong>
    <small>Compared with regular retail pricing</small>
  </div>
  <div class="summary-card">
    <span class="label">Ready to ship</span>
    <strong id="summaryReady"><?= $readyCount ?></strong>
    <small>Items currently available for quick delivery</small>
  </div>
</section>

    <section class="wishlist-layout">
      <aside class="filters-panel" aria-label="Wishlist filters">
        <div class="panel-head">
          <h3>Filter saved items</h3>
          <button type="button" id="clearFilters">Clear</button>
        </div>

        <div class="filter-group">
          <span class="filter-label">Categories</span>
          <div class="tag-list" data-filter="category">
            <button class="chip-btn active" type="button" data-value="all">All</button>
            <button class="chip-btn" type="button" data-value="phones">Phones</button>
            <button class="chip-btn" type="button" data-value="audio">Audio</button>
            <button class="chip-btn" type="button" data-value="laptops">Laptops</button>
          </div>
        </div>

        <div class="filter-group">
          <span class="filter-label">Availability</span>
          <div class="tag-list" data-filter="stock">
            <button class="chip-btn active" type="button" data-value="all">Any</button>
            <button class="chip-btn" type="button" data-value="in">In stock</button>
            <button class="chip-btn" type="button" data-value="low">Low stock</button>
          </div>
        </div>

        <div class="filter-group">
          <span class="filter-label">Price</span>
          <div class="tag-list" data-filter="price">
            <button class="chip-btn active" type="button" data-value="all">Any</button>
            <button class="chip-btn" type="button" data-value="under100">Under $100</button>
            <button class="chip-btn" type="button" data-value="100to500">$100 - $500</button>
            <button class="chip-btn" type="button" data-value="over500">$500+</button>
          </div>
        </div>

          <div class="filter-group">
            <button type="button" id="restoreFilters" class="chip-btn" style="width:100%">Restore defaults</button>
          </div>
      </aside>

      <div class="content-panel">
        <div class="toolbar">
          <div class="results" id="resultsCount">Showing <?= $savedCount ?> saved item<?= $savedCount === 1 ? '' : 's' ?></div>
          <div class="tool-actions">
            <select class="sort-pill" id="sortSelect">
              <option value="match">Sort: Best match</option>
              <option value="priceLow">Price: Low to high</option>
              <option value="priceHigh">Price: High to low</option>
              <option value="rating">Rating</option>
              <option value="name">Name A–Z</option>
            </select>
          </div>
        </div>

        <div class="wishlist-grid" id="wishlistGrid">
          <?php foreach ($wishlistItems as $item): ?>
            <article class="product-card"
                     data-id="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>"
                     data-category="<?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?>"
                     data-stock="<?= htmlspecialchars($item['stockLevel'], ENT_QUOTES, 'UTF-8') ?>"
                     data-price="<?= htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8') ?>"
                     data-rating="<?= htmlspecialchars($item['rating'], ENT_QUOTES, 'UTF-8') ?>"
                     data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
              <a href="product.php?id=<?= urlencode($item['id']) ?>" class="product-image <?= empty($item['image']) ? htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') : '' ?>">
                <span class="wish-badge"><?= htmlspecialchars($item['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($item['image'])): ?>
                      <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" class="product-image-photo">
                    <?php endif; ?>
              </a>
              <div class="product-content">
                <div class="product-topline">
                   <a href="product.php?id=<?= urlencode($item['id']) ?>" class="product-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="meta"><?= htmlspecialchars($item['variant'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="rating-row">
                  <span class="rating-stars"><?= lnx_stars($item['rating']) ?></span>
                  <span><?= htmlspecialchars((string) $item['rating'], ENT_QUOTES, 'UTF-8') ?> rated</span>
                  <span class="stock <?= $item['stockLevel'] === 'low' ? 'low' : '' ?>"><?= htmlspecialchars($item['stock'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="price-row">
                  <span class="price"><?= lnx_money($item['price']) ?></span>
                  <span class="old"><?= lnx_money($item['oldPrice']) ?></span>
                </div>

                <div class="card-actions">
                 <form method="post" class="add-cart-form">
  <input type="hidden" name="action" value="add">
  <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>">
  <button type="submit" class="primary-btn">Add to cart</button>
</form>
                  <form method="post" class="remove-form">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Remove</button>
                  </form>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <?php if ($savedCount === 0): ?>
          <div class="empty-state">
            Your wishlist is empty.
            <br>
            <a href="home.php" class="primary-btn">Start shopping</a>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php if (!empty($relatedItems)): ?>
<section class="related-strip" aria-label="You might also like">
  <h2>You might also like</h2>
  <div class="related-scroll">
    <?php foreach ($relatedItems as $p): ?>
      <a href="product.php?id=<?= urlencode($p['id']) ?>" class="related-card">
        <div class="related-image">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>">
          <?php endif; ?>
        </div>
        <div class="related-name"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="related-price"><?= lnx_money($p['price']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

  </main>

<script>
(function(){
  const grid    = document.getElementById('wishlistGrid');
  if (!grid) return;
  const cards   = Array.from(grid.querySelectorAll('.product-card'));
  const results = document.getElementById('resultsCount');
  const sortSel = document.getElementById('sortSelect');
  const filters = { category:'all', stock:'all', price:'all' };

  function priceBucket(p){
    if (p < 100) return 'under100';
    if (p <= 500) return '100to500';
    return 'over500';
  }

  function apply(){
    let shown = 0;
    cards.forEach(function(card){
      const price = parseFloat(card.dataset.price);
      const ok =
        (filters.category === 'all' || card.dataset.category === filters.category) &&
        (filters.stock    === 'all' || card.dataset.stock    === filters.stock) &&
        (filters.price    === 'all' || priceBucket(price)    === filters.price);
      card.hidden = !ok;
      if (ok) shown++;
    });
    results.textContent = 'Showing ' + shown + ' saved item' + (shown === 1 ? '' : 's');
  }

  document.querySelectorAll('.tag-list').forEach(function(list){
    const key = list.dataset.filter;
    list.addEventListener('click', function(e){
      const btn = e.target.closest('.chip-btn');
      if (!btn) return;
      list.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filters[key] = btn.dataset.value;
      apply();
    });
  });

  function resetFilters(){
    Object.keys(filters).forEach(k => filters[k] = 'all');
    document.querySelectorAll('.tag-list').forEach(function(list){
      list.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
      list.querySelector('.chip-btn[data-value="all"]').classList.add('active');
    });
    apply();
  }

  document.getElementById('clearFilters').addEventListener('click', resetFilters);
  document.getElementById('restoreFilters').addEventListener('click', resetFilters);

  sortSel.addEventListener('change', function(){
    const v = sortSel.value;
    const sorted = cards.slice().sort(function(a,b){
      if (v === 'priceLow')  return a.dataset.price - b.dataset.price;
      if (v === 'priceHigh') return b.dataset.price - a.dataset.price;
      if (v === 'rating')    return b.dataset.rating - a.dataset.rating;
      if (v === 'name')      return a.dataset.name.localeCompare(b.dataset.name);
      return 0;
    });
    sorted.forEach(c => grid.appendChild(c));
  });
  document.querySelectorAll('.remove-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const data = new FormData(form);
      data.append('ajax', '1');
      fetch('wishlist.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
          if (!d.ok) return;
          const card = form.closest('.product-card');
          if (card) card.remove();
          apply(); // refreshes "Showing X saved items" count

          const savedEl  = document.getElementById('summarySaved');
          const valueEl  = document.getElementById('summaryValue');
          const savingEl = document.getElementById('summarySaving');
          const readyEl  = document.getElementById('summaryReady');
          if (savedEl)  savedEl.textContent  = d.savedCount;
          if (valueEl)  valueEl.textContent  = d.totalValue;
          if (savingEl) savingEl.textContent = d.avgSaving + '%';
          if (readyEl)  readyEl.textContent  = d.readyCount;

          if (d.savedCount === 0) location.reload(); // show empty state cleanly
        })
        .catch(console.error);
    });
  });
    document.querySelectorAll('.add-cart-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const btn = form.querySelector('button');
      const data = new FormData(form);
      data.append('ajax', '1');
      btn.disabled = true;
      fetch('wishlist.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
          if (!d.ok) return;
          const countEl = document.querySelector('.icon-count');
          if (countEl) countEl.textContent = d.cartItemCount;
          const original = btn.textContent;
          btn.textContent = 'Added ✓';
          setTimeout(() => { btn.textContent = original; btn.disabled = false; }, 1200);
        })
        .catch(() => { btn.disabled = false; });
    });
  });
})();
</script>
</body>
</html>