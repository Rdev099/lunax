/* ============================================================================
   LUNAX — Live Data World + AI engine  (B4.js)
   ----------------------------------------------------------------------------
   Adds four things on top of the static site (B1.html / B2.css / B3.js):

     1. LIVE MARKET DATA  — real free, keyless, CORS-friendly public APIs:
          • Crypto    → CoinGecko   (price, 24h %, high/low, volume, mcap, 7d spark)
          • Currency  → Frankfurter (ECB daily rates + short time-series)
          • Metals    → gold-api.com (best-effort spot price)
        Every fetch degrades gracefully to realistic *simulated* data when the
        network / API is unavailable, and each section shows whether it is
        showing LIVE or SIMULATED numbers.

     2. RICH DETAIL VIEW  — click any market row (or dashboard tile) to open a
        drill-down: big price + change, an SVG price chart, key stats
        (24h high / low / range, volume or market cap, 7d trend) and a short
        AI analysis for that specific asset.

     3. LIVE DATA WORLD   — a new "WORLD" sidebar entry opens a dashboard with an
        interactive world map (pulsing gold hotspots at financial centres, click
        for a regional AI briefing) on top of a unified cross-asset dashboard
        (crypto + metals + FX + indices, KPI tiles, sparklines) and an AI market
        summary.

     4. REAL LLM          — "Connect AI": the user pastes an Anthropic API key,
        stored ONLY in this browser (localStorage) and sent ONLY to
        api.anthropic.com. Claude then writes the market summary, per-asset
        analysis and regional briefings.

   No backend, no build step. Everything here is progressive enhancement — if a
   request fails, the page still works with simulated data and clear labels.
   ========================================================================== */
