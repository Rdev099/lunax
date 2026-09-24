<?php
/* ============================================================================
   LUNAX — Personal Centre sidebar (personal-centre.php)
   ----------------------------------------------------------------------------
   Sidebar navigation only, structured like the reference screenshot:
     My Account  -> LUNAX Club, LUNAX Saver, LUNAX VIP, My Profile, Address
                    Book, My Payment Options, My Measurements, Manage My Account
     My Assets   -> (collapsed group, placeholder items)
     My Orders   -> All Orders, Unpaid Orders, Processing Orders, Shipped
                    Orders, Review Orders, Return Orders

   Real pages already built (my-orders.php, my-messages.php) are wired in.
   Everything else points to '#' as a placeholder until those pages exist.
   Groups collapse/expand on click, same as the reference UI (+/- indicator).
   ========================================================================== */
session_start();

$pageTitle = 'Personal Centre — LUNAX';

// Set active item by key so the correct link gets highlighted. Pass via
// ?active=my-orders etc, or hardcode here per-page when you split this
// sidebar out into an include.
$active = isset($_GET['active']) ? filter_input(INPUT_GET, 'active', FILTER_SANITIZE_STRING) : '';
// Additional validation: only allow alphanumeric, hyphens, and underscores
if ($active && !preg_match('/^[a-zA-Z0-9_-]+$/', $active)) {
    $active = '';
}

// All links now point back to THIS file with ?active=... so the sidebar
// stays put and only the right-hand panel content changes on reload.
$nav = [
    'my-account' => [
        'label' => 'My Account',
        'open'  => true,
        'items' => [
            ['key' => 'my-profile',   'label' => 'My Profile',        'badge' => '+POINTS'],
            ['key' => 'address-book', 'label' => 'Address Book',      'badge' => ''],
            ['key' => 'payment',      'label' => 'My Payment Options','badge' => ''],
            ['key' => 'measurements', 'label' => 'My Measurements',   'badge' => ''],
            ['key' => 'manage',       'label' => 'Manage My Account', 'badge' => '+POINTS'],
        ],
    ],
    'my-assets' => [
        'label' => 'My Assets',
        'open'  => false,
        'items' => [
            ['key' => 'wishlist',    'label' => 'My Wishlist',  'badge' => ''],
            ['key' => 'my-vouchers', 'label' => 'My Vouchers', 'badge' => ''],
            ['key' => 'my-points',   'label' => 'My Points',   'badge' => ''],
            ['key' => 'my-wallet',   'label' => 'My Wallet',   'badge' => ''],
        ],
    ],
    'my-messages' => [
        'label' => 'My Messages',
        'open'  => false,
        'items' => [
            ['key' => 'inbox', 'label' => 'Inbox', 'badge' => ''],
        ],
    ],
    'my-orders' => [
        'label' => 'My Orders',
        'open'  => true,
        'items' => [
            ['key' => 'all-orders',        'label' => 'All Orders',        'badge' => ''],
            ['key' => 'unpaid-orders',     'label' => 'Unpaid Orders',     'badge' => ''],
            ['key' => 'processing-orders', 'label' => 'Processing Orders', 'badge' => ''],
            ['key' => 'shipped-orders',    'label' => 'Shipped Orders',    'badge' => ''],
            ['key' => 'review-orders',     'label' => 'Review Orders',     'badge' => ''],
            ['key' => 'return-orders',     'label' => 'Return Orders',     'badge' => ''],
        ],
    ],
    'recently-viewed' => [
        'label' => 'Recently Viewed',
        'open'  => false,
        'items' => [
            ['key' => 'recently-viewed-list', 'label' => 'Recently Viewed', 'badge' => ''],
        ],
    ],
];

