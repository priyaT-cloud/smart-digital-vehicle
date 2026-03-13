<?php
$currentUser = getCurrentUser();
$B = baseUrl();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($pageTitle) ? clean($pageTitle).' — '.SITE_NAME : SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#080c18;--surface:#0f1626;--surface2:#161f34;--surface3:#1c2640;
  --accent:#00e5b8;--accent2:#ff5f1f;--accent3:#7c5cfc;--gold:#fbbf24;
  --text:#eef2ff;--muted:#6b7fa3;--muted2:#4a5568;
  --border:rgba(255,255,255,0.07);--border2:rgba(255,255,255,0.12);
  --glow:0 0 40px rgba(0,229,184,.2);--r:14px;--r2:20px;
  --ease:all .3s cubic-bezier(.4,0,.2,1);
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
h1,h2,h3,h4{font-family:'Syne',sans-serif;}
a{color:inherit;text-decoration:none;}
.bg-grid{position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(0,229,184,.025) 1px,transparent 1px),
  linear-gradient(90deg,rgba(0,229,184,.025) 1px,transparent 1px);background-size:48px 48px;}
.bg-orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;}
.orb1{width:500px;height:500px;background:rgba(0,229,184,.06);top:-150px;right:-100px;}
.orb2{width:380px;height:380px;background:rgba(124,92,252,.06);bottom:0;left:-80px;}
.wrap{position:relative;z-index:1;}
.page{min-height:100vh;padding:5.5rem 1.5rem 3rem;}
.page.center{display:flex;align-items:center;justify-content:center;}
.container{max-width:1100px;margin:0 auto;}
/* NAVBAR */
.navbar{position:fixed;top:0;left:0;right:0;z-index:500;
  display:flex;align-items:center;justify-content:space-between;padding:.9rem 2rem;
  background:rgba(8,12,24,.92);backdrop-filter:blur(24px);border-bottom:1px solid var(--border);}
.logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.35rem;color:var(--accent);}
.logo span{color:var(--text);}
.nav-r{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;}
.avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent3));
        display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;color:#000;}
