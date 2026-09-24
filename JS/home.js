/* ============================================================================
   LUNAX — Live Data World engine  (B1.js)
   ----------------------------------------------------------------------------
   Client renderer for the PHP build (home.php + lunax-data.php + B2.css + B3.js).

   All market data is fetched SERVER-SIDE by lunax-data.php and handed to the
   page as window.LUNAX_DATA — there is no browser fetching, no API key and no
   AI here. This script hydrates from that payload and renders:

     1. The Crypto / Metals / Currency market lists in the left panels.
     2. The sidebar live ticker.
     3. A rich per-asset DETAIL overlay (click any row / tile / ticker item).
     4. The "WORLD" dashboard: an interactive world map (click a financial
        centre for a short static briefing) on top of a cross-asset dashboard.

   The refresh control reloads the page, which re-runs the PHP data layer.
   ========================================================================== */
(function () {
  'use strict';

  /* ---------------------------------------------------------------------- */
  /* Asset universe (order + labels; live values come from window.LUNAX_DATA)*/
  /* ---------------------------------------------------------------------- */
  var CRYPTO = [
    { id: 'bitcoin', ticker: 'BTC', name: 'Bitcoin' },
    { id: 'ethereum', ticker: 'ETH', name: 'Ethereum' },
    { id: 'solana', ticker: 'SOL', name: 'Solana' },
    { id: 'ripple', ticker: 'XRP', name: 'XRP' },
    { id: 'dogecoin', ticker: 'DOGE', name: 'Dogecoin' }
  ];
  var METALS = [
    { id: 'XAU', ticker: 'XAU/oz', name: 'Gold' },
    { id: 'XAG', ticker: 'XAG/oz', name: 'Silver' },
    { id: 'XPT', ticker: 'XPT/oz', name: 'Platinum' },
    { id: 'XPD', ticker: 'XPD/oz', name: 'Palladium' }
  ];
  var FX = [
    { id: 'EURUSD', name: 'EUR / USD' },
    { id: 'GBPUSD', name: 'GBP / USD' },
    { id: 'USDJPY', name: 'USD / JPY' },
    { id: 'USDCNY', name: 'USD / CNY' },
    { id: 'AUDUSD', name: 'AUD / USD' }
  ];
  var INDICES = [
    { id: 'SPX', ticker: 'US500', name: 'S&P 500' },
    { id: 'NDX', ticker: 'US100', name: 'Nasdaq 100' },
    { id: 'DJI', ticker: 'US30', name: 'Dow Jones' },
    { id: 'UKX', ticker: 'UK100', name: 'FTSE 100' }
  ];

  // Financial-centre hotspots for the world map. lon/lat → equirectangular.
  var CENTRES = [
    { id: 'ny', label: 'New York', lon: -74, lat: 40.7, anchor: 'US equities, the dollar and Bitcoin liquidity' },
    { id: 'ldn', label: 'London', lon: -0.1, lat: 51.5, anchor: 'FX, gold fixing and European risk' },
    { id: 'fra', label: 'Frankfurt', lon: 8.7, lat: 50.1, anchor: 'the euro and ECB policy' },
    { id: 'dxb', label: 'Dubai', lon: 55.3, lat: 25.2, anchor: 'gold flows and Gulf capital' },
    { id: 'hk', label: 'Hong Kong', lon: 114.2, lat: 22.3, anchor: 'Asian equities and the yuan' },
    { id: 'tky', label: 'Tokyo', lon: 139.7, lat: 35.7, anchor: 'the yen and the Asian session open' },
    { id: 'sgp', label: 'Singapore', lon: 103.8, lat: 1.35, anchor: 'commodities and regional FX' },
    { id: 'syd', label: 'Sydney', lon: 151.2, lat: -33.9, anchor: 'the Aussie dollar and the first open of the day' }
  ];
  var LINKS = [[0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [4, 6], [6, 7], [0, 5]];

  /* ---------------------------------------------------------------------- */
  /* State                                                                  */
  /* ---------------------------------------------------------------------- */
  var state = {
    assets: {},        // key "type:id" -> normalised asset object
    sources: {},       // section -> 'live' | 'sim'
    lastUpdated: null,
    worldBuilt: false
  };

  /* ---------------------------------------------------------------------- */
  /* Tiny helpers                                                           */
  /* ---------------------------------------------------------------------- */
  function h(html) {
    var t = document.createElement('template');
    t.innerHTML = String(html).trim();
    return t.content.firstElementChild;
  }
  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function keyOf(type, id) { return type + ':' + id; }

  function money(v) {
    if (v == null || !isFinite(v)) return '—';
    var abs = Math.abs(v);
    var dp = abs >= 1000 ? 2 : abs >= 1 ? 2 : abs >= 0.1 ? 4 : 6;
    return '$' + v.toLocaleString('en-US', { minimumFractionDigits: dp, maximumFractionDigits: dp });
  }
  function fxfmt(v) {
    if (v == null || !isFinite(v)) return '—';
    var dp = v >= 100 ? 2 : v >= 10 ? 3 : 4;
    return v.toLocaleString('en-US', { minimumFractionDigits: dp, maximumFractionDigits: dp });
  }
  function compact(v) {
    if (v == null || !isFinite(v)) return '—';
    var a = Math.abs(v), s = v < 0 ? '-' : '';
    if (a >= 1e12) return s + '$' + (a / 1e12).toFixed(2) + 'T';
    if (a >= 1e9) return s + '$' + (a / 1e9).toFixed(2) + 'B';
    if (a >= 1e6) return s + '$' + (a / 1e6).toFixed(2) + 'M';
    if (a >= 1e3) return s + '$' + (a / 1e3).toFixed(2) + 'K';
    return s + '$' + a.toFixed(0);
  }
  function pct(v) {
    if (v == null || !isFinite(v)) return '—';
    return (v >= 0 ? '+' : '') + v.toFixed(2) + '%';
  }
  function priceOf(asset, v) {
    if (v == null) v = asset.price;
    return asset.type === 'fx' ? fxfmt(v) : money(v);
  }
  function displayPrice(asset) { return priceOf(asset, asset.price); }

  // Downsample an array to at most n points (keeps first + last).
  function sample(arr, n) {
    if (!arr || arr.length <= n) return (arr || []).slice();
    var out = [], step = (arr.length - 1) / (n - 1);
    for (var i = 0; i < n; i++) out.push(arr[Math.round(i * step)]);
    return out;
  }

  /* ---------------------------------------------------------------------- */
  /* Hydrate from the server payload                                        */
  /* ---------------------------------------------------------------------- */
  function hydrate() {
    var D = window.LUNAX_DATA || {};
    state.assets = (D.assets && typeof D.assets === 'object') ? D.assets : {};
    state.sources = (D.sources && typeof D.sources === 'object') ? D.sources : {};
    state.lastUpdated = D.lastUpdated ? new Date(D.lastUpdated) : new Date();
  }

  /* ---------------------------------------------------------------------- */
  /* SVG sparkline / area chart                                             */
  /* ---------------------------------------------------------------------- */
  function sparkPath(vals, w, hgt, pad) {
    pad = pad || 1;
    var min = Math.min.apply(null, vals), max = Math.max.apply(null, vals);
    var span = (max - min) || 1;
    var n = vals.length;
    return vals.map(function (v, i) {
      var x = pad + (i / (n - 1)) * (w - pad * 2);
      var y = pad + (1 - (v - min) / span) * (hgt - pad * 2);
      return (i ? 'L' : 'M') + x.toFixed(1) + ' ' + y.toFixed(1);
    }).join(' ');
  }
  function miniSpark(vals, up) {
    vals = sample(vals, 22);
    if (vals.length < 2) return '';
    var w = 54, hgt = 22, col = up ? '#1a9c5c' : '#d1373f';
    return '<svg class="ms" viewBox="0 0 ' + w + ' ' + hgt + '" preserveAspectRatio="none" aria-hidden="true">' +
      '<path d="' + sparkPath(vals, w, hgt, 2) + '" fill="none" stroke="' + col + '" stroke-width="1.6" ' +
      'stroke-linejoin="round" stroke-linecap="round"/></svg>';
  }
  function areaChart(vals, up, id) {
    vals = sample(vals, 60);
    if (vals.length < 2) return '<div class="ad-chart-empty">No chart data</div>';
    var w = 560, hgt = 190, pad = 8;
    var line = sparkPath(vals, w, hgt, pad);
    var col = up ? '#38d996' : '#ff6b74';
    var lastX = w - pad, min = Math.min.apply(null, vals), max = Math.max.apply(null, vals), span = (max - min) || 1;
    var lastY = pad + (1 - (vals[vals.length - 1] - min) / span) * (hgt - pad * 2);
    var gid = 'g_' + id;
    return '<svg class="ad-chart-svg" viewBox="0 0 ' + w + ' ' + hgt + '" preserveAspectRatio="none">' +
      '<defs><linearGradient id="' + gid + '" x1="0" y1="0" x2="0" y2="1">' +
      '<stop offset="0" stop-color="' + col + '" stop-opacity="0.34"/>' +
      '<stop offset="1" stop-color="' + col + '" stop-opacity="0"/></linearGradient></defs>' +
      '<path d="' + line + ' L ' + lastX.toFixed(1) + ' ' + (hgt - pad) + ' L ' + pad + ' ' + (hgt - pad) + ' Z" fill="url(#' + gid + ')"/>' +
      '<path d="' + line + '" fill="none" stroke="' + col + '" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>' +
      '<circle cx="' + lastX.toFixed(1) + '" cy="' + lastY.toFixed(1) + '" r="3.4" fill="' + col + '"/>' +
      '</svg>';
  }

  /* ---------------------------------------------------------------------- */
  /* Market lists inside the Crypto / Metals / Currency panels              */
  /* ---------------------------------------------------------------------- */
  function rowHTML(a) {
    var up = (a.change || 0) >= 0;
    var tick = a.ticker && a.ticker !== a.name ? ' <span class="market-ticker">' + esc(a.ticker) + '</span>' : '';
    return '<div class="market-row market-row--live" data-asset="' + keyOf(a.type, a.id) + '" role="button" tabindex="0" ' +
      'aria-label="' + esc(a.name) + ', ' + displayPrice(a) + ', ' + pct(a.change) + '. Open details.">' +
      '<span class="market-name">' + esc(a.name) + tick + '</span>' +
      '<span class="market-spark">' + miniSpark(a.spark, up) + '</span>' +
      '<span class="market-price">' + displayPrice(a) + '</span>' +
      '<span class="market-change ' + (up ? 'up' : 'down') + '">' + pct(a.change) + '</span>' +
      '</div>';
  }
  function srcBadge(src) {
    return src === 'live'
      ? '<span class="src-badge live"><span class="src-dot"></span>Live</span>'
      : '<span class="src-badge sim"><span class="src-dot"></span>Simulated</span>';
  }
  function srcLabel(section) {
    var src = state.sources[section];
    var feed = { crypto: 'CoinGecko', fx: 'Frankfurter · ECB', metals: 'gold-api.com', indices: 'simulated feed' }[section];
    if (src === 'live') return 'Live · ' + feed + ' · updated ' + timeAgo();
    return 'Simulated data — live ' + feed + ' feed unavailable · ' + timeAgo();
  }
  function timeAgo() {
    if (!state.lastUpdated) return 'just now';
    return state.lastUpdated.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  }

  function paintList(section, listId) {
    var el = document.getElementById(listId);
    if (!el) return;
    var items = orderedAssets(section);
    el.innerHTML = items.map(rowHTML).join('');
    // Update this panel's footer "Live · …" line with the true source.
    var panel = el.closest('.center-panel');
    if (panel) {
      var foot = panel.querySelector('.market-updated');
      if (foot) {
        foot.innerHTML = '<span class="ainews-live-dot' + (state.sources[section] === 'live' ? '' : ' is-sim') + '" aria-hidden="true"></span>' + esc(srcLabel(section));
      }
    }
  }
  function orderedAssets(section) {
    var defs = { crypto: CRYPTO, metals: METALS, fx: FX, indices: INDICES }[section] || [];
    var out = [];
    defs.forEach(function (def) {
      var a = state.assets[keyOf(section, def.id)];
      if (a) out.push(a);
    });
    return out;
  }
  function paintMarketPanels() {
    paintList('crypto', 'mlist-crypto');
    paintList('metals', 'mlist-metals');
    paintList('fx', 'mlist-currency');
  }

  /* ---------------------------------------------------------------------- */
  /* Sidebar: live ticker                                                   */
  /* ---------------------------------------------------------------------- */
  var TICKER = [['crypto', 'bitcoin'], ['crypto', 'ethereum'], ['metals', 'XAU'], ['metals', 'XAG'], ['fx', 'EURUSD'], ['fx', 'USDJPY'], ['indices', 'SPX']];
  function paintTicker() {
    var el = document.getElementById('catTicker');
    if (!el) return;
    var cells = TICKER.map(function (t) {
      var a = state.assets[keyOf(t[0], t[1])];
      if (!a) return '';
      var up = (a.change || 0) >= 0;
      return '<span class="cat-ticker-item" data-asset="' + keyOf(a.type, a.id) + '">' +
        '<b>' + esc(a.ticker || a.name) + '</b> ' + displayPrice(a) +
        ' <i class="' + (up ? 'up' : 'down') + '">' + pct(a.change) + '</i></span>';
    }).join('');
    // duplicate for a seamless marquee loop
    el.innerHTML = '<div class="cat-ticker-track">' + cells + cells + '</div>';
  }

  /* ---------------------------------------------------------------------- */
  /* Detail view (overlay)                                                  */
  /* ---------------------------------------------------------------------- */
  var detailEl, detailBackdrop, detailKey = null;
  function buildDetail() {
    if (detailEl) return;
    detailBackdrop = h('<div class="asset-detail-backdrop" id="assetDetailBackdrop"></div>');
    detailEl = h(
      '<div class="asset-detail" id="assetDetail" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Asset detail">' +
      '<button class="ad-close" type="button" aria-label="Close detail">✕</button>' +
      '<div class="ad-inner"></div></div>');
    document.body.appendChild(detailBackdrop);
    document.body.appendChild(detailEl);
    detailBackdrop.addEventListener('click', closeDetail);
    detailEl.querySelector('.ad-close').addEventListener('click', closeDetail);
  }
  function closeDetail() {
    if (!detailEl) return;
    detailEl.classList.remove('open');
    detailBackdrop.classList.remove('open');
    detailEl.setAttribute('aria-hidden', 'true');
    detailKey = null;
  }
  function openDetail(key) {
    var a = state.assets[key];
    if (!a) return;
    buildDetail();
    detailKey = key;
    var up = (a.change || 0) >= 0;
    var trend = a.spark && a.spark.length > 1 ? (a.spark[a.spark.length - 1] >= a.spark[0] ? 'up' : 'down') : 'flat';
    var rangeTxt = (a.low != null && a.high != null) ? priceOf(a, a.low) + ' – ' + priceOf(a, a.high) : '—';
    var big = a.type === 'crypto' ? (a.mcap != null ? { k: 'Market cap', v: compact(a.mcap) } : null)
      : null;
    var vol = a.volume != null ? { k: '24h volume', v: compact(a.volume) } : null;
    var histNote = a.type === 'fx' ? 'Daily ECB reference rates (Frankfurter).'
      : a.type === 'metals' && a.source === 'live' ? 'Spot price is live; intraday shape is illustrative.'
        : a.type === 'indices' ? 'Simulated index data for demonstration.'
          : a.source === 'live' ? '7-day price history.' : 'Simulated data for demonstration.';

    var stats = [
      { k: '24h high', v: a.high != null ? priceOf(a, a.high) : '—' },
      { k: '24h low', v: a.low != null ? priceOf(a, a.low) : '—' },
      { k: 'Range', v: rangeTxt },
      big, vol,
      { k: 'Trend', v: trend === 'up' ? '▲ rising' : trend === 'down' ? '▼ falling' : '—' }
    ].filter(Boolean);

    var html =
      '<div class="ad-head">' +
      '<div class="ad-id"><div class="ad-name">' + esc(a.name) + '</div>' +
      '<div class="ad-sub">' + esc(a.ticker || '') + ' · ' + srcBadge(a.source) + '</div></div>' +
      '<div class="ad-price"><div class="ad-px">' + displayPrice(a) + '</div>' +
      '<div class="ad-chg ' + (up ? 'up' : 'down') + '">' + (up ? '▲ ' : '▼ ') + pct(a.change) + '</div></div>' +
      '</div>' +
      '<div class="ad-chart">' + areaChart(a.spark, trend !== 'down', a.id) +
      '<div class="ad-chart-note">' + esc(histNote) + '</div></div>' +
      '<div class="ad-stats">' + stats.map(function (s) {
        return '<div class="ad-stat"><span class="ad-stat-k">' + esc(s.k) + '</span><span class="ad-stat-v">' + s.v + '</span></div>';
      }).join('') + '</div>';

    detailEl.querySelector('.ad-inner').innerHTML = html;
    detailEl.classList.add('open');
    detailBackdrop.classList.add('open');
    detailEl.setAttribute('aria-hidden', 'false');
    detailEl.scrollTop = 0;
  }

  /* ---------------------------------------------------------------------- */
  /* World map + dashboard                                                  */
  /* ---------------------------------------------------------------------- */
  var CONTINENTS = [
    'M64,44 L96,34 L128,38 L134,52 L118,58 L120,72 L104,86 L92,72 L82,92 L74,74 L64,64 Z',   // N America
    'M150,30 L166,28 L168,40 L154,44 Z',                                                       // Greenland
    'M108,96 L130,98 L128,120 L116,150 L106,132 L102,112 Z',                                   // S America
    'M168,36 L200,32 L204,46 L184,52 L170,50 Z',                                               // Europe
    'M176,58 L214,56 L226,86 L210,120 L192,120 L182,92 L176,74 Z',                             // Africa
    'M204,30 L300,26 L326,48 L306,74 L262,72 L258,90 L246,72 L224,60 L208,46 Z',               // Asia
    'M306,110 L342,108 L340,130 L312,134 Z'                                                    // Australia
  ];
  function proj(lon, lat) { return [lon + 180, 90 - lat]; }

  function worldMapSVG() {
    var parts = [];
    parts.push('<svg class="wmap-svg" viewBox="0 0 360 180" preserveAspectRatio="xMidYMid meet" role="img" aria-label="World map of financial centres">');
    parts.push('<rect x="0" y="0" width="360" height="180" fill="url(#wmapBg)"/>');
    parts.push('<defs><radialGradient id="wmapBg" cx="50%" cy="42%" r="75%">' +
      '<stop offset="0" stop-color="#1a1712"/><stop offset="1" stop-color="#0b0a08"/></radialGradient></defs>');
    // graticule
    var g = '';
    for (var lon = 0; lon <= 360; lon += 30) g += '<line x1="' + lon + '" y1="0" x2="' + lon + '" y2="180"/>';
    for (var lat = 0; lat <= 180; lat += 30) g += '<line x1="0" y1="' + lat + '" x2="360" y2="' + lat + '"/>';
    parts.push('<g class="wmap-grid">' + g + '</g>');
    // continents
    parts.push('<g class="wmap-land">' + CONTINENTS.map(function (d) { return '<path d="' + d + '"/>'; }).join('') + '</g>');
    // links
    var linkP = LINKS.map(function (pair) {
      var a = proj(CENTRES[pair[0]].lon, CENTRES[pair[0]].lat);
      var b = proj(CENTRES[pair[1]].lon, CENTRES[pair[1]].lat);
      var mx = (a[0] + b[0]) / 2, my = Math.min(a[1], b[1]) - Math.abs(a[0] - b[0]) * 0.12 - 6;
      return '<path d="M' + a[0] + ',' + a[1] + ' Q' + mx + ',' + my + ' ' + b[0] + ',' + b[1] + '"/>';
    }).join('');
    parts.push('<g class="wmap-links">' + linkP + '</g>');
    // hotspots
    var hs = CENTRES.map(function (c) {
      var p = proj(c.lon, c.lat);
      var labelAnchor = p[0] > 300 ? 'end' : 'start';
      var lx = labelAnchor === 'end' ? p[0] - 5 : p[0] + 5;
      return '<g class="wmap-hot" data-region="' + c.id + '" tabindex="0" role="button" aria-label="' + esc(c.label) + ' briefing">' +
        '<circle class="wmap-ring" cx="' + p[0] + '" cy="' + p[1] + '" r="4"/>' +
        '<circle class="wmap-core" cx="' + p[0] + '" cy="' + p[1] + '" r="2.2"/>' +
        '<text class="wmap-label" x="' + lx + '" y="' + (p[1] - 5) + '" text-anchor="' + labelAnchor + '">' + esc(c.label) + '</text>' +
        '</g>';
    }).join('');
    parts.push('<g class="wmap-hots">' + hs + '</g>');
    parts.push('</svg>');
    return parts.join('');
  }

  function kpiTile(key) {
    var a = state.assets[key];
    if (!a) return '<div class="kpi-tile is-skeleton"><span class="sk sk-name"></span><span class="sk sk-price"></span></div>';
    var up = (a.change || 0) >= 0;
    var trendUp = a.spark && a.spark.length > 1 ? a.spark[a.spark.length - 1] >= a.spark[0] : up;
    return '<button class="kpi-tile" type="button" data-asset="' + key + '">' +
      '<div class="kpi-top"><span class="kpi-name">' + esc(a.ticker || a.name) + '</span>' + srcBadge(a.source) + '</div>' +
      '<div class="kpi-spark">' + miniSpark(a.spark, trendUp) + '</div>' +
      '<div class="kpi-bottom"><span class="kpi-px">' + displayPrice(a) + '</span>' +
      '<span class="kpi-chg ' + (up ? 'up' : 'down') + '">' + pct(a.change) + '</span></div>' +
      '</button>';
  }

  var DASH = [
    ['crypto', 'bitcoin'], ['crypto', 'ethereum'], ['crypto', 'solana'],
    ['metals', 'XAU'], ['metals', 'XAG'], ['metals', 'XPT'],
    ['fx', 'EURUSD'], ['fx', 'GBPUSD'], ['fx', 'USDJPY'],
    ['indices', 'SPX'], ['indices', 'NDX'], ['indices', 'DJI']
  ];

  function buildWorld() {
    var host = document.getElementById('worldPanelBody');
    if (!host) return;
    host.innerHTML =
      '<div class="world-map-wrap"><div class="wmap-frame">' + worldMapSVG() + '</div>' +
      '<div class="wmap-brief" id="worldBrief"><span class="wmap-brief-hint">Tap a glowing financial centre for a regional briefing.</span></div>' +
      '</div>' +
      '<div class="world-dash-head"><span>Global dashboard</span><span class="world-dash-sub" id="worldDashSub"></span></div>' +
      '<div class="world-dash" id="worldDash"></div>';
    state.worldBuilt = true;
    paintWorld();
  }
  function paintWorld() {
    if (!state.worldBuilt) return;
    var dash = document.getElementById('worldDash');
    if (dash) dash.innerHTML = DASH.map(function (t) { return kpiTile(keyOf(t[0], t[1])); }).join('');
    var sub = document.getElementById('worldDashSub');
    if (sub) {
      var live = ['crypto', 'metals', 'fx'].filter(function (s) { return state.sources[s] === 'live'; });
      sub.textContent = live.length
        ? 'Live: ' + live.join(', ') + ' · indices simulated · ' + timeAgo()
        : 'Simulated data · ' + timeAgo();
    }
  }

  // Static regional briefing (no AI): the centre's focus + relevant live levels.
  function levelLine(type, id) {
    var a = state.assets[keyOf(type, id)];
    if (!a) return '';
    var up = (a.change || 0) >= 0;
    return '<span class="wb-name">' + esc(a.ticker || a.name) + '</span> ' + displayPrice(a) +
      ' <span class="' + (up ? 'up' : 'down') + '">' + pct(a.change) + '</span>';
  }
  function regionBrief(regionId) {
    var c = null;
    for (var i = 0; i < CENTRES.length; i++) if (CENTRES[i].id === regionId) c = CENTRES[i];
    if (!c) return;
    var box = document.getElementById('worldBrief');
    if (!box) return;
    box.classList.add('active');
    var relevant = [];
    if (regionId === 'ny') relevant = [levelLine('indices', 'SPX'), levelLine('crypto', 'bitcoin')];
    else if (regionId === 'ldn' || regionId === 'dxb') relevant = [levelLine('metals', 'XAU'), levelLine('fx', 'GBPUSD')];
    else if (regionId === 'fra') relevant = [levelLine('fx', 'EURUSD'), levelLine('indices', 'UKX')];
    else if (regionId === 'tky') relevant = [levelLine('fx', 'USDJPY')];
    else if (regionId === 'hk' || regionId === 'sgp') relevant = [levelLine('fx', 'USDCNY'), levelLine('metals', 'XAG')];
    else if (regionId === 'syd') relevant = [levelLine('fx', 'AUDUSD')];
    relevant = relevant.filter(Boolean);
    var headHTML = '<div class="wb-title"><span class="wb-dot"></span>' + esc(c.label) + '</div>';
    var body = '<p>' + esc(c.label) + ' anchors ' + esc(c.anchor) + '.</p>';
    if (relevant.length) {
      body += '<ul class="wb-levels">' + relevant.map(function (r) { return '<li>' + r + '</li>'; }).join('') + '</ul>';
    }
    box.innerHTML = headHTML + body;
  }

  /* ---------------------------------------------------------------------- */
  /* Events                                                                 */
  /* ---------------------------------------------------------------------- */
  function wireEvents() {
    // Open detail from any market row / KPI tile / ticker item / data pill.
    document.addEventListener('click', function (e) {
      var opener = e.target.closest('[data-asset]');
      if (opener && !opener.classList.contains('is-skeleton')) { openDetail(opener.getAttribute('data-asset')); return; }
      var hot = e.target.closest('.wmap-hot');
      if (hot) { regionBrief(hot.getAttribute('data-region')); return; }
      var refresh = e.target.closest('#catRefreshBtn');
      if (refresh) { refresh.classList.add('spin'); location.reload(); return; }
    });

    // Keyboard: activate row/tile/hotspot with Enter/Space; Esc closes detail
    // first (capture phase so it pre-empts B3's panel-close handler).
    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Enter' || e.key === ' ')) {
        var t = e.target;
        if (t.matches && t.matches('[data-asset], .wmap-hot')) {
          e.preventDefault();
          if (t.matches('.wmap-hot')) regionBrief(t.getAttribute('data-region'));
          else openDetail(t.getAttribute('data-asset'));
        }
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && detailKey) { e.stopImmediatePropagation(); closeDetail(); }
    }, true);

    // Build/refresh the WORLD dashboard whenever its sidebar entry is clicked.
    var worldLink = document.querySelector('[data-center-open="world"]');
    if (worldLink) worldLink.addEventListener('click', function () {
      setTimeout(function () { if (!state.worldBuilt) buildWorld(); else paintWorld(); }, 0);
    });
  }

  /* ---------------------------------------------------------------------- */
  /* Init                                                                   */
  /* ---------------------------------------------------------------------- */
  function repaintAll() {
    paintMarketPanels();
    paintTicker();
    paintWorld();
  }
  function init() {
    hydrate();
    buildDetail();
    wireEvents();
    repaintAll();
    buildWorld(); // build eagerly so the panel is ready on first open
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
