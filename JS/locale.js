/**
 * LUNAX locale — live Currency & Language across the site.
 * Persists choice in localStorage and reformats prices / UI copy.
 */
(function (global) {
  'use strict';

  var STORAGE_CURRENCY = 'lunax_currency';
  var STORAGE_LANGUAGE = 'lunax_language';

  // Approximate mid-market rates vs 1 USD (display conversion only).
  var CURRENCIES = {
    USD: { code: 'USD', symbol: '$',   rate: 1,     label: 'USD / $ — US Dollar',           decimals: 2 },
    GBP: { code: 'GBP', symbol: '£',   rate: 0.79,  label: 'GBP / £ — British Pound',      decimals: 2 },
    EUR: { code: 'EUR', symbol: '€',   rate: 0.92,  label: 'EUR / € — Euro',               decimals: 2 },
    JPY: { code: 'JPY', symbol: '¥',   rate: 149,   label: 'JPY / ¥ — Japanese Yen',       decimals: 0 },
    CNY: { code: 'CNY', symbol: '¥',   rate: 7.24,  label: 'CNY / ¥ — Chinese Yuan',       decimals: 2 },
    AUD: { code: 'AUD', symbol: 'A$',  rate: 1.53,  label: 'AUD / $ — Australian Dollar',  decimals: 2 },
    CAD: { code: 'CAD', symbol: 'C$',  rate: 1.36,  label: 'CAD / $ — Canadian Dollar',    decimals: 2 },
    CHF: { code: 'CHF', symbol: 'Fr',  rate: 0.88,  label: 'CHF / Fr — Swiss Franc',       decimals: 2 },
    INR: { code: 'INR', symbol: '₹',   rate: 83.5,  label: 'INR / ₹ — Indian Rupee',       decimals: 0 },
    AED: { code: 'AED', symbol: 'د.إ', rate: 3.67,  label: 'AED / د.إ — UAE Dirham',       decimals: 2 },
    IQD: { code: 'IQD', symbol: 'ع.د', rate: 1310, label: 'IQD / ع.د — Iraqi Dinar',       decimals: 0 }
  };

  var LANGUAGES = {
    en: { code: 'en', label: 'English',    dir: 'ltr' },
    es: { code: 'es', label: 'Español',    dir: 'ltr' },
    fr: { code: 'fr', label: 'Français',   dir: 'ltr' },
    de: { code: 'de', label: 'Deutsch',    dir: 'ltr' },
    zh: { code: 'zh', label: '中文',        dir: 'ltr' },
    ja: { code: 'ja', label: '日本語',      dir: 'ltr' },
    ar: { code: 'ar', label: 'العربية',    dir: 'rtl' },
    pt: { code: 'pt', label: 'Português',  dir: 'ltr' }
  };

  // Lightweight UI dictionary for common chrome strings.
  var I18N = {
    en: {
      'Currency & Language': 'Currency & Language',
      'Currency': 'Currency',
      'Language': 'Language',
      'International Site': 'International Site',
      'Continue shopping': 'Continue shopping',
      'Add to Cart': 'Add to Cart',
      'Buy Now': 'Buy Now',
      'Shopping Cart': 'Shopping Cart',
      'Customer Service': 'Customer Service',
      'Secure checkout': 'Secure checkout',
      'Encrypted payments': 'Encrypted payments',
      'Easy buy': 'Easy buy',
      'One-click ordering': 'One-click ordering',
      '24/7 support': '24/7 support',
      'Live chat, always on': 'Live chat, always on',
      'Student discount': 'Student discount',
      '10% off with valid ID': '10% off with valid ID',
      'Easy Buy!': 'Easy Buy!',
      'View more': 'View more',
      'Shop →': 'Shop →',
      'Cart': 'Cart',
      'In Stock': 'In Stock',
      'Subtotal': 'Subtotal',
      'Shipping': 'Shipping',
      'Total': 'Total',
      'FREE': 'FREE'
    },
    es: {
      'Currency & Language': 'Moneda e idioma',
      'Currency': 'Moneda',
      'Language': 'Idioma',
      'International Site': 'Sitio internacional',
      'Continue shopping': 'Seguir comprando',
      'Add to Cart': 'Añadir al carrito',
      'Buy Now': 'Comprar ahora',
      'Shopping Cart': 'Carrito',
      'Customer Service': 'Atención al cliente',
      'Secure checkout': 'Pago seguro',
      'Encrypted payments': 'Pagos cifrados',
      'Easy buy': 'Compra fácil',
      'One-click ordering': 'Pedido en un clic',
      '24/7 support': 'Soporte 24/7',
      'Live chat, always on': 'Chat en vivo',
      'Student discount': 'Descuento estudiante',
      '10% off with valid ID': '10% con ID válida',
      'Easy Buy!': '¡Compra fácil!',
      'View more': 'Ver más',
      'Shop →': 'Tienda →',
      'Cart': 'Carrito',
      'In Stock': 'En stock',
      'Subtotal': 'Subtotal',
      'Shipping': 'Envío',
      'Total': 'Total',
      'FREE': 'GRATIS'
    },
    fr: {
      'Currency & Language': 'Devise et langue',
      'Currency': 'Devise',
      'Language': 'Langue',
      'International Site': 'Site international',
      'Continue shopping': 'Continuer vos achats',
      'Add to Cart': 'Ajouter au panier',
      'Buy Now': 'Acheter',
      'Shopping Cart': 'Panier',
      'Customer Service': 'Service client',
      'Secure checkout': 'Paiement sécurisé',
      'Encrypted payments': 'Paiements chiffrés',
      'Easy buy': 'Achat facile',
      'One-click ordering': 'Commande en 1 clic',
      '24/7 support': 'Support 24/7',
      'Live chat, always on': 'Chat en direct',
      'Student discount': 'Réduc étudiant',
      '10% off with valid ID': '−10% avec carte',
      'Easy Buy!': 'Achat facile !',
      'View more': 'Voir plus',
      'Shop →': 'Boutique →',
      'Cart': 'Panier',
      'In Stock': 'En stock',
      'Subtotal': 'Sous-total',
      'Shipping': 'Livraison',
      'Total': 'Total',
      'FREE': 'GRATUIT'
    },
    de: {
      'Currency & Language': 'Währung & Sprache',
      'Currency': 'Währung',
      'Language': 'Sprache',
      'International Site': 'Internationale Seite',
      'Continue shopping': 'Weiter einkaufen',
      'Add to Cart': 'In den Warenkorb',
      'Buy Now': 'Jetzt kaufen',
      'Shopping Cart': 'Warenkorb',
      'Customer Service': 'Kundenservice',
      'Secure checkout': 'Sicherer Checkout',
      'Encrypted payments': 'Verschlüsselte Zahlung',
      'Easy buy': 'Einfach kaufen',
      'One-click ordering': '1-Klick-Bestellung',
      '24/7 support': 'Support 24/7',
      'Live chat, always on': 'Live-Chat immer an',
      'Student discount': 'Studentenrabatt',
      '10% off with valid ID': '10% mit Ausweis',
      'Easy Buy!': 'Einfach kaufen!',
      'View more': 'Mehr anzeigen',
      'Shop →': 'Shop →',
      'Cart': 'Warenkorb',
      'In Stock': 'Auf Lager',
      'Subtotal': 'Zwischensumme',
      'Shipping': 'Versand',
      'Total': 'Gesamt',
      'FREE': 'KOSTENLOS'
    },
    zh: {
      'Currency & Language': '货币与语言',
      'Currency': '货币',
      'Language': '语言',
      'International Site': '国际站',
      'Continue shopping': '继续购物',
      'Add to Cart': '加入购物车',
      'Buy Now': '立即购买',
      'Shopping Cart': '购物车',
      'Customer Service': '客户服务',
      'Secure checkout': '安全结账',
      'Encrypted payments': '加密支付',
      'Easy buy': '轻松购买',
      'One-click ordering': '一键下单',
      '24/7 support': '全天候支持',
      'Live chat, always on': '在线客服',
      'Student discount': '学生优惠',
      '10% off with valid ID': '凭学生证享九折',
      'Easy Buy!': '轻松买！',
      'View more': '查看更多',
      'Shop →': '商店 →',
      'Cart': '购物车',
      'In Stock': '有货',
      'Subtotal': '小计',
      'Shipping': '运费',
      'Total': '合计',
      'FREE': '免费'
    },
    ja: {
      'Currency & Language': '通貨と言語',
      'Currency': '通貨',
      'Language': '言語',
      'International Site': '国際サイト',
      'Continue shopping': '買い物を続ける',
      'Add to Cart': 'カートに追加',
      'Buy Now': '今すぐ購入',
      'Shopping Cart': 'カート',
      'Customer Service': 'カスタマーサービス',
      'Secure checkout': '安全な決済',
      'Encrypted payments': '暗号化決済',
      'Easy buy': 'かんたん購入',
      'One-click ordering': 'ワンクリック注文',
      '24/7 support': '24時間サポート',
      'Live chat, always on': 'ライブチャット',
      'Student discount': '学割',
      '10% off with valid ID': '学生証で10%OFF',
      'Easy Buy!': 'かんたん購入！',
      'View more': 'もっと見る',
      'Shop →': 'ショップ →',
      'Cart': 'カート',
      'In Stock': '在庫あり',
      'Subtotal': '小計',
      'Shipping': '配送料',
      'Total': '合計',
      'FREE': '無料'
    },
    ar: {
      'Currency & Language': 'العملة واللغة',
      'Currency': 'العملة',
      'Language': 'اللغة',
      'International Site': 'الموقع الدولي',
      'Continue shopping': 'متابعة التسوق',
      'Add to Cart': 'أضف إلى السلة',
      'Buy Now': 'اشترِ الآن',
      'Shopping Cart': 'سلة التسوق',
      'Customer Service': 'خدمة العملاء',
      'Secure checkout': 'دفع آمن',
      'Encrypted payments': 'مدفوعات مشفّرة',
      'Easy buy': 'شراء سهل',
      'One-click ordering': 'طلب بنقرة واحدة',
      '24/7 support': 'دعم على مدار الساعة',
      'Live chat, always on': 'دردشة مباشرة',
      'Student discount': 'خصم الطلاب',
      '10% off with valid ID': 'خصم 10٪ بهوية سارية',
      'Easy Buy!': 'شراء سهل!',
      'View more': 'عرض المزيد',
      'Shop →': 'تسوق ←',
      'Cart': 'السلة',
      'In Stock': 'متوفر',
      'Subtotal': 'المجموع الفرعي',
      'Shipping': 'الشحن',
      'Total': 'الإجمالي',
      'FREE': 'مجاني'
    },
    pt: {
      'Currency & Language': 'Moeda e idioma',
      'Currency': 'Moeda',
      'Language': 'Idioma',
      'International Site': 'Site internacional',
      'Continue shopping': 'Continuar comprando',
      'Add to Cart': 'Adicionar ao carrinho',
      'Buy Now': 'Comprar agora',
      'Shopping Cart': 'Carrinho',
      'Customer Service': 'Atendimento',
      'Secure checkout': 'Checkout seguro',
      'Encrypted payments': 'Pagamentos criptografados',
      'Easy buy': 'Compra fácil',
      'One-click ordering': 'Pedido em 1 clique',
      '24/7 support': 'Suporte 24/7',
      'Live chat, always on': 'Chat ao vivo',
      'Student discount': 'Desconto estudante',
      '10% off with valid ID': '10% com ID válida',
      'Easy Buy!': 'Compra fácil!',
      'View more': 'Ver mais',
      'Shop →': 'Loja →',
      'Cart': 'Carrinho',
      'In Stock': 'Em estoque',
      'Subtotal': 'Subtotal',
      'Shipping': 'Frete',
      'Total': 'Total',
      'FREE': 'GRÁTIS'
    }
  };

  function read(key, fallback) {
    try {
      return localStorage.getItem(key) || fallback;
    } catch (e) {
      return fallback;
    }
  }

  function write(key, value) {
    try {
      localStorage.setItem(key, value);
    } catch (e) { /* ignore */ }
  }

  function getCurrencyCode() {
    var c = read(STORAGE_CURRENCY, 'USD');
    return CURRENCIES[c] ? c : 'USD';
  }

  function getLanguageCode() {
    var l = read(STORAGE_LANGUAGE, 'en');
    return LANGUAGES[l] ? l : 'en';
  }

  function getCurrency() {
    return CURRENCIES[getCurrencyCode()];
  }

  function getLanguage() {
    return LANGUAGES[getLanguageCode()];
  }

  function formatMoney(usdAmount) {
    var cur = getCurrency();
    var n = Number(usdAmount);
    if (!isFinite(n)) n = 0;
    var converted = n * cur.rate;
    var formatted = converted.toLocaleString(undefined, {
      minimumFractionDigits: cur.decimals,
      maximumFractionDigits: cur.decimals
    });
    // Symbol after for some; keep symbol-first for most.
    if (cur.code === 'AED') return formatted + ' ' + cur.symbol;
    return cur.symbol + formatted;
  }

  function t(key) {
    var dict = I18N[getLanguageCode()] || I18N.en;
    return dict[key] != null ? dict[key] : (I18N.en[key] != null ? I18N.en[key] : key);
  }

  function applyPrices(root) {
    var scope = root || document;
    scope.querySelectorAll('[data-usd]').forEach(function (el) {
      var usd = parseFloat(el.getAttribute('data-usd'));
      if (!isFinite(usd)) return;
      var prefix = el.getAttribute('data-money-prefix') || '';
      var suffix = el.getAttribute('data-money-suffix') || '';
      el.textContent = prefix + formatMoney(usd) + suffix;
    });
  }

  function applyLanguage() {
    var lang = getLanguage();
    document.documentElement.lang = lang.code;
    document.documentElement.dir = lang.dir;

    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      if (!key) return;
      // Preserve nested markup: only set text if element has no element children,
      // otherwise update first text node / dedicated span.
      var target = el.querySelector('[data-i18n-text]') || el;
      if (target.children.length && !el.querySelector('[data-i18n-text]')) {
        // Update only direct text if mixed — skip complex nodes.
        return;
      }
      target.textContent = t(key);
    });

    // Translate known select aria / panel titles already marked.
    document.querySelectorAll('.currency-panel-title[data-i18n], .currency-panel-label[data-i18n], .currency-panel-link[data-i18n]').forEach(function (el) {
      el.textContent = t(el.getAttribute('data-i18n'));
    });
  }

  function syncSelects() {
    var cur = getCurrencyCode();
    var lang = getLanguageCode();
    document.querySelectorAll('select[data-lunax-currency]').forEach(function (sel) {
      sel.value = cur;
    });
    document.querySelectorAll('select[data-lunax-language]').forEach(function (sel) {
      sel.value = lang;
    });
  }

  function updateBadges() {
    var cur = getCurrency();
    document.querySelectorAll('[data-lunax-currency-badge]').forEach(function (el) {
      el.textContent = cur.code;
      el.setAttribute('title', cur.label);
    });
    document.querySelectorAll('[data-lunax-lang-badge]').forEach(function (el) {
      el.textContent = getLanguage().code.toUpperCase();
    });
    // Aria on globe triggers
    document.querySelectorAll('.nav-icon[aria-label][data-lunax-locale-trigger]').forEach(function (a) {
      a.setAttribute('aria-label', 'Currency / Language — ' + cur.code + ' · ' + getLanguage().label);
    });
  }

  function emit() {
    var detail = {
      currency: getCurrencyCode(),
      language: getLanguageCode(),
      formatMoney: formatMoney
    };
    try {
      document.dispatchEvent(new CustomEvent('lunax:locale', { detail: detail }));
    } catch (e) { /* IE ignore */ }
    if (typeof global.LUNAX_onLocaleChange === 'function') {
      try { global.LUNAX_onLocaleChange(detail); } catch (e2) { /* ignore */ }
    }
  }

  function setCurrency(code) {
    if (!CURRENCIES[code]) return;
    write(STORAGE_CURRENCY, code);
    syncSelects();
    updateBadges();
    applyPrices();
    emit();
  }

  function setLanguage(code) {
    if (!LANGUAGES[code]) return;
    write(STORAGE_LANGUAGE, code);
    syncSelects();
    updateBadges();
    applyLanguage();
    emit();
  }

  function bindPanels() {
    document.querySelectorAll('.account-trigger').forEach(function (trigger) {
      if (!trigger.querySelector('.currency-panel')) return;
      var link = trigger.querySelector('.nav-icon');
      if (link) {
        link.setAttribute('data-lunax-locale-trigger', '1');
        link.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          var willOpen = !trigger.classList.contains('is-open');
          document.querySelectorAll('.account-trigger.is-open').forEach(function (t) {
            t.classList.remove('is-open');
          });
          if (willOpen) trigger.classList.add('is-open');
        });
      }
      // Keep panel interactive — don't navigate away on International Site unless intended
      var intl = trigger.querySelector('.currency-panel-link');
      if (intl) {
        intl.addEventListener('click', function (e) {
          e.preventDefault();
          // Scroll to currency market panel if present
          var market = document.querySelector('[data-center-open="currency"]');
          if (market) market.click();
        });
      }
    });

    document.querySelectorAll('select[data-lunax-currency]').forEach(function (sel) {
      sel.addEventListener('change', function () {
        setCurrency(sel.value);
      });
      // Stop hover-close issues while focusing select
      sel.addEventListener('mousedown', function (e) { e.stopPropagation(); });
      sel.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    document.querySelectorAll('select[data-lunax-language]').forEach(function (sel) {
      sel.addEventListener('change', function () {
        setLanguage(sel.value);
      });
      sel.addEventListener('mousedown', function (e) { e.stopPropagation(); });
      sel.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    // Click outside closes pinned panel
    document.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('.account-trigger')) return;
      document.querySelectorAll('.account-trigger.is-open').forEach(function (t) {
        t.classList.remove('is-open');
      });
    });
  }

  function enrichMoneyNodes() {
    // Tag common PHP-rendered $X.XX spans that expose a USD source via data-price on parent.
    document.querySelectorAll('.cart-item[data-price]').forEach(function (row) {
      var priceEl = row.querySelector('.cart-item-price');
      if (!priceEl) return;
      var unit = parseFloat(row.dataset.price);
      var qty = parseInt(row.dataset.qty || '1', 10);
      if (!isFinite(unit)) return;
      priceEl.setAttribute('data-usd', String(unit * qty));
    });
    document.querySelectorAll('#summarySubtotal, #summaryShipping, #summaryTax, #summaryTotal').forEach(function (el) {
      if (el.hasAttribute('data-usd')) return;
      // Parse existing $ amount as USD baseline if present
      var m = (el.textContent || '').replace(/[^0-9.]/g, '');
      if (m && el.textContent.indexOf('FREE') === -1 && el.textContent.indexOf('GRAT') === -1) {
        el.setAttribute('data-usd', m);
      }
    });
  }

  function init() {
    bindPanels();
    syncSelects();
    updateBadges();
    enrichMoneyNodes();
    applyPrices();
    applyLanguage();
    emit();
  }

  global.LUNAX = global.LUNAX || {};
  global.LUNAX.locale = {
    currencies: CURRENCIES,
    languages: LANGUAGES,
    getCurrencyCode: getCurrencyCode,
    getLanguageCode: getLanguageCode,
    formatMoney: formatMoney,
    t: t,
    setCurrency: setCurrency,
    setLanguage: setLanguage,
    applyPrices: applyPrices,
    applyLanguage: applyLanguage,
    init: init
  };
  // Shortcut used by product grids
  global.LUNAX.formatMoney = formatMoney;
  global.LUNAX.t = t;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window);
