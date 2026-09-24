<?php
/* ============================================================================
   LUNAX — Checkout page (checkout.php)
   ----------------------------------------------------------------------------
   Sample/placeholder order data below — mirrors cart.php's $cartItems shape.
   Once a database/session/cart backend exists, replace with:

       $cartItems = $_SESSION['cart'] ?? [];

   The form posts to itself (action="") as a placeholder. Wire it to a real
   order-processing endpoint later — look for the TODO markers below.
   ========================================================================== */
session_start();

$pageTitle = 'Checkout — LUNAX';

// ---- Real cart data from session --------------------------------------------
// If session cart is empty, fall back to placeholder data for demo purposes
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $cartItems[] = [
            'id'    => $id,
            'name'  => $item['name'] ?? '',
            'price' => $item['price'] ?? 0,
            'qty'   => $item['qty'] ?? 1,
            'meta'  => $item['meta'] ?? '',
        ];
    }
} else {
    // Fallback placeholder data for demo/preview
    $cartItems = [
        [
            'id'    => 'pulse-earbuds',
            'name'  => 'Pulse Wireless Earbuds Pro, ANC + 30h battery',
            'price' => 49.99,
            'qty'   => 1,
            'meta'  => 'Color: Black',
        ],
        [
            'id'    => 'orbit-phone',
            'name'  => 'Orbit 6.7" AMOLED Phone, 256GB, 5G',
            'price' => 389.00,
            'qty'   => 1,
            'meta'  => 'Color: Midnight Blue · 256GB',
        ],
        [
            'id'    => 'novabook-air',
            'name'  => 'NovaBook Air 14", 16GB / 512GB SSD',
            'price' => 649.00,
            'qty'   => 1,
            'meta'  => 'Silver',
        ],
    ];
}

define('LNX_FREE_SHIP_THRESHOLD', 50.00);
define('LNX_SHIP_FLAT_RATE', 6.99);
define('LNX_TAX_RATE', 0.0);

if (!function_exists('lnx_money')) {
    function lnx_money(float|int|string|null $v): string
    {
        $v = (float) $v;
        return ($v < 0 ? '-' : '') . '$' . number_format(abs($v), 2);
    }
}

$subtotal = 0.0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$shipping  = ($subtotal >= LNX_FREE_SHIP_THRESHOLD || $subtotal === 0.0) ? 0.0 : LNX_SHIP_FLAT_RATE;
$tax       = $subtotal * LNX_TAX_RATE;
$total     = $subtotal + $shipping + $tax;
$itemCount = array_sum(array_column($cartItems, 'qty'));

