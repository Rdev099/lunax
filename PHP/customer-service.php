<?php
/* ============================================================================
   LUNAX — Customer Service page (customer-service.php)
   ----------------------------------------------------------------------------
   Linked from the headset icon in the header (B1.php). Contains:
     - Quick contact channels (chat, email, phone)
     - Searchable FAQ accordion
     - Contact form (placeholder submit handler — wire to real backend later)

   FAQ data below is placeholder copy — swap $faqItems for real content or
   pull from a DB/CMS once one exists.
   ========================================================================== */
session_start();

$pageTitle = 'Customer Service — LUNAX';

$faqItems = [
    [
        'q' => 'Where is my order?',
        'a' => 'Once your order ships, you\'ll receive a tracking link by email. You can also check status anytime under Account &rarr; Orders.',
    ],
    [
        'q' => 'What is your return policy?',
        'a' => 'Most items can be returned within 30 days of delivery for a full refund, provided they\'re unused and in original packaging. Some electronics have a 14-day window &mdash; check the product page for details.',
    ],
    [
        'q' => 'How long does shipping take?',
        'a' => 'Standard shipping is 5&ndash;7 business days. Express (2&ndash;3 days) and Overnight options are available at checkout. Orders over $50 ship free.',
    ],
    [
        'q' => 'Do you ship internationally?',
        'a' => 'Yes, we currently ship to the US, Canada, UK, and Australia. Additional countries are being added &mdash; check the country dropdown at checkout.',
    ],
    [
        'q' => 'How do I cancel or change my order?',
        'a' => 'Orders can be changed or cancelled within 1 hour of placing them from Account &rarr; Orders. After that, the order has usually entered fulfillment and can\'t be modified &mdash; contact us and we\'ll do our best to help.',
    ],
    [
        'q' => 'Is my payment information secure?',
        'a' => 'Yes. All payments are processed through encrypted, PCI-compliant channels. LUNAX never stores your full card number on our servers.',
    ],
];

