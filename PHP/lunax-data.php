<?php
/* ============================================================================
   LUNAX — server-side live-data layer  (lunax-data.php)
   ----------------------------------------------------------------------------
   Fetches real, free, keyless public market data on the SERVER (no browser
   CORS, no API key) and exposes it to the page as a single PHP array via
   lunax_load_all():

       • Crypto    → CoinGecko    (price, 24h %, high/low, volume, mcap, spark)
       • Currency  → Frankfurter  (ECB daily reference rates + short series)
       • Metals    → gold-api.com (best-effort spot price)
       • Indices   → simulated    (no free feed; clearly labelled)
       • News      → Google News RSS (headlines) with a simulated fallback

   Every feed degrades gracefully to realistic *simulated* data when the
   network / API is unavailable, and each section reports whether it is 'live'
   or 'sim'. Results are cached briefly on disk so page loads stay fast and we
   do not hammer the upstream APIs.

   The returned array is shaped to match what B1.js expects on
   window.LUNAX_DATA:

       [
         'assets'  => [ 'crypto:bitcoin' => {type,id,name,ticker,price,change,
                        high,low,spark[],volume,mcap,source}, ... ],
         'sources' => [ 'crypto'=>'live|sim', 'metals'=>..., 'fx'=>...,
                        'indices'=>'sim', 'news'=>'live|sim' ],
         'order'   => [ 'crypto:bitcoin', ... ],   // display order
         'news'    => [ {title,url,summary,source,time,ago}, ... ],
         'lastUpdated' => ISO-8601 string,
         'updatedLabel'=> 'h:mm AM' (server local time)
       ]
   ========================================================================== */


// ── Catalog helpers ──────────────────────────────────────────────────────
function lnx_slug($name)
{
    $s = strtolower($name);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function lnx_money($v)
{
    return '$' . number_format((float) $v, 2);
}

function lnx_stars_html($rating)
{
    $out = '';
    $rounded = (int) round($rating);
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rounded ? '&#9733;' : '&#9734;';
    }
    return $out;
}

function lnx_category_from_name($name)
{
    $n = strtolower($name);
    if (preg_match('/earbud|headphone|headset|speaker/', $n)) return 'Audio';
    if (preg_match('/phone|tablet/', $n)) return 'Phones & Tablets';
    if (preg_match('/laptop|monitor|ssd|hub|keyboard|mouse|numpad|webcam|desk/', $n)) return 'Computers';
    if (preg_match('/drone|camera|dash cam|projector|ring light/', $n)) return 'Cameras & Imaging';
    if (preg_match('/watch|band|fitness|scale/', $n)) return 'Wearables';
    if (preg_match('/smart|bulb|plug|doorbell|vacuum|purifier|router/', $n)) return 'Smart Home';
    return 'Electronics';
}

function lnx_brand_from_name($name)
{
    $first = explode(' ', trim($name))[0];
    return $first ?: 'LUNAX';
}

