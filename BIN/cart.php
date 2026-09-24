<?php
/* ============================================================================
   LUNAX — Shopping Cart (cart.php)
   Replace $_SESSION['cart'] reads/writes with your DB/session logic.
   Item shape: ['name'=>string, 'price'=>float, 'qty'=>int, 'image'=>string, 'icon'=>string]
   ========================================================================== */
session_start();

define('FREE_SHIP', 50.00);
define('SHIP_RATE', 6.99);
define('TAX_RATE',  0.08);   // set 0 to disable tax row

// ── AJAX (POST) ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $_SESSION['cart'] ??= [];

    // Sanitise helpers — FILTER_SANITIZE_STRING removed in PHP 8.2
    $str = fn(string $k) => strip_tags(trim((string)($_POST[$k] ?? '')));
    $int = fn(string $k, int $min = 0) => max($min, (int)($_POST[$k] ?? 0));
    $flt = fn(string $k) => (float)($_POST[$k] ?? 0);

    $fail = function(string $msg) { http_response_code(400); echo json_encode(['error' => $msg]); exit; };
    $ok   = function() { echo json_encode(['ok' => true, 'itemCount' => array_sum(array_column($_SESSION['cart'], 'qty'))]); exit; };

    switch ($_POST['action']) {
        case 'add':
            $index = isset($_POST['index']) ? (int)$_POST['index'] : null;
            $name  = $str('name');
            $price = $flt('price');
            $qty   = max(1, $int('qty', 1));
            if ($index === null || $name === '' || $price <= 0) $fail('Invalid product data');
            isset($_SESSION['cart'][$index])
                ? $_SESSION['cart'][$index]['qty'] += $qty
                : $_SESSION['cart'][$index] = [
                    'name'  => $name,
                    'price' => $price,
                    'qty'   => $qty,
                    'image' => filter_var($str('image'), FILTER_SANITIZE_URL),
                    'icon'  => $str('icon'),
                ];
            break;

            case 'update':
              $id  = $str('id');
              $qty = $int('qty', 0);
              if ($id === '' || !isset($_SESSION['cart'][$id])) $fail('Item not found');
              if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
              } else {
                $_SESSION['cart'][$id]['qty'] = $qty;
              }
            break;

        case 'remove':
            $id = $str('id');
            if ($id === '' || !isset($_SESSION['cart'][$id])) $fail('Item not found');
            unset($_SESSION['cart'][$id]);
            break;

        default:
            $fail('Unknown action');
    }
    $ok();
}

// ── Cart data ─────────────────────────────────────────────────────────────────
$cartItems = array_map(
    fn($id, $item) => ['id' => $id, 'name' => $item['name'], 'price' => (float)$item['price'],
                       'qty' => (int)$item['qty'], 'meta' => '', 'image' => $item['image'] ?? '', 'icon' => $item['icon'] ?? ''],
    array_keys($_SESSION['cart'] ?? []),
    array_values($_SESSION['cart'] ?? [])
);

function lnx_money(float $v): string {
    return ($v < 0 ? '-$' : '$') . number_format(abs($v), 2);
}

