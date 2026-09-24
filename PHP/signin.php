<?php
/* ============================================================================
   LUNAX — Sign In / Register page (signin.php)
   ----------------------------------------------------------------------------
   Converted from signin.html. Purely client-side demo flow (no real backend
   auth yet) — all logic below still runs in JS, this file just serves it as
   .php so it's consistent with the rest of the site and ready for you to
   wire up real PHP session/auth handling later (e.g. checking credentials
   against a database, starting a PHP session, redirecting on success).
   ========================================================================== */
session_start();

$pageTitle = 'Sign In / Register — LUNAX';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/CSS/layout.css">
<link rel="stylesheet" href="/CSS/components.css">
<link rel="stylesheet" href="/CSS/panels.css">

<style>
  /* Scoped styles for the SHEIN-style sign-in/register card.
     Self-contained so it renders correctly even if a2.css doesn't define
     these classes; falls back to LUNAX's ink/cream palette where the
     variables are available. */
  .sh-auth-wrap{
    min-height: 78vh;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding: 56px 20px 80px;
    background: var(--bg, #f6f5f2);
    box-sizing:border-box;
  }
  .sh-auth-card{
    width:100%;
    max-width: 560px;
    background: var(--card, #fff);
    border: 1px solid var(--line, #e7e5df);
    border-radius: 4px;
    padding: 40px 48px 36px;
    box-sizing: border-box;
    transition: max-width .2s ease;
  }
  .sh-auth-card.sh-auth-card-wide{ max-width: 1040px; }
  .sh-step{ display:none; }
  .sh-step.active{ display:block; animation: shFade .25s ease; }
  @keyframes shFade{ from{ opacity:0; transform: translateY(6px);} to{ opacity:1; transform:none; } }

  .sh-title{
    font-family: "Space Grotesk", sans-serif;
    font-size: 1.35rem;
    font-weight: 600;
    text-align:center;
    margin: 0 0 6px;
    color: var(--ink, #1a1a1a);
  }
  .sh-protected{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size: .78rem;
    color: #3f8f5e;
    margin: 0 0 22px;
  }
  .sh-back{
    background:none; border:none; cursor:pointer;
    color: var(--ink, #1a1a1a);
    padding: 4px; margin: -4px 0 10px -4px;
    display:flex; align-items:center;
  }
  .sh-back:hover{ opacity:.6; }

  .sh-student{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background: var(--card-alt, #faf1ee);
    border-radius: 4px;
    padding: 14px 16px;
    margin-bottom: 24px;
    color: var(--ink, #1a1a1a);
  }
  .sh-student svg{ flex:none; }
  .sh-student-text strong{ display:block; font-size:.8rem; font-weight:700; }
  .sh-student-text span{ display:block; font-size:.72rem; color: var(--ink-dim, #71706b); margin-top:2px; }

  /* Country picker modal */
  .sh-modal-overlay{
    position: fixed;
    inset: 0;
    background: rgba(20,20,20,.55);
    display:none;
    align-items:center;
    justify-content:center;
    z-index: 200;
    padding: 20px;
  }
  .sh-modal-overlay.active{ display:flex; }
  .sh-modal{
    width:100%;
    max-width: 480px;
    background: var(--card, #fff);
    border-radius: 4px;
    padding: 32px 32px 24px;
    box-sizing:border-box;
  }
  .sh-modal-title{
    font-family: "Space Grotesk", sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
    text-align:center;
    margin: 0 0 22px;
    color: var(--ink, #1a1a1a);
  }
  .sh-country-list{
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--line, #d8d6cf);
    margin-bottom: 22px;
  }
  .sh-country-item{
    padding: 11px 16px;
    font-size: .92rem;
    color: var(--ink, #1a1a1a);
    cursor:pointer;
  }
  .sh-country-item:hover{ background: var(--card-alt, #f5f4f0); }
  .sh-country-item.selected{
    background: var(--card-alt, #efeee9);
    font-weight: 700;
  }
  .sh-modal-actions{
    display:flex;
    gap:14px;
  }
  .sh-modal-actions button{
    flex:1;
    padding: 13px;
    font-size: .88rem;
    font-weight:700;
    border-radius: 2px;
    cursor:pointer;
  }
  .sh-modal-cancel{
    background: var(--card, #fff);
    border: 1px solid var(--ink, #1a1a1a);
    color: var(--ink, #1a1a1a);
  }
  .sh-modal-cancel:hover{ background: var(--card-alt, #f5f4f0); }
  .sh-modal-confirm{
    background: var(--ink, #17171a);
    border: none;
    color: var(--card, #fff);
  }
  .sh-modal-confirm:hover{ opacity:.88; }

  /* Social account chooser popover */
  .sh-social-wrap{ position: relative; }
  .sh-account-chooser{
    display:none;
    position:absolute;
    top: calc(100% + 8px);
    left:0;
    right:0;
    background: var(--card, #fff);
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 4px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    padding: 14px;
    z-index: 50;
  }
  .sh-account-chooser.active{ display:block; animation: shFade .18s ease; }
  .sh-account-row{
    display:flex;
    align-items:center;
    gap:10px;
    padding: 8px 4px;
    cursor:pointer;
    border-radius: 3px;
  }
  .sh-account-row:hover{ background: var(--card-alt, #f5f4f0); }
  .sh-account-avatar{
    width: 34px; height: 34px;
    border-radius: 50%;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:700; font-size:.85rem;
    flex:none;
  }
  .sh-account-name{ font-size:.86rem; font-weight:600; color: var(--ink, #1a1a1a); }
  .sh-account-email{ font-size:.74rem; color: var(--ink-dim, #6b6a65); }
  .sh-account-other{
    margin-top: 6px;
    padding-top: 10px;
    border-top: 1px solid var(--line, #ece9e2);
    font-size: .8rem;
    color: var(--accent, #2f6fed);
    text-align:center;
    cursor:pointer;
  }

  .sh-label{
    display:block;
    font-size: .78rem;
    color: var(--ink-dim, #6b6a65);
    margin-bottom: 8px;
  }
  .sh-input{
    width:100%;
    box-sizing:border-box;
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 2px;
    padding: 13px 14px;
    font-size: .92rem;
    font-family: "Inter", sans-serif;
    color: var(--ink, #1a1a1a);
    margin-bottom: 18px;
    background: var(--card, #fff);
  }
  .sh-input:focus{
    outline:none;
    border-color: var(--ink, #1a1a1a);
  }

  .sh-btn-primary{
    width:100%;
    background: var(--ink, #17171a);
    color: var(--card, #fff);
    border:none;
    border-radius: 2px;
    padding: 14px;
    font-size: .92rem;
    font-weight:700;
    letter-spacing:.02em;
    cursor:pointer;
    margin-bottom: 18px;
    transition: opacity .15s ease;
  }
  .sh-btn-primary:hover{ opacity:.88; }
  .sh-btn-primary:active{ opacity:.75; }

  .sh-help-link{
    display:block;
    width:100%;
    background:none;
    border:none;
    text-align:center;
    font-size: .72rem;
    color: var(--accent, #2f6fed);
    cursor:pointer;
    margin-bottom: 22px;
    text-decoration: underline;
  }
  .sh-forgot{
    display:block;
    text-align:right;
    font-size:.8rem;
    color: var(--accent, #2f6fed);
    margin: -10px 0 20px;
    text-decoration:none;
  }
  .sh-forgot:hover{ text-decoration:underline; }

  .sh-or{
    display:flex;
    align-items:center;
    gap:14px;
    margin: 0 0 22px;
    color: var(--ink-dim, #9b9a94);
    font-size: .78rem;
  }
  .sh-or::before,
  .sh-or::after{
    content:"";
    flex:1;
    height:1px;
    background: var(--line, #e2e0d9);
  }

  .sh-social{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-bottom: 22px;
  }
  .sh-social-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    width:100%;
    background: var(--card, #fff);
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 2px;
    padding: 12px;
    font-size: .86rem;
    color: var(--ink, #1a1a1a);
    cursor:pointer;
    transition: background .15s ease;
  }
  .sh-social-btn:hover{ background: var(--card-alt, #f5f4f0); }

  .sh-country-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    margin: 0 auto 18px;
    background: var(--card, #fff);
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 999px;
    padding: 8px 18px;
    font-size: .8rem;
    color: var(--ink, #1a1a1a);
    cursor:pointer;
  }
  .sh-country-btn:hover{ border-color: var(--ink, #1a1a1a); }

  .sh-terms{
    text-align:center;
    font-size: .72rem;
    line-height:1.5;
    color: var(--ink-dim, #9b9a94);
    margin: 0 0 4px;
  }
  .sh-terms a{ color: var(--ink, #1a1a1a); }

  .sh-switch{
    text-align:center;
    font-size: .82rem;
    color: var(--ink-dim, #6b6a65);
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid var(--line, #ece9e2);
  }
  .sh-switch-link{
    background:none; border:none; cursor:pointer;
    font-weight:700;
    color: var(--ink, #1a1a1a);
    text-decoration: underline;
    padding:0; font-size: inherit;
  }

  .sh-identifier-chip{
    text-align:center;
    font-size: .85rem;
    color: var(--ink-dim, #6b6a65);
    background: var(--card-alt, #f5f4f0);
    border-radius: 2px;
    padding: 10px;
    margin: 0 0 22px;
    word-break: break-all;
  }

  .sh-checkbox{
    display:flex;
    align-items:flex-start;
    gap:8px;
    font-size: .78rem;
    color: var(--ink-dim, #6b6a65);
    margin-bottom: 20px;
    cursor:pointer;
  }
  .sh-checkbox input{ margin-top:3px; }
  .sh-checkbox a{ color: var(--ink, #1a1a1a); }

  /* ---------- Generic small modal (access / reset / verify) ---------- */
  .sh-modal.sh-modal-sm{ max-width: 620px; position: relative; padding-top: 40px; }
  .sh-modal.sh-modal-lg{ max-width: 900px; position: relative; padding: 36px 40px 32px; }
  .sh-modal-close{
    position:absolute;
    top: 14px; right: 14px;
    width: 28px; height:28px;
    display:flex; align-items:center; justify-content:center;
    background:none; border:none; cursor:pointer;
    color: var(--ink-dim, #6b6a65);
    border-radius: 50%;
  }
  .sh-modal-close:hover{ background: var(--card-alt, #f5f4f0); color: var(--ink, #1a1a1a); }
  .sh-modal-goback{
    position:absolute;
    top: 16px; left: 16px;
    display:flex; align-items:center; gap:4px;
    background:none; border:none; cursor:pointer;
    font-size:.78rem; font-weight:700;
    color: var(--ink, #1a1a1a);
    padding:4px;
  }
  .sh-modal-goback:hover{ opacity:.6; }
  .sh-modal-desc{
    font-size:.84rem;
    color: var(--ink-dim, #6b6a65);
    line-height:1.5;
    margin: 0 0 20px;
    text-align:left;
  }

  .sh-access-list{ display:flex; flex-direction:column; gap:14px; margin-bottom: 4px; }
  .sh-access-option{
    display:flex;
    align-items:center;
    gap:14px;
    background: var(--card-alt, #f7f6f2);
    border-radius: 4px;
    padding: 16px 18px;
    text-align:left;
  }
  .sh-access-option-icon{
    flex:none;
    color: var(--ink, #1a1a1a);
  }
  .sh-access-option-text{ flex:1; min-width:0; }
  .sh-access-option-title{
    display:flex; align-items:center; gap:7px;
    font-weight:700; font-size:.9rem;
    color: var(--ink, #1a1a1a);
    margin-bottom:3px;
  }
  .sh-access-option-desc{ font-size:.76rem; color: var(--ink-dim, #6b6a65); line-height:1.4; }
  .sh-access-option-btn{
    flex:none;
    background: var(--ink, #17171a);
    color: var(--card, #fff);
    border:none;
    border-radius: 2px;
    padding: 11px 16px;
    font-size:.78rem;
    font-weight:700;
    cursor:pointer;
    white-space:nowrap;
  }
  .sh-access-option-btn:hover{ opacity:.88; }

  .sh-field-row{ display:flex; gap:10px; align-items:flex-start; }
  .sh-field-row .sh-input{ flex:1; margin-bottom:0; }
  .sh-field-row .sh-send-btn{
    flex:none;
    background: var(--ink, #17171a);
    color: var(--card, #fff);
    border:none;
    border-radius: 2px;
    padding: 0 18px;
    height: 46px;
    font-size:.78rem;
    font-weight:700;
    cursor:pointer;
    white-space:nowrap;
  }
  .sh-field-row .sh-send-btn:hover{ opacity:.88; }
  .sh-field-row .sh-send-btn:disabled{ opacity:.5; cursor:default; }
  .sh-field-group{ margin-bottom: 18px; }

  .sh-inline-msg{
    font-size:.78rem;
    margin: -10px 0 18px;
    display:none;
  }
  .sh-inline-msg.active{ display:block; }
  .sh-inline-msg.success{ color:#2f8a4c; }
  .sh-inline-msg.error{ color:#c0392b; }

  .sh-modal-btn-row{ display:flex; gap:12px; margin-top: 4px; }
  .sh-modal-btn-row button{
    flex:1;
    padding: 13px;
    font-size: .86rem;
    font-weight:700;
    border-radius: 2px;
    cursor:pointer;
  }
  .sh-modal-btn-outline{
    background: var(--card, #fff);
    border: 1px solid var(--ink, #1a1a1a);
    color: var(--ink, #1a1a1a);
  }
  .sh-modal-btn-outline:hover{ background: var(--card-alt, #f5f4f0); }
  .sh-modal-btn-dark{
    background: var(--ink, #17171a);
    border:none;
    color: var(--card, #fff);
  }
  .sh-modal-btn-dark:hover{ opacity:.88; }

  .sh-modal-link{
    display:block;
    text-align:center;
    font-size:.8rem;
    color: var(--accent, #2f6fed);
    margin-top: 18px;
    background:none; border:none; cursor:pointer;
    text-decoration:underline;
  }

  .sh-phone-row{ display:flex; gap:10px; }
  .sh-phone-code{
    flex:none;
    width: 88px;
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 2px;
    padding: 13px 10px;
    font-size:.88rem;
    font-family:"Inter",sans-serif;
    background: var(--card, #fff);
    color: var(--ink, #1a1a1a);
  }
  .sh-phone-row .sh-input{ flex:1; margin-bottom:0; }

  /* ---------- Verify-identity step (full page, image5 style) ---------- */
  .sh-verify-intro{
    font-size:.84rem;
    color: var(--ink-dim, #6b6a65);
    margin: 0 0 22px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--line, #ece9e2);
  }
  .sh-verify-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
  .sh-verify-option{
    display:flex;
    align-items:center;
    gap:12px;
    border: 1px solid var(--line, #d8d6cf);
    border-radius: 4px;
    padding: 16px;
    background: var(--card, #fff);
    cursor:pointer;
    text-align:left;
    font-family:"Inter",sans-serif;
  }
  .sh-verify-option:hover{ border-color: var(--ink, #1a1a1a); background: var(--card-alt, #f9f8f5); }
  .sh-verify-option-icon{ flex:none; color: var(--ink, #1a1a1a); }
  .sh-verify-option-label{
    flex:1;
    font-size:.86rem;
    font-weight:600;
    color: var(--ink, #1a1a1a);
  }
  .sh-verify-option-chev{ flex:none; color: var(--ink-dim, #9b9a94); }
  @media (max-width: 560px){
    .sh-verify-grid{ grid-template-columns: 1fr; }
  }

  /* ---------- Success / verified state ---------- */
  .sh-success-icon{
    width:56px; height:56px;
    border-radius:50%;
    background: #eaf6ee;
    color:#2f8a4c;
    display:flex; align-items:center; justify-content:center;
    margin: 0 auto 18px;
  }
  .sh-center-text{ text-align:center; }

  /* ---------- OAuth mock browser overlays ---------- */
  .sh-oauth-overlay{
    position: fixed;
    inset: 0;
    background: rgba(15,15,18,.6);
    display:none;
    align-items:flex-start;
    justify-content:flex-end;
    z-index: 300;
    padding: 40px 40px 0 0;
    overflow-y:auto;
  }
  .sh-oauth-overlay.active{ display:flex; }
  .sh-browser-window{
    width: 100%;
    max-width: 460px;
    background: #fff;
    border-radius: 8px;
    overflow:hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
    animation: shFade .2s ease;
    font-family:"Inter",sans-serif;
  }
  .sh-browser-titlebar{
    display:flex;
    align-items:center;
    gap:8px;
    padding: 10px 14px;
    background:#202124;
    color:#e8eaed;
    font-size:.74rem;
  }
  .sh-browser-titlebar.light{ background:#f1f3f4; color:#3c4043; }
  .sh-browser-title-icon{ flex:none; display:flex; }
  .sh-browser-title-text{ flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .sh-browser-dots{ display:flex; gap:6px; flex:none; }
  .sh-browser-dots span{ width:9px; height:9px; border-radius:50%; background:#5f6368; display:block; }
  .sh-browser-titlebar.light .sh-browser-dots span{ background:#c4c7c5; }
  .sh-browser-addressbar{
    display:flex; align-items:center; gap:8px;
    padding: 9px 14px;
    background:#fff;
    border-bottom:1px solid #e0e0e0;
    font-size:.72rem;
    color:#5f6368;
  }
  .sh-browser-addressbar svg{ flex:none; }
  .sh-browser-addressbar span{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .sh-browser-body{ padding: 28px 26px 26px; }

  /* Facebook consent look */
  .sh-fb-top{ display:flex; align-items:center; justify-content:space-between; margin-bottom: 20px; }
  .sh-fb-user{ display:flex; align-items:center; gap:8px; font-size:.82rem; color:#1c1e21; font-weight:600; }
  .sh-fb-user-avatar{ width:26px; height:26px; border-radius:50%; background:#8a8d91; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.7rem; font-weight:700; }
  .sh-fb-badges{ display:flex; align-items:center; gap:10px; margin-bottom:18px; }
  .sh-fb-app-icon{
    width:34px; height:34px; border-radius:8px;
    background: var(--ink, #17171a);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:700; font-family:"Space Grotesk",sans-serif;
  }
  .sh-fb-request-title{ font-size:1.05rem; font-weight:700; color:#1c1e21; margin: 0 0 14px; font-family:"Space Grotesk",sans-serif; }
  .sh-fb-consent-list{ font-size:.86rem; color:#1c1e21; line-height:1.9; margin: 0 0 6px; padding-left: 18px; }
  .sh-fb-edit{ font-size:.82rem; color:#1877f2; text-decoration:none; display:inline-block; margin-bottom:22px; }
  .sh-fb-edit:hover{ text-decoration:underline; }
  .sh-fb-actions{ display:flex; flex-direction:column; gap:10px; margin-bottom: 16px; }
  .sh-fb-btn-primary{
    background:#1877f2; color:#fff; border:none; border-radius:6px;
    padding:12px; font-size:.9rem; font-weight:700; cursor:pointer;
  }
  .sh-fb-btn-primary:hover{ background:#166fe5; }
  .sh-fb-btn-secondary{
    background:#e4e6eb; color:#050505; border:none; border-radius:6px;
    padding:12px; font-size:.9rem; font-weight:700; cursor:pointer;
  }
  .sh-fb-btn-secondary:hover{ background:#d8dadf; }
  .sh-fb-footnote{ font-size:.72rem; color:#8a8d91; line-height:1.6; }
  .sh-fb-footnote a{ color:#385898; text-decoration:none; }
  .sh-fb-footnote a:hover{ text-decoration:underline; }

  @media (max-width: 560px){
    .sh-oauth-overlay{ padding: 0; align-items:stretch; justify-content:stretch; }
    .sh-browser-window{ max-width:none; border-radius:0; height:100%; display:flex; flex-direction:column; }
  }

  @media (max-width: 480px){
    .sh-auth-wrap{ padding: 28px 12px 60px; }
    .sh-auth-card{ padding: 26px 18px 24px; border:none; }
  }
</style>
</head>
<body>

<div class="ticker-bar">
  <div class="ticker-track">
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>FREE SHIPPING ON ORDERS OVER $50</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>LUNAX MEMBERS EARN 2X POINTS ON AUDIO</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>24-MONTH WARRANTY ON ALL DEVICES</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>NEW SIGNAL DROP EVERY FRIDAY</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>FREE SHIPPING ON ORDERS OVER $50</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>LUNAX MEMBERS EARN 2X POINTS ON AUDIO</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>24-MONTH WARRANTY ON ALL DEVICES</span>
    <span><svg class="ticker-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.47 6.97 7.38.24-5.86 4.55 2.15 7.11-6.14-4.3-6.14 4.3 2.15-7.11-5.86-4.55 7.38-.24Z"/></svg>NEW SIGNAL DROP EVERY FRIDAY</span>
  </div>
</div>

<!-- Header -->
<header>
  <div class="nav-main">
    <a href="/PHP/home.php" class="logo">
      <span class="logo-text">LUNAX</span>
       <img class="logo-mark" src="image/l.png" alt="LUNAX logo mark">
    </a>
    <div class="search-wrap">
      <input type="text" placeholder="Search phones, laptops, audio, drones…">
      <button aria-label="Search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      </button>
    </div>
    <div class="nav-icons">
      <div class="account-trigger">
        <a href="signin.php" class="nav-icon" aria-label="Account">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>
        <a href="PHP/personal-centre.php" class="account-tooltip">personal center</a>
        <div class="account-panel">
          <a href="signin.php" class="account-panel-title">Sign In / Register</a>
          <div class="account-panel-divider"></div>
          <a href="PHP/personal-centre.php?active=all-orders" class="account-panel-link">My Orders</a>
          <a href="PHP/personal-centre.php?active=inbox" class="account-panel-link">My Message</a>
          <a href="PHP/wishlist.php" class="account-panel-link">My Wishlist</a>
          <a href="PHP/personal-centre.php?active=my-vouchers" class="account-panel-link">My Vouchers</a>
          <a href="PHP/personal-centre.php?active=my-points" class="account-panel-link">My Points</a>
          <a href="PHP/personal-centre.php?active=recently-viewed-list" class="account-panel-link">Recently Viewed</a>
        </div>
      </div>
      <div class="account-trigger">
        <a href="PHP/wishlist.php" class="nav-icon" aria-label="Wishlist">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 21s-7.5-4.65-9.5-8.7C1.2 9.77 2.76 5 7.08 5c2.1 0 3.47 1.2 4.92 3.1C13.45 6.2 14.82 5 16.92 5c4.32 0 5.88 4.77 4.58 7.3C19.5 16.35 12 21 12 21Z"/></svg>
        </a>
        <a href="PHP/wishlist.php" class="account-tooltip">wishlist</a>
      </div>
      <div class="account-trigger">
        <a href="PHP/cart.php" class="nav-icon nav-icon-count" aria-label="Cart">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 2H2"/><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
          <span class="icon-count">0</span>
        </a>
        <div class="cart-panel">
          <svg class="cart-panel-icon" viewBox="0 0 64 64" fill="none" stroke="var(--ink-dim)" stroke-width="1.6">
            <path d="M18 20h4l4 26h20l4-18H24"/>
            <circle cx="46" cy="19" r="1.4" fill="var(--ink-dim)" stroke="none"/>
            <circle cx="35" cy="10" r="1" fill="var(--ink-dim)" stroke="none"/>
            <path d="M40 12l3 3M43 12l-3 3" stroke-linecap="round"/>
            <line x1="14" y1="52" x2="52" y2="52" stroke-dasharray="2 3"/>
            <circle cx="24" cy="52" r="2.4"/>
            <circle cx="40" cy="52" r="2.4"/>
          </svg>
          <div class="cart-panel-title">Shopping cart is Empty</div>
          <div class="cart-panel-copy">Welcome back! If you had items in your shopping cart, we saved them for you. <a href="PHP/signin.php">SIGN IN</a> now to see them, or whenever you're ready to check out.</div>
        </div>
      </div>
      <div class="account-trigger">
        <a href="#" class="nav-icon" aria-label="Customer Service">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2z"/><path d="M20 13a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2z"/><path d="M18 17.5a4 4 0 0 1-4 3.5h-1.5"/><circle cx="11" cy="21" r="1.2"/></svg>
        </a>
        <div class="cs-panel">
          <div class="cs-panel-title">Customer Service</div>
          <div class="cs-panel-copy">What can we do for you?</div>
        </div>
      </div>
      <div class="account-trigger">
        <a href="#" class="nav-icon" aria-label="Currency / Language">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/></svg>
        </a>
        <div class="currency-panel">
          <div class="currency-panel-title">Currency &amp; Language</div>
          <div class="currency-panel-label">Currency</div>
          <div class="currency-select">
            <select aria-label="Select currency">
              <option>USD / $ — US Dollar</option>
              <option selected>GBP / £ — British Pound</option>
              <option>EUR / € — Euro</option>
              <option>JPY / ¥ — Japanese Yen</option>
              <option>CNY / ¥ — Chinese Yuan</option>
              <option>AUD / $ — Australian Dollar</option>
              <option>CAD / $ — Canadian Dollar</option>
              <option>CHF / Fr — Swiss Franc</option>
              <option>INR / ₹ — Indian Rupee</option>
              <option>AED / د.إ — UAE Dirham</option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </div>
          <div class="currency-panel-label">Language</div>
          <div class="language-select">
            <select aria-label="Select language">
              <option selected>English</option>
              <option>Español</option>
              <option>Français</option>
              <option>Deutsch</option>
              <option>中文</option>
              <option>日本語</option>
              <option>العربية</option>
              <option>Português</option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </div>
          <a href="#" class="currency-panel-link">International Site</a>
        </div>
      </div>
    </div>
  </div>
  <nav class="nav-cats">
    <div class="nav-cats-track">
      <div class="cat-trigger nav-trigger-word">
        <a href="#" class="cat-trigger-link nav-trigger-link">Shop →</a>
        <div class="cat-mega-menu">
        <div class="cat-mega-menu-inner">
          <div class="cat-mega-glow" aria-hidden="true"></div>
          <div class="cat-mega-glow cat-mega-glow-2" aria-hidden="true"></div>
          <ul class="cat-mega-list">
            <li class="active" data-target="phones">Phones & Tablets</li>
            <li data-target="laptops">Laptops & PCs</li>
            <li data-target="audio">Audio</li>
            <li data-target="wearables">Wearables</li>
            <li data-target="gaming">Gaming</li>
            <li data-target="smarthome">Smart Home</li>
            <li data-target="cameras">Cameras</li>
            <li data-target="drones">Drones</li>
            <li data-target="components">Components</li>
            <li data-target="accessories">Accessories</li>
          </ul>
          <div class="cat-mega-detail">
            <div class="cat-detail-panel active" data-panel="phones">
              <h4>Phones & Tablets</h4>
              <div class="cat-detail-links">
                <a href="#">Smartphones</a>
                <a href="#">Tablets</a>
                <a href="#">Phone Cases</a>
                <a href="#">Screen Protectors</a>
                <a href="#">Fast Chargers</a>
                <a href="#">5G Devices</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="laptops">
              <h4>Laptops & PCs</h4>
              <div class="cat-detail-links">
                <a href="#">Ultrabooks</a>
                <a href="#">Gaming Laptops</a>
                <a href="#">Desktop PCs</a>
                <a href="#">Monitors</a>
                <a href="#">Docking Stations</a>
                <a href="#">Laptop Bags</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="audio">
              <h4>Audio</h4>
              <div class="cat-detail-links">
                <a href="#">Wireless Earbuds</a>
                <a href="#">Headphones</a>
                <a href="#">Bluetooth Speakers</a>
                <a href="#">Soundbars</a>
                <a href="#">Turntables</a>
                <a href="#">Microphones</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="wearables">
              <h4>Wearables</h4>
              <div class="cat-detail-links">
                <a href="#">Smartwatches</a>
                <a href="#">Fitness Bands</a>
                <a href="#">Watch Straps</a>
                <a href="#">Smart Rings</a>
                <a href="#">Kids Watches</a>
                <a href="#">Health Trackers</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="gaming">
              <h4>Gaming</h4>
              <div class="cat-detail-links">
                <a href="#">Mechanical Keyboards</a>
                <a href="#">Gaming Mice</a>
                <a href="#">Headsets</a>
                <a href="#">Controllers</a>
                <a href="#">Gaming Chairs</a>
                <a href="#">Consoles</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="smarthome">
              <h4>Smart Home</h4>
              <div class="cat-detail-links">
                <a href="#">Smart Speakers</a>
                <a href="#">Smart Plugs</a>
                <a href="#">Smart Bulbs</a>
                <a href="#">Video Doorbells</a>
                <a href="#">Robot Vacuums</a>
                <a href="#">Security Cameras</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="cameras">
              <h4>Cameras</h4>
              <div class="cat-detail-links">
                <a href="#">Action Cameras</a>
                <a href="#">Webcams</a>
                <a href="#">Dash Cams</a>
                <a href="#">Tripods</a>
                <a href="#">Memory Cards</a>
                <a href="#">Camera Bags</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="drones">
              <h4>Drones</h4>
              <div class="cat-detail-links">
                <a href="#">Camera Drones</a>
                <a href="#">Mini Drones</a>
                <a href="#">FPV Drones</a>
                <a href="#">Batteries</a>
                <a href="#">Propellers</a>
                <a href="#">Carry Cases</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="components">
              <h4>Components</h4>
              <div class="cat-detail-links">
                <a href="#">SSDs & Storage</a>
                <a href="#">Graphics Cards</a>
                <a href="#">RAM</a>
                <a href="#">Motherboards</a>
                <a href="#">Power Supplies</a>
                <a href="#">Cooling</a>
              </div>
            </div>
            <div class="cat-detail-panel" data-panel="accessories">
              <h4>Accessories</h4>
              <div class="cat-detail-links">
                <a href="#">Chargers & Cables</a>
                <a href="#">Cases & Sleeves</a>
                <a href="#">Power Banks</a>
                <a href="#">Cable Organizers</a>
                <a href="#">Screen Protectors</a>
                <a href="#">Mounts & Stands</a>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
      <div class="nav-trigger-word">
        <a href="#" class="nav-trigger-link quick-filter-link" data-filter="newarrivals">News</a>
      </div>
      <div class="nav-trigger-word">
        <a href="#" class="nav-trigger-link quick-filter-link" data-filter="bestsellers">Best Sellers</a>
      </div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="phones">Phones &amp; Tablets</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="laptops">Laptops &amp; PCs</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="audio">Audio</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="drones">Drones</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="gaming">Gaming</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="smarthome">Smart Home</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="cameras">Cameras</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="wearables">Wearables</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="tablets">Tablets</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="laptops2">Laptops</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="components">Components</a></div>
      <div class="nav-trigger-word"><a href="#" class="nav-trigger-link quick-filter-link" data-filter="accessories">Accessories</a></div>
    </div>
  </nav>
</header>

<!-- Sign in / register -->
<main class="sh-auth-wrap">
  <div class="sh-auth-card" id="shAuthCard">

    <!-- STEP 1: identifier -->
    <section class="sh-step active" data-step="start">
      <h1 class="sh-title">Sign In/Register</h1>
      <p class="sh-protected">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        Your data is protected.
      </p>

      <div class="sh-student">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 8l10-4 10 4-10 4-10-4z"/><path d="M6 10.5V16c0 1.4 2.7 3 6 3s6-1.6 6-3v-5.5"/><path d="M22 8v6"/></svg>
        <div class="sh-student-text">
          <strong>Student Discount</strong>
          <span>10% OFF for verified students</span>
        </div>
      </div>

      <label class="sh-label" for="shIdentifier">Mobile number or email address:</label>
      <input class="sh-input" id="shIdentifier" type="text" autocomplete="username" inputmode="email">

      <button type="button" class="sh-btn-primary" id="shContinueBtn">CONTINUE</button>

      <button type="button" class="sh-help-link" id="shHelpLink">Can't access your account?</button>

      <div class="sh-or"><span>Or</span></div>

      <div class="sh-social">
        <button type="button" class="sh-social-btn" id="shGoogleBtn">
          <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.4H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7v3h3.9c2.3-2.1 3.5-5.2 3.5-8.8z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1C3.3 21.4 7.3 24 12 24z"/><path fill="#FBBC05" d="M5.4 14.4c-.2-.7-.4-1.5-.4-2.4s.1-1.6.4-2.4V6.5H1.4C.5 8.2 0 10.1 0 12s.5 3.8 1.4 5.5l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.7 0 3.3.6 4.5 1.8l3.4-3.4C17.9 1.2 15.2 0 12 0 7.3 0 3.3 2.6 1.4 6.5l4 3.1C6.3 6.9 8.9 4.8 12 4.8z"/></svg>
          Continue with Google
        </button>
        <button type="button" class="sh-social-btn" id="shFacebookBtn">
          <svg viewBox="0 0 24 24" width="18" height="18"><circle cx="12" cy="12" r="12" fill="#1877F2"/><path fill="#fff" d="M15.9 15.5l.5-3.5h-3.2v-2.3c0-1 .3-1.7 1.7-1.7h1.7V4.9c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v2.5H7v3.5h2.6V22h3.6v-6.5h2.7z"/></svg>
          Continue with Facebook
        </button>
      </div>

      <button type="button" class="sh-country-btn" id="shCountryBtn">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.4"/></svg>
        <span id="shCountryLabel">Detecting location…</span>
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
      </button>

      <p class="sh-terms">By continuing, you agree to our <a href="#">Privacy &amp; Cookie Policy</a> and <a href="#">Terms &amp; Conditions</a>.</p>

      <div class="sh-switch">
        New to LUNAX? <button type="button" class="sh-switch-link" data-goto="register">Create an account</button>
      </div>
    </section>

    <!-- STEP 2: password (returning user) -->
    <section class="sh-step" data-step="password">
      <button type="button" class="sh-back" data-goto="start">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <h1 class="sh-title">Enter your password</h1>
      <p class="sh-identifier-chip" id="shIdentifierChip"></p>

      <label class="sh-label" for="shPassword">Password</label>
      <input class="sh-input" id="shPassword" type="password" autocomplete="current-password">
      <a href="#" class="sh-forgot">Forgot password?</a>

      <button type="button" class="sh-btn-primary">Sign In</button>
    </section>

    <!-- STEP 3: register -->
    <section class="sh-step" data-step="register">
      <button type="button" class="sh-back" data-goto="start">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <h1 class="sh-title">Create your account</h1>
      <p class="sh-protected">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        Your data is protected.
      </p>

      <label class="sh-label" for="shRegEmail">Email address</label>
      <input class="sh-input" id="shRegEmail" type="email" autocomplete="email">

      <label class="sh-label" for="shRegPassword">Password</label>
      <input class="sh-input" id="shRegPassword" type="password" autocomplete="new-password" placeholder="At least 8 characters">

      <label class="sh-label" for="shRegConfirm">Confirm password</label>
      <input class="sh-input" id="shRegConfirm" type="password" autocomplete="new-password">

      <label class="sh-checkbox">
        <input type="checkbox">
        <span>I agree to the <a href="#">Terms &amp; Conditions</a> and <a href="#">Privacy Policy</a></span>
      </label>

      <button type="button" class="sh-btn-primary">Create Account</button>

      <div class="sh-switch">
        Already have an account? <button type="button" class="sh-switch-link" data-goto="start">Sign in</button>
      </div>
    </section>

    <!-- STEP 4: verify identity (recover account) -->
    <section class="sh-step" data-step="verify-identity">
      <button type="button" class="sh-back" data-goto="start">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <h1 class="sh-title">VERIFY YOUR IDENTITY</h1>
      <p class="sh-verify-intro">Please select a way to verify your identity to recover your account.</p>

      <div class="sh-verify-grid">
        <button type="button" class="sh-verify-option" data-verify="email">
          <span class="sh-verify-option-icon"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 6.5l9 6 9-6"/></svg></span>
          <span class="sh-verify-option-label">Verify with <b>Email Address</b></span>
          <span class="sh-verify-option-chev">›</span>
        </button>
        <button type="button" class="sh-verify-option" data-verify="phone">
          <span class="sh-verify-option-icon"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M10 18h2"/></svg></span>
          <span class="sh-verify-option-label">Verify with <b>Phone Number</b></span>
          <span class="sh-verify-option-chev">›</span>
        </button>
        <button type="button" class="sh-verify-option" data-verify="payment">
          <span class="sh-verify-option-icon"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
          <span class="sh-verify-option-label">Verify with <b>Payment Information</b></span>
          <span class="sh-verify-option-chev">›</span>
        </button>
        <button type="button" class="sh-verify-option" data-verify="order">
          <span class="sh-verify-option-icon"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
          <span class="sh-verify-option-label">Verify with <b>Order / Tracking Number</b></span>
          <span class="sh-verify-option-chev">›</span>
        </button>
      </div>
    </section>

    <!-- STEP 5: set new password (after identity verified) -->
    <section class="sh-step" data-step="new-password">
      <button type="button" class="sh-back" data-goto="start">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <div class="sh-success-icon">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
      </div>
      <h1 class="sh-title">Identity Verified</h1>
      <p class="sh-protected" style="margin-bottom:26px;">Set a new password for your account.</p>

      <label class="sh-label" for="shNewPassword">New password</label>
      <input class="sh-input" id="shNewPassword" type="password" autocomplete="new-password" placeholder="At least 8 characters">

      <label class="sh-label" for="shNewPasswordConfirm">Confirm new password</label>
      <input class="sh-input" id="shNewPasswordConfirm" type="password" autocomplete="new-password">

      <div class="sh-inline-msg error" id="shNewPasswordMsg"></div>

      <button type="button" class="sh-btn-primary" id="shSavePasswordBtn">Save New Password</button>
    </section>

    <!-- STEP 6: signed in via social account -->
    <section class="sh-step" data-step="oauth-success">
      <div class="sh-success-icon">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
      </div>
      <h1 class="sh-title">You're signed in</h1>
      <p class="sh-protected center" id="shOauthSuccessMsg" style="margin-bottom:26px;">Welcome back!</p>
      <a href="PHP/home.php" class="sh-btn-primary" style="display:block; text-align:center; text-decoration:none; box-sizing:border-box;">Continue Shopping</a>
    </section>

  </div>

  <!-- Country picker modal -->
  <div class="sh-modal-overlay" id="shCountryModal">
    <div class="sh-modal">
      <h2 class="sh-modal-title">Choose your location</h2>
      <div class="sh-country-list" id="shCountryList"></div>
      <div class="sh-modal-actions">
        <button type="button" class="sh-modal-cancel" id="shCountryCancel">Cancel</button>
        <button type="button" class="sh-modal-confirm" id="shCountryConfirm">Confirm</button>
      </div>
    </div>
  </div>

  <!-- Can't access your account? -->
  <div class="sh-modal-overlay" id="shAccessModal">
    <div class="sh-modal sh-modal-lg">
      <button type="button" class="sh-modal-close" data-close="shAccessModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">Can't access your account?</h2>
      <div class="sh-access-list">
        <div class="sh-access-option">
          <span class="sh-access-option-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg></span>
          <div class="sh-access-option-text">
            <div class="sh-access-option-title">Forgot Password</div>
            <div class="sh-access-option-desc">If you registered an account with your email address, but forgot your password, you can reset your password.</div>
          </div>
          <button type="button" class="sh-access-option-btn" id="shGoToResetBtn">Reset Password</button>
        </div>
        <div class="sh-access-option">
          <span class="sh-access-option-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <div class="sh-access-option-text">
            <div class="sh-access-option-title">Recover Account</div>
            <div class="sh-access-option-desc">If you forgot your account, you can try to recover your account by email, mobile phone number or order ID.</div>
          </div>
          <button type="button" class="sh-access-option-btn" id="shGoToRecoverBtn">Recover Account</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reset password -->
  <div class="sh-modal-overlay" id="shResetModal">
    <div class="sh-modal sh-modal-sm">
      <button type="button" class="sh-modal-goback" id="shResetGoBack">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 19l-7-7 7-7"/></svg> GO BACK
      </button>
      <button type="button" class="sh-modal-close" data-close="shResetModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">RESET PASSWORD</h2>
      <p class="sh-modal-desc" id="shResetDesc">Confirm your email address and input the correct code to change the password.</p>

      <div class="sh-field-group" id="shResetEmailGroup">
        <label class="sh-label" for="shResetEmail">Email Address</label>
        <input class="sh-input" id="shResetEmail" type="email" autocomplete="email" style="margin-bottom:0;">
      </div>
      <div class="sh-field-group" id="shResetPhoneGroup" style="display:none;">
        <label class="sh-label" for="shResetPhone">Phone Number</label>
        <div class="sh-phone-row">
          <select class="sh-phone-code" id="shResetPhoneCode">
            <option value="+44">GB +44</option>
            <option value="+1">US +1</option>
            <option value="+964">IQ +964</option>
            <option value="+91">IN +91</option>
          </select>
          <input class="sh-input" id="shResetPhone" type="tel" style="margin-bottom:0;">
        </div>
      </div>

      <div class="sh-field-group">
        <label class="sh-label" for="shResetCode">Verification Code:</label>
        <div class="sh-field-row">
          <input class="sh-input" id="shResetCode" type="text" inputmode="numeric" maxlength="6">
          <button type="button" class="sh-send-btn" id="shResetSendBtn">SEND</button>
        </div>
      </div>

      <div class="sh-inline-msg" id="shResetMsg"></div>

      <div class="sh-modal-btn-row">
        <button type="button" class="sh-modal-btn-outline" id="shResetCancelBtn">CANCEL</button>
        <button type="button" class="sh-modal-btn-dark" id="shResetContinueBtn">CONTINUE</button>
      </div>

      <button type="button" class="sh-modal-link" id="shResetToggleMethod">Reset password via phone number</button>
    </div>
  </div>

  <!-- Verify with Email Address -->
  <div class="sh-modal-overlay" id="shVerifyEmailModal">
    <div class="sh-modal sh-modal-sm">
      <button type="button" class="sh-modal-close" data-close="shVerifyEmailModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">Verify with Email Address</h2>
      <p class="sh-modal-desc sh-center-text">Enter your email address to receive a verification link</p>

      <label class="sh-label" for="shVerifyEmailInput">Email Address<span style="color:#c0392b;">*</span></label>
      <input class="sh-input" id="shVerifyEmailInput" type="email" placeholder="Email Address">

      <div class="sh-inline-msg" id="shVerifyEmailMsg"></div>

      <button type="button" class="sh-btn-primary" id="shVerifyEmailBtn" style="margin-bottom:0;">Verify</button>
    </div>
  </div>

  <!-- Verify with Phone Number -->
  <div class="sh-modal-overlay" id="shVerifyPhoneModal">
    <div class="sh-modal sh-modal-sm">
      <button type="button" class="sh-modal-close" data-close="shVerifyPhoneModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">Verify with Phone Number</h2>
      <p class="sh-modal-desc sh-center-text">Enter the phone number associated with your account</p>

      <label class="sh-label" for="shVerifyPhoneInput">Phone Number<span style="color:#c0392b;">*</span></label>
      <div class="sh-phone-row">
        <select class="sh-phone-code" id="shVerifyPhoneCode">
          <option value="GB +44">GB +44</option>
          <option value="US +1">US +1</option>
          <option value="IQ +964">IQ +964</option>
          <option value="IN +91">IN +91</option>
        </select>
        <input class="sh-input" id="shVerifyPhoneInput" type="tel" placeholder="Phone Number" style="margin-bottom:0;">
      </div>

      <div class="sh-inline-msg" id="shVerifyPhoneMsg" style="margin-top:18px;"></div>

      <button type="button" class="sh-btn-primary" id="shVerifyPhoneBtn" style="margin-top:18px; margin-bottom:0;">Verify</button>
    </div>
  </div>

  <!-- Verify with Payment Information -->
  <div class="sh-modal-overlay" id="shVerifyPaymentModal">
    <div class="sh-modal sh-modal-sm">
      <button type="button" class="sh-modal-close" data-close="shVerifyPaymentModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">Verify with Payment Information</h2>
      <p class="sh-modal-desc sh-center-text">Enter details from a payment method saved on your account</p>

      <label class="sh-label" for="shVerifyCardLast4">Last 4 digits of card</label>
      <input class="sh-input" id="shVerifyCardLast4" type="text" inputmode="numeric" maxlength="4" placeholder="e.g. 4417">

      <label class="sh-label" for="shVerifyCardExpiry">Expiry date (MM/YY)</label>
      <input class="sh-input" id="shVerifyCardExpiry" type="text" placeholder="MM/YY" maxlength="5">

      <div class="sh-inline-msg" id="shVerifyPaymentMsg"></div>

      <button type="button" class="sh-btn-primary" id="shVerifyPaymentBtn" style="margin-bottom:0;">Verify</button>
    </div>
  </div>

  <!-- Verify with Order / Tracking Number -->
  <div class="sh-modal-overlay" id="shVerifyOrderModal">
    <div class="sh-modal sh-modal-sm">
      <button type="button" class="sh-modal-close" data-close="shVerifyOrderModal">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <h2 class="sh-modal-title">Verify with Order / Tracking Number</h2>
      <p class="sh-modal-desc sh-center-text">Enter a recent order number or tracking number from your account</p>

      <label class="sh-label" for="shVerifyOrderInput">Order / Tracking Number</label>
      <input class="sh-input" id="shVerifyOrderInput" type="text" placeholder="e.g. LX-2026-004417">

      <div class="sh-inline-msg" id="shVerifyOrderMsg"></div>

      <button type="button" class="sh-btn-primary" id="shVerifyOrderBtn" style="margin-bottom:0;">Verify</button>
    </div>
  </div>

  <!-- Facebook sign-in mock browser popup -->
  <div class="sh-oauth-overlay" id="shFacebookOverlay">
    <div class="sh-browser-window">
      <div class="sh-browser-titlebar light">
        <span class="sh-browser-title-icon"><svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="12" fill="#1877F2"/><path fill="#fff" d="M15.9 15.5l.5-3.5h-3.2v-2.3c0-1 .3-1.7 1.7-1.7h1.7V4.9c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v2.5H7v3.5h2.6V22h3.6v-6.5h2.7z"/></svg></span>
        <span class="sh-browser-title-text">Facebook - Google Chrome</span>
        <span class="sh-browser-dots"><span></span><span></span><span></span></span>
      </div>
      <div class="sh-browser-addressbar">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5f6368" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <span>facebook.com/privacy/consent/?flow=gdp&params%5Bapp_id%5D=lunax</span>
      </div>
      <div class="sh-browser-body">
        <div class="sh-fb-top">
          <svg viewBox="0 0 24 24" width="30" height="30"><circle cx="12" cy="12" r="12" fill="#1877F2"/><path fill="#fff" d="M15.9 15.5l.5-3.5h-3.2v-2.3c0-1 .3-1.7 1.7-1.7h1.7V4.9c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v2.5H7v3.5h2.6V22h3.6v-6.5h2.7z"/></svg>
          <div class="sh-fb-user"><span class="sh-fb-user-avatar">A</span> Alex Morgan</div>
        </div>
        <div class="sh-fb-badges">
          <span class="sh-fb-app-icon">X</span>
        </div>
        <div class="sh-fb-request-title">LUNAX is requesting access to:</div>
        <ul class="sh-fb-consent-list">
          <li>Name and profile picture</li>
          <li>Email address</li>
        </ul>
        <a href="#" class="sh-fb-edit" id="shFbEditAccess">Edit access</a>
        <div class="sh-fb-actions">
          <button type="button" class="sh-fb-btn-primary" id="shFbContinueBtn">Continue as Alex Morgan</button>
          <button type="button" class="sh-fb-btn-secondary" id="shFbCancelBtn">Cancel</button>
        </div>
        <p class="sh-fb-footnote">By continuing, LUNAX will receive ongoing access to the information that you share and Facebook will record when LUNAX accesses it. <a href="#">Learn more</a> about this sharing and the settings that you have.<br><br>LUNAX's <a href="#">Privacy Policy</a></p>
      </div>
    </div>
  </div>
</main>

<!-- Footer -->
<footer>
  <div class="footer-grid">
    <div class="footer-col">
      <h3>Company</h3>
      <a href="#">About Lunax</a>
      <a href="#">Careers</a>
      <a href="#">Lunax Journal</a>
    </div>
    <div class="footer-col">
      <h3>Help & Support</h3>
      <a href="#">Shipping Info</a>
      <a href="#">Returns</a>
      <a href="#">Warranty</a>
      <a href="#">How to Track</a>
      <a href="#">FAQ</a>
    </div>
    <div class="footer-col">
      <h3>Customer Care</h3>
      <a href="#">Contact Us</a>
      <a href="#">Payment & Tax</a>
      <a href="#">Gift Cards</a>
    </div>
    <div class="footer-col">
      <h3>Sign up for Lunax News</h3>
      <div class="newsletter-form">
        <input type="email" placeholder="Your email address">
        <button>Subscribe</button>
      </div>
      <span style="font-size:0.7rem; color:rgba(237,233,224,0.5);">Weekly drops, no spam. Unsubscribe anytime.</span>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="copy">© 2026 LUNAX. All rights reserved.</span>
    <div class="footer-links">
      <a href="#">Privacy Center</a>
      <a href="#">Terms & Conditions</a>
      <a href="#">Do Not Sell My Info</a>
      <a href="#">Accessibility</a>
    </div>
    <div class="pay-icons">
      <span>MASTERCARD</span><span>GOOGLE PAY</span>
    </div>
  </div>
</footer>

<script>
  const shSteps = document.querySelectorAll('.sh-step');

  const shAuthCard = document.getElementById('shAuthCard');
  const shWideSteps = ['verify-identity'];

  function shGoTo(step){
    shSteps.forEach(s => s.classList.toggle('active', s.dataset.step === step));
    shAuthCard.classList.toggle('sh-auth-card-wide', shWideSteps.includes(step));
  }

  document.querySelectorAll('[data-goto]').forEach(btn => {
    btn.addEventListener('click', () => shGoTo(btn.dataset.goto));
  });

  const shIdentifier = document.getElementById('shIdentifier');
  const shContinueBtn = document.getElementById('shContinueBtn');
  const shIdentifierChip = document.getElementById('shIdentifierChip');

  shContinueBtn.addEventListener('click', () => {
    const value = shIdentifier.value.trim();
    if(!value){
      shIdentifier.focus();
      return;
    }
    shIdentifierChip.textContent = value;
    shGoTo('password');
  });

  shIdentifier.addEventListener('keydown', e => {
    if(e.key === 'Enter'){
      e.preventDefault();
      shContinueBtn.click();
    }
  });

  /* ---------- Generic modal open/close helpers ---------- */
  function shOpenModal(id){
    document.getElementById(id).classList.add('active');
  }
  function shCloseModal(id){
    document.getElementById(id).classList.remove('active');
  }

  document.getElementById('shHelpLink').addEventListener('click', () => {
    shOpenModal('shAccessModal');
  });

  // Close (X) buttons — close whichever modal they belong to
  document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => shCloseModal(btn.dataset.close));
  });

  // Clicking the dark backdrop of any small modal closes it
  document.querySelectorAll('.sh-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if(e.target === overlay) overlay.classList.remove('active');
    });
  });

  /* ---------- Country picker ---------- */
  const shCountries = [
    "Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua and Barbuda","Argentina",
    "Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus",
    "Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei",
    "Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Chad","Chile","China",
    "Colombia","Comoros","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti",
    "Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Estonia","Eswatini","Ethiopia","Fiji",
    "Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea",
    "Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel",
    "Italy","Ivory Coast","Jamaica","Japan","Jordan","Kurdistan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan",
    "Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg",
    "Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Mauritania","Mauritius","Mexico","Moldova",
    "Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nepal","Netherlands",
    "New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman","Pakistan",
    "Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar",
    "Romania","Russia","Rwanda","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore",
    "Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain",
    "Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania",
    "Thailand","Togo","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda",
    "Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu",
    "Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"
  ];

  const shCountryLabel = document.getElementById('shCountryLabel');
  const shCountryList = document.getElementById('shCountryList');
  const shCountryModal = document.getElementById('shCountryModal');
  let shSelectedCountry = null;
  let shPendingCountry = null;

  function shRenderCountryList(){
    shCountryList.innerHTML = '';
    shCountries.forEach(name => {
      const item = document.createElement('div');
      item.className = 'sh-country-item' + (name === shPendingCountry ? ' selected' : '');
      item.textContent = name;
      item.addEventListener('click', () => {
        shPendingCountry = name;
        shRenderCountryList();
      });
      shCountryList.appendChild(item);
    });
  }

  document.getElementById('shCountryBtn').addEventListener('click', () => {
    shPendingCountry = shSelectedCountry;
    shRenderCountryList();
    shCountryModal.classList.add('active');
    const selectedEl = shCountryList.querySelector('.selected');
    if(selectedEl) selectedEl.scrollIntoView({ block: 'center' });
  });

  document.getElementById('shCountryCancel').addEventListener('click', () => {
    shCountryModal.classList.remove('active');
  });

  document.getElementById('shCountryConfirm').addEventListener('click', () => {
    if(shPendingCountry){
      shSelectedCountry = shPendingCountry;
      shCountryLabel.textContent = shSelectedCountry;
    }
    shCountryModal.classList.remove('active');
  });

  shCountryModal.addEventListener('click', e => {
    if(e.target === shCountryModal) shCountryModal.classList.remove('active');
  });

  // Detect the visitor's location automatically and pre-select it.
  fetch('https://ipapi.co/json/')
    .then(res => res.json())
    .then(data => {
      const detected = data && data.country_name;
      if(detected && shCountries.includes(detected)){
        shSelectedCountry = detected;
      } else {
        shSelectedCountry = detected || 'United States';
      }
      shCountryLabel.textContent = shSelectedCountry;
    })
    .catch(() => {
      shSelectedCountry = 'United States';
      shCountryLabel.textContent = shSelectedCountry;
    });

  /* ---------- Google (real sign-in link) / Facebook (mock browser popup) ---------- */
  document.getElementById('shGoogleBtn').addEventListener('click', () => {
    // Opens Google's actual sign-in page in a new tab. A full OAuth
    // handshake back into this page would require a registered
    // OAuth client + backend, which a static demo page can't provide.
    window.open('https://accounts.google.com/signin/v2/identifier?hl=en', '_blank', 'noopener');
  });
  document.getElementById('shFacebookBtn').addEventListener('click', () => {
    shOpenModal('shFacebookOverlay');
  });

  function shOauthSuccess(message){
    shCloseModal('shFacebookOverlay');
    document.getElementById('shOauthSuccessMsg').textContent = message;
    shGoTo('oauth-success');
  }

  document.getElementById('shFbContinueBtn').addEventListener('click', () => {
    shOauthSuccess('Signed in as Alex Morgan via Facebook.');
  });
  document.getElementById('shFbCancelBtn').addEventListener('click', () => {
    shCloseModal('shFacebookOverlay');
  });
  document.getElementById('shFbEditAccess').addEventListener('click', e => {
    e.preventDefault();
    alert('This would let you choose exactly what info LUNAX can access before continuing.');
  });

  // Clicking the dark backdrop of the OAuth popups closes them
  document.querySelectorAll('.sh-oauth-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if(e.target === overlay) overlay.classList.remove('active');
    });
  });

  /* ---------- Can't access your account? ---------- */
  document.getElementById('shGoToResetBtn').addEventListener('click', () => {
    shCloseModal('shAccessModal');
    shResetSetMethod('email');
    shOpenModal('shResetModal');
  });
  document.getElementById('shGoToRecoverBtn').addEventListener('click', () => {
    shCloseModal('shAccessModal');
    shGoTo('verify-identity');
  });

  /* ---------- Reset password modal ---------- */
  let shResetMethod = 'email';
  let shResetSentCode = null;
  let shResetCooldown = null;

  function shResetSetMethod(method){
    shResetMethod = method;
    const emailGroup = document.getElementById('shResetEmailGroup');
    const phoneGroup = document.getElementById('shResetPhoneGroup');
    const toggleLink = document.getElementById('shResetToggleMethod');
    const desc = document.getElementById('shResetDesc');
    if(method === 'phone'){
      emailGroup.style.display = 'none';
      phoneGroup.style.display = 'block';
      toggleLink.textContent = 'Reset password via email address';
      desc.textContent = 'Confirm your phone number and input the correct code to change the password.';
    } else {
      emailGroup.style.display = 'block';
      phoneGroup.style.display = 'none';
      toggleLink.textContent = 'Reset password via phone number';
      desc.textContent = 'Confirm your email address and input the correct code to change the password.';
    }
  }

  function shResetShowMsg(text, type){
    const el = document.getElementById('shResetMsg');
    el.textContent = text;
    el.className = 'sh-inline-msg active' + (type ? ' ' + type : '');
  }

  document.getElementById('shResetGoBack').addEventListener('click', () => {
    shCloseModal('shResetModal');
    shOpenModal('shAccessModal');
  });

  document.getElementById('shResetToggleMethod').addEventListener('click', () => {
    shResetSetMethod(shResetMethod === 'email' ? 'phone' : 'email');
    shResetShowMsg('', '');
  });

  document.getElementById('shResetSendBtn').addEventListener('click', () => {
    const identifier = shResetMethod === 'email'
      ? document.getElementById('shResetEmail').value.trim()
      : document.getElementById('shResetPhone').value.trim();
    if(!identifier){
      shResetShowMsg('Please enter your ' + (shResetMethod === 'email' ? 'email address' : 'phone number') + ' first.', 'error');
      return;
    }
    shResetSentCode = String(Math.floor(1000 + Math.random() * 9000));
    shResetShowMsg('A verification code has been sent to your ' + (shResetMethod === 'email' ? 'email' : 'phone') + '.', 'success');

    const btn = document.getElementById('shResetSendBtn');
    let seconds = 30;
    btn.disabled = true;
    btn.textContent = seconds + 's';
    clearInterval(shResetCooldown);
    shResetCooldown = setInterval(() => {
      seconds -= 1;
      if(seconds <= 0){
        clearInterval(shResetCooldown);
        btn.disabled = false;
        btn.textContent = 'SEND';
      } else {
        btn.textContent = seconds + 's';
      }
    }, 1000);
  });

  document.getElementById('shResetCancelBtn').addEventListener('click', () => {
    shCloseModal('shResetModal');
    shGoTo('start');
  });

  document.getElementById('shResetContinueBtn').addEventListener('click', () => {
    const identifier = shResetMethod === 'email'
      ? document.getElementById('shResetEmail').value.trim()
      : document.getElementById('shResetPhone').value.trim();
    const code = document.getElementById('shResetCode').value.trim();
    if(!identifier){
      shResetShowMsg('Please enter your ' + (shResetMethod === 'email' ? 'email address' : 'phone number') + '.', 'error');
      return;
    }
    if(!shResetSentCode){
      shResetShowMsg('Please request a verification code first.', 'error');
      return;
    }
    if(!code){
      shResetShowMsg('Please enter the verification code.', 'error');
      return;
    }
    if(code !== shResetSentCode){
      shResetShowMsg('Incorrect verification code. Please try again.', 'error');
      return;
    }
    shCloseModal('shResetModal');
    shResetSentCode = null;
    shGoTo('new-password');
  });

  /* ---------- Verify your identity (recover account) ---------- */
  const shVerifyModalByType = {
    email: 'shVerifyEmailModal',
    phone: 'shVerifyPhoneModal',
    payment: 'shVerifyPaymentModal',
    order: 'shVerifyOrderModal'
  };
  document.querySelectorAll('[data-verify]').forEach(btn => {
    btn.addEventListener('click', () => {
      shOpenModal(shVerifyModalByType[btn.dataset.verify]);
    });
  });

  function shVerifySucceed(modalId, msgElId, message){
    const msgEl = document.getElementById(msgElId);
    msgEl.textContent = message;
    msgEl.className = 'sh-inline-msg active success';
    setTimeout(() => {
      shCloseModal(modalId);
      msgEl.className = 'sh-inline-msg';
      shGoTo('new-password');
    }, 900);
  }

  document.getElementById('shVerifyEmailBtn').addEventListener('click', () => {
    const email = document.getElementById('shVerifyEmailInput').value.trim();
    const msgEl = document.getElementById('shVerifyEmailMsg');
    if(!/^\S+@\S+\.\S+$/.test(email)){
      msgEl.textContent = 'Please enter a valid email address.';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    shVerifySucceed('shVerifyEmailModal', 'shVerifyEmailMsg', 'Verification link sent! Redirecting…');
  });

  document.getElementById('shVerifyPhoneBtn').addEventListener('click', () => {
    const phone = document.getElementById('shVerifyPhoneInput').value.trim();
    const msgEl = document.getElementById('shVerifyPhoneMsg');
    if(phone.length < 6){
      msgEl.textContent = 'Please enter a valid phone number.';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    shVerifySucceed('shVerifyPhoneModal', 'shVerifyPhoneMsg', 'Verification code sent via SMS. Confirming…');
  });

  document.getElementById('shVerifyPaymentBtn').addEventListener('click', () => {
    const last4 = document.getElementById('shVerifyCardLast4').value.trim();
    const expiry = document.getElementById('shVerifyCardExpiry').value.trim();
    const msgEl = document.getElementById('shVerifyPaymentMsg');
    if(!/^\d{4}$/.test(last4) || !/^\d{2}\/\d{2}$/.test(expiry)){
      msgEl.textContent = 'Please enter the last 4 digits and expiry (MM/YY).';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    shVerifySucceed('shVerifyPaymentModal', 'shVerifyPaymentMsg', 'Payment details matched. Confirming…');
  });

  document.getElementById('shVerifyOrderBtn').addEventListener('click', () => {
    const order = document.getElementById('shVerifyOrderInput').value.trim();
    const msgEl = document.getElementById('shVerifyOrderMsg');
    if(!order){
      msgEl.textContent = 'Please enter an order or tracking number.';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    shVerifySucceed('shVerifyOrderModal', 'shVerifyOrderMsg', 'Order matched to your account. Confirming…');
  });

  /* ---------- Set new password (after verification) ---------- */
  document.getElementById('shSavePasswordBtn').addEventListener('click', () => {
    const pw = document.getElementById('shNewPassword').value;
    const confirm = document.getElementById('shNewPasswordConfirm').value;
    const msgEl = document.getElementById('shNewPasswordMsg');
    if(pw.length < 8){
      msgEl.textContent = 'Password must be at least 8 characters.';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    if(pw !== confirm){
      msgEl.textContent = 'Passwords do not match.';
      msgEl.className = 'sh-inline-msg active error';
      return;
    }
    msgEl.className = 'sh-inline-msg';
    document.getElementById('shNewPassword').value = '';
    document.getElementById('shNewPasswordConfirm').value = '';
    alert('Password updated successfully! Please sign in with your new password.');
    shGoTo('start');
  });
</script>

</body>
</html>