.uname{font-size:.85rem;color:var(--muted);max-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.nbtn{background:none;border:1px solid var(--border2);color:var(--muted);padding:.38rem .9rem;
      border-radius:8px;font-size:.84rem;cursor:pointer;transition:var(--ease);display:inline-block;}
.nbtn:hover{border-color:var(--accent);color:var(--accent);}
/* CARD */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r2);padding:2rem;}
.card-sm{background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);padding:1.2rem;}
/* FORM */
.fg{margin-bottom:1.1rem;}
.fg label{display:block;font-size:.74rem;color:var(--muted);font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.42rem;}
.fg input,.fg select,.fg textarea{width:100%;background:var(--bg);border:1px solid var(--border2);border-radius:10px;padding:.72rem 1rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.93rem;transition:var(--ease);}
.fg input:focus,.fg select:focus,.fg textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(0,229,184,.07);}
.fg select option{background:var(--surface);}
.fg textarea{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:580px){.form-row{grid-template-columns:1fr;}}
/* BUTTONS */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.78rem 1.7rem;border:none;border-radius:10px;cursor:pointer;font-family:'Syne',sans-serif;font-weight:700;font-size:.93rem;transition:var(--ease);}
.btn-primary{background:var(--accent);color:#000;}
.btn-primary:hover{background:#00ccaa;transform:translateY(-1px);box-shadow:var(--glow);}
.btn-purple{background:var(--accent3);color:#fff;}
.btn-purple:hover{background:#6b45e8;transform:translateY(-1px);}
.btn-outline{background:transparent;border:1px solid var(--border2);color:var(--text);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.btn-danger{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:.42rem .88rem;font-size:.82rem;cursor:pointer;transition:var(--ease);}
.btn-danger:hover{background:rgba(239,68,68,.25);}
.btn-edit{background:rgba(0,229,184,.1);color:var(--accent);border:1px solid rgba(0,229,184,.2);border-radius:8px;padding:.42rem .88rem;font-size:.82rem;cursor:pointer;transition:var(--ease);}
.btn-edit:hover{background:rgba(0,229,184,.2);}
.btn-sm{padding:.42rem .88rem;font-size:.82rem;border-radius:8px;}
.btn-block{width:100%;}
/* ALERTS */
.alert{padding:.82rem 1.1rem;border-radius:10px;margin-bottom:1.1rem;font-size:.88rem;}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);color:#f87171;}
.alert-success{background:rgba(0,229,184,.08);border:1px solid rgba(0,229,184,.2);color:var(--accent);}
.alert-warn{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.25);color:#fbbf24;}
/* BADGES */
.badge{display:inline-block;padding:.22rem .7rem;border-radius:999px;font-size:.7rem;font-weight:700;letter-spacing:.05em;}
.badge-parking{background:rgba(124,92,252,.15);color:#a78bfa;border:1px solid rgba(124,92,252,.25);}
.badge-washing{background:rgba(0,229,184,.12);color:var(--accent);border:1px solid rgba(0,229,184,.2);}
.badge-rental{background:rgba(255,95,31,.12);color:var(--accent2);border:1px solid rgba(255,95,31,.2);}
.badge-confirmed{background:rgba(0,229,184,.1);color:var(--accent);border:1px solid rgba(0,229,184,.18);}
.badge-taken{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.22);}
.badge-completed{background:rgba(110,231,183,.12);color:#6ee7b7;border:1px solid rgba(110,231,183,.2);}
.badge-cancelled{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2);}
.badge-overdue{background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.3);}
/* TABS */
.tabs{display:flex;gap:.45rem;margin-bottom:1.8rem;flex-wrap:wrap;}
.tab{padding:.52rem 1.1rem;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--muted);font-family:'DM Sans',sans-serif;font-size:.87rem;cursor:pointer;transition:var(--ease);}
.tab:hover{border-color:var(--accent);color:var(--accent);}
.tab.active{background:rgba(0,229,184,.1);border-color:var(--accent);color:var(--accent);font-weight:600;}
/* GRID */
.grid3{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1.4rem;}
/* LISTING CARD */
.lcard{background:var(--surface);border:1px solid var(--border);border-radius:var(--r2);overflow:hidden;transition:var(--ease);}
.lcard:hover{transform:translateY(-4px);border-color:rgba(0,229,184,.22);box-shadow:0 20px 55px rgba(0,0,0,.5);}
.lcard-img{width:100%;height:185px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:4rem;overflow:hidden;position:relative;}
.lcard-img img{width:100%;height:100%;object-fit:cover;}
.taken-overlay{position:absolute;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:1.3rem;color:#f87171;letter-spacing:.1em;}
.lcard-body{padding:1.2rem;}
.lcard-title{font-size:.97rem;font-weight:700;margin-bottom:.28rem;}
.lcard-meta{color:var(--muted);font-size:.79rem;margin-bottom:.42rem;}
.lcard-extra{font-size:.76rem;color:var(--muted);margin-bottom:.45rem;}
.lcard-price{color:var(--accent);font-weight:700;font-size:.92rem;margin-bottom:.55rem;}
.lcard-rating{margin-bottom:.9rem;font-size:.82rem;}
.lcard-actions{display:flex;gap:.45rem;}
.lcard-btn{flex:1;padding:.48rem .4rem;border:none;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;transition:var(--ease);text-align:center;display:inline-block;}
.lcard-btn-p{background:var(--accent);color:#000;}.lcard-btn-p:hover{background:#00ccaa;}
.lcard-btn-s{background:var(--surface2);color:var(--text);border:1px solid var(--border);}.lcard-btn-s:hover{border-color:var(--accent);color:var(--accent);}
/* MANAGE CARD */
.mcard{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;display:flex;align-items:center;gap:1rem;margin-bottom:.9rem;flex-wrap:wrap;}
.mcard-thumb{width:68px;height:68px;border-radius:10px;background:var(--surface2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.7rem;overflow:hidden;}
.mcard-thumb img{width:100%;height:100%;object-fit:cover;}
.mcard-info{flex:1;min-width:140px;}
.mcard-title{font-weight:700;font-size:.93rem;margin-bottom:.22rem;}
.mcard-sub{color:var(--muted);font-size:.79rem;}
.mcard-actions{display:flex;gap:.45rem;flex-wrap:wrap;}
/* BOOKING ITEM */
.bitem{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;margin-bottom:.9rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.9rem;}
.bitem-info h4{font-size:.93rem;font-weight:700;margin-bottom:.25rem;}
.bitem-info p{color:var(--muted);font-size:.79rem;line-height:1.55;}
/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:1rem;margin-bottom:1.8rem;}
.stat{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;}
.stat-num{font-family:'Syne',sans-serif;font-size:1.75rem;font-weight:800;margin-bottom:.18rem;}
.stat-lbl{color:var(--muted);font-size:.79rem;}
/* UPLOAD */
.upload-box{border:2px dashed var(--border2);border-radius:12px;padding:1.8rem;text-align:center;cursor:pointer;transition:var(--ease);}
.upload-box:hover{border-color:var(--accent);background:rgba(0,229,184,.03);}
/* FILTER */
.filter-bar{display:flex;gap:.75rem;margin-bottom:1.4rem;flex-wrap:wrap;}
.filter-bar input,.filter-bar select{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:.48rem .88rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.87rem;}
.filter-bar input:focus,.filter-bar select:focus{outline:none;border-color:var(--accent);}
.filter-bar input{flex:1;min-width:190px;}
/* REVIEW */
.rv-item{border-bottom:1px solid var(--border);padding:.9rem 0;}
.rv-item:last-child{border-bottom:none;}
.rv-user{font-weight:600;font-size:.88rem;margin-bottom:.25rem;}
.rv-text{color:var(--muted);font-size:.83rem;line-height:1.55;}
.rv-date{font-size:.73rem;color:var(--muted2);margin-top:.2rem;}
/* EMPTY */
.empty{text-align:center;padding:3.5rem 1.5rem;color:var(--muted);}
.empty .ei{font-size:2.8rem;margin-bottom:.8rem;}
/* DIVIDER */
.divider{height:1px;background:var(--border);margin:1.4rem 0;}
/* COST BOX */
.cost-box{background:rgba(0,229,184,.05);border:1px solid rgba(0,229,184,.18);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem;}
.cost-box .clbl{font-size:.76rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;}
.cost-box .cval{font-size:1.5rem;font-weight:800;color:var(--accent);}
/* CTX BAR */
.ctx-bar{background:rgba(0,229,184,.06);border:1px solid rgba(0,229,184,.15);border-radius:10px;padding:.72rem 1rem;margin-bottom:1.3rem;font-size:.83rem;color:var(--accent);}
/* PAYMENT */
.pay-options{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1rem;}
.pay-opt{background:var(--surface2);border:2px solid var(--border);border-radius:12px;padding:.9rem 1rem;cursor:pointer;transition:var(--ease);text-align:center;}
.pay-opt:hover{border-color:var(--accent);}
.pay-opt.selected{border-color:var(--accent);background:rgba(0,229,184,.08);}
.pay-opt .po-icon{font-size:1.6rem;margin-bottom:.3rem;}
.pay-opt .po-label{font-size:.8rem;font-weight:600;color:var(--text);}
.upi-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.7rem;}
.upi-btn{background:var(--surface2);border:2px solid var(--border);border-radius:10px;padding:.6rem;cursor:pointer;font-size:.82rem;font-weight:600;transition:var(--ease);text-align:center;}
.upi-btn:hover,.upi-btn.selected{border-color:var(--accent3);background:rgba(124,92,252,.08);color:#a78bfa;}
/* TRACKING */
.track-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;margin-bottom:.9rem;}
.track-status{display:flex;align-items:center;gap:.7rem;margin-bottom:.6rem;flex-wrap:wrap;}
.status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.dot-green{background:var(--accent);box-shadow:0 0 8px var(--accent);}
.dot-yellow{background:#fbbf24;box-shadow:0 0 8px #fbbf24;}
.dot-red{background:#f87171;box-shadow:0 0 8px #f87171;}
/* OVERDUE WARN */
.overdue-banner{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1rem;font-size:.87rem;color:#fca5a5;}
/* RECEIPT MODAL */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1000;align-items:center;justify-content:center;padding:1.5rem;}
.modal-bg.open{display:flex;}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:var(--r2);width:100%;max-width:520px;max-height:90vh;overflow-y:auto;}
/* SCROLLBAR */
::-webkit-scrollbar{width:6px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:3px;}
/* ANIMATION */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fadeUp .45s ease both;}

/* ═══════════════ RECEIPT PRINT STYLES ═══════════════ */
@media print{
  body{background:#fff;color:#111;}
  .navbar,.no-print{display:none!important;}
  .receipt-box{box-shadow:none!important;border:1px solid #ccc!important;page-break-inside:avoid;}
}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-orb orb1"></div>
<div class="bg-orb orb2"></div>
<nav class="navbar">
  <a href="<?= $B ?>/index.php" class="logo">Smart<span>Vehi</span></a>
  <div class="nav-r">
    <?php if ($currentUser): ?>
      <div class="avatar"><?= strtoupper(substr($currentUser['full_name'],0,1)) ?></div>
      <span class="uname"><?= clean($currentUser['full_name']) ?></span>
      <a href="<?= $B ?>/logout.php" class="nbtn">Logout</a>
    <?php else: ?>
      <a href="<?= $B ?>/index.php" class="nbtn">← Home</a>
    <?php endif; ?>
  </div>
</nav>
<div class="wrap">
