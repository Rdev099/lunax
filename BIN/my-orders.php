<?php
/* ============================================================================
   LUNAX — My Orders page (my-orders.php)
   ----------------------------------------------------------------------------
   Sample/placeholder order data below. Once you have a database, replace the
   $orders array with a query, e.g.:

       $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY placed_at DESC");
       $stmt->execute([$userId]);
       $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

   Each order should keep this shape so the markup below keeps working:
       [
         'id'        => 'LNX-10493',
         'placed_at' => '2026-08-29',
         'status'    => 'delivered' | 'shipped' | 'processing' | 'cancelled',
         'total'     => 214.99,
         'items'     => [
            ['name' => 'Pulse Wireless Earbuds Pro', 'qty' => 1, 'price' => 49.99, 'img' => ''],
            ...
         ],
       ]
   ========================================================================== */

$pageTitle = 'My Orders — LUNAX';

// ---- Placeholder data (swap for a real DB query later) --------------------
$orders = [
    [
        'id'        => 'LNX-10493',
        'placed_at' => '2026-08-29',
        'status'    => 'delivered',
        'total'     => 214.99,
        'items'     => [
            ['name' => 'Pulse Wireless Earbuds Pro, ANC + 30h battery', 'qty' => 1, 'price' => 49.99],
            ['name' => 'Orbit 6.7" AMOLED Phone, 256GB, 5G',            'qty' => 1, 'price' => 165.00],
        ],
    ],
    [
        'id'        => 'LNX-10471',
        'placed_at' => '2026-08-14',
        'status'    => 'shipped',
        'total'     => 649.00,
        'items'     => [
            ['name' => 'NovaBook Air 14", 16GB / 512GB SSD', 'qty' => 1, 'price' => 649.00],
        ],
    ],
    [
        'id'        => 'LNX-10402',
        'placed_at' => '2026-07-30',
        'status'    => 'processing',
        'total'     => 84.00,
        'items'     => [
            ['name' => 'Signal Drop Autumn Edition — Charging Dock', 'qty' => 2, 'price' => 42.00],
        ],
    ],
    [
        'id'        => 'LNX-10355',
        'placed_at' => '2026-07-02',
        'status'    => 'cancelled',
        'total'     => 132.43,
        'items'     => [
            ['name' => 'Signal Drop — Smart Speaker', 'qty' => 1, 'price' => 132.43],
        ],
    ],
];

function lnx_status_label($status)
{
    $map = [
        'delivered'  => 'Delivered',
        'shipped'    => 'Shipped',
        'processing' => 'Processing',
        'cancelled'  => 'Cancelled',
    ];
    return $map[$status] ?? ucfirst($status);
}

function lnx_status_class($status)
{
    $map = [
        'delivered'  => 'st-delivered',
        'shipped'    => 'st-shipped',
        'processing' => 'st-processing',
        'cancelled'  => 'st-cancelled',
    ];
    return $map[$status] ?? '';
}

function lnx_money($v)
{
    return '$' . number_format((float) $v, 2);
}

