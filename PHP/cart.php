<?php
/* ============================================================================
   LUNAX — Shopping Cart (cart.php) · v2 full-detail redesign
   --------------------------------------------------------------------------
   • Session cart, AJAX actions: add / update / remove / save / restore / promo
   • Item shape: ['name','price','qty','image','icon','meta','sku','stock']
   • Promo codes live in PROMOS below. One active code per session.
   • Set LNX_DEMO_SEED to false in production (it seeds a realistic cart so
     the page is fully testable out of the box).
   ========================================================================== */
session_start();

define('FREE_SHIP',     50.00);
define('SHIP_RATE',     6.99);
define('TAX_RATE',      0.08);          // set 0 to disable tax row
define('LNX_DEMO_SEED', true);          // set false when wired to real catalog

const PROMOS = [
    'LUNAX10'  => ['type' => 'pct',   'value' => 10, 'label' => '10% off your order'],
    'WELCOME5' => ['type' => 'fixed', 'value' => 5,  'label' => '$5 off your order'],
];

// ── Real catalog items (demo seed + recommendations) ─────────────────────────
function lnx_catalog(): array {
    return [
        101 => ['name' => 'Pulse Wireless Earbuds Pro', 'price' => 129.00, 'qty' => 1,
                'meta' => 'Midnight Black · ANC · 36h battery', 'sku' => 'LNX-AUD-0142', 'stock' => 'in',
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=240&q=70'],
        102 => ['name' => 'AeroBook Pro 14" Laptop', 'price' => 1199.00, 'qty' => 1,
                'meta' => 'Space Grey · 16GB RAM · 512GB SSD', 'sku' => 'LNX-LAP-0014', 'stock' => 'in',
                'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=240&q=70'],
        103 => ['name' => 'Nebula 27" 4K UHD Monitor', 'price' => 449.00, 'qty' => 1,
                'meta' => 'IPS · 144Hz · HDR600 · USB-C 90W', 'sku' => 'LNX-MON-0027', 'stock' => 'low',
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=240&q=70'],
    ];
}

function lnx_recommendations(): array {
    return [
        201 => ['name' => 'Orbit X2 Camera Drone', 'price' => 499.00, 'tag' => 'Best seller',
                'image' => 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?auto=format&fit=crop&w=400&q=70'],
        202 => ['name' => 'Halo Over-Ear Headphones', 'price' => 189.00, 'tag' => 'New',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=70'],
        203 => ['name' => 'MechKeys TKL Keyboard', 'price' => 99.00, 'tag' => 'Hot',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=400&q=70'],
        204 => ['name' => 'Pulse Smartwatch S2', 'price' => 229.00, 'tag' => '',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=70'],
    ];
}

// Seed a realistic cart once per session so the page is demoable immediately.
if (LNX_DEMO_SEED && empty($_SESSION['cart']) && empty($_SESSION['lnx_seeded'])) {
    $_SESSION['cart']        = lnx_catalog();
    $_SESSION['lnx_seeded']  = true;
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function lnx_money(float $v): string {
    return ($v < 0 ? '-$' : '$') . number_format(abs($v), 2);
}

function lnx_totals(array $cart, ?string $promoCode): array {
    $subtotal = 0.0;
    foreach ($cart as $i) $subtotal += (float)$i['price'] * (int)$i['qty'];

    $discount = 0.0;
    $promo    = null;
    if ($promoCode && isset(PROMOS[$promoCode]) && $subtotal > 0) {
        $p = PROMOS[$promoCode];
        $discount = $p['type'] === 'pct'
            ? round($subtotal * $p['value'] / 100, 2)
            : min((float)$p['value'], $subtotal);
        $promo = ['code' => $promoCode, 'label' => $p['label']];
    }

    $shipping = ($subtotal <= 0.0 || $subtotal >= FREE_SHIP) ? 0.0 : SHIP_RATE;
    $taxable  = max(0.0, $subtotal - $discount);
    $tax      = round($taxable * TAX_RATE, 2);
    $total    = $taxable + $shipping + $tax;

    return [
        'subtotal' => $subtotal, 'discount' => $discount, 'shipping' => $shipping,
        'tax' => $tax, 'total' => $total, 'promo' => $promo,
    ];
}

// Formatted view-model sent to the browser after every mutation.
function lnx_view(): array {
    $cart  = $_SESSION['cart'] ?? [];
    $t     = lnx_totals($cart, $_SESSION['promo'] ?? null);
    $count = array_sum(array_column($cart, 'qty'));
    $saved = count($_SESSION['saved'] ?? []);

    $pct = $t['subtotal'] <= 0 ? 0 : min(100, round($t['subtotal'] / FREE_SHIP * 100));
    if ($t['subtotal'] <= 0)                $note = 'Add items to unlock free shipping';
    elseif ($t['subtotal'] >= FREE_SHIP)    $note = "You've unlocked free shipping";
    else                                    $note = 'Add ' . lnx_money(FREE_SHIP - $t['subtotal']) . ' more for free shipping';

    return [
        'ok'         => true,
        'itemCount'  => $count,
        'savedCount' => $saved,
        'totals'     => [
            'subtotal' => lnx_money($t['subtotal']),
            'discount' => $t['discount'] > 0 ? '-' . lnx_money($t['discount']) : null,
            'shipping' => $t['shipping'] > 0 ? lnx_money($t['shipping']) : 'FREE',
            'tax'      => lnx_money($t['tax']),
            'total'    => lnx_money($t['total']),
            'shipPct'  => $pct,
            'shipNote' => $note,
            'unlocked' => $t['subtotal'] >= FREE_SHIP && $t['subtotal'] > 0,
        ],
        'promo' => $t['promo'],
    ];
}

// ── AJAX (POST) ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $_SESSION['cart']  ??= [];
    $_SESSION['saved'] ??= [];

    $str  = fn(string $k) => strip_tags(trim((string)($_POST[$k] ?? '')));
    $int  = fn(string $k, int $min = 0) => max($min, (int)($_POST[$k] ?? 0));
    $flt  = fn(string $k) => (float)($_POST[$k] ?? 0);
    $fail = function (string $msg) { http_response_code(400); echo json_encode(['error' => $msg]); exit; };

    switch ($_POST['action']) {
        case 'add':
            $index = isset($_POST['index']) ? (int)$_POST['index'] : null;
            $name  = $str('name');
            $price = $flt('price');
            $qty   = max(1, $int('qty', 1));
            if ($index === null || $name === '' || $price <= 0) $fail('Invalid product data');
            if (isset($_SESSION['cart'][$index])) {
                $_SESSION['cart'][$index]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$index] = [
                    'name'  => $name,
                    'price' => $price,
                    'qty'   => $qty,
                    'image' => filter_var($str('image'), FILTER_SANITIZE_URL),
                    'icon'  => $str('icon'),
                    'meta'  => $str('meta'),
                    'sku'   => $str('sku'),
                    'stock' => 'in',
                ];
            }
            break;

        case 'update':
            $id  = $str('id');
            $qty = min(99, $int('qty', 0));
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

        case 'save':    // cart → saved for later
            $id = $str('id');
            if ($id === '' || !isset($_SESSION['cart'][$id])) $fail('Item not found');
            $_SESSION['saved'][$id] = $_SESSION['cart'][$id];
            unset($_SESSION['cart'][$id]);
            break;

        case 'restore': // saved for later → cart
            $id = $str('id');
            if ($id === '' || !isset($_SESSION['saved'][$id])) $fail('Item not found');
            if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]['qty'] += $_SESSION['saved'][$id]['qty'];
            else $_SESSION['cart'][$id] = $_SESSION['saved'][$id];
            unset($_SESSION['saved'][$id]);
            break;

        case 'promo':
            $code = strtoupper($str('code'));
            if ($code === '') { unset($_SESSION['promo']); break; }   // empty = remove
            if (!isset(PROMOS[$code])) $fail('That code is not valid');
            $_SESSION['promo'] = $code;
            break;

        default:
            $fail('Unknown action');
    }

    echo json_encode(lnx_view());
    exit;
}

// ── Page data ────────────────────────────────────────────────────────────────
$cart      = $_SESSION['cart']  ?? [];
$saved     = $_SESSION['saved'] ?? [];
$view      = lnx_view();
$T         = $view['totals'];
$promo     = $view['promo'];
$itemCount = $view['itemCount'];
$recos     = lnx_recommendations();

$e  = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$SVG_ALLOW = '<path><circle><rect><polyline><line><polygon><ellipse><g>';
$etaFrom = date('D, M j', strtotime('+4 days'));
$etaTo   = date('D, M j', strtotime('+6 days'));

// Renders one cart line item (shared by cart + saved-for-later lists).
function lnx_item_row(array $item, $id, callable $e, string $SVG_ALLOW, bool $isSaved): string {
    $stock = $item['stock'] ?? 'in';
    ob_start(); ?>
    <div class="cart-item <?= $isSaved ? 'is-saved' : '' ?>" data-id="<?= $e($id) ?>" data-price="<?= $e($item['price']) ?>" data-qty="<?= (int)$item['qty'] ?>">
      <div class="cart-item-thumb">
        <?php if (!empty($item['image'])): ?>
          <img src="<?= $e($item['image']) ?>" alt="<?= $e($item['name']) ?>" loading="lazy" onerror="lnxImgFallback(this)">
        <?php elseif (!empty($item['icon'])): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><?= strip_tags($item['icon'], $SVG_ALLOW) ?></svg>
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1h-13a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
        <?php endif; ?>
      </div>
      <div class="cart-item-body">
        <div class="cart-item-top">
          <div class="cart-item-name"><?= $e($item['name']) ?></div>
          <?php if (!$isSaved): ?><div class="cart-item-price"><?= lnx_money($item['price'] * $item['qty']) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($item['meta'])): ?><div class="cart-item-meta"><?= $e($item['meta']) ?></div><?php endif; ?>
        <div class="cart-item-sub">
          <?php if (!empty($item['sku'])): ?><span class="cart-item-sku">SKU <?= $e($item['sku']) ?></span><?php endif; ?>
          <?php if ($stock === 'low'): ?><span class="stock-badge low">Only a few left</span>
          <?php else: ?><span class="stock-badge">In stock</span><?php endif; ?>
        </div>
        <div class="cart-item-row">
          <?php if ($isSaved): ?>
            <span class="saved-price"><?= lnx_money($item['price']) ?></span>
            <span class="item-links">
              <button type="button" class="link-btn item-restore">Move to cart</button>
              <button type="button" class="link-btn danger item-remove">Remove</button>
            </span>
          <?php else: ?>
            <div class="qty-control">
              <button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">&minus;</button>
              <span class="qty-value"><?= (int)$item['qty'] ?></span>
              <button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>
            </div>
            <span class="unit-price"><?= lnx_money($item['price']) ?> each</span>
            <span class="item-links">
              <button type="button" class="link-btn item-save">Save for later</button>
              <button type="button" class="link-btn danger item-remove">Remove</button>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart — LUNAX</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#140F0C; --ink-soft:#3A332D; --gold:#F2C94C; --gold-d:#D1A83B;
  --cream:#FBF6EC; --paper:#FFFFFF; --sand:#F1ECDD;
  --line:rgba(20,15,12,.12); --muted:rgba(20,15,12,.55);
  --green:#0d5c37; --green-bg:rgba(26,156,92,.12); --amber:#8a5a05; --amber-bg:rgba(242,201,76,.22); --red:#7a1b21;
  --r-lg:16px; --r-md:10px; --r-sm:8px;
}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--ink);font-family:'JetBrains Mono',monospace;font-size:14px}
a{color:inherit;text-decoration:none}
button{font-family:inherit}
img{display:block}

/* ── Top bar ── */
.topbar{background:var(--ink);color:#fff;padding:0 1.75rem;height:58px;display:flex;align-items:center;gap:2rem;position:sticky;top:0;z-index:50}
.logo{font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:.14em;font-size:1.15rem}
.logo em{font-style:normal;color:var(--gold)}
.topnav{display:flex;gap:1.4rem;font-size:.75rem;color:rgba(255,255,255,.72)}
.topnav a:hover{color:var(--gold)}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:1.1rem}
.cart-pill{display:flex;align-items:center;gap:.45rem;background:rgba(242,201,76,.14);border:1px solid rgba(242,201,76,.45);color:var(--gold);border-radius:999px;padding:.32rem .8rem;font-size:.72rem;font-weight:600}
.cart-pill b{background:var(--gold);color:var(--ink);border-radius:999px;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.68rem;padding:0 4px}

/* ── Breadcrumb strip ── */
.crumb{background:var(--ink);color:rgba(255,255,255,.55);font-size:.68rem;padding:.45rem 1.75rem;display:flex;gap:.5rem;letter-spacing:.04em}
.crumb a:hover{color:var(--gold)}
.crumb .here{color:var(--gold)}

/* ── Layout ── */
.wrap{max-width:1160px;margin:0 auto;padding:2rem 1.5rem 4rem;display:grid;grid-template-columns:minmax(0,1fr) 348px;gap:2.2rem;align-items:start}

/* ── Cart header ── */
.cart-head{margin-bottom:1.2rem;display:flex;flex-wrap:wrap;align-items:flex-end;gap:.4rem 1.2rem}
.cart-head h1{font-family:'Space Grotesk',sans-serif;font-size:1.9rem;margin:0;letter-spacing:-.01em}
.cart-head .count{color:var(--muted);font-size:.82rem;margin:0 0 .35rem}
.cart-head .eta{margin-left:auto;font-size:.72rem;color:var(--green);background:var(--green-bg);padding:.4rem .75rem;border-radius:999px}

/* ── Free-shipping progress ── */
.shipbar{background:var(--paper);border:1px solid var(--line);border-radius:var(--r-md);padding:.85rem 1rem;margin-bottom:1.1rem}
.shipbar .note{font-size:.74rem;margin-bottom:.55rem;display:flex;justify-content:space-between;gap:1rem}
.shipbar .note b{color:var(--amber)}
.shipbar.done .note b{color:var(--green)}
.shipbar .track{height:8px;background:var(--sand);border-radius:999px;overflow:hidden}
.shipbar .fill{height:100%;width:0;background:linear-gradient(90deg,var(--gold),var(--gold-d));border-radius:999px;transition:width .45s ease}
.shipbar.done .fill{background:var(--green)}

/* ── Cart item ── */
.cart-item{display:flex;gap:1.1rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--r-lg);padding:1.1rem 1.2rem;margin-bottom:.9rem}
.cart-item-thumb{width:92px;height:92px;border-radius:var(--r-md);background:var(--sand);flex:0 0 auto;display:flex;align-items:center;justify-content:center;overflow:hidden}
.cart-item-thumb img{width:100%;height:100%;object-fit:cover}
.cart-item-thumb svg{width:55%;height:55%;color:var(--muted)}
.cart-item-body{flex:1;min-width:0}
.cart-item-top{display:flex;justify-content:space-between;gap:1rem;align-items:baseline}
.cart-item-name{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.95rem}
.cart-item-price{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1rem;white-space:nowrap}
.cart-item-meta{font-size:.72rem;color:var(--muted);margin-top:.25rem}
.cart-item-sub{display:flex;align-items:center;gap:.7rem;margin-top:.45rem}
.cart-item-sku{font-size:.64rem;color:var(--muted);letter-spacing:.04em}
.stock-badge{font-size:.62rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--green);background:var(--green-bg);border-radius:999px;padding:.18rem .55rem}
.stock-badge.low{color:var(--amber);background:var(--amber-bg)}
.cart-item-row{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-top:.8rem}
.unit-price{font-size:.7rem;color:var(--muted)}
.item-links{margin-left:auto;display:flex;gap:1rem}
.link-btn{background:none;border:none;padding:0;cursor:pointer;font-size:.7rem;color:var(--ink-soft);text-decoration:underline;text-underline-offset:2px}
.link-btn:hover{color:var(--gold-d)}
.link-btn.danger{color:var(--red)}
.link-btn.danger:hover{opacity:.7}

