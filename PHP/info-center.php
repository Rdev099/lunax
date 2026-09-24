<?php
/* ============================================================================
   LUNAX — Info Center (info-center.php)
   ----------------------------------------------------------------------------
   One page. Sidebar on the left lists every footer title, grouped by
   section (Company / Help & Support / Customer Care / Legal). Clicking a
   title swaps the body content on the right — no page reload — and updates
   the URL hash (e.g. #shipping) so links are shareable and back/forward
   works. Also works with ?page=slug on first load, and falls back to
   #slug in the URL if JS somehow doesn't run (content is still readable
   server-side rendered, just all sections instead of one at a time).

   To add a new page: add an entry to $pages AND add it to $nav under the
   right group so it shows up in the sidebar.
   ========================================================================== */
session_start();

$pages = [

    // ---- COMPANY --------------------------------------------------------------
'about' => [
    'title' => 'About LUNAX',
    'body'  => [
        ['heading' => 'Technology You Can Trust'],
        "LUNAX started in 2026 with one simple idea: buying technology should feel easy, clear, and trustworthy.",
        "Choosing the right phone, laptop, headphones, drone, or smart device can be difficult. There are thousands of products, different specifications, and countless marketing claims. We created LUNAX to make that decision easier.",
        ['heading' => 'What We Do'],
        "LUNAX is an independent online technology store offering a carefully selected range of phones, laptops, computers, audio products, smart home devices, drones, wearables, and other technology products.",
        "We focus on products that offer real value, useful features, and reliable performance. Our goal is not to sell everything &mdash; it is to sell products we believe are worth buying.",
        ['heading' => 'Carefully Selected Products'],
        "Every product listed on LUNAX goes through an internal review before it becomes available in our store.",
        "We look at important details such as:",
        ['list' => [
            'Product specifications',
            'Features and performance',
            'Build quality',
            'Compatibility',
            'Value for money',
            'Brand reliability',
        ]],
        "We believe customers deserve real information instead of unnecessary marketing language.",
        ['heading' => 'Clear &amp; Honest Information'],
        "We make product information as clear as possible so you can understand what you are buying before placing an order.",
        "We provide important specifications, features, pricing, availability, and other useful information directly on each product page.",
        "If we wouldn't buy it ourselves, we don't sell it.",
        ['heading' => 'Built Around Customers'],
        "A good shopping experience doesn't end when you place an order.",
        "That's why LUNAX aims to provide clear shipping, tracking, returns, refunds, and warranty information so you know what to expect before and after your purchase.",
        "If something goes wrong, our goal is to make the process simple and straightforward.",
        ['heading' => 'Growing With Technology'],
        "Technology changes quickly, and so do the products people use every day.",
        "LUNAX continues to explore new products, emerging technologies, and useful innovations &mdash; from smartphones and laptops to drones, smart home devices, audio, and wearables.",
        "We're building LUNAX one product, one improvement, and one customer at a time.",
        ['heading' => 'Our Promise'],
        "We want LUNAX to be a place where you can discover technology with confidence.",
        "Real products. Clear information. Simple shopping.",
        "Thank you for choosing LUNAX.",
    ],
],
'careers' => [
    'title' => 'Careers',
    'body'  => [
        "Join LUNAX and help us build a better technology shopping experience.",
        "We're looking for people who care about technology, design, e-commerce, and customers.",
        ['heading' => 'Open Positions'],
        "Available jobs will be listed here when positions are open.",
        ['heading' => 'Want to Join LUNAX?'],
        "Don't see the right position? Send us your resume and tell us how you can help.",
        "Email: <a href=\"mailto:careers@lunax.com\">careers@lunax.com</a>",
    ],
],
'journal' => [
    'title' => 'LUNAX Journal',
    'body'  => [
        "The LUNAX Journal is where we share useful information about technology, the products we sell, new tech trends, buying guides, and practical tips to help you make better choices.",
        ['heading' => 'Recent Posts'],
        ['list' => [
            'Buying Your First Mirrorless Camera &mdash; What really matters before you buy',
            'ANC vs. Passive Noise Isolation &mdash; Which one is right for you?',
            'How to Choose the Right Smartphone &mdash; The features that actually matter',
            "A Beginner's Guide to Drones &mdash; What to know before your first purchase",
            'Tech Trends to Watch &mdash; The latest products and innovations shaping technology',
        ]],
        "New articles are published regularly. Subscribe to the LUNAX Journal to receive the latest tech guides, news, and tips by email.",
    ],
],
'press' => [
    'title' => 'Press &amp; Media',
    'body'  => [
        "For media inquiries, interview requests, product reviews, or brand asset requests, please contact the LUNAX team at:",
        "Email: <a href=\"mailto:press@lunax.com\">press@lunax.com</a>",
        "We aim to respond to media inquiries within 2 business days.",
        ['heading' => 'Brand Assets'],
        "Official LUNAX logos, product images, and other brand assets may be available upon request.",
        "When using LUNAX-provided images or brand materials, please credit \"LUNAX\" and do not modify the logo or use our brand assets in a misleading way.",
    ],
],
'investors' => [
    'title' => 'Investor Relations',
    'body'  => [
        "LUNAX is privately operated and is not currently seeking outside investment.",
        "For business partnerships, wholesale opportunities, or other business inquiries, please contact us:",
        "Email: <a href=\"mailto:partnerships@lunax.com\">partnerships@lunax.com</a>",
        "If LUNAX opens investment opportunities in the future, additional information will be published on this page.",
    ],
],
'sustainability' => [
    'title' => 'Sustainability',
    'body'  => [
        "At LUNAX, we're working to make our products and operations more environmentally responsible.",
        "We aim to use recyclable and minimal packaging whenever possible and are working with our suppliers to reduce unnecessary plastic and packaging materials.",
        ['heading' => 'Trade-In &amp; Recycling'],
        "We're working toward offering a Trade-In program that allows eligible working devices to be exchanged for LUNAX store credit.",
        "We also aim to provide responsible e-waste recycling options for eligible electronic devices that are no longer working or useful.",
        "More information about our Trade-In and Recycling programs will be available as these services are introduced.",
    ],
],

    // ---- HELP & SUPPORT ---------------------------------------------------
'shipping' => [
    'title' => 'Shipping Info',
    'body'  => [
        "At LUNAX, we work to process and deliver your orders as quickly as possible. Shipping options, delivery times, and costs may vary depending on your location and the items in your order.",
        ['heading' => 'Shipping Options'],
        ['list' => [
            'Standard Shipping &mdash; Estimated delivery: 5&ndash;7 business days',
            'Express Shipping &mdash; Estimated delivery: 2&ndash;3 business days',
            'Overnight Shipping &mdash; Estimated delivery: next business day, where available',
        ]],
        "Available shipping options and the exact cost will be shown during checkout.",
        ['heading' => 'Order Processing'],
        "Orders are normally processed during our business hours. Orders placed after the daily processing cutoff may be processed on the next business day.",
        "Once your order ships, you'll receive a tracking link by email so you can follow your delivery.",
        ['heading' => 'International Shipping'],
        "International orders may be subject to customs duties, taxes, or other fees imposed by the destination country. These charges may not be included in your LUNAX order total and are the responsibility of the customer where applicable.",
    ],
],
'returns' => [
    'title' => 'Returns &amp; Exchanges',
    'body'  => [
        "At LUNAX, we want you to be satisfied with your purchase. Eligible items can be returned within 30 days of delivery for a refund, provided they are unused and in their original condition and packaging.",
        "Some electronics may have a 14-day return period. Any special return conditions or time limits will be clearly stated on the product page.",
        ['heading' => 'How to Start a Return'],
        ['list' => [
            'Log in to your LUNAX account',
            'Go to Account &rarr; Orders',
            'Select the item you want to return',
            'Choose a return reason and follow the return instructions',
            'If eligible, use the provided return label to send the item back',
        ]],
        ['heading' => 'Refunds'],
        "Once we receive and inspect your returned item, your refund will be processed to your original payment method.",
        "Refund processing may take 5&ndash;7 business days, depending on your payment provider.",
    ],
],
'warranty' => [
    'title' => 'Warranty',
    'body'  => [
        "At LUNAX, eligible products are covered by the manufacturer's warranty according to the warranty terms provided with the product.",
        "Some products may also be eligible for LUNAX Extended Care, which may be available as an additional option at checkout.",
        ['heading' => 'What Does the Warranty Cover?'],
        "Warranty coverage generally applies to:",
        ['list' => [
            'Manufacturing defects',
            'Hardware failures caused by normal use',
            "Other issues covered by the manufacturer's warranty terms",
        ]],
        "Warranty coverage does not normally include:",
        ['list' => [
            'Accidental or physical damage',
            'Water or liquid damage',
            'Misuse or improper installation',
            'Normal wear and tear',
            'Damage caused by unauthorized modifications or repairs',
        ]],
        "Coverage may vary depending on the product and manufacturer.",
        ['heading' => 'How to Make a Warranty Claim'],
        "To submit a warranty claim, contact LUNAX Support with:",
        ['list' => [
            'Your order number',
            'Product name or model',
            'A description of the issue',
            'Photos or other information requested by our support team',
        ]],
        "After reviewing your claim, we may arrange an appropriate solution, such as a repair, replacement, or refund, depending on the product, warranty terms, and applicable policies.",
    ],
],
'track-order' => [
    'title' => 'How to Track Your Order',
    'body'  => [
        "Once your order has been shipped, you'll receive a shipping confirmation email with your tracking number and a link to the carrier's tracking page.",
        ['heading' => 'Track Your Order'],
        ['list' => [
            'Log in to your LUNAX account',
            'Go to Account &rarr; Orders',
            'Select your order',
            'Click Track Package to view the latest shipping information',
        ]],
        ['heading' => 'Tracking Updates'],
        "Tracking information may take some time to appear after your shipping label is created. If your tracking information hasn't been updated for several business days, please contact LUNAX Support and we'll be happy to help.",
    ],
],
'financing' => [
    'title' => 'Financing &amp; Installments',
    'body'  => [
        "Make your LUNAX purchase easier with flexible payment options available at checkout.",
        "Depending on your location and eligibility, you may be able to split your purchase into multiple payments or choose a monthly financing option.",
        ['heading' => 'How It Works'],
        ['list' => [
            'Add your items to your cart and continue to checkout',
            'Select an available installment or financing option',
            "Complete the provider's approval process",
            'If approved, complete your purchase and follow the payment schedule provided',
        ]],
        "Your order may be shipped according to our normal processing and shipping times.",
        ['heading' => 'Important Information'],
        "Financing and installment plans are provided by third-party payment providers. Availability, eligibility, fees, payment schedules, and terms depend on the provider and your location.",
        "Please review the provider's terms carefully before choosing a financing option.",
    ],
],
'faq' => [
    'title' => 'FAQ',
    'body'  => [
        ['heading' => 'Where is my order?'],
        "Once your order has been shipped, you'll receive a tracking link by email. You can also check your order status anytime under Account &rarr; Orders.",
        ['heading' => 'What is your return policy?'],
        "Eligible items can be returned within 30 days of delivery, provided they are unused and in their original condition and packaging. For full details, please see our Returns &amp; Refunds policy.",
        ['heading' => 'Do you ship internationally?'],
        "We currently ship to the locations available at checkout. Shipping availability, delivery times, and costs may vary depending on your location.",
        ['heading' => 'How do I cancel or change my order?'],
        "You can request a cancellation or change shortly after placing your order by going to Account &rarr; Orders. Once an order has entered processing or shipping, changes may no longer be possible. Contact LUNAX Support as soon as possible and we'll do our best to help.",
        ['heading' => 'What payment methods do you accept?'],
        "Available payment methods are displayed during checkout. Payment options may vary depending on your location.",
        ['heading' => 'How can I contact LUNAX?'],
        "If you need help with an order or have any questions, contact us through our Contact Us page or email <a href=\"mailto:support@lunax.com\">support@lunax.com</a>.",
    ],
],

    // ---- CUSTOMER CARE ------------------------------------------------------
'payment-tax' => [
    'title' => 'Payment &amp; Tax',
    'body'  => [
        "At LUNAX, we aim to make checkout simple and secure. We accept the payment methods currently available at checkout.",
        "All payments are processed through secure payment providers. LUNAX does not store your full payment card number.",
        ['heading' => 'Taxes'],
        "Any applicable taxes or fees are calculated during checkout based on your shipping address and applicable local regulations.",
        "You'll see the final total, including any applicable taxes and fees, before you confirm your order.",
        ['heading' => 'Secure Payments'],
        "Your payment information is handled securely by our payment providers. We use appropriate security measures to help protect your payment and personal information.",
    ],
],
'gift-cards' => [
    'title' => 'Gift Cards',
    'body'  => [
        "Give the perfect gift with a LUNAX Gift Card. Choose any amount from $10 to $500 and send it directly to the recipient by email.",
        ['list' => [
            'Instant delivery — sent by email within minutes of purchase',
            'No expiration date — use your gift card whenever you\'re ready',
            'Use anywhere on LUNAX — redeem it toward eligible products across our store',
            'Check your balance anytime — view your remaining balance during checkout',
        ]],
        "Ready to give the gift of choice? Buy a LUNAX Gift Card today and send it instantly.",
    ],
    ],
'price-match' => [
    'title' => 'Price Match Guarantee',
    'body'  => [
        "Found the same product for a lower price at another authorized retailer within 7 days of your purchase? LUNAX will match the eligible price difference.",
        ['heading' => 'Eligibility'],
        "To qualify:",
        ['list' => [
            'The product must be identical, including the same model, color, and configuration',
            'The competitor must be an authorized retailer and have the product in stock',
            "The competitor's price must be publicly available and verifiable",
            'Price matching does not apply to flash sales, clearance items, limited-time promotions, or marketplace listings',
        ]],
        ['heading' => 'How to Request a Price Match'],
        "Contact LUNAX Support within 7 days of delivery and provide a link or screenshot showing the lower price.",
        "After verification, we'll review your request and, if eligible, refund the difference.",
        "LUNAX reserves the right to verify and approve price-match requests.",
    ],
    ],
    'loyalty' => [
        'title' => 'LUNAX Rewards',
        'body'  => [
           "Get rewarded every time you shop with LUNAX. Earn 1 point for every $1 you spend and redeem your points for discounts on future orders.",
           ['heading' => '500 points = $10 off'],
           ['list' => [
                'Free to join — automatically activated with your first order.',
                'Earn bonus points on your birthday and during special LUNAX promotions.',
                'Points never expire as long as your account remains active.',
            ]],
            "Start shopping, earn points, and enjoy more rewards with LUNAX.",
        ],
    ],
    'refer' => [
        'title' => 'Refer a Friend',
        'body'  => [
            "Invite your friends to shop with LUNAX and <strong>earn rewards together</strong>.
            Share your unique referral link with a friend. When your friend makes their first eligible purchase, you’ll both receive LUNAX store credit.",
            "You can find your referral link anytime under Account → Rewards.",
            "There’s no limit to the number of friends you can refer. Share LUNAX and earn more rewards!"
        ],
    ],

    // ---- LEGAL --------------------------------------------------------------
    'privacy' => [
        'title' => 'Privacy Center',
        'body'  => [
            "At LUNAX, we respect your privacy and are committed to protecting your personal information. This page explains what information we collect, how we use it, and the choices you have.",
            ['heading' => 'We may collect:'],
            ['list' => [
                'Account information, such as your name and email address.',
                'Order and purchase history.',
                'Information needed to process and deliver your orders.',
                'Basic website usage and browsing data to improve our services.',
            ]],
            ['heading' => 'We use your information to:'],
            ['list' => [
                'Create and manage your account.',
                'Process and deliver your orders.',
                'Provide customer support.',
                'Improve our website, products, and services.',
                'Keep our website secure.',
            ]],
            ['heading' => 'Your Privacy'],
            "LUNAX does not sell your personal information to third parties.
            We may share necessary information with trusted service providers, such as payment and delivery services, only when required to provide our services.",
            ['heading' => 'Your Choices'],
            "You can contact us if you want to ask about your personal information, correct inaccurate information, or request assistance with your account.",
            ['heading' => 'Contact Us'],
            "If you have questions about this Privacy Policy, please contact us:",
            "Email: support@lunax.com",
       
            ],
    ],
    'terms' => [
        'title' => 'Terms &amp; Conditions',
        'body'  => [
            "Last updated: September 13, 2026",
            "Welcome to LUNAX. By accessing or using our website, you agree to these Terms & Conditions. If you do not agree with these terms, please do not use our website.",
            ['heading' => '1. Using LUNAX'],
            "You agree to use LUNAX only for lawful purposes. You are responsible for providing accurate information when creating an account or placing an order.  You are also responsible for keeping your account information secure and for all activity that occurs through your account.",
            ['heading' => '2. Products and Orders'],
            "We aim to provide accurate product descriptions, images, specifications, availability, and prices. However, errors may occasionally occur.
            Placing an order does not necessarily mean that the order has been accepted. LUNAX may cancel or refuse an order when necessary, including when a product is unavailable or there is an obvious pricing or product information error.
            If we cancel an order after payment has been made, we will provide an appropriate refund.",
            ['heading' => '3. Prices and Payments'],
            "All product prices displayed on LUNAX are subject to change without prior notice.
            We may correct pricing errors discovered on the website. If an incorrect price affects an order you have placed, we may contact you before processing the order or cancel the order and provide a refund where applicable.",
            ['heading' => "4. Intellectual Property"],
            "All content on LUNAX, including logos, graphics, text, product information, designs, and website elements, belongs to LUNAX or its respective content owners unless otherwise stated.
            You may not copy, reproduce, modify, distribute, or use our content for commercial purposes without permission.",
            ['heading' => "5. Availability of the Website"],
            "We try to keep LUNAX available and functioning properly, but we cannot guarantee that the website will always be uninterrupted, error-free, or available.
            We may update, modify, suspend, or discontinue parts of the website when necessary.",
            ['heading' => "6. Limitation of Liability"],
            "To the extent permitted by applicable law, LUNAX will not be responsible for indirect, incidental, or consequential losses arising from the use of our website or products.
            Nothing in these Terms limits any rights or protections that cannot legally be excluded under applicable law.",
            ['heading' => "7. Third-Party Services"],
            "LUNAX may use third-party services such as payment providers, delivery services, authentication providers, or external links.
            Third-party services may have their own terms and privacy policies. LUNAX is not responsible for the content or policies of third-party websites or services.",
            ['heading' => "8. Changes to These Terms"],
            "We may update these Terms & Conditions from time to time. Changes will be posted on this page with an updated date.
            Your continued use of LUNAX after changes are posted means that you accept the updated terms.",
            ['heading' => "9. Disputes and Applicable Law"],
            "Any dispute relating to LUNAX should first be resolved by contacting us so that we can attempt to resolve the issue.
            Where required, disputes will be handled according to the applicable laws and jurisdiction governing LUNAX and its operations.",
            ['heading' => "10. Contact Us"],
            "If you have questions about these Terms & Conditions, please contact us:",
            ['heading' => "LUNAX Support"],
            "Email: support@lunax.com",
            ],
    ],
    'accessibility' => [
        'title' => 'Accessibility',
        'body'  => [
            "At LUNAX, we’re committed to making our website accessible and easy to use for everyone, including people who use screen readers, keyboard navigation, or other assistive technologies.",
            "If you experience an accessibility issue or barrier while using our website, please contact us and tell us which page you were using and what problem you experienced. We’ll review your feedback and work to improve the experience.",
            "Accessibility Support: support@lunax.com"
        ],
    ],

];

