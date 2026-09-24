<?php
/* ============================================================================
   LUNAX — Hero Ad Carousel (components/hero-carousel.php)
   ----------------------------------------------------------------------------
   Real PHP component: 4 slides defined in the $heroSlides array below and
   rendered with a foreach loop — not hardcoded HTML. To add, remove, or
   reorder slides (or eventually pull them from a database/CMS), you only
   ever touch the array, never the markup below it.

   Usage from B1.php:
       <?php include __DIR__ . '/components/hero-carousel.php'; ?>

   Each slide needs: eyebrow, heading (HTML allowed for <br>/<em>), image,
   cta_text, cta_href, note (the small mono text next to the button).
   ========================================================================== */

$heroSlides = [
    [
        'eyebrow'   => '/// Signal Drop — Autumn Edition',
        'heading'   => 'Tech that runs<br>on <em>silence</em>,<br>up to 60% off',
        'image'     => 'samurai.jpg',
        'cta_text'  => 'Shop the drop',
        'cta_href'  => '/B1.php?promo=lunax99',
        'note'      => 'code <b>LUNAX99</b> at checkout',
    ],
    [
        'eyebrow'   => '/// New Arrival',
        'heading'   => 'Orbit 6.7"<br>now in <em>Midnight Blue</em>',
        'image'     => 'samurai.jpg',
        'cta_text'  => 'Shop phones',
        'cta_href'  => '/B1.php?cat=phones',
        'note'      => 'Free shipping over <b>$50</b>',
    ],
    [
        'eyebrow'   => '/// Student Offer',
        'heading'   => '10% off<br>with a <em>valid ID</em>',
        'image'     => 'samurai.jpg',
        'cta_text'  => 'Learn more',
        'cta_href'  => '/info-center.php#faq',
        'note'      => 'Verify once, save all year',
    ],
    [
        'eyebrow'   => '/// Gaming Week',
        'heading'   => 'Level up<br>your <em>setup</em>,<br>gear from $29',
        'image'     => 'samurai.jpg',
        'cta_text'  => 'Shop gaming',
        'cta_href'  => '/B1.php?cat=gaming',
        'note'      => 'New drops every Friday',
    ],
];

$heroAutoplayMs = 5000; // change speed here in one place
?>

<div class="hero-carousel" id="heroCarousel" data-autoplay-ms="<?= (int) $heroAutoplayMs ?>">
  <div class="hero-track" id="heroTrack">

    <?php foreach ($heroSlides as $i => $slide): ?>
      <div class="hero-banner" data-slide-index="<?= $i ?>" style="background-image:url('<?= htmlspecialchars($slide['image'], ENT_QUOTES, 'UTF-8') ?>');">
        <div class="hero-banner-overlay"></div>
        <div class="hero-banner-inner">
          <div class="eyebrow"><?= htmlspecialchars($slide['eyebrow'], ENT_QUOTES, 'UTF-8') ?></div>
          <h1><?= $slide['heading'] ?></h1>
          <svg class="hero-wave" viewBox="0 0 280 38">
            <path d="M0 19 L40 19 L52 4 L64 34 L76 19 L120 19 L132 30 L144 8 L156 19 L280 19"/>
          </svg>
          <div class="hero-cta-row">
            <a href="<?= htmlspecialchars($slide['cta_href'], ENT_QUOTES, 'UTF-8') ?>" class="btn-primary"><?= htmlspecialchars($slide['cta_text'], ENT_QUOTES, 'UTF-8') ?></a>
            <span class="discount-mono"><?= $slide['note'] ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>

  <button type="button" class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Previous slide">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
  </button>
  <button type="button" class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Next slide">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </button>

  <div class="hero-dots" id="heroDots">
    <?php foreach ($heroSlides as $i => $slide): ?>
      <button type="button" class="hero-dot<?= $i === 0 ? ' active' : '' ?>" data-index="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>

  <div class="hero-progress" id="heroProgress"><span id="heroProgressBar"></span></div>
</div>