function lnx_date($v)
{
    $ts = strtotime($v);
    return $ts ? date('M j, Y', $ts) : $v;
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

  .orders-wrap{ max-width:960px; margin:0 auto; padding:2rem 1.5rem 4rem; }

  .orders-head{ margin-bottom:1.5rem; }
  .orders-head h1{
    font-family:'Space Grotesk',sans-serif; font-size:1.6rem; margin:0 0 0.3rem;
  }
  .orders-head p{ margin:0; color:rgba(20,15,12,0.6); font-size:0.85rem; }

  .orders-filter{
    display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;
  }
  .orders-filter button{
    font-family:'JetBrains Mono',monospace; font-size:0.72rem; font-weight:600;
    padding:0.4rem 0.9rem; border-radius:999px; border:1px solid var(--line);
    background:#fff; cursor:pointer; text-transform:uppercase; letter-spacing:0.04em;
    color:var(--ink); transition:background .15s ease, border-color .15s ease;
  }
  .orders-filter button.active{
    background:var(--gold); border-color:var(--gold-dark);
  }
  .orders-filter button:hover{ border-color:var(--gold-dark); }

  .order-card{
    background:#fff; border:1px solid var(--line); border-radius:14px;
    margin-bottom:1.1rem; overflow:hidden;
  }
  .order-card-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:0.9rem 1.2rem; background:#FAF7EE; border-bottom:1px solid var(--line);
    flex-wrap:wrap; gap:0.6rem;
  }
  .order-id{ font-weight:700; font-size:0.85rem; }
  .order-meta{ font-size:0.72rem; color:rgba(20,15,12,0.6); }

  .order-status{
    font-family:'Space Grotesk',sans-serif; font-size:0.68rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.04em; padding:0.25rem 0.7rem;
    border-radius:999px; white-space:nowrap;
  }
  .st-delivered{ color:#0d5c37; background:rgba(26,156,92,0.15); }
  .st-shipped{ color:#8a5a05; background:rgba(242,201,76,0.25); }
  .st-processing{ color:#1a4d8f; background:rgba(52,120,214,0.15); }
  .st-cancelled{ color:#7a1b21; background:rgba(209,55,63,0.15); }

  .order-items{ padding:0.9rem 1.2rem; }
  .order-item{
    display:flex; justify-content:space-between; align-items:center;
    padding:0.5rem 0; font-size:0.8rem;
  }
  .order-item + .order-item{ border-top:1px dashed var(--line); }
  .order-item-name{ padding-right:1rem; }
  .order-item-qty{ color:rgba(20,15,12,0.55); font-size:0.72rem; margin-left:0.4rem; }
  .order-item-price{ font-weight:600; white-space:nowrap; }

  .order-card-foot{
    display:flex; align-items:center; justify-content:space-between;
    padding:0.9rem 1.2rem; border-top:1px solid var(--line); flex-wrap:wrap; gap:0.6rem;
  }
  .order-total{ font-size:0.85rem; font-weight:700; }
  .order-total span{ font-weight:400; color:rgba(20,15,12,0.6); margin-right:0.4rem; }

  .order-actions{ display:flex; gap:0.5rem; }
  .order-actions a{
    font-size:0.72rem; font-weight:600; padding:0.4rem 0.85rem; border-radius:8px;
    border:1px solid var(--line); background:#fff; text-transform:uppercase; letter-spacing:0.03em;
  }
  .order-actions a.primary{ background:var(--gold); border-color:var(--gold-dark); }
  .order-actions a:hover{ border-color:var(--gold-dark); }

  .orders-empty{
    text-align:center; padding:3rem 1rem; color:rgba(20,15,12,0.55); font-size:0.85rem;
  }
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="/B1.php">&larr; Back to shop</a>
</div>

<div class="orders-wrap">

  <div class="orders-head">
    <h1>My Orders</h1>
    <p>Track, manage, and review everything you've bought from LUNAX.</p>
  </div>

  <div class="orders-filter" id="ordersFilter">
    <button type="button" class="active" data-filter="all">All orders</button>
    <button type="button" data-filter="processing">Processing</button>
    <button type="button" data-filter="shipped">Shipped</button>
    <button type="button" data-filter="delivered">Delivered</button>
    <button type="button" data-filter="cancelled">Cancelled</button>
  </div>

  <div id="ordersList">
  <?php if (empty($orders)): ?>
    <div class="orders-empty">You haven't placed any orders yet.</div>
  <?php else: ?>
    <?php foreach ($orders as $o): ?>
      <div class="order-card" data-status="<?= htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="order-card-head">
          <div>
            <div class="order-id">Order #<?= htmlspecialchars($o['id'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="order-meta">Placed <?= htmlspecialchars(lnx_date($o['placed_at']), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <span class="order-status <?= lnx_status_class($o['status']) ?>">
            <?= htmlspecialchars(lnx_status_label($o['status']), ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>

        <div class="order-items">
          <?php foreach ($o['items'] as $it): ?>
            <div class="order-item">
              <div class="order-item-name">
                <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>
                <span class="order-item-qty">&times;<?= (int) $it['qty'] ?></span>
              </div>
              <div class="order-item-price"><?= lnx_money($it['price'] * $it['qty']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="order-card-foot">
          <div class="order-total"><span>Total</span><?= lnx_money($o['total']) ?></div>
          <div class="order-actions">
            <a href="#">View details</a>
            <?php if ($o['status'] === 'delivered'): ?>
              <a href="#" class="primary">Buy again</a>
            <?php elseif (in_array($o['status'], ['processing', 'shipped'], true)): ?>
              <a href="#">Track order</a>
            <?php else: ?>
              <a href="#">Reorder</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>

</div>

<script>
// Simple client-side filter — no reload needed.
(function(){
  var buttons = document.querySelectorAll('#ordersFilter button');
  var cards   = document.querySelectorAll('#ordersList .order-card');

  buttons.forEach(function(btn){
    btn.addEventListener('click', function(){
      buttons.forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var filter = btn.getAttribute('data-filter');
      cards.forEach(function(card){
        var show = (filter === 'all') || (card.getAttribute('data-status') === filter);
        card.style.display = show ? '' : 'none';
      });
    });
  });
})();
</script>

</body>
</html>