(function () {
  'use strict';

  /* ---------------------------------------------------------------------- */
  /* Config                                                                 */
  /* ---------------------------------------------------------------------- */
  var AI_URL = 'https://api.anthropic.com/v1/messages';
  var LS_KEY = 'lunax_ai_key';
  var LS_MODEL = 'lunax_ai_model';
  var REFRESH_MS = 90000; // refresh live data every 90s while the tab is visible

  var MODELS = [
    { id: 'claude-opus-5', label: 'Claude Opus 5 — deepest' },
    { id: 'claude-sonnet-5', label: 'Claude Sonnet 5 — balanced' },
    { id: 'claude-haiku-4-5', label: 'Claude Haiku 4.5 — fastest' }
  ];
  var DEFAULT_MODEL = 'claude-opus-5';

  // Asset universe. `id` is stable and used as the detail-view key.
  var CRYPTO = [
    { id: 'bitcoin', ticker: 'BTC', name: 'Bitcoin', base: 64210.5 },
    { id: 'ethereum', ticker: 'ETH', name: 'Ethereum', base: 3142.8 },
    { id: 'solana', ticker: 'SOL', name: 'Solana', base: 146.32 },
    { id: 'ripple', ticker: 'XRP', name: 'XRP', base: 0.612 },
    { id: 'dogecoin', ticker: 'DOGE', name: 'Dogecoin', base: 0.148 }
  ];
  var METALS = [
    { id: 'XAU', ticker: 'XAU/oz', name: 'Gold', base: 2384.2 },
    { id: 'XAG', ticker: 'XAG/oz', name: 'Silver', base: 28.61 },
    { id: 'XPT', ticker: 'XPT/oz', name: 'Platinum', base: 968.4 },
    { id: 'XPD', ticker: 'XPD/oz', name: 'Palladium', base: 1012.75 }
  ];
  // FX pairs quoted as base/quote. `sym` is the ECB symbol vs USD; `invert`
  // means the displayed price is USD-per-unit (1 / rate) rather than rate.
  var FX = [
    { id: 'EURUSD', name: 'EUR / USD', sym: 'EUR', invert: true, base: 1.0842 },
    { id: 'GBPUSD', name: 'GBP / USD', sym: 'GBP', invert: true, base: 1.2716 },
    { id: 'USDJPY', name: 'USD / JPY', sym: 'JPY', invert: false, base: 151.64 },
    { id: 'USDCNY', name: 'USD / CNY', sym: 'CNY', invert: false, base: 7.238 },
    { id: 'AUDUSD', name: 'AUD / USD', sym: 'AUD', invert: true, base: 0.6591 }
  ];
  // Indices have no reliable free CORS feed → always simulated, clearly labelled.
  var INDICES = [
    { id: 'SPX', ticker: 'US500', name: 'S&P 500', base: 5232.4 },
    { id: 'NDX', ticker: 'US100', name: 'Nasdaq 100', base: 18344.0 },
    { id: 'DJI', ticker: 'US30', name: 'Dow Jones', base: 39210.0 },
    { id: 'UKX', ticker: 'UK100', name: 'FTSE 100', base: 8214.0 }
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
  // A few great-circle-ish links drawn between centres (by index into CENTRES).
  var LINKS = [[0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [4, 6], [6, 7], [0, 5]];

  /* ---------------------------------------------------------------------- */
  /* State                                                                  */
  /* ---------------------------------------------------------------------- */
  var state = {
    assets: {},        // key "type:id" -> normalised asset object
    sources: {},       // section -> 'live' | 'sim'
    aiCache: {},       // cache key -> generated text (per session)
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

  // Format an asset's price with its own convention.
  function displayPrice(asset) { return priceOf(asset, asset.price); }

  /* ---------------------------------------------------------------------- */
  /* Simulated data (fallback + indices)                                    */
  /* ---------------------------------------------------------------------- */
  function walk(base, n, vol) {
    var out = [], v = base * (1 - vol * 0.5);
    for (var i = 0; i < n; i++) {
      v = v * (1 + (Math.random() - 0.48) * vol);
      out.push(v);
    }
    // normalise so the series ends near `base`
    var k = base / out[out.length - 1];
    return out.map(function (x) { return x * k; });
  }
  function simAsset(type, def, vol) {
    vol = vol || 0.02;
    var spark = walk(def.base, 48, vol);
    var price = spark[spark.length - 1];
    var change = (Math.random() - 0.45) * (vol * 200); // ~ +/- a couple %
    var hi = Math.max.apply(null, spark) * (1 + vol * 0.3);
    var lo = Math.min.apply(null, spark) * (1 - vol * 0.3);
    return normalise(type, def, {
      price: price, change: change, high: hi, low: lo, spark: spark,
      volume: type === 'crypto' ? def.base * 1e6 * (0.5 + Math.random()) : null,
      mcap: type === 'crypto' ? price * 1e7 * (1 + Math.random()) : null,
      source: 'sim'
    });
  }
  function normalise(type, def, d) {
    return {
      type: type, id: def.id, name: def.name, ticker: def.ticker || def.name,
      price: d.price, change: d.change, high: d.high, low: d.low,
      spark: d.spark && d.spark.length ? d.spark : [d.price, d.price],
      volume: d.volume != null ? d.volume : null,
      mcap: d.mcap != null ? d.mcap : null,
      source: d.source || 'sim'
    };
  }

  /* ---------------------------------------------------------------------- */
  /* Network helpers                                                        */
  /* ---------------------------------------------------------------------- */
  function getJSON(url, ms) {
    ms = ms || 9000;
    var ctrl = new AbortController();
    var timer = setTimeout(function () { ctrl.abort(); }, ms);
    return fetch(url, { signal: ctrl.signal, headers: { accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .finally(function () { clearTimeout(timer); });
  }

  // Downsample an array to at most n points (keeps first + last).
  function sample(arr, n) {
    if (!arr || arr.length <= n) return (arr || []).slice();
    var out = [], step = (arr.length - 1) / (n - 1);
    for (var i = 0; i < n; i++) out.push(arr[Math.round(i * step)]);
    return out;
  }

  /* ---------------------------------------------------------------------- */
  /* Fetchers (each resolves to an array of normalised assets)              */
  /* ---------------------------------------------------------------------- */
  function fetchCrypto() {
    var ids = CRYPTO.map(function (c) { return c.id; }).join(',');
    var url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=' + ids +
      '&order=market_cap_desc&sparkline=true&price_change_percentage=24h';
    return getJSON(url).then(function (rows) {
      var byId = {};
      rows.forEach(function (r) { byId[r.id] = r; });
      return CRYPTO.map(function (def) {
        var r = byId[def.id];
        if (!r || r.current_price == null) return simAsset('crypto', def, 0.03);
        var spark = (r.sparkline_in_7d && r.sparkline_in_7d.price) || [];
        return normalise('crypto', def, {
          price: r.current_price,
          change: r.price_change_percentage_24h,
          high: r.high_24h, low: r.low_24h,
          spark: spark.length ? spark : [r.current_price, r.current_price],
          volume: r.total_volume, mcap: r.market_cap, source: 'live'
        });
      });
    }).then(function (list) { state.sources.crypto = 'live'; return list; })
      .catch(function () {
        state.sources.crypto = 'sim';
        return CRYPTO.map(function (def) { return simAsset('crypto', def, 0.03); });
      });
  }

  function fetchFX() {
    var syms = FX.map(function (f) { return f.sym; }).join(',');
    var end = new Date();
    var start = new Date(end.getTime() - 11 * 864e5); // ~11 days back for a spark
    var d = function (x) { return x.toISOString().slice(0, 10); };
    var tsURL = 'https://api.frankfurter.app/' + d(start) + '..?base=USD&symbols=' + syms;
    return getJSON(tsURL).then(function (data) {
      var dates = Object.keys(data.rates || {}).sort();
      if (!dates.length) throw new Error('no fx');
      return FX.map(function (def) {
        var series = dates.map(function (dt) {
          var raw = data.rates[dt][def.sym];
          return def.invert ? 1 / raw : raw;
        }).filter(function (x) { return isFinite(x); });
        if (series.length < 2) return simAsset('fx', def, 0.006);
        var price = series[series.length - 1];
        var prev = series[series.length - 2];
        return normalise('fx', def, {
          price: price,
          change: prev ? ((price - prev) / prev) * 100 : 0,
          high: Math.max.apply(null, series),
          low: Math.min.apply(null, series),
          spark: series, source: 'live'
        });
      });
    }).then(function (list) { state.sources.fx = 'live'; return list; })
      .catch(function () {
        state.sources.fx = 'sim';
        return FX.map(function (def) { return simAsset('fx', def, 0.006); });
      });
  }

  function fetchMetals() {
    // gold-api.com exposes one symbol per request; try them in parallel and
    // fall back per-metal. Intraday history isn't provided, so the sparkline is
    // illustrative even when the spot price is live.
    var jobs = METALS.map(function (def) {
      return getJSON('https://api.gold-api.com/price/' + def.id, 7000)
        .then(function (d) {
          var price = d && (d.price != null ? d.price : d.rate);
          if (price == null || !isFinite(price)) throw new Error('no price');
          var spark = walk(price, 40, 0.006);
          spark[spark.length - 1] = price;
          return normalise('metals', def, {
            price: price,
            change: (price / spark[0] - 1) * 100,
            high: Math.max.apply(null, spark),
            low: Math.min.apply(null, spark),
            spark: spark, source: 'live'
          });
        })
        .catch(function () { return simAsset('metals', def, 0.012); });
    });
    return Promise.all(jobs).then(function (list) {
      state.sources.metals = list.some(function (a) { return a.source === 'live'; }) ? 'live' : 'sim';
      return list;
    });
  }

  function buildIndices() {
    state.sources.indices = 'sim';
    return Promise.resolve(INDICES.map(function (def) { return simAsset('indices', def, 0.008); }));
  }

  /* ---------------------------------------------------------------------- */
  /* Load + store                                                           */
  /* ---------------------------------------------------------------------- */
  function ingest(type, list) {
    list.forEach(function (a) { a.type = type; state.assets[keyOf(type, a.id)] = a; });
    return list;
  }

  function loadAll() {
    return Promise.all([fetchCrypto(), fetchMetals(), fetchFX(), buildIndices()])
      .then(function (res) {
        ingest('crypto', res[0]);
        ingest('metals', res[1]);
        ingest('fx', res[2]);
        ingest('indices', res[3]);
        state.lastUpdated = new Date();
        return state;
      });
  }

  /* ---------------------------------------------------------------------- */
  /* SVG sparkline / area chart                                             */
  /* ---------------------------------------------------------------------- */
  function sparkPath(vals, w, h, pad) {
    pad = pad || 1;
    var min = Math.min.apply(null, vals), max = Math.max.apply(null, vals);
    var span = (max - min) || 1;
    var n = vals.length;
    return vals.map(function (v, i) {
      var x = pad + (i / (n - 1)) * (w - pad * 2);
      var y = pad + (1 - (v - min) / span) * (h - pad * 2);
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
  function skeletonList(n) {
    var s = '';
    for (var i = 0; i < n; i++) {
      s += '<div class="market-row market-row--live is-skeleton">' +
        '<span class="sk sk-name"></span><span class="sk sk-spark"></span>' +
        '<span class="sk sk-price"></span><span class="sk sk-chg"></span></div>';
    }
    return s;
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
  function showSkeletons() {
    ['mlist-crypto', 'mlist-metals', 'mlist-currency'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.innerHTML = skeletonList(id === 'mlist-currency' ? 5 : id === 'mlist-metals' ? 4 : 5);
    });
  }

  /* ---------------------------------------------------------------------- */
  /* Sidebar: live ticker + AI status                                       */
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

  function aiConnected() { return !!getKey(); }
  function getKey() { try { return localStorage.getItem(LS_KEY) || ''; } catch (e) { return ''; } }
  function getModel() { try { return localStorage.getItem(LS_MODEL) || DEFAULT_MODEL; } catch (e) { return DEFAULT_MODEL; } }
  function setKey(k, m) {
    try {
      if (k) localStorage.setItem(LS_KEY, k); else localStorage.removeItem(LS_KEY);
      if (m) localStorage.setItem(LS_MODEL, m);
    } catch (e) {}
    paintAiStatus();
  }
  function modelLabel() {
    var m = getModel();
    for (var i = 0; i < MODELS.length; i++) if (MODELS[i].id === m) return MODELS[i].label.split(' — ')[0];
    return m;
  }
  function paintAiStatus() {
    var btn = document.getElementById('catAiStatus');
    if (!btn) return;
    var on = aiConnected();
    btn.classList.toggle('on', on);
    var dot = btn.querySelector('.cat-ai-dot');
    var lab = btn.querySelector('.cat-ai-label');
    if (lab) lab.textContent = on ? 'AI: ' + modelLabel() : 'Connect AI';
    if (dot) dot.title = on ? 'AI connected' : 'AI not connected';
    // reflect on any open AI-dependent surfaces
    document.querySelectorAll('[data-ai-gate]').forEach(function (n) { n.classList.toggle('ai-off', !on); });
  }

  /* ---------------------------------------------------------------------- */
  /* Anthropic call (browser → api.anthropic.com)                           */
  /* ---------------------------------------------------------------------- */
  function callAI(userText, opts) {
    opts = opts || {};
    var key = getKey();
    if (!key) { var e = new Error('AI not connected'); e.code = 'NO_KEY'; return Promise.reject(e); }
    var model = getModel();
    var payload = {
      model: model,
      max_tokens: opts.max_tokens || 320,
      messages: [{ role: 'user', content: userText }]
    };
    if (opts.system) payload.system = opts.system;

    function send(withEffort) {
      var body = Object.assign({}, payload);
      if (withEffort) body.output_config = { effort: 'low' };
      return fetch(AI_URL, {
        method: 'POST',
        headers: {
          'content-type': 'application/json',
          'x-api-key': key,
          'anthropic-version': '2023-06-01',
          // Required for direct browser (CORS) access to the Anthropic API.
          'anthropic-dangerous-direct-browser-access': 'true'
        },
        body: JSON.stringify(body)
      });
    }

    return send(true).then(function (r) {
      // Some models reject output_config/effort → retry once without it.
      if (r.status === 400) return send(false);
      return r;
    }).then(function (r) {
      if (!r.ok) {
        var msg = 'AI request failed (' + r.status + ')';
        if (r.status === 401) msg = 'That API key was rejected. Check it and reconnect.';
        else if (r.status === 429) msg = 'Rate limited — wait a moment and try again.';
        else if (r.status === 529) msg = 'The API is busy right now — try again shortly.';
        var err = new Error(msg); err.code = r.status; throw err;
      }
      return r.json();
    }).then(function (data) {
      var text = (data.content || []).filter(function (b) { return b && b.type === 'text'; })
        .map(function (b) { return b.text; }).join('\n').trim();
      return text || '(The model returned no text.)';
    });
  }

  // Compact one-line snapshot of an asset for prompts.
  function snap(a) {
    return a.name + ' ' + displayPrice(a) + ' (' + pct(a.change) + ', ' + a.source + ')';
  }
  var AI_SYSTEM = 'You are a market commentator for LUNAX, a fictional luxury fintech storefront. ' +
    'Write concise, neutral, plain-language commentary about market data you are given. ' +
    'Never invent specific numbers beyond what is provided. Do not give personalised financial advice. ' +
    'If some data is marked "sim" it is simulated placeholder data — say so if it is central to the point.';

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
      }).join('') + '</div>' +
      '<div class="ad-ai" data-ai-gate>' +
      '<div class="ad-ai-head"><span class="ad-ai-title">AI analysis</span>' +
      '<button class="ad-ai-btn" type="button" data-ai-run="asset">' + (aiConnected() ? 'Regenerate' : 'Connect AI') + '</button></div>' +
      '<div class="ad-ai-body" id="adAiBody"></div>' +
      '<div class="ai-note">AI-generated commentary · not financial advice</div>' +
      '</div>';

    detailEl.querySelector('.ad-inner').innerHTML = html;
    detailEl.classList.add('open');
    detailBackdrop.classList.add('open');
    detailEl.setAttribute('aria-hidden', 'false');
    detailEl.scrollTop = 0;

    // AI analysis: use cache, else auto-generate if connected.
    var body = detailEl.querySelector('#adAiBody');
    var cacheKey = 'asset:' + key;
    if (state.aiCache[cacheKey]) {
      body.innerHTML = '<p>' + esc(state.aiCache[cacheKey]) + '</p>';
    } else if (aiConnected()) {
      runAssetAI(key, body);
    } else {
      body.innerHTML = '<p class="ai-cta">Connect an Anthropic API key to get a short AI read on ' +
        esc(a.name) + '.</p>';
    }
  }

  function runAssetAI(key, body) {
    var a = state.assets[key];
    if (!a) return;
    body.innerHTML = '<p class="ai-loading">Analysing ' + esc(a.name) + '…</p>';
    var trend = a.spark && a.spark.length > 1 ? (a.spark[a.spark.length - 1] >= a.spark[0] ? 'rising' : 'falling') : 'flat';
    var prompt = 'Asset: ' + a.name + ' (' + (a.ticker || '') + '), type ' + a.type + '.\n' +
      'Price ' + displayPrice(a) + '; 24h change ' + pct(a.change) + '; ' +
      '24h range ' + (a.low != null ? priceOf(a, a.low) : '?') + ' to ' + (a.high != null ? priceOf(a, a.high) : '?') + '; ' +
      'recent trend ' + trend + '; data source ' + a.source + (a.mcap != null ? ('; market cap ' + compact(a.mcap)) : '') + '.\n' +
      'Write 2–3 sentences (max ~55 words) on momentum and what traders are watching. Neutral tone. No advice.';
    callAI(prompt, { system: AI_SYSTEM, max_tokens: 260 }).then(function (text) {
      state.aiCache['asset:' + key] = text;
      if (detailKey === key) body.innerHTML = '<p>' + esc(text) + '</p>';
    }).catch(function (err) {
      body.innerHTML = '<p class="ai-error">' + esc(err.code === 'NO_KEY'
        ? 'Connect an Anthropic API key to enable AI analysis.' : err.message) + '</p>';
    });
  }

  /* ---------------------------------------------------------------------- */
  /* Connect-AI modal                                                       */
  /* ---------------------------------------------------------------------- */
  var modalEl;
  function buildModal() {
    if (modalEl) return;
    var opts = MODELS.map(function (m) {
      return '<option value="' + m.id + '"' + (m.id === getModel() ? ' selected' : '') + '>' + esc(m.label) + '</option>';
    }).join('');
    modalEl = h(
      '<div class="ai-modal-backdrop" id="aiModalBackdrop">' +
      '<div class="ai-modal" role="dialog" aria-modal="true" aria-label="Connect AI">' +
      '<button class="ai-modal-close" type="button" aria-label="Close">✕</button>' +
      '<h3 class="ai-modal-title">Connect a real LLM</h3>' +
      '<p class="ai-modal-lead">Paste an <b>Anthropic API key</b> to let Claude write live market summaries, ' +
      'per-asset analysis and regional briefings.</p>' +
      '<label class="ai-field"><span>Anthropic API key</span>' +
      '<input type="password" id="aiKeyInput" placeholder="sk-ant-…" autocomplete="off" spellcheck="false"></label>' +
      '<label class="ai-field"><span>Model</span><select id="aiModelInput">' + opts + '</select></label>' +
      '<div class="ai-modal-actions">' +
      '<button class="ai-btn-primary" type="button" id="aiSaveBtn">Save &amp; connect</button>' +
      '<button class="ai-btn-ghost" type="button" id="aiClearBtn">Disconnect</button>' +
      '</div>' +
      '<div class="ai-modal-msg" id="aiModalMsg" aria-live="polite"></div>' +
      '<p class="ai-modal-fine">🔒 Your key is stored only in this browser (localStorage) and sent only to ' +
      'api.anthropic.com when generating text. It is never logged or sent anywhere else. ' +
      'Calls use your Anthropic account and consume credits.</p>' +
      '</div></div>');
    document.body.appendChild(modalEl);

    modalEl.addEventListener('click', function (e) { if (e.target === modalEl) closeModal(); });
    modalEl.querySelector('.ai-modal-close').addEventListener('click', closeModal);
    modalEl.querySelector('#aiClearBtn').addEventListener('click', function () {
      setKey('', getModel());
      modalEl.querySelector('#aiKeyInput').value = '';
      msg('Disconnected. Live data still works without AI.');
    });
    modalEl.querySelector('#aiSaveBtn').addEventListener('click', function () {
      var k = modalEl.querySelector('#aiKeyInput').value.trim();
      var m = modalEl.querySelector('#aiModelInput').value;
      if (!k) { msg('Paste a key first, or press Disconnect.', true); return; }
      setKey(k, m);
      msg('Testing connection…');
      callAI('Reply with the single word: ready', { max_tokens: 16 }).then(function () {
        msg('Connected ✓  Claude is ready.');
        setTimeout(closeModal, 750);
        // refresh AI-driven surfaces
        var body = document.getElementById('adAiBody');
        if (detailKey && body) runAssetAI(detailKey, body);
        if (state.worldBuilt) runWorldSummary(true);
      }).catch(function (err) {
        msg(err.message || 'Could not connect.', true);
      });
    });
    function msg(t, bad) {
      var m = modalEl.querySelector('#aiModalMsg');
      m.textContent = t; m.classList.toggle('bad', !!bad);
    }
    modalEl._msg = msg;
  }
  function openModal() {
    buildModal();
    modalEl.querySelector('#aiModelInput').value = getModel();
    var input = modalEl.querySelector('#aiKeyInput');
    input.value = getKey();
    modalEl.querySelector('#aiModalMsg').textContent = '';
    modalEl.classList.add('open');
    setTimeout(function () { input.focus(); }, 60);
  }
  function closeModal() { if (modalEl) modalEl.classList.remove('open'); }

  /* ---------------------------------------------------------------------- */
  /* World map + dashboard                                                  */
  /* ---------------------------------------------------------------------- */
  // Rough, low-opacity continent blobs in a 0 0 360 180 (deg) space — a
  // stylised suggestion of landmasses behind the graticule, not a survey map.
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
      '<div class="world-summary" data-ai-gate>' +
      '<div class="ws-head"><span class="ws-title">AI market summary</span>' +
      '<button class="ws-btn" type="button" data-ai-run="world">' + (aiConnected() ? 'Regenerate' : 'Connect AI') + '</button></div>' +
      '<div class="ws-body" id="worldSummaryBody"></div>' +
      '<div class="ai-note">AI-generated overview · not financial advice</div>' +
      '</div>' +
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
    // seed / refresh AI summary
    var body = document.getElementById('worldSummaryBody');
    if (body && !body.dataset.filled) {
      if (state.aiCache.world) { body.innerHTML = '<p>' + esc(state.aiCache.world) + '</p>'; body.dataset.filled = '1'; }
      else if (aiConnected()) runWorldSummary(false);
      else body.innerHTML = '<p class="ai-cta">Connect an Anthropic API key for a live, plain-language read across crypto, metals, FX and indices.</p>';
    }
  }
  function runWorldSummary(force) {
    var body = document.getElementById('worldSummaryBody');
    if (!body) return;
    if (state.aiCache.world && !force) { body.innerHTML = '<p>' + esc(state.aiCache.world) + '</p>'; body.dataset.filled = '1'; return; }
    body.dataset.filled = '1';
    body.innerHTML = '<p class="ai-loading">Reading the tape across markets…</p>';
    var lines = [];
    ['crypto', 'metals', 'fx', 'indices'].forEach(function (sec) {
      orderedAssets(sec).slice(0, 3).forEach(function (a) { lines.push(snap(a)); });
    });
    var prompt = 'Current market snapshot (name price (24h change, source)):\n' + lines.join('\n') +
      '\n\nWrite a 3–4 sentence market summary (max ~80 words): note the biggest movers, any cross-asset theme ' +
      '(risk-on/off, dollar strength, gold as a haven), and mention if key inputs are simulated. Neutral, no advice.';
    callAI(prompt, { system: AI_SYSTEM, max_tokens: 340 }).then(function (text) {
      state.aiCache.world = text;
      if (body.isConnected) body.innerHTML = '<p>' + esc(text) + '</p>';
    }).catch(function (err) {
      body.innerHTML = '<p class="ai-error">' + esc(err.code === 'NO_KEY'
        ? 'Connect an Anthropic API key to generate the market summary.' : err.message) + '</p>';
    });
  }

  function regionBrief(regionId) {
    var c = null;
    for (var i = 0; i < CENTRES.length; i++) if (CENTRES[i].id === regionId) c = CENTRES[i];
    if (!c) return;
    var box = document.getElementById('worldBrief');
    if (!box) return;
    box.classList.add('active');
    var cacheKey = 'region:' + regionId;
    var headHTML = '<div class="wb-title"><span class="wb-dot"></span>' + esc(c.label) + '</div>';
    if (state.aiCache[cacheKey]) { box.innerHTML = headHTML + '<p>' + esc(state.aiCache[cacheKey]) + '</p><div class="ai-note">AI-generated · not financial advice</div>'; return; }
    if (!aiConnected()) {
      box.innerHTML = headHTML + '<p class="ai-cta">' + esc(c.label) + ' anchors ' + esc(c.anchor) +
        '. Connect an Anthropic API key for a live regional briefing.</p>';
      return;
    }
    box.innerHTML = headHTML + '<p class="ai-loading">Briefing on ' + esc(c.label) + '…</p>';
    var relevant = [];
    if (regionId === 'ny') relevant = [snapKey('indices', 'SPX'), snapKey('crypto', 'bitcoin')];
    else if (regionId === 'ldn' || regionId === 'dxb') relevant = [snapKey('metals', 'XAU'), snapKey('fx', 'GBPUSD')];
    else if (regionId === 'fra') relevant = [snapKey('fx', 'EURUSD'), snapKey('indices', 'UKX')];
    else if (regionId === 'tky') relevant = [snapKey('fx', 'USDJPY')];
    else if (regionId === 'hk' || regionId === 'sgp') relevant = [snapKey('fx', 'USDCNY'), snapKey('metals', 'XAG')];
    else if (regionId === 'syd') relevant = [snapKey('fx', 'AUDUSD')];
    relevant = relevant.filter(Boolean);
    var prompt = c.label + ' is a financial centre focused on ' + c.anchor + '.\n' +
      (relevant.length ? 'Relevant live levels: ' + relevant.join('; ') + '.\n' : '') +
      'Write a 2–3 sentence trading-desk style briefing (max ~55 words) for this centre right now. Neutral, no advice.';
    callAI(prompt, { system: AI_SYSTEM, max_tokens: 240 }).then(function (text) {
      state.aiCache[cacheKey] = text;
      if (box.isConnected) box.innerHTML = headHTML + '<p>' + esc(text) + '</p><div class="ai-note">AI-generated · not financial advice</div>';
    }).catch(function (err) {
      box.innerHTML = headHTML + '<p class="ai-error">' + esc(err.message) + '</p>';
    });
  }
  function snapKey(type, id) { var a = state.assets[keyOf(type, id)]; return a ? snap(a) : ''; }

  /* ---------------------------------------------------------------------- */
  /* Events                                                                 */
  /* ---------------------------------------------------------------------- */
  function wireEvents() {
    // Open detail from any market row / KPI tile / ticker item.
    document.addEventListener('click', function (e) {
      var opener = e.target.closest('[data-asset]');
      if (opener && !opener.classList.contains('is-skeleton')) { openDetail(opener.getAttribute('data-asset')); return; }
      var run = e.target.closest('[data-ai-run]');
      if (run) {
        var kind = run.getAttribute('data-ai-run');
        if (!aiConnected()) { openModal(); return; }
        if (kind === 'asset' && detailKey) runAssetAI(detailKey, detailEl.querySelector('#adAiBody'));
        else if (kind === 'world') runWorldSummary(true);
        return;
      }
      var hot = e.target.closest('.wmap-hot');
      if (hot) { regionBrief(hot.getAttribute('data-region')); return; }
      var aiStatus = e.target.closest('#catAiStatus');
      if (aiStatus) { openModal(); return; }
      var refresh = e.target.closest('#catRefreshBtn');
      if (refresh) { refresh.classList.add('spin'); refreshAll().finally(function () { setTimeout(function () { refresh.classList.remove('spin'); }, 400); }); return; }
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

    // Refresh the WORLD dashboard whenever its sidebar entry is clicked.
    var worldLink = document.querySelector('[data-center-open="world"]');
    if (worldLink) worldLink.addEventListener('click', function () {
      // let B3 toggle the panel, then make sure content is current
      setTimeout(function () { if (!state.worldBuilt) buildWorld(); else paintWorld(); }, 0);
    });

    // Pause/refresh with tab visibility.
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden && state.lastUpdated && (Date.now() - state.lastUpdated.getTime() > REFRESH_MS)) refreshAll();
    });
  }

  /* ---------------------------------------------------------------------- */
  /* Refresh loop                                                           */
  /* ---------------------------------------------------------------------- */
  function repaintAll() {
    paintMarketPanels();
    paintTicker();
    paintWorld();
    // if a detail view is open, refresh its header numbers in place
    if (detailKey && detailEl && detailEl.classList.contains('open')) {
      var a = state.assets[detailKey];
      if (a) {
        var px = detailEl.querySelector('.ad-px'); if (px) px.textContent = displayPrice(a);
        var chg = detailEl.querySelector('.ad-chg');
        if (chg) { var up = (a.change || 0) >= 0; chg.className = 'ad-chg ' + (up ? 'up' : 'down'); chg.textContent = (up ? '▲ ' : '▼ ') + pct(a.change); }
      }
    }
  }
  function refreshAll() {
    return loadAll().then(repaintAll).catch(function () {});
  }

  /* ---------------------------------------------------------------------- */
  /* Init                                                                   */
  /* ---------------------------------------------------------------------- */
  function init() {
    buildDetail();
    buildModal();
    paintAiStatus();
    showSkeletons();
    paintTicker();
    wireEvents();
    loadAll().then(function () {
      repaintAll();
      buildWorld();     // build eagerly so the panel is ready on first open
    });
    setInterval(function () { if (!document.hidden) refreshAll(); }, REFRESH_MS);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