// ---- Placeholder contact form submit handler -------------------------------
// TODO: replace with real validation + email/ticket system integration.
$formSubmitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['support_submit'])) {
    // Basic input sanitization for form fields
    $name = isset($_POST['name']) ? filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) : '';
    $email = isset($_POST['email']) ? filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) : '';
    $topic = isset($_POST['topic']) ? filter_input(INPUT_POST, 'topic', FILTER_SANITIZE_STRING) : '';
    $message = isset($_POST['message']) ? filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) : '';
    $order_number = isset($_POST['order_number']) ? filter_input(INPUT_POST, 'order_number', FILTER_SANITIZE_STRING) : '';
    
    // Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = ''; // Invalid email, clear it
    }
    
    $formSubmitted = true;
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
    --muted:rgba(20,15,12,0.55);
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

  .cs-hero{
    background: linear-gradient(180deg, var(--ink) 0%, #241a13 100%);
    color:#fff; padding:3rem 1.5rem 2.5rem; text-align:center;
  }
  .cs-hero h1{
    font-family:'Space Grotesk',sans-serif; font-size:2rem; margin:0 0 0.5rem;
  }
  .cs-hero p{ color:rgba(255,255,255,0.65); font-size:0.88rem; margin:0 0 1.5rem; }
  .cs-search{
    max-width:520px; margin:0 auto; display:flex; align-items:center; gap:0.5rem;
    background:#fff; border-radius:10px; padding:0.15rem;
  }
  .cs-search input{
    flex:1; border:none; outline:none; padding:0.7rem 0.9rem;
    font-family:'JetBrains Mono',monospace; font-size:0.85rem; color:var(--ink); background:transparent;
  }
  .cs-search button{
    background:var(--gold); border:none; border-radius:8px; width:38px; height:38px;
    display:flex; align-items:center; justify-content:center; cursor:pointer; flex:0 0 auto;
  }

  .cs-wrap{ max-width:1000px; margin:0 auto; padding:2.5rem 1.5rem 4rem; }

  .cs-channels{
    display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:3rem;
  }
  .cs-channel{
    background:#fff; border:1px solid var(--line); border-radius:14px; padding:1.4rem;
    text-align:center; transition: box-shadow .15s ease, transform .15s ease;
  }
  .cs-channel:hover{ box-shadow:0 8px 20px rgba(20,15,12,0.08); transform:translateY(-2px); }
  .cs-channel-icon{
    width:46px; height:46px; border-radius:50%; background:rgba(242,201,76,0.18); color:var(--gold-dark);
    display:flex; align-items:center; justify-content:center; margin:0 auto 0.8rem;
  }
  .cs-channel h3{ font-family:'Space Grotesk',sans-serif; font-size:0.95rem; margin:0 0 0.3rem; }
  .cs-channel p{ color:var(--muted); font-size:0.76rem; margin:0 0 0.9rem; line-height:1.4; }
  .cs-channel .status{
    display:inline-flex; align-items:center; gap:0.35rem; font-size:0.68rem; font-weight:700;
    color:#0d5c37; background:rgba(26,156,92,0.12); padding:0.25rem 0.6rem; border-radius:99px;
  }
  .cs-channel .status.dot{ width:6px; height:6px; border-radius:50%; background:#1a9c5c; }
  .cs-channel .cs-btn{
    display:inline-block; margin-top:0.9rem; border:1px solid var(--line); border-radius:8px;
    padding:0.5rem 1.1rem; font-size:0.72rem; font-weight:700; text-transform:uppercase; cursor:pointer;
    background:#fff;
  }
  .cs-channel .cs-btn:hover{ border-color:var(--gold-dark); }

  .cs-section-title{
    font-family:'Space Grotesk',sans-serif; font-size:1.2rem; margin:0 0 1.2rem;
  }

  .faq-list{ margin-bottom:3rem; }
  .faq-item{
    background:#fff; border:1px solid var(--line); border-radius:12px; margin-bottom:0.7rem; overflow:hidden;
  }
  .faq-question{
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    padding:1rem 1.2rem; cursor:pointer; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.86rem;
  }
  .faq-question .chev{ transition: transform .18s ease; flex:0 0 auto; color:var(--muted); }
  .faq-item.open .faq-question .chev{ transform: rotate(180deg); }
  .faq-answer{
    max-height:0; overflow:hidden; transition: max-height .22s ease;
  }
  .faq-answer-inner{
    padding:0 1.2rem 1.1rem; color:rgba(20,15,12,0.72); font-size:0.8rem; line-height:1.55;
  }
  .faq-item.open .faq-answer{ max-height:260px; }
  .faq-empty{
    display:none; text-align:center; color:var(--muted); font-size:0.82rem; padding:2rem 0;
  }

  .cs-contact{
    background:#fff; border:1px solid var(--line); border-radius:16px; padding:2rem;
  }
  .cs-contact-grid{ display:flex; gap:2.5rem; flex-wrap:wrap; }
  .cs-contact-form{ flex:1; min-width:280px; }
  .cs-contact-aside{
    flex:0 0 240px; font-size:0.78rem; color:var(--muted); line-height:1.6;
  }
  .cs-contact-aside strong{ color:var(--ink); display:block; margin-bottom:0.3rem; font-family:'Space Grotesk',sans-serif; }
  .cs-contact-aside .hrs{ margin-bottom:1.2rem; }

  .field-row{ display:flex; gap:0.8rem; margin-bottom:0.9rem; flex-wrap:wrap; }
  .field{ flex:1; min-width:180px; display:flex; flex-direction:column; gap:0.35rem; }
  .field label{ font-size:0.68rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.03em; }
  .field input, .field select, .field textarea{
    padding:0.62rem 0.75rem; border:1px solid var(--line); border-radius:8px;
    font-family:'JetBrains Mono',monospace; font-size:0.82rem; background:#fff; color:var(--ink); resize:vertical;
  }
  .field input:focus, .field select:focus, .field textarea:focus{ outline:2px solid var(--gold-dark); outline-offset:1px; }

  .cs-submit-btn{
    padding:0.8rem 1.6rem; border:none; border-radius:10px; background:var(--ink); color:#fff;
    font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; cursor:pointer;
    text-transform:uppercase; letter-spacing:0.03em;
  }
  .cs-submit-btn:hover{ opacity:0.9; }

  .cs-success{
    display:flex; align-items:center; gap:0.6rem; background:rgba(26,156,92,0.1); color:#0d5c37;
    border-radius:10px; padding:0.9rem 1.1rem; font-size:0.82rem; margin-bottom:1.2rem;
  }

  @media (max-width: 720px){
    .cs-channels{ grid-template-columns:1fr; }
    .cs-contact-grid{ flex-direction:column; }
  }
</style>
</head>
<body>

<div class="lnx-topbar">
  <div class="lnx-logo">LUNAX</div>
  <a class="lnx-back" href="PHP/home.php">&larr; Back to store</a>
</div>

<div class="cs-hero">
  <h1>How can we help?</h1>
  <p>Search our help center or reach a real human below.</p>
  <div class="cs-search">
    <input type="text" id="faqSearchInput" placeholder="Search FAQs, e.g. &ldquo;returns&rdquo;, &ldquo;shipping&rdquo;...">
    <button type="button" aria-label="Search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#140F0C" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
    </button>
  </div>
</div>

<div class="cs-wrap">

  <div class="cs-channels">
    <div class="cs-channel">
      <div class="cs-channel-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <h3>Live Chat</h3>
      <p>Get an answer in minutes from our support team.</p>
      <span class="status"><span class="dot"></span> Online now</span>
      <br>
      <button type="button" class="cs-btn" id="openChatBtn">Start chat</button>
    </div>

    <div class="cs-channel">
      <div class="cs-channel-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6l-10 7L2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
      </div>
      <h3>Email Us</h3>
      <p>support@lunax.com &mdash; typical reply time under 24h.</p>
      <span class="status" style="color:#8a5a05; background:rgba(242,201,76,0.2);"><span class="dot" style="background:var(--gold-dark);"></span> ~4h response</span>
      <br>
      <a class="cs-btn" href="mailto:support@lunax.com" style="display:inline-block; text-decoration:none;">Send email</a>
    </div>

    <div class="cs-channel">
      <div class="cs-channel-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
      </div>
      <h3>Call Us</h3>
      <p>1-800-LUNAX-00 &mdash; Mon&ndash;Fri, 8am&ndash;8pm EST.</p>
      <span class="status" style="color:#7a1b21; background:rgba(122,27,33,0.1);"><span class="dot" style="background:#7a1b21;"></span> Closed now</span>
      <br>
      <a class="cs-btn" href="tel:18005862290" style="display:inline-block; text-decoration:none;">Call now</a>
    </div>
  </div>

  <h2 class="cs-section-title">Frequently Asked Questions</h2>
  <div class="faq-list" id="faqList">
    <?php foreach ($faqItems as $i => $item): ?>
      <div class="faq-item" data-index="<?= $i ?>">
        <div class="faq-question">
          <span class="faq-q-text"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></span>
          <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
        </div>
        <div class="faq-answer">
          <div class="faq-answer-inner"><?= $item['a'] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="faq-empty" id="faqEmptyState">No results &mdash; try a different search term, or contact us below.</div>
  </div>

  <h2 class="cs-section-title">Still need help?</h2>
  <div class="cs-contact">
    <?php if ($formSubmitted): ?>
      <div class="cs-success">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        Message sent &mdash; we'll get back to you within 24 hours.
      </div>
    <?php endif; ?>

    <div class="cs-contact-grid">
      <form class="cs-contact-form" method="post" action="">
        <input type="hidden" name="support_submit" value="1">
        <div class="field-row">
          <div class="field"><label>Name</label><input type="text" name="name" required></div>
          <div class="field"><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Order number (optional)</label>
            <input type="text" name="order_number" placeholder="LNX-00000">
          </div>
          <div class="field">
            <label>Topic</label>
            <select name="topic">
              <option>Order status</option>
              <option>Returns & refunds</option>
              <option>Product question</option>
              <option>Billing issue</option>
              <option>Something else</option>
            </select>
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Message</label>
            <textarea name="message" rows="5" required placeholder="Tell us what's going on..."></textarea>
          </div>
        </div>
        <button type="submit" class="cs-submit-btn">Send message</button>
      </form>

      <div class="cs-contact-aside">
        <div class="hrs">
          <strong>Support hours</strong>
          Mon&ndash;Fri: 8am&ndash;8pm EST<br>
          Sat&ndash;Sun: 10am&ndash;5pm EST
        </div>
        <div>
          <strong>Before you write in</strong>
          Have your order number ready &mdash; it speeds things up a lot. You can find it under Account &rarr; Orders.
        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function(){
  // FAQ accordion
  document.querySelectorAll('.faq-question').forEach(function(q){
    q.addEventListener('click', function(){
      var item = q.closest('.faq-item');
      var wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function(el){ el.classList.remove('open'); });
      if (!wasOpen) item.classList.add('open');
    });
  });

  // FAQ live search
  var searchInput = document.getElementById('faqSearchInput');
  var faqItems = document.querySelectorAll('.faq-item');
  var emptyState = document.getElementById('faqEmptyState');

  searchInput.addEventListener('input', function(){
    var term = this.value.trim().toLowerCase();
    var visibleCount = 0;

    faqItems.forEach(function(item){
      var text = item.textContent.toLowerCase();
      var match = term === '' || text.indexOf(term) !== -1;
      item.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
  });

  // Live chat launcher — placeholder until a real chat widget is wired in.
  document.getElementById('openChatBtn').addEventListener('click', function(){
    // TODO: replace with real chat widget init, e.g. Intercom, Zendesk, custom socket.
    alert('Live chat widget will open here once connected to a support platform.');
  });
})();
</script>

</body>
</html>
