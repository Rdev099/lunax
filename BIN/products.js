  const products = [
    {name:"Vacuum Cleaner", price:40000, was:null, badge:"new", brand:"Yesido", category:"Music Accessories",
     icon:'<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z"/><path d="M12 6v8M8 12h8M12 18v2"/>' ,image:'../image/vacuum.jpg'},
    {name:"Screwdriver Set Electric & Cordless", price:40000, was:null, badge:"new", brand:"Yesido", category:"Music Accessories",
     icon:'<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M12 10v4M8 12h8M12 6v2"/>' ,image:'../image/screwdriver.jpg'},
    {name:"Coffee Grinder", price:40000, was:null, badge:"new", brand:"Yesido", category:"Music Accessories",
     icon:'<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>' ,image:'../image/coffee-grinder.jpg'},
    {name:"Smart Glasses", price:35000, was:null, badge:null, brand:"Yesido", category:"Music Accessories",
     icon:'<circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/>' ,image:'../image/glasses.jpg'},
    {name:"Neck Massager Handheld Curved", price:85000, was:null, badge:"new", brand:"Yesido", category:"Music Accessories",
     icon:'<path d="M12 2c5 0 9 4 9 9s-4 9-9 9-9-4-9-9 4-9 9-9Z"/><path d="M12 6v12"/>' ,image:'../image/massager.jpg'},
    {name:"Pulse Wireless Earbuds Pro, ANC + 30h battery", price:49.99, was:null, badge:"new", brand:"Yesido", category:"Music Accessories",
     icon:'<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>' ,image:'../image/s.jpg'},
    {name:"Orbit 6.7\" AMOLED Phone, 256GB, 5G", price:389.00, was:459.00, badge:"sale", brand:"Yesido", category:"Music Accessories",
     icon:'<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>' ,image:'../image/b.jpg'},
    {name:"NovaBook Air 14\", 16GB / 512GB SSD", price:649.00, was:null, badge:null,
     icon:'<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>' ,image:'../image/z.jpg'},
    {name:"Aether Smartwatch Series 5, GPS + Cellular", price:89.99, was:null, badge:"new",
     icon:'<rect x="7" y="5" width="10" height="14" rx="4"/><path d="M12 8v0"/>'},
    {name:"Halo RGB Mechanical Keyboard, Hot-swap", price:59.90, was:79.90, badge:"sale",
     icon:'<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'},
    {name:"DriftCam 4K Action Camera, Waterproof", price:129.00, was:null, badge:null,
     icon:'<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'},
    {name:"VoltPack 20000mAh Fast Charger, 65W", price:24.99, was:null, badge:null,
     icon:'<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>'},
    {name:"EchoDome Smart Speaker, Voice Assistant", price:34.50, was:null, badge:"new",
     icon:'<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'},
    {name:"Glide Wireless Mouse, Silent Click", price:19.99, was:null, badge:null,
     icon:'<rect x="8" y="3" width="8" height="14" rx="4"/><path d="M12 3v6"/>'},
    {name:"Skyline Drone 4K, Foldable, 30min flight", price:199.00, was:249.00, badge:"sale",
     icon:'<path d="M12 3v4M12 17v4M3 12h4M17 12h4"/><circle cx="12" cy="12" r="4"/>'},
    {name:"Comet USB-C Hub, 8-in-1 Dock", price:44.90, was:null, badge:null,
     icon:'<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h.01M11 10h.01M15 10h.01"/>'},
    {name:"Nimbus Portable SSD, 1TB, USB 3.2", price:79.99, was:99.99, badge:"sale",
     icon:'<rect x="4" y="7" width="16" height="10" rx="2"/><path d="M8 12h8"/>'},
    {name:"Zenith 27\" QHD Monitor, 165Hz", price:229.00, was:null, badge:"new",
     icon:'<rect x="2" y="4" width="20" height="13" rx="1"/><path d="M8 21h8M12 17v4"/>'},
    {name:"Arc Wireless Charging Pad, 15W", price:22.50, was:null, badge:null,
     icon:'<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/>'},
    {name:"Vortex Gaming Mouse, 16000 DPI", price:39.99, was:null, badge:null,
     icon:'<rect x="8" y="3" width="8" height="14" rx="4"/><path d="M12 3v6"/>'},
    {name:"Solace Noise-Cancelling Headphones", price:99.00, was:129.00, badge:"sale",
     icon:'<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>'},
    {name:"Fable E-Reader, 7\" Glare-Free Display", price:109.00, was:null, badge:"new",
     icon:'<rect x="6" y="3" width="12" height="18" rx="2"/><path d="M9 7h6M9 11h6"/>'},
    {name:"Ridge Bluetooth Tracker, 4-Pack", price:29.99, was:null, badge:null,
     icon:'<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/>'},
    {name:"Lumen Smart Bulb, RGB, 4-Pack", price:32.00, was:null, badge:null,
     icon:'<path d="M9 18h6M9 21h6M12 3a6 6 0 0 0-3 11c1 1 1 2 1 3h4c0-1 0-2 1-3a6 6 0 0 0-3-11Z"/>'},
    {name:"Cascade Robot Vacuum, LiDAR Mapping", price:249.00, was:299.00, badge:"sale",
     icon:'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/>'},
    {name:"Aster Mechanical Keyboard, 60%", price:69.00, was:null, badge:null,
     icon:'<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'},
    {name:"Beam Portable Projector, 1080p", price:159.00, was:null, badge:"new",
     icon:'<rect x="2" y="8" width="14" height="9" rx="2"/><circle cx="9" cy="12.5" r="2.5"/><path d="M18 11l4-2v7l-4-2z"/>'},
    {name:"Tide Waterproof Bluetooth Speaker", price:54.90, was:69.90, badge:"sale",
     icon:'<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'},
    {name:"Orbit Mini Tablet, 8.3\", 128GB", price:219.00, was:null, badge:null,
     icon:'<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 19h2"/>'},
    {name:"Flux Gaming Headset, 7.1 Surround", price:64.99, was:null, badge:"new",
     icon:'<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>'},
    {name:"Anchor Laptop Stand, Aluminum", price:27.50, was:null, badge:null,
     icon:'<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'},
    {name:"Prism Webcam, 4K Auto-Focus", price:74.00, was:89.00, badge:"sale",
     icon:'<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'},
    {name:"Cinder Power Strip, 6-Outlet + USB-C", price:26.99, was:null, badge:null,
     icon:'<rect x="2" y="9" width="20" height="7" rx="2"/><path d="M7 9v-2M12 9v-2M17 9v-2"/>'},
    {name:"Vantage Ring Light, 10\", Tripod", price:31.90, was:null, badge:"new",
     icon:'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/>'},
    {name:"Motion Fitness Band, Heart Rate + SpO2", price:44.00, was:null, badge:null,
     icon:'<rect x="7" y="5" width="10" height="14" rx="4"/><path d="M12 8v0"/>'},
    {name:"Reef Mechanical Numpad, Hot-swap", price:34.90, was:null, badge:null,
     icon:'<rect x="2" y="8" width="20" height="9" rx="2"/><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 15h12"/>'},
    {name:"Slate Drawing Tablet, Pressure Pen", price:89.90, was:109.90, badge:"sale",
     icon:'<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/>'},
    {name:"Drift Dash Cam, 2K, Night Vision", price:69.99, was:null, badge:null,
     icon:'<rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3.5"/>'},
    {name:"Pulse Fitness Scale, Body Composition", price:29.00, was:null, badge:"new",
     icon:'<rect x="4" y="9" width="16" height="10" rx="2"/><circle cx="12" cy="14" r="2"/>'},
    {name:"Nomad Travel Router, Pocket Wi-Fi", price:36.00, was:null, badge:null,
     icon:'<path d="M4 11 12 4l8 7"/><path d="M6 10v9h12v-9"/>'},
    {name:"Halo Desk Lamp, Wireless Charge Base", price:41.50, was:49.50, badge:"sale",
     icon:'<path d="M9 18h6M9 21h6M12 3a6 6 0 0 0-3 11c1 1 1 2 1 3h4c0-1 0-2 1-3a6 6 0 0 0-3-11Z"/>'},
    {name:"Circuit Soldering Kit, Digital Iron", price:52.00, was:null, badge:null,
     icon:'<rect x="7" y="7" width="10" height="10" rx="1"/><path d="M9 3v2M15 3v2M9 19v2M15 19v2M3 9h2M3 15h2M19 9h2M19 15h2"/>'},
    {name:"Orbit Car Phone Mount, Magnetic", price:14.99, was:null, badge:null,
     icon:'<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'},
    {name:"Ember Heated Mug, Smart Temp Control", price:79.00, was:null, badge:"new",
     icon:'<path d="M4 8h12v7a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4Z"/><path d="M16 10h2a2 2 0 0 1 0 4h-2"/>'},
    {name:"Trace GPS Bike Computer", price:94.00, was:null, badge:null,
     icon:'<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/>'},
    {name:"Cove Bookshelf Speakers, Pair", price:139.00, was:169.00, badge:"sale",
     icon:'<path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4Z"/><path d="M6 11a6 6 0 0 0 12 0M12 19v3"/>'},
    {name:"Lattice Cable Organizer Set", price:12.99, was:null, badge:null,
     icon:'<rect x="4" y="9" width="16" height="6" rx="3"/>'},
    {name:"Verge Smart Doorbell, 1080p HDR", price:99.99, was:null, badge:"new",
     icon:'<rect x="8" y="3" width="8" height="18" rx="2"/><circle cx="12" cy="9" r="1.3" fill="#D68A2E" stroke="none"/>'},
    {name:"Kinetic Standing Desk Converter", price:169.00, was:199.00, badge:"sale",
     icon:'<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'},
    {name:"Aura Air Purifier, HEPA, Quiet Mode", price:119.00, was:null, badge:null,
     icon:'<rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 9h6M9 13h6"/>'},
    {name:"Sprint Gaming Chair Footrest", price:38.00, was:null, badge:null,
     icon:'<rect x="3" y="10" width="18" height="6" rx="2"/><path d="M6 16v3M18 16v3"/>'},
    {name:"Glacier Cooling Laptop Pad", price:21.99, was:null, badge:"new",
     icon:'<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M2 20h20l-2-4H4z"/>'},
    {name:"Fathom Waterproof Phone Pouch", price:9.99, was:null, badge:null,
     icon:'<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'},
    {name:"Relay Smart Plug, Energy Monitor, 2-Pack", price:24.00, was:29.00, badge:"sale",
     icon:'<path d="M4 11 12 4l8 7"/><path d="M6 10v9h12v-9"/>'},
    {name:"Quartz Portable Monitor, 15.6\" USB-C", price:189.00, was:null, badge:null,
     icon:'<rect x="2" y="4" width="20" height="13" rx="1"/><path d="M8 21h8M12 17v4"/>'},
      {name:"Pulse Wireless Earbuds Pro, ANC + 30h battery", price:49.99, was:null, badge:"new",
     icon:'<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="14" width="5" height="7" rx="1.5"/><rect x="17" y="14" width="5" height="7" rx="1.5"/>' ,image: '../image/s.jpg'},
   
  ];

  // Left category sidebar — reveals in full while the cursor is over it,
  // settles back to a thin peek strip the instant the cursor leaves.
  const catSidebar = document.getElementById('catSidebar');
  const catSidebarPeek = document.getElementById('catSidebarPeek');
  const catSidebarBackdrop = document.getElementById('catSidebarBackdrop');
  const catSidebarClose = document.getElementById('catSidebarClose');

  function openCatSidebar(){
    catSidebar.classList.add('open');
    catSidebarBackdrop.classList.add('open');
    catSidebarPeek.setAttribute('aria-expanded', 'true');
    catSidebar.setAttribute('aria-hidden', 'false');
  }
  function closeCatSidebar(){
    catSidebar.classList.remove('open');
    catSidebarBackdrop.classList.remove('open');
    catSidebarPeek.setAttribute('aria-expanded', 'false');
    catSidebar.setAttribute('aria-hidden', 'true');
  }

  // Pointer devices: hovering the panel (even just the visible sliver) opens it,
  // moving away closes it again.
  catSidebar.addEventListener('mouseenter', openCatSidebar);
  catSidebar.addEventListener('mouseleave', closeCatSidebar);

  // Touch / keyboard fallback: tapping the peek strip toggles it, close button
  // and backdrop close it, Escape closes it.
  catSidebarPeek.addEventListener('click', () => {
    catSidebar.classList.contains('open') ? closeCatSidebar() : openCatSidebar();
  });
  catSidebarClose.addEventListener('click', closeCatSidebar);
  catSidebarBackdrop.addEventListener('click', closeCatSidebar);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCatSidebar();
  });

  document.querySelectorAll('.cat-mega-list li').forEach(li => {
    li.addEventListener('mouseenter', () => {
      document.querySelectorAll('.cat-mega-list li').forEach(x => x.classList.remove('active'));
      document.querySelectorAll('.cat-detail-panel').forEach(p => p.classList.remove('active'));
      li.classList.add('active');
      const panel = document.querySelector(`.cat-detail-panel[data-panel="${li.dataset.target}"]`);
      if (panel) panel.classList.add('active');
    });
    // Clicking a mega-menu category word filters the products by that category.
    li.addEventListener('click', () => {
      if (li.dataset.target) applyFilter(li.dataset.target);
    });
  });

  const starSvg = (filled) => `<svg viewBox="0 0 24 24" stroke-width="1.5" class="${filled ? 'filled' : 'empty'}"><path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.2 1 5.9-5.2-2.8-5.2 2.8 1-5.9-4.3-4.2 5.9-.8Z"/></svg>`;
  function renderStars(rating){
    let out = '';
    for (let i = 1; i <= 5; i++) out += starSvg(i <= Math.round(rating));
    return out;
  }

  const grid = document.getElementById('productGrid');

  // Stable index + slug id so /php/product.php?id=... matches the detail page catalog.
  function slugify(name){
    return String(name).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }
  products.forEach((p, i) => { p.origIndex = i; p.id = slugify(p.name); });

  function ratingFor(p){ return 3.5 + ((p.origIndex * 37) % 15) / 10; }
  function reviewCountFor(p){ return 20 + ((p.origIndex * 53) % 480); }

  const money = (usd) => (window.LUNAX && typeof LUNAX.formatMoney === 'function')
    ? LUNAX.formatMoney(usd)
    : ('$' + Number(usd).toFixed(2));

  function renderProducts(list){
    if (!list.length){
      grid.innerHTML = `<p class="filter-empty">No products match this category yet.</p>`;
      return;
    }
    grid.innerHTML = list.map((p) => {
      const brand = p.brand || title.trim().split(' ')[0];
      const rating = ratingFor(p);
      const reviewCount = reviewCountFor(p);
      const discount = p.was ? Math.round(((p.was - p.price) / p.was) * 100) : null;
      const [title, ...rest] = p.name.split(',');
      const desc = rest.join(',').trim() || 'Premium quality, fast shipping';
      const stars = Math.round(rating);
      const esc = (s) => String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

      return `
<div class="product-card">
  <button class="heart-icon" data-id="${esc(p.id)}" data-index="${p.origIndex}" data-name="${esc(p.name)}" data-price="${p.price}" data-image="" data-icon="${esc(p.icon || '')}" aria-label="Add to wishlist">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.5-7 10-7 10Z"/></svg>
  </button>
  <a href="/PHP/product.php?id=${p.id}" class="product-link">
    <div class="product-thumb">
      ${p.badge === 'new' ? `<span class="badge new">New</span>` : ''}
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.4">${p.icon}</svg>
      
<div class="product-thumb">
  <span class="brand-logo"><b>${esc(brand.charAt(0))}</b>${esc(brand)}</span>
  ${p.badge === 'new' ? `<span class="badge new">New</span>` : ''}
  <svg ...>
      
    </div>
  </a>
  <div class="product-info">
    <a href="/PHP/product.php?id=${p.id}" class="product-link"><div class="pname">${esc(title.trim())}</div></a>
    <p class="pdesc">${esc(desc)}</p>
    <div class="rating">${'★'.repeat(stars)}${'☆'.repeat(5 - stars)} <span>${rating.toFixed(1)}</span></div>
    <div class="price-row">
      <span class="price">${window.LUNAX && typeof LUNAX.formatMoney === 'function' ? LUNAX.formatMoney(p.price) : (p.price % 1 === 0 ? '$' + p.price.toLocaleString() : '$' + p.price.toFixed(2))}</span>
      ${p.was ? `<span class="was">${window.LUNAX && typeof LUNAX.formatMoney === 'function' ? LUNAX.formatMoney(p.was) : '$' + p.was.toFixed(2)}</span><span class="discount">-${discount}%</span>` : ''}
    </div>
    <div class="card-actions">
      <button class="add-btn" data-id="${esc(p.id)}" data-index="${p.origIndex}" data-name="${esc(p.name)}" data-price="${p.price}" data-image="" data-icon="${esc(p.icon || '')}">Add to Cart</button>
      <a class="quick-btn" href="/PHP/product.php?id=${p.id}">Quick View</a>
    </div>
  </div>
</div>
    `;}).join('');
  }

  
  // ===== Quick-filter nav words (the 13 links under the search bar) =====
  // Each filter key maps to the keyword(s) a product's name is checked
  // against. "bestsellers" uses ratings instead of a name match.
  const categoryKeywords = {
    phones:      ['phone', 'tablet', 'card reader'],
    laptops:     ['laptop', 'desk'],
    audio:       ['headphone', 'speaker', 'earbud'],
    drones:      [],           // no drones in the current catalog
    gaming:      ['gaming', 'controller'],
    smarthome:   ['smart', 'bulb', 'plug', 'doorbell', 'vacuum', 'purifier'],
    cameras:     ['camera', 'webcam', 'dash cam', 'ring light'],
    wearables:   ['fitness', 'watch', 'band'],
    tablets:     ['tablet', 'e-reader', 'drawing tablet'],
    laptops2:    ['laptop'],
    components:  ['keyboard', 'numpad', 'cable', 'power strip', 'soldering', 'mount'],
    accessories: ['stand', 'mount', 'case', 'pouch', 'organizer', 'charger']
  };

  function matchesCategory(product, filterKey){
    if (filterKey === 'newarrivals'){
      // "New Arrivals" = everything the catalog flags as new.
      return product.badge === 'new';
    }
    if (filterKey === 'bestsellers'){
      // "Logical" bestseller rule: top-rated items with plenty of reviews.
      return ratingFor(product) >= 4.4 && reviewCountFor(product) >= 200;
    }
    const keywords = categoryKeywords[filterKey] || [];
    const name = product.name.toLowerCase();
    return keywords.some(k => name.includes(k));
  }

  // ===== Shared filter state: one category + one search query =====
  // Both the category words and the search box feed a single render path so
  // they can never fight each other or leave the grid in a stale state.
  let activeFilter = null;   // e.g. 'phones', or null for "all"
  let searchQuery = '';      // trimmed text from the search box
  const quickFilterLinks = document.querySelectorAll('.quick-filter-link');
  const searchInput = document.getElementById('siteSearchInput');
  const searchBtn = document.getElementById('siteSearchBtn');

  function currentList(){
    let list = products;
    if (activeFilter) list = list.filter(p => matchesCategory(p, activeFilter));
    if (searchQuery){
      const q = searchQuery.toLowerCase();
      list = list.filter(p => p.name.toLowerCase().includes(q));
    }
    return list;
  }

  function render(){
    quickFilterLinks.forEach(l =>
      l.classList.toggle('active', !!activeFilter && l.dataset.filter === activeFilter));
    document.querySelectorAll('.cat-mega-list li[data-target]').forEach(li =>
      li.classList.toggle('active', !!activeFilter && li.dataset.target === activeFilter));
    renderProducts(currentList());
  }
  const style = document.createElement('style');
  style.textContent = `
    .cart-bump svg{ animation: cartBumpAnim .5s ease; }
    @keyframes cartBumpAnim{
      0%{ transform: scale(1) rotate(0deg); color: inherit; }
      25%{ transform: scale(1.6) rotate(-8deg); color: #F2C94C; }
      50%{ transform: scale(1.3) rotate(6deg); color: #D1A83B; }
      75%{ transform: scale(1.45) rotate(-3deg); color: #F2C94C; }
      100%{ transform: scale(1) rotate(0deg); color: inherit; }
    }
    .cart-bump .icon-count{ animation: countPulse .5s ease; }
    @keyframes countPulse{
      0%, 100%{ transform: scale(1); }
      50%{ transform: scale(1.5); color: #F2C94C; }
    }
  `;
  document.head.appendChild(style);
  const cartIconLink = document.querySelector('.nav-icon-count');
  function bumpCartIcon(){
    if (!cartIconLink) return;
    cartIconLink.classList.remove('cart-bump');
    void cartIconLink.offsetWidth;
    cartIconLink.classList.add('cart-bump');
  }
  grid.addEventListener('click', function(e){
    const btn = e.target.closest('.add-btn');
    if (!btn) return;
    e.preventDefault();

    fetch('/PHP/cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=add&index=${btn.dataset.index}&name=${encodeURIComponent(btn.dataset.name)}&price=${btn.dataset.price}&qty=1&image=${encodeURIComponent(btn.dataset.image)}&icon=${encodeURIComponent(btn.dataset.icon)}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        document.querySelector('.nav-icon-count .icon-count').textContent = data.itemCount;
        bumpCartIcon();
      }
    })
    .catch(err => console.error('Add to cart failed', err));
  });

  function clearSearchBox(){
    searchQuery = '';
    if (searchInput) searchInput.value = '';
  }

  function applyFilter(filterKey){
    // Clicking the same word again clears the filter (toggle back to "all").
    activeFilter = (activeFilter === filterKey) ? null : filterKey;
    // Picking a category starts a fresh view, so drop any running search.
    clearSearchBox();
    render();
    grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  quickFilterLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      applyFilter(link.dataset.filter);
    });
  });

  // Sub-category links inside each "Shop" mega-menu detail panel filter by
  // that panel's parent category (e.g. "Headphones" → the "audio" catalog).
  // Uses a direct set (not applyFilter's toggle) so clicking two links in the
  // same panel keeps that category active instead of switching it back off.
  document.querySelectorAll('.cat-detail-panel').forEach(panel => {
    const key = panel.dataset.panel;
    if (!key) return;
    panel.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        activeFilter = key;
        clearSearchBox();
        render();
        grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  });

  // ===== Search box =====
  function runSearch(){
    if (!searchInput) return;
    searchQuery = searchInput.value.trim();
    // A search spans the whole catalog, so it clears any active category.
    if (searchQuery) activeFilter = null;
    render();
    grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  if (searchBtn) searchBtn.addEventListener('click', runSearch);
  if (searchInput){
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter'){ e.preventDefault(); runSearch(); }
    });
    // Emptying the box by hand resets to the full catalog.
    searchInput.addEventListener('input', () => {
      if (searchInput.value.trim() === '' && searchQuery !== ''){
        searchQuery = '';
        render();
      }
    });
  }

  // ===== Logo → Home =====
  // On the home page (where the product grid lives) clicking the LUNAX fox-mask
  // logo resets the view to the full catalog instead of triggering a reload.
  // On any other page it just follows its href back to B1.html.
  const siteLogo = document.getElementById('siteLogo');
  if (siteLogo){
    siteLogo.addEventListener('click', (e) => {
      e.preventDefault();
      activeFilter = null;
      clearSearchBox();
      render();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  render();

  // Re-format product prices when currency / language changes.
  document.addEventListener('lunax:locale', function () {
    render();
  });

  // ===== Left sidebar quick-open panels (NEWS / CRYPTO / GOLD & SILVER / CURRENCY) =====
  // Each nav link has data-center-open="key"; its matching panel carries
  // data-center-panel="key" and starts out nested inside the sidebar markup.
  // Panels are moved ("hoisted") to <body> the first time they're opened,
  // together with a shared backdrop, since the sidebar itself is
  // transform+overflow:hidden and would otherwise clip position:fixed content.
  const centerPanelHost = document.createElement('div');
  centerPanelHost.id = 'centerPanelHost';
  document.body.appendChild(centerPanelHost);

  const centerBackdrop = document.createElement('div');
  centerBackdrop.className = 'center-backdrop';
  centerPanelHost.appendChild(centerBackdrop);

  let openCenterPanel = null;

  function closeCenterPanel(){
    if (!openCenterPanel) return;
    openCenterPanel.classList.remove('open');
    openCenterPanel.setAttribute('aria-hidden', 'true');
    centerBackdrop.classList.remove('open');
    openCenterPanel = null;
  }

  function openCenterPanelByKey(key){
    const panel = document.querySelector(`.center-panel[data-center-panel="${key}"]`);
    if (!panel) return;

    // Move the panel into the host (only needs to happen once per panel).
    if (panel.parentElement !== centerPanelHost) {
      centerPanelHost.appendChild(panel);
      const closeBtn = panel.querySelector('.center-panel-close');
      if (closeBtn) closeBtn.addEventListener('click', closeCenterPanel);
    }

    if (openCenterPanel === panel) {
      closeCenterPanel();
      return;
    }

    if (openCenterPanel) closeCenterPanel();

    panel.classList.add('open');
    panel.setAttribute('aria-hidden', 'false');
    centerBackdrop.classList.add('open');
    openCenterPanel = panel;
  }

  document.querySelectorAll('[data-center-open]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      openCenterPanelByKey(link.dataset.centerOpen);
    });
  });

  grid.addEventListener('click', function(e){
    const btn = e.target.closest('.wish-btn') || e.target.closest('.heart-icon');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const body = new URLSearchParams({
      action: 'add_item',
      id:     btn.dataset.id,
      name:   btn.dataset.name,
      price:  btn.dataset.price,
      image:  btn.dataset.image || '',
      icon:   btn.dataset.icon  || ''
    });
    fetch('/PHP/wishlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
      btn.classList.add('filled');
      const countEl = document.querySelector('.wish-icon-count');
      if (countEl) countEl.textContent = data.wishCount;
    })    .catch(err => console.error('Wishlist failed', err));
  });

  centerBackdrop.addEventListener('click', closeCenterPanel);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCenterPanel();
  });

