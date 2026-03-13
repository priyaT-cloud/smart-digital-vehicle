<?php
require_once 'includes/config.php';
$role = $_GET['role'] ?? '';
if (!in_array($role,['provider','receiver'],true)) redirect('index.php');
$_SESSION['chosen_role'] = $role;
$isP = ($role==='provider');
$pageTitle='Choose Service'; require_once 'includes/header.php'; ?>
<div class="page center fu" style="text-align:center;">
  <div>
    <span class="badge <?= $isP?'badge-rental':'badge-washing' ?>" style="margin-bottom:1rem;display:inline-block;font-size:.78rem;padding:.3rem .9rem;">
      <?= $isP?'🏢 SERVICE PROVIDER':'🙋 SERVICE RECEIVER' ?>
    </span>
    <h2 style="font-size:1.9rem;font-weight:800;margin-bottom:.5rem;">
      <?= $isP?'What service do you offer?':'What are you looking for?' ?>
    </h2>
    <p style="color:var(--muted);margin-bottom:2.5rem;">
      <?= $isP?'Choose a category to manage your service.':'Select a service to browse and book.' ?>
    </p>
    <div style="display:flex;gap:1.3rem;flex-wrap:wrap;justify-content:center;">
      <?php
      $B=baseUrl();
      $svcs=['parking'=>['🅿️','Parking',$isP?'List parking zones with slots &amp; pricing.':'Find &amp; book parking near you.'],
             'washing'=>['🚿','Car Washing',$isP?'Register your washing center &amp; services.':'Book a wash with optional pickup &amp; drop.'],
             'rental' =>['🚗','Vehicle Rental',$isP?'Add vehicles for rent with tracking.':'Browse &amp; rent vehicles.']];
      foreach($svcs as $k=>[$icon,$label,$desc]): ?>
      <a href="<?= $B ?>/login.php?service=<?= $k ?>">
        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:20px;
             padding:2rem 1.6rem;width:210px;text-align:center;transition:var(--ease);cursor:pointer;"
             onmouseenter="this.style.borderColor='var(--accent)';this.style.transform='translateY(-5px)';this.style.boxShadow='0 0 40px rgba(0,229,184,.18)'"
             onmouseleave="this.style.borderColor='';this.style.transform='';this.style.boxShadow=''">
          <div style="font-size:2.6rem;margin-bottom:.9rem;"><?= $icon ?></div>
          <h3 style="font-size:1rem;margin-bottom:.35rem;"><?= $label ?></h3>
          <p style="color:var(--muted);font-size:.8rem;line-height:1.55;"><?= $desc ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
