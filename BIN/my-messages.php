<?php
/* ============================================================================
   LUNAX — My Messages page (my-messages.php)
   ----------------------------------------------------------------------------
   Sample/placeholder notification data below. Once you have a database,
   replace the $messages array with a query, e.g.:

       $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
       $stmt->execute([$userId]);
       $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

   Each message should keep this shape so the markup below keeps working:
       [
         'id'      => 1,
         'type'    => 'order' | 'promo' | 'restock' | 'system',
         'title'   => 'Your order has shipped',
         'body'    => 'Order #LNX-10493 is on its way...',
         'time'    => '2026-09-05 14:22:00',
         'read'    => true|false,
         'link'    => '' (optional URL),
       ]
   ========================================================================== */

$pageTitle = 'My Messages — LUNAX';

// ---- Placeholder data (swap for a real DB query later) --------------------
$messages = [
    [
        'id'    => 1,
        'type'  => 'order',
        'title' => 'Your order has shipped',
        'body'  => 'Order #LNX-10471 (NovaBook Air 14") is on its way. Estimated arrival Sep 12.',
        'time'  => '2026-09-08 09:14:00',
        'read'  => false,
        'link'  => 'my-orders.php',
    ],
    [
        'id'    => 2,
        'type'  => 'restock',
        'title' => 'Back in stock: Pulse Wireless Earbuds Pro',
        'body'  => 'An item on your wishlist is available again. Grab it before it sells out.',
        'time'  => '2026-09-07 18:40:00',
        'read'  => false,
        'link'  => '',
    ],
    [
        'id'    => 3,
        'type'  => 'promo',
        'title' => 'Signal Drop — Autumn Edition is live',
        'body'  => 'Up to 60% off audio and accessories. Use code LUNAX60 at checkout.',
        'time'  => '2026-09-05 08:00:00',
        'read'  => true,
        'link'  => '',
    ],
    [
        'id'    => 4,
        'type'  => 'order',
        'title' => 'Order delivered',
        'body'  => 'Order #LNX-10493 was delivered. Let us know what you think — leave a review.',
        'time'  => '2026-08-30 16:05:00',
        'read'  => true,
        'link'  => 'my-orders.php',
    ],
    [
        'id'    => 5,
        'type'  => 'system',
        'title' => 'Your student discount was verified',
        'body'  => '10% student pricing is now active on your account for future orders.',
        'time'  => '2026-08-22 11:30:00',
        'read'  => true,
        'link'  => '',
    ],
];

function lnx_msg_icon($type)
{
    // Minimal inline SVGs, no external deps, matches site's line-icon style.
    $icons = [
        'order'   => '<path d="M4 5h13v14H6a2 2 0 0 1-2-2z"/><path d="M17 8h3v9a2 2 0 0 1-2 2"/><path d="M7 8h7M7 11h7M7 14h5"/>',
        'promo'   => '<path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/>',
        'restock' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3v4M8 3v4M2 11h20"/>',
        'system'  => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16v.01"/>',
    ];
    return $icons[$type] ?? $icons['system'];
}

function lnx_msg_label($type)
{
    $map = ['order' => 'Order', 'promo' => 'Promo', 'restock' => 'Restock', 'system' => 'Account'];
    return $map[$type] ?? 'Notice';
}

function lnx_time_ago($ts)
{
    $t = is_numeric($ts) ? (int) $ts : strtotime($ts);
    if (!$t) {
        return '';
    }
    $diff = time() - $t;
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $m = (int) floor($diff / 60);
        return $m . ' min' . ($m === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);
        return $h . ' hour' . ($h === 1 ? '' : 's') . ' ago';
    }
    $d = (int) floor($diff / 86400);
    if ($d < 30) {
        return $d . ' day' . ($d === 1 ? '' : 's') . ' ago';
    }
    return date('M j, Y', $t);
}