/* Qty stepper */
.qty-control{display:flex;align-items:center;border:1px solid var(--line);border-radius:var(--r-sm);overflow:hidden;background:var(--paper)}
.qty-btn{width:30px;height:30px;border:none;background:var(--paper);cursor:pointer;font-size:1rem;font-weight:700;display:flex;align-items:center;justify-content:center}
.qty-btn:hover{background:var(--cream)}
.qty-value{width:36px;text-align:center;font-size:.85rem;font-weight:600;user-select:none}

/* ── Section titles ── */
.section-title{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;margin:2rem 0 .9rem;display:flex;align-items:center;gap:.6rem}
.section-title .n{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:500;color:var(--muted);background:var(--sand);border-radius:999px;padding:.2rem .6rem}

/* ── Saved for later ── */
.cart-item.is-saved{background:transparent;border-style:dashed}

/* ── Recommendations ── */
.reco-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.9rem}
.reco-card{background:var(--paper);border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden;display:flex;flex-direction:column;transition:transform .15s ease,box-shadow .15s ease}
.reco-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(20,15,12,.08)}
.reco-thumb{position:relative;aspect-ratio:1/1;background:var(--sand);overflow:hidden}
.reco-thumb img{width:100%;height:100%;object-fit:cover}
.reco-tag{position:absolute;top:.5rem;left:.5rem;background:var(--ink);color:var(--gold);font-size:.58rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;border-radius:999px;padding:.2rem .55rem}
.reco-body{padding:.7rem .8rem .9rem;display:flex;flex-direction:column;gap:.35rem;flex:1}
.reco-name{font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:.78rem;line-height:1.3}
.reco-price{font-size:.75rem;font-weight:600;color:var(--ink-soft)}
.reco-add{margin-top:auto;border:1px solid var(--ink);background:var(--paper);border-radius:var(--r-sm);padding:.45rem 0;font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;transition:background .15s,color .15s}
.reco-add:hover{background:var(--ink);color:var(--gold)}
.reco-add.added{background:var(--green);border-color:var(--green);color:#fff}

/* ── Empty state ── */
.cart-empty{text-align:center;padding:3.5rem 1.5rem;border:1px dashed var(--line);border-radius:var(--r-lg);color:var(--muted);background:var(--paper)}
.cart-empty svg{width:44px;height:44px;margin:0 auto 1rem;color:var(--gold-d)}
.cart-empty .t{font-family:'Space Grotesk',sans-serif;font-size:1.15rem;font-weight:700;color:var(--ink);margin-bottom:.3rem}
.cart-empty a.cta{display:inline-block;margin-top:1.1rem;background:var(--gold);border:1px solid var(--gold-d);color:var(--ink);padding:.65rem 1.5rem;border-radius:var(--r-sm);font-weight:700;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em}

/* ── Summary ── */
.summary{background:var(--paper);border:1px solid var(--line);border-radius:var(--r-lg);padding:1.35rem;position:sticky;top:78px}
.summary h2{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin:0 0 1.1rem;text-transform:uppercase;letter-spacing:.05em}
.promo-row{display:flex;gap:.5rem;margin-bottom:.4rem}
.promo-row input{flex:1;min-width:0;padding:.6rem .7rem;border:1px solid var(--line);border-radius:var(--r-sm);font-family:inherit;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em}
.promo-row input:focus{outline:2px solid var(--gold);border-color:transparent}
.promo-row button{padding:0 .95rem;border:1px solid var(--ink);border-radius:var(--r-sm);background:var(--paper);font-size:.7rem;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.05em}
.promo-row button:hover{background:var(--ink);color:var(--gold)}
.promo-hint{font-size:.64rem;color:var(--muted);margin-bottom:1.1rem}
.promo-hint b{color:var(--ink-soft);cursor:pointer}
.promo-msg{font-size:.68rem;margin:-.2rem 0 .8rem;display:none}
.promo-msg.err{display:block;color:var(--red)}
.promo-applied{display:none;align-items:center;justify-content:space-between;background:var(--green-bg);color:var(--green);border-radius:var(--r-sm);padding:.5rem .7rem;font-size:.7rem;margin-bottom:1rem}
.promo-applied.show{display:flex}
.promo-applied button{background:none;border:none;color:inherit;cursor:pointer;font-size:1rem;line-height:1;padding:0}
.summary-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.65rem;color:rgba(20,15,12,.78)}
.summary-row.discount{color:var(--green);font-weight:600}
.summary-row.total{font-weight:700;font-size:1.02rem;color:var(--ink);border-top:1px solid var(--line);padding-top:.9rem;margin-top:.5rem;margin-bottom:0}
.ship-note{font-size:.7rem;color:var(--green);background:var(--green-bg);border-radius:var(--r-sm);padding:.5rem .7rem;margin:.9rem 0}
.ship-note.pending{color:var(--amber);background:var(--amber-bg)}
.checkout-btn{width:100%;padding:.95rem;border:none;border-radius:var(--r-md);background:var(--ink);color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.92rem;cursor:pointer;text-transform:uppercase;letter-spacing:.04em;transition:opacity .15s}
.checkout-btn:hover{opacity:.88}
.checkout-btn:disabled{opacity:.35;cursor:not-allowed}
.secure-note{display:flex;align-items:center;gap:.4rem;justify-content:center;font-size:.66rem;color:var(--muted);margin-top:.85rem}
.pay-chips{display:flex;gap:.45rem;justify-content:center;margin-top:.9rem}
.pay-chips span{border:1px solid var(--line);border-radius:5px;padding:.22rem .5rem;font-size:.56rem;font-weight:700;letter-spacing:.05em;color:var(--ink-soft);background:var(--cream)}
.summary-meta{margin-top:1rem;padding-top:.9rem;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:.5rem}
.summary-meta div{display:flex;gap:.5rem;align-items:center;font-size:.66rem;color:var(--muted)}
.summary-meta svg{flex:0 0 auto;color:var(--gold-d)}