$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cartItems));
$shipping  = ($subtotal >= FREE_SHIP || $subtotal === 0.0) ? 0.0 : SHIP_RATE;
$tax       = round($subtotal * TAX_RATE, 2);
$total     = $subtotal + $shipping + $tax;
$itemCount = array_sum(array_column($cartItems, 'qty'));
$e         = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
// Safe SVG child elements allowed for product icons
$SVG_ALLOW = '<path><circle><rect><polyline><line><polygon><ellipse><g>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart — LUNAX</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>

  :root{--ink:#140F0C;--gold:#F2C94C;--gold-d:#D1A83B;--cream:#FBF6EC;--line:rgba(20,15,12,.12);--muted:rgba(20,15,12,.55)}
  *{box-sizing:border-box}
  body{margin:0;background:var(--cream);color:var(--ink);font-family:'JetBrains Mono',monospace}
  a{color:inherit;text-decoration:none}
  /* Top bar */
  .lnx-topbar{background:var(--ink);color:#fff;padding:.9rem 1.75rem;display:flex;align-items:center;justify-content:space-between}
  .lnx-logo{font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:.12em;font-size:1.1rem}
  .lnx-back{font-size:.78rem;color:var(--gold)}
  .lnx-back:hover{text-decoration:underline}
  /* Layout */
  .cart-wrap{max-width:1100px;margin:0 auto;padding:2rem 1.5rem 4rem;display:flex;gap:2.5rem;align-items:flex-start}
  .cart-main{flex:1;min-width:0}
  .cart-head{margin-bottom:1.5rem}
  .cart-head h1{font-family:'Space Grotesk',sans-serif;font-size:1.6rem;margin:0 0 .3rem}
  .cart-head p{margin:0;color:var(--muted);font-size:.85rem}
  /* Cart item */
  .cart-item{display:flex;gap:1rem;align-items:flex-start;background:#fff;border:1px solid var(--line);border-radius:14px;padding:1.1rem;margin-bottom:.9rem}
  .cart-item-thumb{width:76px;height:76px;border-radius:10px;background:#F1ECDD;flex:0 0 auto;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .cart-item-body{flex:1;min-width:0}
  .cart-item-name{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.9rem;margin-bottom:.2rem}
  .cart-item-meta{font-size:.72rem;color:var(--muted);margin-bottom:.6rem}
  .cart-item-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
  /* Qty control */
  .qty-control{display:flex;align-items:center;border:1px solid var(--line);border-radius:8px;overflow:hidden}
  .qty-btn{width:30px;height:30px;border:none;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;display:flex;align-items:center;justify-content:center}
  .qty-btn:hover{background:#FAF7EE}
  .qty-value{width:36px;text-align:center;font-size:.85rem;font-weight:600;user-select:none}
  .cart-item-price{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.95rem;white-space:nowrap}
  .cart-item-remove{font-size:.7rem;color:#7a1b21;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;margin-top:.5rem}
  .cart-item-remove:hover{opacity:.7}
  /* Empty state */
  .cart-empty{text-align:center;padding:3rem 1.5rem;border:1px dashed var(--line);border-radius:14px;color:var(--muted)}
  .cart-empty a{display:inline-block;margin-top:1rem;background:var(--gold);border:1px solid var(--gold-d);padding:.6rem 1.4rem;border-radius:8px;font-weight:700;font-size:.78rem;text-transform:uppercase}
  /* Summary sidebar */
  .cart-summary{flex:0 0 320px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:1.3rem;position:sticky;top:1.5rem}
  .cart-summary h2{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.03em}
  .summary-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.6rem;color:rgba(20,15,12,.78)}
  .summary-row.total{font-weight:700;font-size:.95rem;color:var(--ink);border-top:1px solid var(--line);padding-top:.8rem;margin-top:.4rem}
  .summary-shipnote{font-size:.72rem;color:#0d5c37;background:rgba(26,156,92,.12);border-radius:8px;padding:.5rem .7rem;margin:.8rem 0 1rem}
  .summary-shipnote.pending{color:#8a5a05;background:rgba(242,201,76,.2)}
  /* Promo */
  .promo-row{display:flex;gap:.5rem;margin-bottom:1.1rem}
  .promo-row input{flex:1;padding:.55rem .7rem;border:1px solid var(--line);border-radius:8px;font-family:'JetBrains Mono',monospace;font-size:.78rem}
  .promo-row button{padding:0 .9rem;border:1px solid var(--line);border-radius:8px;background:#fff;font-size:.72rem;font-weight:700;cursor:pointer;text-transform:uppercase}
  .promo-row button:hover{border-color:var(--gold-d)}
  /* Checkout */
  .checkout-btn{width:100%;padding:.85rem;border:none;border-radius:10px;background:var(--ink);color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.9rem;cursor:pointer;text-transform:uppercase;letter-spacing:.03em}
  .checkout-btn:hover{opacity:.9}
  .checkout-btn:disabled{opacity:.4;cursor:not-allowed}
  .secure-note{display:flex;align-items:center;gap:.4rem;justify-content:center;font-size:.68rem;color:var(--muted);margin-top:.9rem}
  @media(max-width:820px){.cart-wrap{flex-direction:column}.cart-summary{flex:1 1 auto;width:100%;position:static}}
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="home.php">&larr; Continue shopping</a>
</div>

<div class="cart-wrap">

  <div class="cart-main">
    <div class="cart-head">
      <h1>Shopping Cart</h1>
      <p id="cartItemCountLabel"><?= $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?> in your cart</p>
    </div>

    <div id="cartItemsList">
    <?php if (empty($cartItems)): ?>
      <div class="cart-empty" id="cartEmptyState">
        Your cart is empty.<br>
        <a href="home.php">Start shopping</a>
      </div>
    <?php else: ?>
      <?php foreach ($cartItems as $item): ?>
        <div class="cart-item" data-id="<?= $e($item['id']) ?>" data-price="<?= $e($item['price']) ?>" data-qty="<?= (int)$item['qty'] ?>">
          <div class="cart-item-thumb">
            <?php if (!empty($item['image'])): ?>
              <img src="<?= $e($item['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
            <?php elseif (!empty($item['icon'])): ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:60%;height:60%;margin:20%"><?= strip_tags($item['icon'], $SVG_ALLOW) ?></svg>
            <?php endif; ?>
          </div>
          <div class="cart-item-body">
            <div class="cart-item-name"><?= $e($item['name']) ?></div>
            <?php if (!empty($item['meta'])): ?><div class="cart-item-meta"><?= $e($item['meta']) ?></div><?php endif; ?>
            <div class="cart-item-row">
              <div class="qty-control">
                <button class="qty-btn qty-minus" aria-label="Decrease">&minus;</button>
                <span class="qty-value"><?= (int)$item['qty'] ?></span>
                <button class="qty-btn qty-plus" aria-label="Increase">+</button>
              </div>
              <div class="cart-item-price"><?= lnx_money($item['price'] * $item['qty']) ?></div>
            </div>
            <button class="cart-item-remove">Remove</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
  </div>

  <div class="cart-summary">
    <h2>Order Summary</h2>
    <div class="summary-row"><span>Subtotal</span><span id="summarySubtotal"><?= lnx_money($subtotal) ?></span></div>
    <div class="summary-row"><span>Shipping</span><span id="summaryShipping"><?= $shipping > 0 ? lnx_money($shipping) : 'FREE' ?></span></div>
    <?php if (TAX_RATE > 0): ?>
    <div class="summary-row"><span>Tax (<?= TAX_RATE * 100 ?>%)</span><span id="summaryTax"><?= lnx_money($tax) ?></span></div>
    <?php endif; ?>
    <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= lnx_money($total) ?></span></div>

    <div class="summary-shipnote<?= $shipping > 0 ? ' pending' : '' ?>" id="summaryShipNote">
      <?= empty($cartItems) ? 'Add items to your cart to see shipping options' : ($shipping > 0 ? 'Add ' . lnx_money(FREE_SHIP - $subtotal) . ' more for free shipping' : "You've unlocked free shipping!") ?>
    </div>

    <div class="promo-row">
      <input type="text" placeholder="Promo code" id="promoInput">
      <button type="button" id="promoApplyBtn">Apply</button>
    </div>

    <button class="checkout-btn" id="checkoutBtn" <?= empty($cartItems) ? 'disabled' : '' ?>>Proceed to Checkout</button>

    <div class="secure-note">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      Secure checkout · Encrypted payments
    </div>
  </div>

</div>

<script>
(function(){
  const FREE = <?= FREE_SHIP ?>, SHIP = <?= SHIP_RATE ?>, TAX = <?= TAX_RATE ?>;
  const $ = id => document.getElementById(id);
  const money = v => '$' + (+v).toFixed(2);

  function recalc(){
    let sub = 0, count = 0;
    document.querySelectorAll('.cart-item').forEach(r => {
      const price = +r.dataset.price, qty = +r.dataset.qty;
      sub += price * qty;
      count += qty;
      r.querySelector('.cart-item-price').textContent = money(price * qty);
    });

    const ship = (sub >= FREE || sub === 0) ? 0 : SHIP;
    const tax  = sub * TAX;

    $('summarySubtotal').textContent  = money(sub);
    $('summaryShipping').textContent  = ship > 0 ? money(ship) : 'FREE';
    const taxEl = $('summaryTax');
    if (taxEl) taxEl.textContent = money(tax);
    $('summaryTotal').textContent = money(sub + ship + tax);

    const note = $('summaryShipNote');
    if (note) {
      note.textContent = sub === 0
        ? 'Add items to your cart to see shipping options'
        : ship > 0
          ? `Add ${money(FREE - sub)} more for free shipping`
          : "You've unlocked free shipping!";
      note.classList.toggle('pending', ship > 0 && sub > 0);
    }

    $('cartItemCountLabel').textContent = `${count} item${count === 1 ? '' : 's'} in your cart`;
    $('checkoutBtn').disabled = count === 0;

    if (count === 0 && !$('cartEmptyState'))
      $('cartItemsList').innerHTML = '<div class="cart-empty" id="cartEmptyState">Your cart is empty.<br><a href="home.php">Start shopping</a></div>';
  }

  // Single fetch helper
  const post = body => fetch('cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body
  }).then(r => r.json());

  $('cartItemsList').addEventListener('click', e => {
    const row = e.target.closest('.cart-item');
    if (!row) return;
    const id = row.dataset.id;

    // Qty +/-
    if (e.target.classList.contains('qty-plus') || e.target.classList.contains('qty-minus')) {
      const qty = Math.max(0, +row.dataset.qty + (e.target.classList.contains('qty-plus') ? 1 : -1));
      post(`action=update&id=${encodeURIComponent(id)}&qty=${qty}`)
        .then(d => {
          if (!d.ok) return;
          qty <= 0 ? row.remove() : (row.dataset.qty = qty, row.querySelector('.qty-value').textContent = qty);
          recalc();
        }).catch(console.error);
    }

    // Remove
    if (e.target.classList.contains('cart-item-remove'))
      post(`action=remove&id=${encodeURIComponent(id)}`)
        .then(d => { if (d.ok) { row.remove(); recalc(); } })
        .catch(console.error);
  });

  $('promoApplyBtn').addEventListener('click', () => {
    const code = $('promoInput').value.trim();
    if (code) alert(`Promo code "${code}" will be validated once checkout is connected.`);
  });

  $('checkoutBtn').addEventListener('click', function(){
    if (!this.disabled) window.location.href = 'checkout.php';
  });

  document.addEventListener('lunax:locale', recalc);
})();
</script>
</body>
</html>