// ---- Sample order data (same shape as before; swap for a DB query later) --
$allOrders = [
    ['id' => 'LNX-10493', 'placed_at' => '2026-08-29', 'status' => 'delivered',  'total' => 214.99,
        'items' => [
            ['name' => 'Pulse Wireless Earbuds Pro, ANC + 30h battery', 'qty' => 1, 'price' => 49.99],
            ['name' => 'Orbit 6.7" AMOLED Phone, 256GB, 5G',            'qty' => 1, 'price' => 165.00],
        ]],
    ['id' => 'LNX-10471', 'placed_at' => '2026-08-14', 'status' => 'shipped',     'total' => 649.00,
        'items' => [['name' => 'NovaBook Air 14", 16GB / 512GB SSD', 'qty' => 1, 'price' => 649.00]]],
    ['id' => 'LNX-10402', 'placed_at' => '2026-07-30', 'status' => 'processing', 'total' => 84.00,
        'items' => [['name' => 'Signal Drop Autumn Edition — Charging Dock', 'qty' => 2, 'price' => 42.00]]],
    ['id' => 'LNX-10355', 'placed_at' => '2026-07-02', 'status' => 'unpaid',     'total' => 132.43,
        'items' => [['name' => 'Signal Drop — Smart Speaker', 'qty' => 1, 'price' => 132.43]]],
];

// ---- Sample messages (same shape as my-messages.php) -----------------------
$allMessages = [
    ['id' => 1, 'type' => 'order',   'title' => 'Your order has shipped',
        'body' => 'Order #LNX-10471 (NovaBook Air 14") is on its way. Estimated arrival Sep 12.',
        'time' => '2026-09-08 09:14:00', 'read' => false],
    ['id' => 2, 'type' => 'restock', 'title' => 'Back in stock: Pulse Wireless Earbuds Pro',
        'body' => 'An item on your wishlist is available again. Grab it before it sells out.',
        'time' => '2026-09-07 18:40:00', 'read' => false],
    ['id' => 3, 'type' => 'promo',   'title' => 'Signal Drop — Autumn Edition is live',
        'body' => 'Up to 60% off audio and accessories. Use code LUNAX60 at checkout.',
        'time' => '2026-09-05 08:00:00', 'read' => true],
];

// ---- Sample vouchers (swap for a DB query later) ---------------------------
$allVouchers = [
    ['code' => 'LUNAX60',   'label' => '60% off Audio',         'desc' => 'Valid on all headphones and earbuds.', 'expires' => '2026-09-30', 'status' => 'active'],
    ['code' => 'WELCOME10', 'label' => '$10 off your next order','desc' => 'Minimum spend $50.',                   'expires' => '2026-10-15', 'status' => 'active'],
    ['code' => 'FREESHIP',  'label' => 'Free shipping',          'desc' => 'No minimum spend required.',            'expires' => '2026-09-20', 'status' => 'active'],
    ['code' => 'STUDENT25', 'label' => '25% student discount',   'desc' => 'Requires valid student ID verification.', 'expires' => '2026-08-01', 'status' => 'expired'],
    ['code' => 'SUMMER15',  'label' => '15% off Summer Sale',    'desc' => 'Applied automatically at checkout.',    'expires' => '2026-07-15', 'status' => 'used'],
];

// ---- Sample points activity (swap for a DB query later) --------------------
$pointsBalance = 1280;
$pointsHistory = [
    ['label' => 'Order #LNX-10493 delivered', 'points' => 65,   'time' => '2026-08-30'],
    ['label' => 'Referral bonus — friend joined LUNAX', 'points' => 200, 'time' => '2026-08-20'],
    ['label' => 'Order #LNX-10471 placed', 'points' => 130,  'time' => '2026-08-14'],
    ['label' => 'Redeemed for WELCOME10 voucher', 'points' => -500, 'time' => '2026-08-10'],
    ['label' => 'Order #LNX-10402 placed', 'points' => 42,   'time' => '2026-07-30'],
];

// ---- Sample recently viewed products (swap for a DB/session query later) --
$recentlyViewed = [
    ['name' => 'Pulse Wireless Earbuds Pro, ANC + 30h battery', 'price' => 49.99,  'viewed' => '2 hours ago'],
    ['name' => 'Orbit 6.7" AMOLED Phone, 256GB, 5G',             'price' => 389.00, 'viewed' => '5 hours ago'],
    ['name' => 'NovaBook Air 14", 16GB / 512GB SSD',             'price' => 649.00, 'viewed' => 'Yesterday'],
    ['name' => 'Signal Drop — Smart Speaker',                    'price' => 132.43, 'viewed' => '2 days ago'],
];