<style>
  .hero-carousel{
    position:relative;
    overflow:hidden;
    border-radius:16px;
  }

  .hero-track{
    display:flex;
    transition: transform .55s cubic-bezier(.65,0,.35,1);
    will-change: transform;
  }

  .hero-track .hero-banner{
    flex:0 0 100%;
    min-width:100%;
    position:relative;
    background-size:cover;
    background-position:center;
    background-color:#140F0C; /* fallback if the image is missing/loading */
    display:flex;
    align-items:center;
    min-height:400px;
  }

  .hero-banner-overlay{
    position:absolute; inset:0;
    background:linear-gradient(90deg, rgba(20,15,12,0.92) 0%, rgba(20,15,12,0.55) 55%, rgba(20,15,12,0.15) 100%);
  }

  .hero-banner-inner{
    position:relative; z-index:2;
    padding:0 3rem;
    max-width:520px;
    color:#fff;
  }

  /* ---- Arrows ---- */
  .hero-arrow{
    position:absolute; top:50%; transform:translateY(-50%);
    width:40px; height:40px; border-radius:50%;
    background:rgba(20,15,12,0.45); border:1px solid rgba(255,255,255,0.25);
    color:#fff; display:flex; align-items:center; justify-content:center;
    cursor:pointer; z-index:3;
    opacity:0; transition: opacity .18s ease, background-color .15s ease;
  }
  .hero-carousel:hover .hero-arrow{ opacity:1; }
  .hero-arrow:hover{ background:rgba(20,15,12,0.75); }
  .hero-arrow-prev{ left:1rem; }
  .hero-arrow-next{ right:1rem; }

  /* ---- Dots ---- */
  .hero-dots{
    position:absolute; bottom:1.1rem; left:50%; transform:translateX(-50%);
    display:flex; gap:0.5rem; z-index:3;
  }
  .hero-dot{
    width:8px; height:8px; border-radius:50%;
    background:rgba(255,255,255,0.4); border:none; cursor:pointer; padding:0;
    transition: background-color .2s ease, transform .2s ease;
  }
  .hero-dot:hover{ background:rgba(255,255,255,0.7); }
  .hero-dot.active{ background:#F2C94C; transform:scale(1.25); }

  /* ---- Autoplay progress bar ---- */
  .hero-progress{
    position:absolute; top:0; left:0; right:0; height:3px;
    background:rgba(255,255,255,0.15); z-index:3;
  }
  .hero-progress span{
    display:block; height:100%; width:0%;
    background:#F2C94C;
  }
  .hero-progress span.animating{
    transition-property: width;
    transition-timing-function: linear;
    width:100%;
  }

  @media (max-width: 900px){
    .hero-track .hero-banner{ min-height:340px; }
    .hero-banner-inner{ padding:0 1.75rem; max-width:100%; }
    .hero-arrow{ opacity:1; }
  }
</style>

<script>
(function(){
  const carousel     = document.getElementById('heroCarousel');
  const track         = document.getElementById('heroTrack');
  const slides         = track.querySelectorAll('.hero-banner');
  const dots           = document.querySelectorAll('.hero-dot');
  const prevBtn         = document.getElementById('heroPrev');
  const nextBtn         = document.getElementById('heroNext');
  const progressBar   = document.getElementById('heroProgressBar');

  const SLIDE_COUNT     = slides.length;
  const AUTOPLAY_MS     = parseInt(carousel.dataset.autoplayMs, 10) || 5000;
  let current           = 0;
  let autoplayTimer     = null;

  function goTo(index){
    current = (index + SLIDE_COUNT) % SLIDE_COUNT;
    track.style.transform = 'translateX(-' + (current * 100) + '%)';
    dots.forEach(function(d, i){ d.classList.toggle('active', i === current); });
    restartProgress();
  }

  function next(){ goTo(current + 1); }
  function prev(){ goTo(current - 1); }

  function restartProgress(){
    progressBar.classList.remove('animating');
    progressBar.style.width = '0%';
    progressBar.style.transitionDuration = '0s';
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        progressBar.style.transitionDuration = AUTOPLAY_MS + 'ms';
        progressBar.classList.add('animating');
      });
    });
  }

  function startAutoplay(){
    stopAutoplay();
    autoplayTimer = setInterval(next, AUTOPLAY_MS);
    restartProgress();
  }
  function stopAutoplay(){
    clearInterval(autoplayTimer);
    progressBar.classList.remove('animating');
  }

  nextBtn.addEventListener('click', function(){ next(); startAutoplay(); });
  prevBtn.addEventListener('click', function(){ prev(); startAutoplay(); });

  dots.forEach(function(dot){
    dot.addEventListener('click', function(){
      goTo(parseInt(this.dataset.index, 10));
      startAutoplay();
    });
  });

  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);

  let touchStartX = 0;
  carousel.addEventListener('touchstart', function(e){
    touchStartX = e.touches[0].clientX;
  }, { passive: true });
  carousel.addEventListener('touchend', function(e){
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 40) {
      dx < 0 ? next() : prev();
      startAutoplay();
    }
  }, { passive: true });

  goTo(0);
  startAutoplay();
})();
</script>