// Sidebar grouping — controls what shows up and in what order/section.
$nav = [
    'Company'          => ['about', 'careers', 'journal', 'press', 'investors', 'sustainability'],
    'Help & Support'   => ['shipping', 'returns', 'warranty', 'track-order', 'financing', 'faq'],
    'Customer Care'    => ['payment-tax', 'gift-cards', 'price-match', 'loyalty', 'refer'],
    'Legal'            => ['privacy', 'terms', 'do-not-sell', 'accessibility'],
];

$slug = isset($_GET['page']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING))) : 'about';
if (!isset($pages[$slug])) {
    $slug = 'about';
}

function lnx_render_body($body)
{
    foreach ($body as $block) {
        if (is_string($block)) {
            echo '<p>' . $block . '</p>' . "\n";
        } elseif (isset($block['heading'])) {
            echo '<h2>' . $block['heading'] . '</h2>' . "\n";
        } elseif (isset($block['list'])) {
            echo '<ul>' . "\n";
            foreach ($block['list'] as $li) {
                echo '<li>' . $li . '</li>' . "\n";
            }
            echo '</ul>' . "\n";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Info Center &mdash; LUNAX</title>
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
    --muted:rgba(20,15,12,0.6);
  }
  *{ box-sizing:border-box; }
  body{
    margin:0; background:var(--cream); color:var(--ink);
    font-family:'JetBrains Mono', monospace;
  }
  a{ color:inherit; }

  .lnx-topbar{
    background:var(--ink); color:#fff; padding:0.9rem 1.75rem;
    display:flex; align-items:center; justify-content:space-between;
  }
  .lnx-logo{ font-family:'Space Grotesk',sans-serif; font-weight:700; letter-spacing:0.12em; font-size:1.1rem; text-decoration:none; color:#fff; }
  .lnx-back{ font-size:0.78rem; color:var(--gold); text-decoration:none; }
  .lnx-back:hover{ text-decoration:underline; }

  .ic-wrap{
    max-width:1200px; margin:0 auto; padding:2rem 1.5rem 5rem;
    display:flex; gap:2.5rem; align-items:flex-start;
  }

  /* ---- Sidebar ---- */
  .ic-sidebar{
    flex:0 0 250px; position:sticky; top:1.5rem;
  }
  .ic-sidebar-search{
    width:100%; padding:0.6rem 0.75rem; margin-bottom:1.3rem;
    border:1px solid var(--line); border-radius:8px;
    font-family:'JetBrains Mono',monospace; font-size:0.78rem; background:#fff; color:var(--ink);
  }
  .ic-sidebar-search:focus{ outline:2px solid var(--gold-dark); outline-offset:1px; }

  .ic-group{ margin-bottom:1.4rem; }
  .ic-group-title{
    font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.68rem;
    text-transform:uppercase; letter-spacing:0.06em; color:var(--black);
    margin-bottom:0.5rem; padding-left:0.75rem;
  }
  .ic-link{
    display:block; padding:0.5rem 0.75rem; border-radius:8px;
    font-size:0.82rem; color:rgba(20,15,12,0.78); text-decoration:none;
    cursor:pointer; border-left:2px solid transparent;
    transition: background-color .12s ease, color .12s ease, border-color .12s ease;
  }
  .ic-link:hover{ background:rgba(20,15,12,0.04); }
  .ic-link.active{
    background:rgba(242,201,76,0.15); color:var(--ink); font-weight:700;
    border-left:2px solid var(--gold-dark);
  }
  .ic-link[hidden]{ display:none; }
  .ic-group[data-empty="true"]{ display:none; }

  /* ---- Content ---- */
  .ic-content{
    flex:1; min-width:0; background:#fff; border:1px solid var(--line); border-radius:16px;
    padding:2.2rem 2.4rem;
  }
  .ic-crumb{ font-size:0.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:1rem; }
  .ic-content h1{
    font-family:'Space Grotesk',sans-serif; font-size:1.75rem; margin:0 0 1.5rem;
    border-bottom:1px solid var(--line); padding-bottom:1.1rem;
  }
  .ic-content h2{
    font-family:'Space Grotesk',sans-serif; font-size:1rem; margin:1.7rem 0 0.6rem;
  }
  .ic-content p{
    font-size:0.9rem; line-height:1.75; color:rgba(20,15,12,0.82); margin:0 0 1rem;
  }
  .ic-content ul{ margin:0 0 1.2rem; padding-left:1.3rem; }
  .ic-content li{ font-size:0.88rem; line-height:1.7; color:rgba(20,15,12,0.82); margin-bottom:0.35rem; }

  .ic-content-section[hidden]{ display:none; }

  @media (max-width: 860px){
    .ic-wrap{ flex-direction:column; }
    .ic-sidebar{ flex:1 1 auto; width:100%; position:static; }
    .ic-content{ padding:1.5rem; }
  }
</style>
</head>
<body>

<div class="lnx-topbar">
  <a href="PHP/home.php" class="lnx-logo">LUNAX</a>
  <a class="lnx-back" href="home.php">&larr; Back to store</a>
</div>

<div class="ic-wrap">

  <nav class="ic-sidebar" aria-label="Info Center navigation">
    <input type="text" class="ic-sidebar-search" id="icSearch" placeholder="Search help topics...">

    <?php foreach ($nav as $groupTitle => $slugs): ?>
      <div class="ic-group">
        <div class="ic-group-title"><?= htmlspecialchars($groupTitle, ENT_QUOTES, 'UTF-8') ?></div>
        <?php foreach ($slugs as $s): ?>
          <?php if (!isset($pages[$s])) continue; ?>
          <a href="#<?= $s ?>"
             class="ic-link<?= $s === $slug ? ' active' : '' ?>"
             data-slug="<?= $s ?>">
            <?= $pages[$s]['title'] ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </nav>

  <div class="ic-content" id="icContent">
    <?php foreach ($pages as $s => $p): ?>
      <div class="ic-content-section" id="section-<?= $s ?>" data-slug="<?= $s ?>" <?= $s !== $slug ? 'hidden' : '' ?>>
        <div class="ic-crumb">Info Center &rsaquo; <?= strip_tags($p['title']) ?></div>
        <h1><?= $p['title'] ?></h1>
        <?php lnx_render_body($p['body']); ?>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
(function(){
  var links    = document.querySelectorAll('.ic-link');
  var sections = document.querySelectorAll('.ic-content-section');
  var searchEl = document.getElementById('icSearch');
  var content  = document.getElementById('icContent');

  function showSlug(slug, pushState){
    var found = false;
    sections.forEach(function(sec){
      var match = sec.dataset.slug === slug;
      sec.hidden = !match;
      if (match) found = true;
    });
    if (!found) return;

    links.forEach(function(l){
      l.classList.toggle('active', l.dataset.slug === slug);
    });

    if (pushState !== false) {
      history.pushState(null, '', '#' + slug);
    }
    content.scrollTop = 0;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  links.forEach(function(link){
    link.addEventListener('click', function(e){
      e.preventDefault();
      showSlug(this.dataset.slug);
    });
  });

  window.addEventListener('popstate', function(){
    var slug = window.location.hash.replace('#', '');
    if (slug) showSlug(slug, false);
  });

  // On load, respect #hash if present (overrides the server-rendered default)
  var initialSlug = window.location.hash.replace('#', '');
  if (initialSlug) showSlug(initialSlug, false);

  // Sidebar search — filters link labels + hides empty groups
  searchEl.addEventListener('input', function(){
    var term = this.value.trim().toLowerCase();
    document.querySelectorAll('.ic-group').forEach(function(group){
      var groupLinks = group.querySelectorAll('.ic-link');
      var visibleCount = 0;
      groupLinks.forEach(function(link){
        var match = term === '' || link.textContent.toLowerCase().indexOf(term) !== -1;
        link.hidden = !match;
        if (match) visibleCount++;
      });
      group.dataset.empty = (visibleCount === 0) ? 'true' : 'false';
    });
  });
})();
</script>

</body>
</html>
