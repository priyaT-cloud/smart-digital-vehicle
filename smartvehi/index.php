<?php
require_once 'includes/config.php';
if (isLoggedIn()) {
    $u = getCurrentUser();
    redirect($u['role']==='provider' ? 'provider/dashboard.php' : 'receiver/browse.php');
}
$pageTitle='Welcome'; require_once 'includes/header.php'; ?>
<div class="page center fu" style="text-align:center;">
  <div>
    <div style="display:inline-block;background:rgba(0,229,184,.1);border:1px solid rgba(0,229,184,.25);
                color:var(--accent);padding:.32rem 1rem;border-radius:999px;
                font-size:.76rem;font-weight:700;letter-spacing:.08em;margin-bottom:1.4rem;">
      🚗 SMART VEHICLE PLATFORM — BCA PROJECT 2026
    </div>
    <h1 style="font-size:clamp(2rem,6vw,3.8rem);font-weight:800;line-height:1.1;margin-bottom:1rem;">
      Your Vehicle Services,<br><span style="color:var(--accent)">All In One Place</span>
    </h1>
    <p style="color:var(--muted);font-size:1rem;max-width:480px;margin:0 auto 2.8rem;line-height:1.7;">
      Parking · Washing · Rental — with digital payments, receipts &amp; live tracking.
    </p>
    <div style="display:flex;gap:1.4rem;flex-wrap:wrap;justify-content:center;">
      <a href="service.php?role=provider">
        <div class="role-card" style="background:rgba(255,255,255,.03);border:1px solid var(--border);
             border-radius:20px;padding:2.4rem 2rem;width:245px;text-align:center;transition:var(--ease);cursor:pointer;"
             onmouseenter="this.style.borderColor='rgba(255,95,31,.5)';this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 50px rgba(0,0,0,.4)'"
             onmouseleave="this.style.borderColor='';this.style.transform='';this.style.boxShadow=''">
          <div style="font-size:2.8rem;margin-bottom:1rem;">🏢</div>
          <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Service Provider</h3>
          <p style="color:var(--muted);font-size:.85rem;line-height:1.6;">List services, track vehicles, manage bookings &amp; earnings.</p>
          <span class="badge" style="margin-top:1rem;background:rgba(255,95,31,.12);color:var(--accent2);border:1px solid rgba(255,95,31,.2);">LIST &amp; EARN</span>
        </div>
      </a>
      <a href="service.php?role=receiver">
        <div class="role-card" style="background:rgba(255,255,255,.03);border:1px solid var(--border);
             border-radius:20px;padding:2.4rem 2rem;width:245px;text-align:center;transition:var(--ease);cursor:pointer;"
             onmouseenter="this.style.borderColor='rgba(0,229,184,.5)';this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 50px rgba(0,0,0,.4)'"
             onmouseleave="this.style.borderColor='';this.style.transform='';this.style.boxShadow=''">
          <div style="font-size:2.8rem;margin-bottom:1rem;">🙋</div>
          <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Service Receiver</h3>
          <p style="color:var(--muted);font-size:.85rem;line-height:1.6;">Find, book, pay digitally &amp; get instant receipts.</p>
          <span class="badge badge-washing" style="margin-top:1rem;">FIND &amp; BOOK</span>
        </div>
      </a>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