function lnx_money($v) { return '$' . number_format((float) $v, 2); }
function lnx_date($v)  { $ts = strtotime($v); return $ts ? date('M j, Y', $ts) : $v; }

function lnx_order_status_label($s)
{
    $map = ['delivered' => 'Delivered', 'shipped' => 'Shipped', 'processing' => 'Processing', 'unpaid' => 'Unpaid'];
    return $map[$s] ?? ucfirst($s);
}
function lnx_order_status_class($s)
{
    $map = ['delivered' => 'st-delivered', 'shipped' => 'st-shipped', 'processing' => 'st-processing', 'unpaid' => 'st-unpaid'];
    return $map[$s] ?? '';
}

// Map each sidebar item key to which order status it should filter by
// (null = show all). This is what makes "Unpaid Orders" etc. actually filter.
$orderStatusFilters = [
    'all-orders'        => null,
    'unpaid-orders'     => 'unpaid',
    'processing-orders' => 'processing',
    'shipped-orders'    => 'shipped',
    'review-orders'     => 'delivered', // delivered orders are eligible for review
    'return-orders'     => null,        // no return data yet — placeholder
];

// Simple icon set (line style, matches LUNAX's existing icon language).
function lnx_pc_icon($key)
{
    $icons = [
        'lunax-club'          => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'lunax-saver'         => '<path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'lunax-vip'           => '<path d="M4 6l4 4 4-6 4 6 4-4-2 12H6z"/>',
        'my-profile'          => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
        'address-book'        => '<path d="M12 21s-7-5.3-7-10a7 7 0 0 1 14 0c0 4.7-7 10-7 10Z"/><circle cx="12" cy="11" r="2.3"/>',
        'payment'             => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 9h20"/>',
        'measurements'        => '<path d="M4 17 17 4l3 3-13 13H4Z"/><path d="M13 8l3 3M9 12l3 3"/>',
        'manage'              => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/><path d="M17 15l1.5 1.5L21 14"/>',
        'my-vouchers'         => '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4Z"/>',
        'my-points'           => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'my-wallet'           => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h.01"/><path d="M3 10h18"/>',
        'inbox'               => '<path d="M4 5h13v14H6a2 2 0 0 1-2-2z"/><path d="M17 8h3v9a2 2 0 0 1-2 2"/><path d="M7 8h7M7 11h7M7 14h5"/>',
        'all-orders'          => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h5"/>',
        'unpaid-orders'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h2"/>',
        'processing-orders'   => '<rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'shipped-orders'      => '<rect x="2" y="8" width="13" height="9" rx="1"/><path d="M15 11h4l3 3v3h-7z"/><circle cx="7" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/>',
        'review-orders'       => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9.5 13l1.8 1.8L15 11"/>',
        'return-orders'       => '<path d="M4 8h11a4 4 0 0 1 0 8H9"/><path d="M8 12 4 8l4-4"/>',
        'recently-viewed-list'=> '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    ];
    return $icons[$key] ?? '<circle cx="12" cy="12" r="9"/>';
}

if (!function_exists('lnx_msg_icon')) {
    function lnx_msg_icon(string $type): string
    {
        $icons = [
            'order'   => '<path d="M4 5h13v14H6a2 2 0 0 1-2-2z"/><path d="M17 8h3v9a2 2 0 0 1-2 2"/><path d="M7 8h7M7 11h7M7 14h5"/>',
            'promo'   => '<path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/>',
            'restock' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3v4M8 3v4M2 11h20"/>',
        ];
        return $icons[$type] ?? '<circle cx="12" cy="12" r="9"/>';
    }
}

if (!function_exists('lnx_time_ago')) {
    function lnx_time_ago(?string $ts): string
    {
        $t = strtotime((string) $ts);
        if (!$t) { return ''; }
        $diff = time() - $t;
        if ($diff < 3600)  { return max(1, (int) floor($diff / 60)) . ' min ago'; }
        if ($diff < 86400) { return (int) floor($diff / 3600) . ' hr ago'; }
        return (int) floor($diff / 86400) . ' day(s) ago';
    }
}

if (!function_exists('lnx_voucher_status_label')) {
    function lnx_voucher_status_label(string $s): string
    {
        $map = ['active' => 'Active', 'used' => 'Used', 'expired' => 'Expired'];
        return $map[$s] ?? ucfirst($s);
    }
}

if (!function_exists('lnx_voucher_status_class')) {
    function lnx_voucher_status_class(string $s): string
    {
        $map = ['active' => 'st-delivered', 'used' => 'st-processing', 'expired' => 'st-unpaid'];
        return $map[$s] ?? '';
    }
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
    --gold:#F2C94C;
    --gold-dark:#D1A83B;
    --cream:#FBF6EC;
    --line:rgba(20,15,12,0.10);
    --muted:rgba(20,15,12,0.55);
  }
  *{ box-sizing:border-box; }
  body{
    margin:0; background:#fff; color:var(--ink);
    font-family:'JetBrains Mono', monospace; font-size:0.85rem;
  }
  a{ color:inherit; text-decoration:none; }

  .lnx-topbar{
    background:var(--ink); color:#fff; padding:0.9rem 1.75rem;
    display:flex; align-items:center; justify-content:space-between;
  }
  .lnx-logo{ font-family:'Space Grotesk',sans-serif; font-weight:700; letter-spacing:0.12em; font-size:1.1rem; }
  .lnx-back{ font-size:0.78rem; color:var(--gold); }
  .lnx-back:hover{ text-decoration:underline; }

  .lnx-crumb{
    padding:0.9rem 1.75rem; font-size:0.78rem; color:var(--muted);
    border-bottom:1px solid var(--line);
  }
  .lnx-crumb a:hover{ text-decoration:underline; }

  .pc-layout{
    max-width:1200px; margin:0 auto; display:flex; gap:2.5rem;
    padding:2rem 1.75rem 4rem; align-items:flex-start;
  }

  .pc-sidebar{ flex:0 0 300px; }
  .pc-title{
    font-family:'Space Grotesk',sans-serif; font-size:1.4rem; font-weight:700;
    margin:0 0 1.2rem;
  }

  .pc-group{ margin-bottom:0.4rem; }
  .pc-group-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:0.6rem 0; font-family:'Space Grotesk',sans-serif; font-weight:700;
    font-size:0.95rem; cursor:pointer; user-select:none;
  }
  .pc-group-toggle{
    width:18px; height:18px; border-radius:4px; border:1px solid var(--line);
    display:flex; align-items:center; justify-content:center; font-size:0.9rem;
    color:var(--muted); flex:0 0 auto;
  }
  .pc-group-items{
    list-style:none; margin:0; padding:0 0 0.4rem;
    display:flex; flex-direction:column;
  }
  .pc-group.collapsed .pc-group-items{ display:none; }
  .pc-group.collapsed .pc-group-toggle{ transform:none; }

  .pc-item{
    display:flex; align-items:center; gap:0.6rem;
    padding:0.55rem 0.3rem 0.55rem 0.2rem; border-radius:8px;
    color:rgba(20,15,12,0.78);
  }
  .pc-item:hover{ background:#FAF7EE; color:var(--ink); }
  .pc-item.active{ background:#FFF7DE; color:var(--ink); font-weight:600; }
  .pc-item svg{ width:16px; height:16px; flex:0 0 auto; color:rgba(20,15,12,0.55); }
  .pc-item.active svg{ color:var(--gold-dark); }

  .pc-item-label{ flex:1; min-width:0; }
  .pc-badge{
    font-size:0.6rem; font-weight:700; letter-spacing:0.02em;
    padding:0.12rem 0.4rem; border-radius:5px; white-space:nowrap;
    background:#F1ECDD; color:#8a5a05;
  }
  .pc-badge.points{ background:#FDEDE3; color:#B5551A; }

  .pc-divider{ height:1px; background:var(--line); margin:0.8rem 0; }

  .pc-main{
    flex:1; min-width:0; background:#fff;
  }
  .pc-main h2{
    font-family:'Space Grotesk',sans-serif; font-size:1.05rem; margin:0 0 0.7rem;
    text-transform:uppercase; letter-spacing:0.03em;
  }
  .pc-main p{ color:rgba(20,15,12,0.7); line-height:1.6; margin:0 0 1rem; max-width:640px; }
  .pc-main-empty{
    border:1px dashed var(--line); border-radius:12px; padding:2.5rem 1.5rem;
    text-align:center; color:var(--muted); font-size:0.85rem;
  }

  /* Order cards (rendered inline in the main panel) */
  .order-list{ display:flex; flex-direction:column; gap:1rem; }
  .order-card{ background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
  .order-card-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:0.9rem 1.1rem; background:#FAF7EE; border-bottom:1px solid var(--line); flex-wrap:wrap; gap:0.6rem;
  }
  .order-id{ font-weight:700; font-size:0.85rem; }
  .order-meta{ font-size:0.72rem; color:var(--muted); }
  .order-status{
    font-family:'Space Grotesk',sans-serif; font-size:0.65rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.04em; padding:0.25rem 0.7rem; border-radius:999px;
  }
  .st-delivered{ color:#0d5c37; background:rgba(26,156,92,0.15); }
  .st-shipped{ color:#8a5a05; background:rgba(242,201,76,0.25); }
  .st-processing{ color:#1a4d8f; background:rgba(52,120,214,0.15); }
  .st-unpaid{ color:#7a1b21; background:rgba(209,55,63,0.15); }
  .order-items{ padding:0.9rem 1.1rem; }
  .order-item{ display:flex; justify-content:space-between; align-items:center; padding:0.4rem 0; font-size:0.8rem; }
  .order-item + .order-item{ border-top:1px dashed var(--line); }
  .order-item-qty{ color:var(--muted); font-size:0.72rem; margin-left:0.4rem; }
  .order-item-price{ font-weight:600; white-space:nowrap; }
  .order-card-foot{ display:flex; justify-content:flex-end; padding:0.8rem 1.1rem; border-top:1px solid var(--line); }
  .order-total{ font-size:0.85rem; font-weight:700; }
  .order-total span{ font-weight:400; color:var(--muted); margin-right:0.4rem; }

  /* Message cards (rendered inline in the main panel) */
  .msg-list{ display:flex; flex-direction:column; gap:0.7rem; }
  .msg-card{ display:flex; gap:0.9rem; background:#fff; border:1px solid var(--line); border-radius:14px; padding:1rem 1.1rem; position:relative; }
  .msg-card.unread{ background:#FFFDF4; border-color:var(--gold-dark); }
  .msg-icon{ flex:0 0 auto; width:36px; height:36px; border-radius:10px; background:var(--ink); color:var(--gold); display:flex; align-items:center; justify-content:center; }
  .msg-icon svg{ width:17px; height:17px; }
  .msg-title{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; }
  .msg-text{ font-size:0.8rem; color:rgba(20,15,12,0.78); margin:0.3rem 0 0.4rem; line-height:1.5; }
  .msg-time{ font-size:0.7rem; color:var(--muted); }

  /* Vouchers */
  .voucher-list{ display:flex; flex-direction:column; gap:0.8rem; }
  .voucher-card{
    display:flex; justify-content:space-between; align-items:center; gap:1rem;
    background:#fff; border:1px dashed var(--gold-dark); border-radius:14px; padding:1rem 1.2rem;
  }
  .voucher-card.voucher-inactive{ border-style:solid; border-color:var(--line); opacity:0.6; }
  .voucher-code{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.95rem; letter-spacing:0.03em; }
  .voucher-label{ font-size:0.82rem; font-weight:600; margin-top:0.15rem; }
  .voucher-desc{ font-size:0.75rem; color:rgba(20,15,12,0.7); margin-top:0.2rem; }
  .voucher-expiry{ font-size:0.7rem; color:var(--muted); margin-top:0.35rem; }
  .voucher-right{ display:flex; flex-direction:column; align-items:flex-end; gap:0.5rem; flex:0 0 auto; }
  .voucher-use-btn{
    font-family:'JetBrains Mono',monospace; font-size:0.7rem; font-weight:700;
    padding:0.35rem 0.8rem; border-radius:8px; border:1px solid var(--gold-dark);
    background:var(--gold); cursor:pointer; text-transform:uppercase; letter-spacing:0.03em;
  }
  .voucher-use-btn:hover{ background:var(--gold-dark); }

  /* Points */
  .points-balance-card{
    background:var(--ink); color:#fff; border-radius:14px; padding:1.4rem 1.5rem; margin-bottom:1.2rem;
  }
  .points-balance-label{ font-size:0.75rem; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:0.04em; }
  .points-balance-value{ font-family:'Space Grotesk',sans-serif; font-size:2rem; font-weight:700; margin-top:0.3rem; }
  .points-balance-value span{ font-size:1rem; font-weight:500; color:var(--gold); }
  .points-history{ display:flex; flex-direction:column; }
  .points-row{
    display:flex; justify-content:space-between; align-items:center;
    padding:0.75rem 0; border-bottom:1px solid var(--line);
  }
  .points-row-label{ font-size:0.82rem; font-weight:600; }
  .points-row-date{ font-size:0.7rem; color:var(--muted); margin-top:0.15rem; }
  .points-row-value{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.9rem; white-space:nowrap; }
  .points-row-value.positive{ color:#0d5c37; }
  .points-row-value.negative{ color:#7a1b21; }

  /* Recently viewed */
  .rv-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:1rem; }
  .rv-card{ background:#fff; border:1px solid var(--line); border-radius:12px; padding:0.8rem; }
  .rv-thumb{ background:#F1ECDD; border-radius:8px; aspect-ratio:1; margin-bottom:0.6rem; }
  .rv-name{ font-size:0.78rem; font-weight:600; line-height:1.35; margin-bottom:0.3rem; }
  .rv-price{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; color:#8a5a05; }
  .rv-viewed{ font-size:0.68rem; color:var(--muted); margin-top:0.25rem; }

  @media (max-width: 860px){
    .pc-layout{ flex-direction:column; }
    .pc-sidebar{ flex:1 1 auto; width:100%; }
  }
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="/B1.php">&larr; Back to shop</a>
</div>

<div class="lnx-crumb"><a href="PHP/home.php">Home</a> / Personal Centre</div>

<div class="pc-layout">

  <aside class="pc-sidebar">
    <h1 class="pc-title">Personal Centre</h1>

    <?php foreach ($nav as $groupKey => $group): ?>
      <div class="pc-group<?= $group['open'] ? '' : ' collapsed' ?>" data-group="<?= htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8') ?>">
        <div class="pc-group-head" data-toggle>
          <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="pc-group-toggle"><?= $group['open'] ? '&minus;' : '+' ?></span>
        </div>
        <ul class="pc-group-items">
          <?php foreach ($group['items'] as $item): ?>
            <li>
              <a class="pc-item<?= $active === $item['key'] ? ' active' : '' ?>" href="?active=<?= urlencode($item['key']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= lnx_pc_icon($item['key']) ?></svg>
                <span class="pc-item-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($item['badge'])): ?>
                  <span class="pc-badge<?= $item['badge'] === '+POINTS' ? ' points' : '' ?>"><?= htmlspecialchars($item['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="pc-divider"></div>
      </div>
    <?php endforeach; ?>
  </aside>

  <main class="pc-main">
  <?php if (array_key_exists($active, $orderStatusFilters)): ?>
    <?php
      $filterStatus = $orderStatusFilters[$active];
      $shown = $filterStatus === null
        ? $allOrders
        : array_values(array_filter($allOrders, fn($o) => $o['status'] === $filterStatus));
      $headings = [
        'all-orders' => 'All Orders', 'unpaid-orders' => 'Unpaid Orders',
        'processing-orders' => 'Processing Orders', 'shipped-orders' => 'Shipped Orders',
        'review-orders' => 'Review Orders', 'return-orders' => 'Return Orders',
      ];
    ?>
    <h2><?= htmlspecialchars($headings[$active], ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if (empty($shown)): ?>
      <div class="pc-main-empty">No orders in this category yet.</div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($shown as $o): ?>
          <div class="order-card">
            <div class="order-card-head">
              <div>
                <div class="order-id">Order #<?= htmlspecialchars($o['id'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="order-meta">Placed <?= htmlspecialchars(lnx_date($o['placed_at']), ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <span class="order-status <?= lnx_order_status_class($o['status']) ?>">
                <?= htmlspecialchars(lnx_order_status_label($o['status']), ENT_QUOTES, 'UTF-8') ?>
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
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($active === 'inbox'): ?>
    <h2>My Messages</h2>
    <?php if (empty($allMessages)): ?>
      <div class="pc-main-empty">No messages yet.</div>
    <?php else: ?>
      <div class="msg-list">
        <?php foreach ($allMessages as $m): ?>
          <div class="msg-card<?= empty($m['read']) ? ' unread' : '' ?>">
            <div class="msg-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= lnx_msg_icon($m['type']) ?></svg>
            </div>
            <div class="msg-body">
              <div class="msg-title"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></div>
              <p class="msg-text"><?= htmlspecialchars($m['body'], ENT_QUOTES, 'UTF-8') ?></p>
              <span class="msg-time"><?= htmlspecialchars(lnx_time_ago($m['time']), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($active === 'wishlist'): ?>
    <h2>My Wishlist</h2>
    <div class="wishlist-summary">
      <div class="wishlist-stat">
        <span class="wishlist-stat-label">Saved items</span>
        <strong>4</strong>
      </div>
      <div class="wishlist-stat">
        <span class="wishlist-stat-label">Average discount</span>
        <strong>18%</strong>
      </div>
      <div class="wishlist-stat">
        <span class="wishlist-stat-label">Next delivery</span>
        <strong>2 days</strong>
      </div>
    </div>
    <div class="wishlist-list">
      <div class="wishlist-item">
        <div class="wishlist-thumb thumb-a"></div>
        <div class="wishlist-details">
          <div class="wishlist-product">Pulse Wireless Earbuds Pro</div>
          <div class="wishlist-meta">Midnight · In stock · 4.9/5 rating</div>
          <div class="wishlist-price-row">
            <span class="wishlist-price">$49.99</span>
            <span class="wishlist-old">$69.99</span>
          </div>
        </div>
        <div class="wishlist-actions">
          <button type="button" class="wishlist-btn primary">Add to cart</button>
          <button type="button" class="wishlist-btn">Remove</button>
        </div>
      </div>
      <div class="wishlist-item">
        <div class="wishlist-thumb thumb-b"></div>
        <div class="wishlist-details">
          <div class="wishlist-product">Orbit 6.7" AMOLED Phone</div>
          <div class="wishlist-meta">Ocean Blue · 256GB · Free shipping</div>
          <div class="wishlist-price-row">
            <span class="wishlist-price">$389.00</span>
            <span class="wishlist-old">$429.00</span>
          </div>
        </div>
        <div class="wishlist-actions">
          <button type="button" class="wishlist-btn primary">Add to cart</button>
          <button type="button" class="wishlist-btn">Remove</button>
        </div>
      </div>
      <div class="wishlist-item">
        <div class="wishlist-thumb thumb-c"></div>
        <div class="wishlist-details">
          <div class="wishlist-product">NovaBook Air 14"</div>
          <div class="wishlist-meta">16GB / 512GB SSD · Ready to ship</div>
          <div class="wishlist-price-row">
            <span class="wishlist-price">$649.00</span>
            <span class="wishlist-old">$799.00</span>
          </div>
        </div>
        <div class="wishlist-actions">
          <button type="button" class="wishlist-btn primary">Add to cart</button>
          <button type="button" class="wishlist-btn">Remove</button>
        </div>
      </div>
      <div class="wishlist-item">
        <div class="wishlist-thumb thumb-d"></div>
        <div class="wishlist-details">
          <div class="wishlist-product">Signal Drop Smart Speaker</div>
          <div class="wishlist-meta">Walnut finish · Voice assistant ready</div>
          <div class="wishlist-price-row">
            <span class="wishlist-price">$132.43</span>
            <span class="wishlist-old">$149.99</span>
          </div>
        </div>
        <div class="wishlist-actions">
          <button type="button" class="wishlist-btn primary">Add to cart</button>
          <button type="button" class="wishlist-btn">Remove</button>
        </div>
      </div>
    </div>

  <?php elseif ($active === 'my-vouchers'): ?>
    <h2>My Vouchers</h2>
    <?php if (empty($allVouchers)): ?>
      <div class="pc-main-empty">No vouchers yet.</div>
    <?php else: ?>
      <div class="voucher-list">
        <?php foreach ($allVouchers as $v): ?>
          <div class="voucher-card<?= $v['status'] !== 'active' ? ' voucher-inactive' : '' ?>">
            <div class="voucher-left">
              <div class="voucher-code"><?= htmlspecialchars($v['code'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="voucher-label"><?= htmlspecialchars($v['label'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="voucher-desc"><?= htmlspecialchars($v['desc'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="voucher-expiry">Expires <?= htmlspecialchars(lnx_date($v['expires']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="voucher-right">
              <span class="order-status <?= lnx_voucher_status_class($v['status']) ?>">
                <?= htmlspecialchars(lnx_voucher_status_label($v['status']), ENT_QUOTES, 'UTF-8') ?>
              </span>
              <?php if ($v['status'] === 'active'): ?>
                <button type="button" class="voucher-use-btn" data-code="<?= htmlspecialchars($v['code'], ENT_QUOTES, 'UTF-8') ?>">Use now</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($active === 'my-points'): ?>
    <h2>My Points</h2>
    <div class="points-balance-card">
      <div class="points-balance-label">Available balance</div>
      <div class="points-balance-value"><?= number_format($pointsBalance) ?> <span>pts</span></div>
    </div>
    <?php if (empty($pointsHistory)): ?>
      <div class="pc-main-empty">No points activity yet.</div>
    <?php else: ?>
      <div class="points-history">
        <?php foreach ($pointsHistory as $p): ?>
          <div class="points-row">
            <div>
              <div class="points-row-label"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="points-row-date"><?= htmlspecialchars(lnx_date($p['time']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="points-row-value<?= $p['points'] < 0 ? ' negative' : ' positive' ?>">
              <?= $p['points'] > 0 ? '+' : '' ?><?= number_format($p['points']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($active === 'recently-viewed-list'): ?>
    <h2>Recently Viewed</h2>
    <?php if (empty($recentlyViewed)): ?>
      <div class="pc-main-empty">You haven't viewed any products yet.</div>
    <?php else: ?>
      <div class="rv-grid">
        <?php foreach ($recentlyViewed as $r): ?>
          <div class="rv-card">
            <div class="rv-thumb"></div>
            <div class="rv-name"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="rv-price"><?= lnx_money($r['price']) ?></div>
            <div class="rv-viewed">Viewed <?= htmlspecialchars($r['viewed'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($active !== ''): ?>
    <h2><?= htmlspecialchars(ucwords(str_replace('-', ' ', $active)), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="pc-main-empty">This section hasn't been built yet.</div>

  <?php else: ?>
    <h2>Welcome back</h2>
    <p>Pick a section from the left to manage your account, view orders, or check your messages.</p>
    <div class="pc-main-empty">Select an item from Personal Centre to get started.</div>
  <?php endif; ?>
  </main>

</div>

<script>
// Collapse/expand groups, mirroring the +/- behavior in the reference UI.
(function(){
  document.querySelectorAll('.pc-group-head[data-toggle]').forEach(function(head){
    head.addEventListener('click', function(){
      var group = head.closest('.pc-group');
      var toggle = head.querySelector('.pc-group-toggle');
      var collapsed = group.classList.toggle('collapsed');
      toggle.innerHTML = collapsed ? '+' : '&minus;';
    });
  });

  // Voucher "Use now" — copies the code to clipboard as a simple placeholder
  // action until this is wired to actually apply the voucher at checkout.
  document.querySelectorAll('.voucher-use-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      var code = btn.getAttribute('data-code');
      if (navigator.clipboard) {
        navigator.clipboard.writeText(code);
      }
      var original = btn.textContent;
      btn.textContent = 'Copied!';
      setTimeout(function(){ btn.textContent = original; }, 1500);
    });
  });
})();
</script>

</body>
</html>