/** Build rich marketplace copy from the product name when no override exists. */
function lnx_auto_detail($name, $brand, $cat)
{
    $n = strtolower($name);
    $desc = "The {$name} brings marketplace-grade specs, everyday durability, and LUNAX fulfillment — the same depth of detail you expect from major online stores.";
    $bullets = [
        "Sold & shipped by LUNAX — authentic {$brand}",
        '30-day hassle-free returns',
        '12-month limited warranty',
        'Secure encrypted checkout',
        'Customer support 24/7',
    ];
    $colors = ['Black', 'Graphite'];
    $specs = [
        'Brand' => $brand,
        'Category' => $cat,
        'Condition' => 'New',
        'Warranty' => '12 months',
        'Fulfilled by' => 'LUNAX',
        'Return window' => '30 days',
    ];
    $box = ["{$brand} {$name}", 'User documentation', 'LUNAX warranty card'];

    if (preg_match('/earbud/', $n)) {
        // ... all your existing elseif branches, completely unchanged ...
    }

    // ↓↓↓ ONLY THIS LAST LINE CHANGES ↓↓↓
    return [
        'desc'    => $desc,
        'bullets' => $bullets,
        'colors'  => $colors,
        'specs'   => $specs,
        'box'     => $box,
    ];
}
// ── Catalog builder ──────────────────────────────────────────────────────
function lunax_products()
{
    // ---- Catalog (synced with B3.js) ------------------------------------------
    $raw = [
        ['Pulse Wireless Earbuds Pro, ANC + 30h battery', 49.99, null, 'new', '../image/s.jpg',
            '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>'],
        ['Orbit 6.7" AMOLED Phone, 256GB, 5G', 389.00, 459.00, 'sale', '../image/b.jpg',
            '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'],
        ['NovaBook Air 14", 16GB / 512GB SSD', 649.00, null, null, '../image/z.jpg',
            '<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'],
        ['Aether Smartwatch Series 5, GPS + Cellular', 89.99, null, 'new', null,
            '<rect x="7" y="5" width="10" height="14" rx="4"/><path d="M12 8v0"/>'],
        ['Halo RGB Mechanical Keyboard, Hot-swap', 59.90, 79.90, 'sale', null,
            '<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'],
        ['DriftCam 4K Action Camera, Waterproof', 129.00, null, null, null,
            '<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'],
        ['VoltPack 20000mAh Fast Charger, 65W', 24.99, null, null, null,
            '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>'],
        ['EchoDome Smart Speaker, Voice Assistant', 34.50, null, 'new', null,
            '<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'],
        ['Glide Wireless Mouse, Silent Click', 19.99, null, null, null,
            '<rect x="8" y="3" width="8" height="14" rx="4"/><path d="M12 3v6"/>'],
        ['Skyline Drone 4K, Foldable, 30min flight', 199.00, 249.00, 'sale', null,
            '<path d="M12 3v4M12 17v4M3 12h4M17 12h4"/><circle cx="12" cy="12" r="4"/>'],
        ['Comet USB-C Hub, 8-in-1 Dock', 44.90, null, null, null,
            '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h.01M11 10h.01M15 10h.01"/>'],
        ['Nimbus Portable SSD, 1TB, USB 3.2', 79.99, 99.99, 'sale', null,
            '<rect x="4" y="7" width="16" height="10" rx="2"/><path d="M8 12h8"/>'],
        ['Zenith 27" QHD Monitor, 165Hz', 229.00, null, 'new', null,
            '<rect x="2" y="4" width="20" height="13" rx="1"/><path d="M8 21h8M12 17v4"/>'],
        ['Arc Wireless Charging Pad, 15W', 22.50, null, null, null,
            '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/>'],
        ['Vortex Gaming Mouse, 16000 DPI', 39.99, null, null, null,
            '<rect x="8" y="3" width="8" height="14" rx="4"/><path d="M12 3v6"/>'],
        ['Solace Noise-Cancelling Headphones', 99.00, 129.00, 'sale', null,
            '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>'],
        ['Fable E-Reader, 7" Glare-Free Display', 109.00, null, 'new', null,
            '<rect x="6" y="3" width="12" height="18" rx="2"/><path d="M9 7h6M9 11h6"/>'],
        ['Ridge Bluetooth Tracker, 4-Pack', 29.99, null, null, null,
            '<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/>'],
        ['Lumen Smart Bulb, RGB, 4-Pack', 32.00, null, null, null,
            '<path d="M9 18h6M9 21h6M12 3a6 6 0 0 0-3 11c1 1 1 2 1 3h4c0-1 0-2 1-3a6 6 0 0 0-3-11Z"/>'],
        ['Cascade Robot Vacuum, LiDAR Mapping', 249.00, 299.00, 'sale', null,
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/>'],
        ['Aster Mechanical Keyboard, 60%', 69.00, null, null, null,
            '<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'],
        ['Beam Portable Projector, 1080p', 159.00, null, 'new', null,
            '<rect x="2" y="8" width="14" height="9" rx="2"/><circle cx="9" cy="12.5" r="2.5"/><path d="M18 11l4-2v7l-4-2z"/>'],
        ['Tide Waterproof Bluetooth Speaker', 54.90, 69.90, 'sale', null,
            '<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'],
        ['Orbit Mini Tablet, 8.3", 128GB', 219.00, null, null, null,
            '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 19h2"/>'],
        ['Flux Gaming Headset, 7.1 Surround', 64.99, null, 'new', null,
            '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>'],
        ['Anchor Laptop Stand, Aluminum', 27.50, null, null, null,
            '<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'],
        ['Prism Webcam, 4K Auto-Focus', 74.00, 89.00, 'sale', null,
            '<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'],
        ['Cinder Power Strip, 6-Outlet + USB-C', 26.99, null, null, null,
            '<rect x="2" y="9" width="20" height="7" rx="2"/><path d="M7 9v-2M12 9v-2M17 9v-2"/>'],
        ['Vantage Ring Light, 10", Tripod', 31.90, null, 'new', null,
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/>'],
        ['Motion Fitness Band, Heart Rate + SpO2', 44.00, null, null, null,
            '<rect x="7" y="5" width="10" height="14" rx="4"/><path d="M12 8v0"/>'],
        ['Reef Mechanical Numpad, Hot-swap', 34.90, null, null, null,
            '<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'],
        ['Slate Drawing Tablet, Pressure Pen', 89.90, 109.90, 'sale', null,
            '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/>'],
        ['Drift Dash Cam, 2K, Night Vision', 69.99, null, null, null,
            '<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'],
        ['Pulse Fitness Scale, Body Composition', 29.00, null, 'new', null,
            '<rect x="4" y="9" width="16" height="10" rx="2"/><circle cx="12" cy="14" r="2"/>'],
        ['Nomad Travel Router, Pocket Wi-Fi', 36.00, null, null, null,
            '<path d="M4 11 12 4l8 7"/><path d="M6 10v9h12v-9"/>'],
        ['Halo Desk Lamp, Wireless Charge Base', 41.50, 49.50, 'sale', null,
            '<path d="M9 18h6M9 21h6M12 3a6 6 0 0 0-3 11c1 1 1 2 1 3h4c0-1 0-2 1-3a6 6 0 0 0-3-11Z"/>'],
        ['Circuit Soldering Kit, Digital Iron', 52.00, null, null, null,
            '<rect x="7" y="7" width="10" height="10" rx="1"/><path d="M9 3v2M15 3v2M9 19v2M15 19v2M3 9h2M3 15h2M19 9h2M19 15h2"/>'],
        ['Orbit Car Phone Mount, Magnetic', 14.99, null, null, null,
            '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'],
        ['Ember Heated Mug, Smart Temp Control', 79.00, null, 'new', null,
            '<path d="M4 8h12v7a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4Z"/><path d="M16 10h2a2 2 0 0 1 0 4h-2"/>'],
        ['Trace GPS Bike Computer', 94.00, null, null, null,
            '<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/>'],
        ['Cove Bookshelf Speakers, Pair', 139.00, 169.00, 'sale', null,
            '<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'],
        ['Lattice Cable Organizer Set', 12.99, null, null, null,
            '<rect x="4" y="9" width="16" height="6" rx="3"/>'],
        ['Verge Smart Doorbell, 1080p HDR', 99.99, null, 'new', null,
            '<rect x="8" y="3" width="8" height="18" rx="2"/><circle cx="12" cy="9" r="1.3" fill="#D68A2E" stroke="none"/>'],
        ['Kinetic Standing Desk Converter', 169.00, 199.00, 'sale', null,
            '<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'],
        ['Aura Air Purifier, HEPA, Quiet Mode', 119.00, null, null, null,
            '<rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 9h6M9 13h6"/>'],
        ['Sprint Gaming Chair Footrest', 38.00, null, null, null,
            '<rect x="3" y="10" width="18" height="6" rx="2"/><path d="M6 16v3M18 16v3"/>'],
        ['Glacier Cooling Laptop Pad', 21.99, null, 'new', null,
            '<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'],
        ['Fathom Waterproof Phone Pouch', 9.99, null, null, null,
            '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'],
        ['Relay Smart Plug, Energy Monitor, 2-Pack', 24.00, 29.00, 'sale', null,
            '<path d="M4 11 12 4l8 7"/><path d="M6 10v9h12v-9"/>'],
        ['Quartz Portable Monitor, 15.6" USB-C', 189.00, null, null, null,
            '<rect x="2" y="4" width="20" height="13" rx="1"/><path d="M8 21h8M12 17v4"/>'],
    ];

    // Explicit overrides for hero SKUs (extra marketplace depth)
    $detailExtras = [
        'pulse-wireless-earbuds-pro-anc-30h-battery' => [
            'desc' => 'Studio-tuned 11mm drivers, hybrid ANC, and a pocket case that unlocks a full week of listening. Built for commute, gym, and all-day calls — the listing depth you expect from a top marketplace.',
            'bullets' => ['Hybrid ANC + transparency', '30h total with case', 'IPX5 sweat & splash proof', 'BT 5.3 multipoint', '10 min charge → 2 h play'],
            'colors' => ['Black', 'White', 'Midnight'],
            'specs' => ['Driver' => '11mm dynamic', 'ANC' => 'Hybrid −35 dB', 'Battery' => '7 h / 30 h case', 'BT' => '5.3', 'IP' => 'IPX5', 'Weight' => '4.8 g/bud'],
            'box' => ['Earbuds', 'Case', 'Tips S/M/L', 'USB-C cable', 'Guide'],
            'variants' => ['Standard', 'Pro Case+'],
        ],
        'orbit-6-7-amoled-phone-256gb-5g' => [
            'desc' => '6.7" 120Hz AMOLED, 256GB, full 5G — flagship feel without flagship pricing. Triple camera, fast charge, dual SIM / eSIM.',
            'bullets' => ['6.7" AMOLED 120Hz', '256GB · 5G', '48MP triple camera', '4500mAh + 45W', 'Dual SIM / eSIM'],
            'colors' => ['Midnight Blue', 'Graphite', 'Silver'],
            'specs' => ['Display' => '6.7" AMOLED 120Hz', 'Storage' => '256GB', 'Camera' => '48+12+8MP', 'Battery' => '4500mAh', 'Charge' => '45W', 'Network' => '5G / Wi‑Fi 6'],
            'box' => ['Phone', 'USB-C', 'SIM tool', 'Case', 'Docs'],
            'variants' => ['128GB', '256GB', '512GB'],
        ],
        'novabook-air-14-16gb-512gb-ssd' => [
            'desc' => '14" all-day laptop: 16GB RAM, 512GB SSD, quiet cooling, Thunderbolt ports. Ready for work, class, and travel.',
            'bullets' => ['14" IPS panel', '16GB / 512GB SSD', 'Up to 18 h battery', 'Backlit keyboard', 'TB4 + HDMI'],
            'colors' => ['Silver', 'Space Gray'],
            'specs' => ['Display' => '14" 1920×1200', 'RAM' => '16GB LPDDR5', 'SSD' => '512GB NVMe', 'Battery' => '≤18 h', 'Ports' => '2×TB4, HDMI, USB-A', 'Weight' => '1.35 kg'],
            'box' => ['NovaBook', '65W charger', 'Sleeve', 'Guide'],
            'variants' => ['16/512', '32/1TB'],
        ],
    ];

    $products = [];
    foreach ($raw as $i => $row) {
        [$name, $price, $was, $badge, $image, $icon] = $row;
        $id = lnx_slug($name);
        $rating = round(3.5 + (($i * 37) % 15) / 10, 1);
        $reviews = 20 + (($i * 53) % 480);
        $cat = lnx_category_from_name($name);
        $brand = lnx_brand_from_name($name);
        $auto = lnx_auto_detail($name, $brand, $cat);
        $extra = $detailExtras[$id] ?? [];

        $products[$id] = [
            'id'       => $id,
            'index'    => $i,
            'name'     => $name,
            'price'    => $price,
            'was'      => $was,
            'badge'    => $badge,
            'image'    => $image,
            'icon'     => $icon,
            'rating'   => $rating,
            'reviews'  => $reviews,
            'category' => $cat,
            'brand'    => $brand,
            'stock'    => ($i % 11 === 0) ? 4 : (($i % 7 === 0) ? 12 : 48 + ($i % 20)),
            'sku'      => 'LNX-' . strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $id), 0, 8)) . '-' . (1000 + $i),
            'ship'     => $price >= 50 ? 'FREE delivery' : 'Standard shipping $6.99',
            'arrive'   => date('D, M j', strtotime('+' . (2 + ($i % 4)) . ' days')),
            'desc'     => $extra['desc'] ?? $auto['desc'],
            'bullets'  => $extra['bullets'] ?? $auto['bullets'],
            'colors'   => $extra['colors'] ?? $auto['colors'],
            'specs'    => $extra['specs'] ?? $auto['specs'],
            'box'      => $extra['box'] ?? $auto['box'],
            'variants' => $extra['variants'] ?? null,
            'coupon'   => ($price >= 40 && $i % 3 === 0) ? min(15, (int) floor($price * 0.08)) : 0,
            'prime'    => $price >= 25,
            'seller'   => 'LUNAX Official',
            'sold'     => 100 + (($i * 91) % 2400),
        ];
    }

    return $products;
}