/* ── Footer ── */
.foot{background:var(--ink);color:rgba(255,255,255,.55);font-size:.68rem;padding:1.4rem 1.75rem;display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.foot b{color:#fff;font-family:'Space Grotesk',sans-serif;letter-spacing:.12em}

/* ── Toast ── */
.toast{position:fixed;bottom:1.4rem;left:50%;transform:translate(-50%,20px);background:var(--ink);color:#fff;border-radius:var(--r-md);padding:.75rem 1.2rem;font-size:.75rem;opacity:0;pointer-events:none;transition:all .25s ease;z-index:99;box-shadow:0 12px 32px rgba(20,15,12,.3)}
.toast.show{opacity:1;transform:translate(-50%,0)}

@media(max-width:920px){
  .wrap{grid-template-columns:1fr}
  .summary{position:static}
  .topnav{display:none}
  .cart-head .eta{margin-left:0}
}
</style>
</head>
<body>

<header class="topbar">
  <a class="logo" href="home.php">LUNA<em>X</em></a>
  <nav class="topnav">
    <a href="home.php">Store</a>
    <a href="home.php#deals">Deals</a>
    <a href="home.php#support">Support</a>
  </nav>
  <div class="topbar-right">
    <a class="cart-pill" href="cart.php" aria-label="Cart">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1h-13a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
      Cart <b id="navCartCount"><?= (int)$itemCount ?></b>
    </a>
  </div>
</header>
<div class="crumb">
  <a href="home.php">Home</a><span>/</span><span class="here">Shopping cart</span>
</div>

<main class="wrap">

  <section class="main-col">
    <div class="cart-head">
      <h1>Your cart</h1>
      <p class="count" id="cartItemCountLabel"><?= $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?></p>
      <?php if ($itemCount > 0): ?>
      <span class="eta" id="etaNote">Estimated delivery <?= $e($etaFrom) ?> &ndash; <?= $e($etaTo) ?></span>
      <?php endif; ?>
    </div>

    <div class="shipbar <?= $T['unlocked'] ? 'done' : '' ?>" id="shipBar">
      <div class="note"><span id="shipNote"><?= $e($T['shipNote']) ?></span><b id="shipGoal"><?= $T['unlocked'] ? 'Free shipping' : lnx_money(FREE_SHIP) . ' goal' ?></b></div>
      <div class="track"><div class="fill" id="shipFill" style="width:<?= (int)$T['shipPct'] ?>%"></div></div>
    </div>

    <div id="cartItemsList">
    <?php if (empty($cart)): ?>
      <div class="cart-empty" id="cartEmptyState">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1h-13a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
        <div class="t">Your cart is empty</div>
        Real tech, real prices. Go find something good.
        <br><a class="cta" href="home.php">Start shopping</a>
      </div>
    <?php else: ?>
      <?php foreach ($cart as $id => $item): ?>
        <?= lnx_item_row($item, $id, $e, $SVG_ALLOW, false) ?>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>

    <div id="savedSection" <?= empty($saved) ? 'hidden' : '' ?>>
      <h2 class="section-title">Saved for later <span class="n" id="savedCount"><?= count($saved) ?></span></h2>
      <div id="savedItemsList">
        <?php foreach ($saved as $id => $item): ?>
          <?= lnx_item_row($item, $id, $e, $SVG_ALLOW, true) ?>
        <?php endforeach; ?>
      </div>
    </div>

    <h2 class="section-title">Complete your setup</h2>
    <div class="reco-grid">
      <?php foreach ($recos as $rid => $r): ?>
      <div class="reco-card">
        <div class="reco-thumb">
          <img src="<?= $e($r['image']) ?>" alt="<?= $e($r['name']) ?>" loading="lazy" onerror="lnxImgFallback(this)">
          <?php if ($r['tag'] !== ''): ?><span class="reco-tag"><?= $e($r['tag']) ?></span><?php endif; ?>
        </div>
        <div class="reco-body">
          <div class="reco-name"><?= $e($r['name']) ?></div>
          <div class="reco-price"><?= lnx_money($r['price']) ?></div>
          <button type="button" class="reco-add"
                  data-index="<?= (int)$rid ?>"
                  data-name="<?= $e($r['name']) ?>"
                  data-price="<?= $e($r['price']) ?>"
                  data-image="<?= $e($r['image']) ?>">Add to cart</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <aside class="summary">
    <h2>Order summary</h2>

    <div class="promo-applied<?= $promo ? ' show' : '' ?>" id="promoApplied">
      <span><b id="promoCodeLabel"><?= $e($promo['code'] ?? '') ?></b> &middot; <span id="promoDescLabel"><?= $e($promo['label'] ?? '') ?></span></span>
      <button type="button" id="promoRemoveBtn" aria-label="Remove promo">&times;</button>
    </div>
    <div class="promo-row" id="promoRow" <?= $promo ? 'style="display:none"' : '' ?>>
      <input type="text" placeholder="Promo code" id="promoInput" maxlength="20">
      <button type="button" id="promoApplyBtn">Apply</button>
    </div>
    <div class="promo-msg" id="promoMsg"></div>
    <div class="promo-hint" <?= $promo ? 'style="display:none"' : '' ?>>Try <b data-code="LUNAX10">LUNAX10</b> for 10% off</div>

    <div class="summary-row"><span>Subtotal</span><span id="summarySubtotal"><?= $T['subtotal'] ?></span></div>
    <div class="summary-row discount" id="discountRow" <?= $T['discount'] ? '' : 'hidden' ?>><span>Discount</span><span id="summaryDiscount"><?= $T['discount'] ?? '' ?></span></div>
    <div class="summary-row"><span>Shipping</span><span id="summaryShipping"><?= $T['shipping'] ?></span></div>
    <?php if (TAX_RATE > 0): ?>
    <div class="summary-row"><span>Tax (<?= TAX_RATE * 100 ?>%)</span><span id="summaryTax"><?= $T['tax'] ?></span></div>
    <?php endif; ?>
    <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= $T['total'] ?></span></div>

    <div class="ship-note <?= !$T['unlocked'] && $itemCount > 0 ? 'pending' : '' ?>" id="summaryShipNote"><?= $e($T['shipNote']) ?></div>

    <button class="checkout-btn" id="checkoutBtn" <?= $itemCount === 0 ? 'disabled' : '' ?>>Proceed to checkout</button>

    <div class="secure-note">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      Secure checkout &middot; 256-bit encrypted
    </div>
    <div class="pay-chips"><span>VISA</span><span>MASTERCARD</span><span>AMEX</span><span>PAYPAL</span></div>

    <div class="summary-meta">
      <div><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 8h14v9H1zM15 11h4l3 3v3h-7z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg> Free shipping over <?= lnx_money(FREE_SHIP) ?></div>
      <div><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg> 30-day free returns</div>
      <div><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l7 4v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6z"/></svg> 2-year LUNAX warranty</div>
    </div>
  </aside>

</main>

<footer class="foot">
  <span><b>LUNAX</b> &middot; Tech, delivered.</span>
  <span>Questions? support@lunax.example</span>
</footer>

<div class="toast" id="toast"></div>

<script>
(function(){
  const $ = id => document.getElementById(id);
  const money = v => '$' + (+v).toFixed(2);

  // Image fallback: swap a broken photo for the bag icon tile.
  window.lnxImgFallback = function(img){
    const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svg.setAttribute('viewBox','0 0 24 24');
    svg.setAttribute('fill','none');
    svg.setAttribute('stroke','currentColor');
    svg.setAttribute('stroke-width','1.5');
    svg.innerHTML = '<path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1h-13a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/>';
    img.replaceWith(svg);
  };

  let toastTimer;
  function toast(msg){
    const t = $('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
  }

  const post = body => fetch('cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body
  }).then(r => r.json().then(d => ({status: r.status, data: d})));

  // Paint the server-authoritative view into the summary + header.
  function render(d){
    const T = d.totals;
    $('summarySubtotal').textContent = T.subtotal;
    $('summaryShipping').textContent = T.shipping;
    const taxEl = $('summaryTax');
    if (taxEl) taxEl.textContent = T.tax;
    $('summaryTotal').textContent = T.total;

    const dRow = $('discountRow');
    if (T.discount) { dRow.hidden = false; $('summaryDiscount').textContent = T.discount; }
    else dRow.hidden = true;

    // Promo UI
    const applied = $('promoApplied');
    if (d.promo) {
      applied.classList.add('show');
      $('promoRow').style.display = 'none';
      document.querySelector('.promo-hint').style.display = 'none';
      $('promoCodeLabel').textContent = d.promo.code;
      $('promoDescLabel').textContent = d.promo.label;
    } else {
      applied.classList.remove('show');
      $('promoRow').style.display = '';
      document.querySelector('.promo-hint').style.display = '';
    }

    // Shipping progress
    const bar = $('shipBar');
    bar.classList.toggle('done', T.unlocked);
    $('shipFill').style.width = T.shipPct + '%';
    $('shipNote').textContent = T.shipNote;
    $('shipGoal').textContent = T.unlocked ? 'Free shipping' : '';
    const note = $('summaryShipNote');
    note.textContent = T.shipNote;
    note.classList.toggle('pending', !T.unlocked && d.itemCount > 0);

    // Counts + checkout
    $('cartItemCountLabel').textContent = d.itemCount + ' item' + (d.itemCount === 1 ? '' : 's');
    $('navCartCount').textContent = d.itemCount;
    $('checkoutBtn').disabled = d.itemCount === 0;

    const eta = $('etaNote');
    if (eta) eta.style.display = d.itemCount === 0 ? 'none' : '';

    // Empty state
    if (d.itemCount === 0 && !$('cartEmptyState')) {
      $('cartItemsList').innerHTML =
        '<div class="cart-empty" id="cartEmptyState">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1h-13a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>' +
        '<div class="t">Your cart is empty</div>Real tech, real prices. Go find something good.<br>' +
        '<a class="cta" href="home.php">Start shopping</a></div>';
    }
  }

  // ── Cart list interactions (qty / remove / save / restore) ──
  function bindList(listEl){
    listEl.addEventListener('click', e => {
      const row = e.target.closest('.cart-item');
      if (!row) return;
      const id = row.dataset.id;

      if (e.target.classList.contains('qty-plus') || e.target.classList.contains('qty-minus')) {
        const qty = Math.max(0, +row.dataset.qty + (e.target.classList.contains('qty-plus') ? 1 : -1));
        post(`action=update&id=${encodeURIComponent(id)}&qty=${qty}`)
          .then(({data}) => {
            if (!data.ok) return;
            if (qty <= 0) row.remove();
            else {
              row.dataset.qty = qty;
              row.querySelector('.qty-value').textContent = qty;
              row.querySelector('.cart-item-price').textContent = money(+row.dataset.price * qty);
            }
            render(data);
          }).catch(console.error);
      }

      if (e.target.classList.contains('item-remove'))
        post(`action=remove&id=${encodeURIComponent(id)}`)
          .then(({data}) => { if (data.ok) { row.remove(); render(data); toast('Item removed'); } })
          .catch(console.error);

      if (e.target.classList.contains('item-save'))
        post(`action=save&id=${encodeURIComponent(id)}`)
          .then(() => location.reload())   // saved list is server-rendered; reload keeps it truthful
          .catch(console.error);

      if (e.target.classList.contains('item-restore'))
        post(`action=restore&id=${encodeURIComponent(id)}`)
          .then(() => location.reload())
          .catch(console.error);
    });
  }
  bindList($('cartItemsList'));
  bindList($('savedItemsList'));

  // ── Promo ──
  function applyPromo(code){
    post(`action=promo&code=${encodeURIComponent(code)}`)
      .then(({status, data}) => {
        const msg = $('promoMsg');
        if (status !== 200 || !data.ok) {
          msg.textContent = data.error || 'That code is not valid';
          msg.classList.add('err');
          return;
        }
        msg.classList.remove('err');
        render(data);
        if (data.promo) toast(data.promo.code + ' applied');
      }).catch(console.error);
  }
  $('promoApplyBtn').addEventListener('click', () => {
    const code = $('promoInput').value.trim();
    if (code) applyPromo(code);
  });
  $('promoInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); $('promoApplyBtn').click(); }
  });
  $('promoRemoveBtn').addEventListener('click', () => applyPromo(''));
  document.querySelector('.promo-hint b').addEventListener('click', function(){
    $('promoInput').value = this.dataset.code;
    applyPromo(this.dataset.code);
  });

  // ── Recommendations: add to cart via the same API home.php uses ──
  document.querySelectorAll('.reco-add').forEach(btn => {
    btn.addEventListener('click', () => {
      const body = new URLSearchParams({
        action: 'add',
        index: btn.dataset.index,
        name: btn.dataset.name,
        price: btn.dataset.price,
        qty: '1',
        image: btn.dataset.image,
        icon: ''
      });
      post(body.toString()).then(({data}) => {
        if (!data.ok) return;
        btn.textContent = 'Added';
        btn.classList.add('added');
        render(data);
        toast(btn.dataset.name + ' added to cart');
        setTimeout(() => location.reload(), 650);  // refresh to show the new line item
      }).catch(console.error);
    });
  });

  $('checkoutBtn').addEventListener('click', function(){
    if (!this.disabled) window.location.href = 'checkout.php';
  });
})();
</script>
</body>
</html>