$unreadCount = count(array_filter($messages, fn($m) => empty($m['read'])));
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

  .msg-wrap{ max-width:820px; margin:0 auto; padding:2rem 1.5rem 4rem; }

  .msg-head{
    display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap;
    gap:0.5rem; margin-bottom:1.5rem;
  }
  .msg-head h1{ font-family:'Space Grotesk',sans-serif; font-size:1.6rem; margin:0 0 0.3rem; }
  .msg-head p{ margin:0; color:rgba(20,15,12,0.6); font-size:0.85rem; }
  .msg-unread-pill{
    font-family:'Space Grotesk',sans-serif; font-size:0.72rem; font-weight:700;
    background:var(--gold); color:var(--ink); padding:0.25rem 0.7rem; border-radius:999px;
    white-space:nowrap;
  }

  .msg-filter{ display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap; }
  .msg-filter button{
    font-family:'JetBrains Mono',monospace; font-size:0.72rem; font-weight:600;
    padding:0.4rem 0.9rem; border-radius:999px; border:1px solid var(--line);
    background:#fff; cursor:pointer; text-transform:uppercase; letter-spacing:0.04em;
    color:var(--ink);
  }
  .msg-filter button.active{ background:var(--gold); border-color:var(--gold-dark); }
  .msg-filter button:hover{ border-color:var(--gold-dark); }

  .msg-list{ display:flex; flex-direction:column; gap:0.7rem; }

  .msg-card{
    display:flex; gap:0.9rem; align-items:flex-start;
    background:#fff; border:1px solid var(--line); border-radius:14px;
    padding:1rem 1.1rem; position:relative;
  }
  .msg-card.unread{ background:#FFFDF4; border-color:var(--gold-dark); }
  .msg-card.unread::before{
    content:''; position:absolute; left:-1px; top:14px; bottom:14px; width:3px;
    background:var(--gold); border-radius:2px;
  }

  .msg-icon{
    flex:0 0 auto; width:38px; height:38px; border-radius:10px;
    background:var(--ink); color:var(--gold); display:flex; align-items:center; justify-content:center;
  }
  .msg-icon svg{ width:18px; height:18px; }

  .msg-body{ flex:1; min-width:0; }
  .msg-top-row{
    display:flex; align-items:center; justify-content:space-between; gap:0.6rem; flex-wrap:wrap;
  }
  .msg-title{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.88rem; }
  .msg-tag{
    font-size:0.62rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em;
    color:rgba(20,15,12,0.55); background:#F1ECDD; padding:0.15rem 0.5rem; border-radius:999px;
  }
  .msg-text{ font-size:0.8rem; color:rgba(20,15,12,0.78); margin:0.35rem 0 0.5rem; line-height:1.5; }
  .msg-meta-row{
    display:flex; align-items:center; justify-content:space-between; gap:0.6rem; flex-wrap:wrap;
  }
  .msg-time{ font-size:0.7rem; color:rgba(20,15,12,0.5); }
  .msg-link{ font-size:0.72rem; font-weight:600; color:#8a5a05; }
  .msg-link:hover{ text-decoration:underline; }

  .msg-empty{ text-align:center; padding:3rem 1rem; color:rgba(20,15,12,0.55); font-size:0.85rem; }
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="/B1.php">&larr; Back to shop</a>
</div>

<div class="msg-wrap">

  <div class="msg-head">
    <div>
      <h1>My Messages</h1>
      <p>Order updates, promos, and restock alerts, all in one place.</p>
    </div>
    <?php if ($unreadCount > 0): ?>
      <span class="msg-unread-pill"><?= (int) $unreadCount ?> unread</span>
    <?php endif; ?>
  </div>

  <div class="msg-filter" id="msgFilter">
    <button type="button" class="active" data-filter="all">All</button>
    <button type="button" data-filter="order">Orders</button>
    <button type="button" data-filter="promo">Promos</button>
    <button type="button" data-filter="restock">Restocks</button>
    <button type="button" data-filter="system">Account</button>
  </div>

  <div class="msg-list" id="msgList">
  <?php if (empty($messages)): ?>
    <div class="msg-empty">No messages yet. Come back soon.</div>
  <?php else: ?>
    <?php foreach ($messages as $m): ?>
      <div class="msg-card<?= empty($m['read']) ? ' unread' : '' ?>" data-type="<?= htmlspecialchars($m['type'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="msg-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= lnx_msg_icon($m['type']) ?></svg>
        </div>
        <div class="msg-body">
          <div class="msg-top-row">
            <div class="msg-title"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <span class="msg-tag"><?= htmlspecialchars(lnx_msg_label($m['type']), ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <p class="msg-text"><?= htmlspecialchars($m['body'], ENT_QUOTES, 'UTF-8') ?></p>
          <div class="msg-meta-row">
            <span class="msg-time"><?= htmlspecialchars(lnx_time_ago($m['time']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php if (!empty($m['link'])): ?>
              <a class="msg-link" href="<?= htmlspecialchars($m['link'], ENT_QUOTES, 'UTF-8') ?>">View &rarr;</a>
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
  var buttons = document.querySelectorAll('#msgFilter button');
  var cards   = document.querySelectorAll('#msgList .msg-card');

  buttons.forEach(function(btn){
    btn.addEventListener('click', function(){
      buttons.forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var filter = btn.getAttribute('data-filter');
      cards.forEach(function(card){
        var show = (filter === 'all') || (card.getAttribute('data-type') === filter);
        card.style.display = show ? '' : 'none';
      });
    });
  });
})();
</script>

</body>
</html>