if (!function_exists('lunax_load_all')) {

    /* ------------------------------------------------------------------ */
    /* Asset universe (mirrors the client defs in B1.js)                  */
    /* ------------------------------------------------------------------ */
    function lunax_defs()
    {
        return array(
            'crypto' => array(
                array('id' => 'bitcoin',  'ticker' => 'BTC',  'name' => 'Bitcoin',  'base' => 64210.5),
                array('id' => 'ethereum', 'ticker' => 'ETH',  'name' => 'Ethereum', 'base' => 3142.8),
                array('id' => 'solana',   'ticker' => 'SOL',  'name' => 'Solana',   'base' => 146.32),
                array('id' => 'ripple',   'ticker' => 'XRP',  'name' => 'XRP',      'base' => 0.612),
                array('id' => 'dogecoin', 'ticker' => 'DOGE', 'name' => 'Dogecoin', 'base' => 0.148),
            ),
            'metals' => array(
                array('id' => 'XAU', 'ticker' => 'XAU/oz', 'name' => 'Gold',      'base' => 2384.2),
                array('id' => 'XAG', 'ticker' => 'XAG/oz', 'name' => 'Silver',    'base' => 28.61),
                array('id' => 'XPT', 'ticker' => 'XPT/oz', 'name' => 'Platinum',  'base' => 968.4),
                array('id' => 'XPD', 'ticker' => 'XPD/oz', 'name' => 'Palladium', 'base' => 1012.75),
            ),
            // FX quoted base/quote. `sym` is the ECB symbol vs USD; `invert`
            // means the displayed price is USD-per-unit (1 / rate).
            'fx' => array(
                array('id' => 'EURUSD', 'ticker' => 'EUR/USD', 'name' => 'EUR / USD', 'sym' => 'EUR', 'invert' => true,  'base' => 1.0842),
                array('id' => 'GBPUSD', 'ticker' => 'GBP/USD', 'name' => 'GBP / USD', 'sym' => 'GBP', 'invert' => true,  'base' => 1.2716),
                array('id' => 'USDJPY', 'ticker' => 'USD/JPY', 'name' => 'USD / JPY', 'sym' => 'JPY', 'invert' => false, 'base' => 151.64),
                array('id' => 'USDCNY', 'ticker' => 'USD/CNY', 'name' => 'USD / CNY', 'sym' => 'CNY', 'invert' => false, 'base' => 7.238),
                array('id' => 'AUDUSD', 'ticker' => 'AUD/USD', 'name' => 'AUD / USD', 'sym' => 'AUD', 'invert' => true,  'base' => 0.6591),
            ),
            'indices' => array(
                array('id' => 'SPX', 'ticker' => 'US500', 'name' => 'S&P 500',    'base' => 5232.4),
                array('id' => 'NDX', 'ticker' => 'US100', 'name' => 'Nasdaq 100', 'base' => 18344.0),
                array('id' => 'DJI', 'ticker' => 'US30',  'name' => 'Dow Jones',  'base' => 39210.0),
                array('id' => 'UKX', 'ticker' => 'UK100', 'name' => 'FTSE 100',   'base' => 8214.0),
            ),
        );
    }

    /* ------------------------------------------------------------------ */
    /* HTTP helpers                                                       */
    /* ------------------------------------------------------------------ */
    function lunax_http_get($url, $timeout = 8)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 4,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT      => 'LunaxBot/1.0 (+https://lunax.example)',
                CURLOPT_HTTPHEADER     => array('Accept: application/json, application/xml, text/xml, */*'),
            ));
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 300) {
                return $body;
            }
            return null;
        }
        // Fallback when cURL is unavailable.
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => $timeout,
                'header'  => "Accept: */*\r\nUser-Agent: LunaxBot/1.0\r\n",
            ),
            'ssl' => array('verify_peer' => true, 'verify_peer_name' => true),
        ));
        $body = @file_get_contents($url, false, $ctx);
        return $body === false ? null : $body;
    }

    function lunax_get_json($url, $timeout = 8)
    {
        $raw = lunax_http_get($url, $timeout);
        if ($raw === null) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /* ------------------------------------------------------------------ */
    /* Small numeric helpers + simulation                                 */
    /* ------------------------------------------------------------------ */
    function lunax_rand()
    {
        return mt_rand() / mt_getrandmax();
    }

    function lunax_sample($arr, $n)
    {
        $arr = array_values($arr);
        $len = count($arr);
        if ($len <= $n) {
            return $arr;
        }
        $out  = array();
        $step = ($len - 1) / ($n - 1);
        for ($i = 0; $i < $n; $i++) {
            $out[] = $arr[(int) round($i * $step)];
        }
        return $out;
    }

    function lunax_walk($base, $n, $vol)
    {
        $out = array();
        $v   = $base * (1 - $vol * 0.5);
        for ($i = 0; $i < $n; $i++) {
            $v = $v * (1 + (lunax_rand() - 0.48) * $vol);
            $out[] = $v;
        }
        $last = $out[count($out) - 1];
        if ($last == 0) {
            $last = $base ? $base : 1;
        }
        $k = $base / $last;
        foreach ($out as $i => $x) {
            $out[$i] = $x * $k;
        }
        return $out;
    }

    function lunax_normalise($type, $def, $d)
    {
        $spark = (isset($d['spark']) && is_array($d['spark']) && count($d['spark']) > 1)
            ? array_values($d['spark'])
            : array($d['price'], $d['price']);
        return array(
            'type'   => $type,
            'id'     => $def['id'],
            'name'   => $def['name'],
            'ticker' => isset($def['ticker']) ? $def['ticker'] : $def['name'],
            'price'  => isset($d['price']) ? (float) $d['price'] : null,
            'change' => isset($d['change']) ? (float) $d['change'] : 0,
            'high'   => isset($d['high']) && $d['high'] !== null ? (float) $d['high'] : null,
            'low'    => isset($d['low']) && $d['low'] !== null ? (float) $d['low'] : null,
            'spark'  => lunax_sample($spark, 60),
            'volume' => isset($d['volume']) && $d['volume'] !== null ? (float) $d['volume'] : null,
            'mcap'   => isset($d['mcap']) && $d['mcap'] !== null ? (float) $d['mcap'] : null,
            'source' => isset($d['source']) ? $d['source'] : 'sim',
        );
    }

    function lunax_sim_asset($type, $def, $vol = 0.02)
    {
        $spark  = lunax_walk($def['base'], 48, $vol);
        $price  = $spark[count($spark) - 1];
        $change = (lunax_rand() - 0.45) * ($vol * 200);
        $hi     = max($spark) * (1 + $vol * 0.3);
        $lo     = min($spark) * (1 - $vol * 0.3);
        return lunax_normalise($type, $def, array(
            'price'  => $price,
            'change' => $change,
            'high'   => $hi,
            'low'    => $lo,
            'spark'  => $spark,
            'volume' => $type === 'crypto' ? $def['base'] * 1e6 * (0.5 + lunax_rand()) : null,
            'mcap'   => $type === 'crypto' ? $price * 1e7 * (1 + lunax_rand()) : null,
            'source' => 'sim',
        ));
    }

    /* ------------------------------------------------------------------ */
    /* Fetchers — each returns array(sourceLabel, list-of-assets)         */
    /* ------------------------------------------------------------------ */
    function lunax_fetch_crypto($defs)
    {
        $ids = implode(',', array_map(function ($c) { return $c['id']; }, $defs));
        $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=' . $ids .
            '&order=market_cap_desc&sparkline=true&price_change_percentage=24h';
        $rows = lunax_get_json($url, 9);
        if (!is_array($rows) || !count($rows)) {
            return array('sim', array_map(function ($d) { return lunax_sim_asset('crypto', $d, 0.03); }, $defs));
        }
        $byId = array();
        foreach ($rows as $r) {
            if (isset($r['id'])) {
                $byId[$r['id']] = $r;
            }
        }
        $out = array();
        foreach ($defs as $def) {
            $r = isset($byId[$def['id']]) ? $byId[$def['id']] : null;
            if (!$r || !isset($r['current_price'])) {
                $out[] = lunax_sim_asset('crypto', $def, 0.03);
                continue;
            }
            $spark = (isset($r['sparkline_in_7d']['price']) && is_array($r['sparkline_in_7d']['price']))
                ? $r['sparkline_in_7d']['price'] : array();
            $out[] = lunax_normalise('crypto', $def, array(
                'price'  => $r['current_price'],
                'change' => isset($r['price_change_percentage_24h']) ? $r['price_change_percentage_24h'] : 0,
                'high'   => isset($r['high_24h']) ? $r['high_24h'] : null,
                'low'    => isset($r['low_24h']) ? $r['low_24h'] : null,
                'spark'  => count($spark) ? $spark : array($r['current_price'], $r['current_price']),
                'volume' => isset($r['total_volume']) ? $r['total_volume'] : null,
                'mcap'   => isset($r['market_cap']) ? $r['market_cap'] : null,
                'source' => 'live',
            ));
        }
        return array('live', $out);
    }

    function lunax_fetch_fx($defs)
    {
        $syms  = implode(',', array_map(function ($f) { return $f['sym']; }, $defs));
        $start = gmdate('Y-m-d', time() - 11 * 86400);
        $url   = 'https://api.frankfurter.app/' . $start . '..?base=USD&symbols=' . $syms;
        $data  = lunax_get_json($url, 9);
        $rates = (is_array($data) && isset($data['rates']) && is_array($data['rates'])) ? $data['rates'] : null;
        if (!$rates || !count($rates)) {
            return array('sim', array_map(function ($d) { return lunax_sim_asset('fx', $d, 0.006); }, $defs));
        }
        $dates = array_keys($rates);
        sort($dates);
        $out = array();
        foreach ($defs as $def) {
            $series = array();
            foreach ($dates as $dt) {
                if (!isset($rates[$dt][$def['sym']])) {
                    continue;
                }
                $raw = $rates[$dt][$def['sym']];
                if (!is_numeric($raw) || $raw == 0) {
                    continue;
                }
                $series[] = $def['invert'] ? (1.0 / $raw) : (float) $raw;
            }
            if (count($series) < 2) {
                $out[] = lunax_sim_asset('fx', $def, 0.006);
                continue;
            }
            $price = $series[count($series) - 1];
            $prev  = $series[count($series) - 2];
            $out[] = lunax_normalise('fx', $def, array(
                'price'  => $price,
                'change' => $prev ? (($price - $prev) / $prev) * 100 : 0,
                'high'   => max($series),
                'low'    => min($series),
                'spark'  => $series,
                'source' => 'live',
            ));
        }
        return array('live', $out);
    }

    function lunax_fetch_metals($defs)
    {
        $out     = array();
        $anyLive = false;
        foreach ($defs as $def) {
            $d     = lunax_get_json('https://api.gold-api.com/price/' . $def['id'], 7);
            $price = null;
            if (is_array($d)) {
                if (isset($d['price'])) {
                    $price = $d['price'];
                } elseif (isset($d['rate'])) {
                    $price = $d['rate'];
                }
            }
            if ($price === null || !is_numeric($price)) {
                $out[] = lunax_sim_asset('metals', $def, 0.012);
                continue;
            }
            $price = (float) $price;
            // Intraday history isn't provided → the sparkline is illustrative.
            $spark = lunax_walk($price, 40, 0.006);
            $spark[count($spark) - 1] = $price;
            $out[] = lunax_normalise('metals', $def, array(
                'price'  => $price,
                'change' => (($price / $spark[0]) - 1) * 100,
                'high'   => max($spark),
                'low'    => min($spark),
                'spark'  => $spark,
                'source' => 'live',
            ));
            $anyLive = true;
        }
        return array($anyLive ? 'live' : 'sim', $out);
    }

    function lunax_build_indices($defs)
    {
        // No reliable free CORS/keyless feed → always simulated, clearly labelled.
        return array('sim', array_map(function ($d) { return lunax_sim_asset('indices', $d, 0.008); }, $defs));
    }

    /* ------------------------------------------------------------------ */
    /* News (RSS headlines with a simulated fallback)                     */
    /* ------------------------------------------------------------------ */
    function lunax_time_ago($ts)
    {
        $diff = time() - $ts;
        if ($diff < 0) {
            $diff = 0;
        }
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return (int) floor($diff / 60) . 'm ago';
        }
        if ($diff < 86400) {
            return (int) floor($diff / 3600) . 'h ago';
        }
        return (int) floor($diff / 86400) . 'd ago';
    }

    function lunax_parse_rss($xmlStr, $fallbackSource, $limit = 8)
    {
        if (!function_exists('simplexml_load_string')) {
            return null;
        }
        $prev = libxml_use_internal_errors(true);
        $xml  = simplexml_load_string($xmlStr);
        libxml_use_internal_errors($prev);
        if ($xml === false) {
            return null;
        }
        $nodes = null;
        if (isset($xml->channel->item)) {
            $nodes = $xml->channel->item;   // RSS 2.0
        } elseif (isset($xml->entry)) {
            $nodes = $xml->entry;           // Atom
        }
        if (!$nodes) {
            return null;
        }
        $items = array();
        foreach ($nodes as $it) {
            $title = trim((string) $it->title);
            if ($title === '') {
                continue;
            }
            // Link (RSS text node, or Atom href attribute).
            $link = '';
            if (isset($it->link)) {
                if (isset($it->link['href'])) {
                    $link = (string) $it->link['href'];
                } else {
                    $link = trim((string) $it->link);
                }
            }
            // Summary.
            $desc = '';
            if (isset($it->description)) {
                $desc = (string) $it->description;
            } elseif (isset($it->summary)) {
                $desc = (string) $it->summary;
            }
            $desc = trim(preg_replace('/\s+/', ' ', strip_tags($desc)));
            if (function_exists('mb_strlen') ? mb_strlen($desc) > 160 : strlen($desc) > 160) {
                $desc = (function_exists('mb_substr') ? mb_substr($desc, 0, 157) : substr($desc, 0, 157)) . '…';
            }
            // Timestamp.
            $when = '';
            if (isset($it->pubDate)) {
                $when = (string) $it->pubDate;
            } elseif (isset($it->updated)) {
                $when = (string) $it->updated;
            } elseif (isset($it->published)) {
                $when = (string) $it->published;
            }
            $ts = $when ? strtotime($when) : false;
            // Google News titles come as "Headline - Source".
            $src = $fallbackSource;
            if (preg_match('/^(.*?) - ([^-]{2,40})$/u', $title, $m)) {
                $title = trim($m[1]);
                $src   = trim($m[2]);
            }
            $items[] = array(
                'title'   => $title,
                'url'     => $link,
                'summary' => $desc,
                'source'  => $src,
                'time'    => $ts ? $ts : null,
                'ago'     => $ts ? lunax_time_ago($ts) : '',
            );
            if (count($items) >= $limit) {
                break;
            }
        }
        return count($items) ? $items : null;
    }

    function lunax_sim_news()
    {
        $now  = time();
        $seed = array(
            array('Next-gen flagship phones headline a packed launch week', 'New foldables, faster silicon and brighter displays lead a busy stretch of consumer-tech reveals.', 'Tech wire', 42),
            array('Laptop makers push thinner chassis with all-day battery', 'A wave of ultraportables pairs efficiency-focused chips with larger batteries for longer unplugged runtimes.', 'Tech wire', 96),
            array('Audio brands lean into spatial sound and lossless streaming', 'Fresh earbuds and headphones emphasise adaptive noise control and higher-resolution playback.', 'Tech wire', 170),
            array('Consumer drones add smarter obstacle avoidance', 'Updated camera drones focus on stability, longer range and easier one-tap cinematic modes.', 'Tech wire', 240),
            array('Markets steady as investors weigh growth and rates', 'Cross-asset moves stay contained, with metals firm and major currencies rangebound.', 'Markets wire', 300),
            array('Crypto majors consolidate after a volatile session', 'Large-cap tokens trade in a tight band as liquidity normalises across venues.', 'Markets wire', 380),
        );
        $out = array();
        foreach ($seed as $s) {
            $ts    = $now - $s[3] * 60;
            $out[] = array(
                'title'   => $s[0],
                'url'     => '',
                'summary' => $s[1],
                'source'  => $s[2],
                'time'    => $ts,
                'ago'     => lunax_time_ago($ts),
            );
        }
        return $out;
    }

    function lunax_fetch_news()
    {
        $feeds = array(
            array(
                'url'   => 'https://news.google.com/rss/search?q=' .
                    rawurlencode('(technology OR smartphones OR laptops OR gadgets) when:3d') .
                    '&hl=en-US&gl=US&ceid=US:en',
                'label' => 'Tech wire',
            ),
            array(
                'url'   => 'https://news.google.com/rss/search?q=' .
                    rawurlencode('(markets OR stocks OR crypto OR gold) when:2d') .
                    '&hl=en-US&gl=US&ceid=US:en',
                'label' => 'Markets wire',
            ),
        );
        foreach ($feeds as $f) {
            $raw = lunax_http_get($f['url'], 8);
            if ($raw === null) {
                continue;
            }
            $items = lunax_parse_rss($raw, $f['label'], 8);
            if ($items) {
                return array('live', $items);
            }
        }
        return array('sim', lunax_sim_news());
    }

    /* ------------------------------------------------------------------ */
    /* Assemble everything                                                */
    /* ------------------------------------------------------------------ */
    function lunax_build_all()
    {
        $defs = lunax_defs();

        list($cSrc, $crypto)  = lunax_fetch_crypto($defs['crypto']);
        list($mSrc, $metals)  = lunax_fetch_metals($defs['metals']);
        list($fSrc, $fx)      = lunax_fetch_fx($defs['fx']);
        list($iSrc, $indices) = lunax_build_indices($defs['indices']);
        list($nSrc, $news)    = lunax_fetch_news();

        $assets = array();
        $bucket = array('crypto' => $crypto, 'metals' => $metals, 'fx' => $fx, 'indices' => $indices);
        foreach ($bucket as $type => $list) {
            foreach ($list as $a) {
                $assets[$type . ':' . $a['id']] = $a;
            }
        }

        // Stable display order for the yellow all-data panel.
        $order = array();
        foreach (array('crypto', 'metals', 'fx', 'indices') as $sec) {
            foreach ($defs[$sec] as $def) {
                $k = $sec . ':' . $def['id'];
                if (isset($assets[$k])) {
                    $order[] = $k;
                }
            }
        }

        return array(
            'assets'  => $assets,
            'sources' => array(
                'crypto'  => $cSrc,
                'metals'  => $mSrc,
                'fx'      => $fSrc,
                'indices' => $iSrc,
                'news'    => $nSrc,
            ),
            'order'        => $order,
            'news'         => $news,
            'lastUpdated'  => gmdate('c'),
            'updatedLabel' => date('g:i A'),
        );
    }

    /**
     * Public entry point. Returns the assembled data array, using a short-lived
     * on-disk cache so repeated page loads are fast and don't hammer the APIs.
     */
    function lunax_load_all($ttl = 90)
    {
        $cacheFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'lunax_data_cache.json';
        if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
            $cached = json_decode(@file_get_contents($cacheFile), true);
            if (is_array($cached) && isset($cached['assets'])) {
                return $cached;
            }
        }
        $data = lunax_build_all();
        @file_put_contents($cacheFile, json_encode($data));
        return $data;
    }

    /* ------------------------------------------------------------------ */
    /* Formatters for server-side rendering (mirror the JS helpers)       */
    /* ------------------------------------------------------------------ */
    function lunax_money($v)
    {
        if (!is_numeric($v)) {
            return '—';
        }
        $abs = abs($v);
        $dp  = $abs >= 1 ? 2 : ($abs >= 0.1 ? 4 : 6);
        return '$' . number_format($v, $dp);
    }

    function lunax_fxfmt($v)
    {
        if (!is_numeric($v)) {
            return '—';
        }
        $dp = $v >= 100 ? 2 : ($v >= 10 ? 3 : 4);
        return number_format($v, $dp);
    }

    function lunax_pct($v)
    {
        if (!is_numeric($v)) {
            return '—';
        }
        return ($v >= 0 ? '+' : '') . number_format($v, 2) . '%';
    }

    function lunax_price_of($a)
    {
        if (!isset($a['price']) || !is_numeric($a['price'])) {
            return '—';
        }
        return $a['type'] === 'fx' ? lunax_fxfmt($a['price']) : lunax_money($a['price']);
    }
}