// ---- Placeholder order submit handler --------------------------------------
// TODO: replace with real validation + payment processing + order creation.
$orderPlaced = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pretend the order succeeded so the confirmation state can be previewed.
    $orderPlaced = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#140F0C;
    --gold-light:#F6DC7D;
    --gold:#F2C94C;
    --gold-dark:#D1A83B;
    --cream:#FBF6EC;
    --line:rgba(20,15,12,0.12);
    --muted:rgba(20,15,12,0.55);
  }
  *{ box-sizing:border-box; }
  body{
    margin:0; background:var(--cream); color:var(--ink);
    font-family:'JetBrains Mono', monospace;
  }
  a{ color:inherit; text-decoration:none; }

  .lnx-topbar{
    background:var(--ink); color:#fff; padding:0.9rem 1.75rem;
    display:flex; align-items:center; justify-content:space-between;
  }
  .lnx-logo{ font-family:'Space Grotesk',sans-serif; font-weight:700; letter-spacing:0.12em; font-size:1.1rem; }
  .lnx-back{ font-size:0.78rem; color:var(--gold); }
  .lnx-back:hover{ text-decoration:underline; }

  .co-steps{
    display:flex; align-items:center; justify-content:center; gap:0.5rem;
    padding:1rem 1.5rem 0; font-size:0.72rem; color:var(--muted);
    text-transform:uppercase; letter-spacing:0.04em;
  }
  .co-steps span.active{ color:var(--ink); font-weight:700; }
  .co-steps .sep{ color:var(--line); }

  .co-wrap{
    max-width:1100px; margin:0 auto; padding:1.5rem 1.5rem 4rem;
    display:flex; gap:2.5rem; align-items:flex-start;
  }

  .co-main{ flex:1; min-width:0; }
  .co-head{ margin-bottom:1.5rem; }
  .co-head h1{ font-family:'Space Grotesk',sans-serif; font-size:1.6rem; margin:0 0 0.3rem; }
  .co-head p{ margin:0; color:var(--muted); font-size:0.85rem; }

  .co-section{
    background:#fff; border:1px solid var(--line); border-radius:14px;
    padding:1.3rem; margin-bottom:1rem;
  }
  .co-section h2{
    font-family:'Space Grotesk',sans-serif; font-size:0.85rem; margin:0 0 1rem;
    text-transform:uppercase; letter-spacing:0.03em; display:flex; align-items:center; gap:0.5rem;
  }
  .co-section h2 .num{
    width:20px; height:20px; border-radius:50%; background:var(--gold);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:0.68rem; font-weight:700;
  }

  .field-row{ display:flex; gap:0.8rem; margin-bottom:0.8rem; flex-wrap:wrap; }
  .field{ flex:1; min-width:160px; display:flex; flex-direction:column; gap:0.35rem; }
  .field label{ font-size:0.68rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.03em; }
  .field input, .field select{
    padding:0.62rem 0.75rem; border:1px solid var(--line); border-radius:8px;
    font-family:'JetBrains Mono',monospace; font-size:0.82rem; background:#fff; color:var(--ink);
  }
  .field input:focus, .field select:focus{ outline:2px solid var(--gold-dark); outline-offset:1px; }

  .ship-methods{ display:flex; flex-direction:column; gap:0.6rem; }
  .ship-option{
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    border:1px solid var(--line); border-radius:10px; padding:0.75rem 0.9rem; cursor:pointer;
  }
  .ship-option.selected{ border-color:var(--gold-dark); background:rgba(242,201,76,0.08); }
  .ship-option-left{ display:flex; align-items:center; gap:0.6rem; }
  .ship-option input[type="radio"]{ accent-color:var(--gold-dark); }
  .ship-option-name{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.82rem; }
  .ship-option-eta{ font-size:0.7rem; color:var(--muted); }
  .ship-option-price{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.82rem; }

  .pay-tabs{ display:flex; gap:0.5rem; margin-bottom:1rem; }
  .pay-tab{
    flex:1; text-align:center; padding:0.6rem; border:1px solid var(--line); border-radius:8px;
    font-size:0.72rem; font-weight:700; text-transform:uppercase; cursor:pointer; background:#fff;
  }
  .pay-tab.selected{ border-color:var(--gold-dark); background:rgba(242,201,76,0.1); }

  .co-order-list{ margin-top:0; }
  .co-order-item{
    display:flex; gap:0.75rem; align-items:center; padding:0.6rem 0;
    border-bottom:1px solid var(--line);
  }
  .co-order-item:last-child{ border-bottom:none; }
  .co-order-thumb{ width:44px; height:44px; border-radius:8px; background:#F1ECDD; flex:0 0 auto; position:relative; }
  .co-order-thumb .qty-badge{
    position:absolute; top:-6px; right:-6px; background:var(--ink); color:#fff;
    font-size:0.6rem; width:16px; height:16px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
  }
  .co-order-item-body{ flex:1; min-width:0; }
  .co-order-item-name{
    font-size:0.76rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .co-order-item-meta{ font-size:0.66rem; color:var(--muted); }
  .co-order-item-price{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.8rem; white-space:nowrap; }

  .co-summary{
    flex:0 0 340px; background:#fff; border:1px solid var(--line); border-radius:14px; padding:1.3rem;
    position:sticky; top:1.5rem;
  }
  .co-summary h2{
    font-family:'Space Grotesk',sans-serif; font-size:1rem; margin:0 0 1rem; text-transform:uppercase; letter-spacing:0.03em;
  }
  .summary-row{
    display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:0.6rem; color:rgba(20,15,12,0.78);
  }
  .summary-row.total{
    font-weight:700; font-size:0.95rem; color:var(--ink); border-top:1px solid var(--line);
    padding-top:0.8rem; margin-top:0.4rem;
  }

  .place-order-btn{
    width:100%; padding:0.9rem; border:none; border-radius:10px; background:var(--ink); color:#fff;
    font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.9rem; cursor:pointer;
    text-transform:uppercase; letter-spacing:0.03em; margin-top:1.2rem;
  }
  .place-order-btn:hover{ opacity:0.9; }
  .place-order-btn:disabled{ opacity:0.4; cursor:not-allowed; }

  .secure-note{
    display:flex; align-items:center; gap:0.4rem; justify-content:center;
    font-size:0.68rem; color:var(--muted); margin-top:0.9rem;
  }

  /* Confirmation state */
  .co-confirm{
    max-width:560px; margin:3rem auto; text-align:center; background:#fff;
    border:1px solid var(--line); border-radius:16px; padding:2.5rem 2rem;
  }
  .co-confirm .check{
    width:56px; height:56px; border-radius:50%; background:rgba(26,156,92,0.12); color:#0d5c37;
    display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;
  }
  .co-confirm h1{ font-family:'Space Grotesk',sans-serif; font-size:1.4rem; margin:0 0 0.5rem; }
  .co-confirm p{ color:var(--muted); font-size:0.85rem; margin:0 0 1.5rem; }
  .co-confirm .order-num{
    font-family:'JetBrains Mono',monospace; font-weight:700; background:var(--cream);
    border:1px dashed var(--line); border-radius:8px; padding:0.6rem 1rem; display:inline-block;
    margin-bottom:1.5rem;
  }
  .co-confirm a.cta{
    display:inline-block; background:var(--gold); border:1px solid var(--gold-dark);
    padding:0.7rem 1.6rem; border-radius:8px; font-weight:700; font-size:0.78rem; text-transform:uppercase;
  }

  @media (max-width: 820px){
    .co-wrap{ flex-direction:column; }
    .co-summary{ flex:1 1 auto; width:100%; position:static; }
  }
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="PHP/cart.php">&larr; Back to cart</a>
</div>

<?php if ($orderPlaced): ?>

  <div class="co-confirm">
    <div class="check">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <h1>Order confirmed</h1>
    <p>Thanks for shopping with LUNAX. A confirmation email is on its way.</p>
    <div class="order-num">Order #LNX-<?= str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT) ?></div>
    <br>
    <a class="cta" href="PHP/home.php">Continue shopping</a>
  </div>

<?php else: ?>

  <div class="co-steps">
    <span>Cart</span><span class="sep">&rsaquo;</span>
    <span class="active">Checkout</span><span class="sep">&rsaquo;</span>
    <span>Confirmation</span>
  </div>

  <form class="co-wrap" method="post" action="" id="checkoutForm">

    <div class="co-main">
      <div class="co-head">
        <h1>Checkout</h1>
        <p><?= (int) $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?> · Secure, encrypted checkout</p>
      </div>

      <div class="co-section">
        <h2><span class="num">1</span> Contact & Shipping Address</h2>
        <div class="field-row">
          <div class="field"><label>Full name</label><input type="text" name="full_name" required></div>
          <div class="field"><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div class="field-row">
          <div class="field" style="flex:2;"><label>Street address</label><input type="text" name="address" required></div>
          <div class="field"><label>Apt / Suite</label><input type="text" name="address2"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>City</label><input type="text" name="city" required></div>
          <div class="field"><label>State / Province</label><input type="text" name="state" required></div>
          <div class="field"><label>ZIP / Postal code</label><input type="text" name="zip" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Country</label>
            <select name="country">
              <option>United States</option>
              <option>United Kingdom</option>
              <option>Canada</option>
              <option>Australia</option>
              <option>Other</option>
            </select>
          </div>
          <div class="field"><label>Phone</label><input type="tel" name="phone" required></div>
        </div>
      </div>

      <div class="co-section">
        <h2><span class="num">2</span> Shipping Method</h2>
        <div class="ship-methods">
          <label class="ship-option selected">
            <div class="ship-option-left">
              <input type="radio" name="ship_method" value="standard" checked>
              <div>
                <div class="ship-option-name">Standard</div>
                <div class="ship-option-eta">5&ndash;7 business days</div>
              </div>
            </div>
            <div class="ship-option-price"><?= $shipping > 0 ? lnx_money($shipping) : 'FREE' ?></div>
          </label>
          <label class="ship-option">
            <div class="ship-option-left">
              <input type="radio" name="ship_method" value="express">
              <div>
                <div class="ship-option-name">Express</div>
                <div class="ship-option-eta">2&ndash;3 business days</div>
              </div>
            </div>
            <div class="ship-option-price">$14.99</div>
          </label>
          <label class="ship-option">
            <div class="ship-option-left">
              <input type="radio" name="ship_method" value="overnight">
              <div>
                <div class="ship-option-name">Overnight</div>
                <div class="ship-option-eta">Next business day</div>
              </div>
            </div>
            <div class="ship-option-price">$29.99</div>
          </label>
        </div>
      </div>

      <div class="co-section">
        <h2><span class="num">3</span> Payment</h2>
        <div class="pay-tabs">
          <div class="pay-tab selected" data-pay="card">Card</div>
          <div class="pay-tab" data-pay="paypal">PayPal</div>
          <div class="pay-tab" data-pay="applepay">Apple Pay</div>
        </div>
        <div id="payCardFields">
          <div class="field-row">
            <div class="field" style="flex:2;"><label>Card number</label><input type="text" name="card_number" placeholder="0000 0000 0000 0000" inputmode="numeric"></div>
            <div class="field"><label>Expiry</label><input type="text" name="card_exp" placeholder="MM / YY"></div>
            <div class="field"><label>CVC</label><input type="text" name="card_cvc" placeholder="123" inputmode="numeric"></div>
          </div>
          <div class="field-row">
            <div class="field"><label>Name on card</label><input type="text" name="card_name"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="co-summary">
      <h2>Order Summary</h2>

      <div class="co-order-list">
        <?php foreach ($cartItems as $item): ?>
          <div class="co-order-item">
            <div class="co-order-thumb"><span class="qty-badge"><?= (int) $item['qty'] ?></span></div>
            <div class="co-order-item-body">
              <div class="co-order-item-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php if (!empty($item['meta'])): ?>
                <div class="co-order-item-meta"><?= htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>
            <div class="co-order-item-price"><?= lnx_money($item['price'] * $item['qty']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:1rem;">
        <div class="summary-row"><span>Subtotal</span><span><?= lnx_money($subtotal) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span id="summaryShipping"><?= $shipping > 0 ? lnx_money($shipping) : 'FREE' ?></span></div>
        <?php if (LNX_TAX_RATE > 0): ?>
          <div class="summary-row"><span>Tax</span><span><?= lnx_money($tax) ?></span></div>
        <?php endif; ?>
        <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= lnx_money($total) ?></span></div>
      </div>

      <button type="submit" class="place-order-btn" id="placeOrderBtn" <?= empty($cartItems) ? 'disabled' : '' ?>>
        Place Order &middot; <?= lnx_money($total) ?>
      </button>

      <div class="secure-note">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        Secure checkout · Encrypted payments
      </div>
    </div>

  </form>

  <script>
  (function(){
    // Shipping method selection — updates radio + highlight + total (client-side only).
    var shipOptions = document.querySelectorAll('.ship-option');
    var shipPrices = { standard: <?= json_encode($shipping) ?>, express: 14.99, overnight: 29.99 };
    var subtotal = <?= json_encode($subtotal) ?>;
    var taxRate = <?= json_encode(LNX_TAX_RATE) ?>;

    function money(v){ return '$' + v.toFixed(2); }

    shipOptions.forEach(function(opt){
      opt.addEventListener('click', function(){
        shipOptions.forEach(function(o){ o.classList.remove('selected'); });
        opt.classList.add('selected');
        opt.querySelector('input[type="radio"]').checked = true;

        var method = opt.querySelector('input[type="radio"]').value;
        var shipCost = shipPrices[method] || 0;
        var tax = subtotal * taxRate;
        var total = subtotal + shipCost + tax;

        document.getElementById('summaryShipping').textContent = shipCost > 0 ? money(shipCost) : 'FREE';
        document.getElementById('summaryTotal').textContent = money(total);
        document.getElementById('placeOrderBtn').innerHTML = 'Place Order &middot; ' + money(total);
      });
    });

    // Payment method tabs
    var payTabs = document.querySelectorAll('.pay-tab');
    var cardFields = document.getElementById('payCardFields');
    payTabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        payTabs.forEach(function(t){ t.classList.remove('selected'); });
        tab.classList.add('selected');
        cardFields.style.display = (tab.dataset.pay === 'card') ? '' : 'none';
        // TODO: swap in PayPal / Apple Pay SDK buttons here when integrated.
      });
    });

    // TODO: on submit, POST to a real order-processing endpoint, e.g.
    // fetch('/api/checkout/place-order.php', { method:'POST', body: new FormData(form) })
    // Currently the form just submits back to this page (?), which sets
    // $orderPlaced = true and renders the confirmation screen as a preview.
  })();
  </script>

<?php endif; ?>

</body>
</html>


