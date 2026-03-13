<?php
require_once '../includes/config.php';
requireRole('receiver');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$uid=$user['id'];
$rows=$db->query("SELECT b.*,l.name AS lname,l.location AS lloc,l.type AS ltype,u.full_name AS pname
  FROM bookings b JOIN listings l ON b.listing_id=l.id JOIN users u ON l.user_id=u.id
  WHERE b.user_id=$uid ORDER BY b.created_at DESC");
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$msg=$_GET['msg']??'';
$pageTitle='My Bookings'; require_once '../includes/header.php'; ?>
<div class="page fu" style="padding-top:5.5rem;">
<div class="container" style="max-width:900px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;">📅 My Bookings</h2>
      <div style="color:var(--muted);font-size:.87rem;margin-top:.2rem;"><?= clean($user['full_name']) ?> · <?= $rows->num_rows ?> booking(s)</div>
    </div>
    <a href="<?= $B ?>/receiver/browse.php" class="nbtn">← Browse Services</a>
  </div>
  <?php if($msg==='booked'): ?><div class="alert alert-success">✅ Booking confirmed! Your receipt is ready.</div><?php endif; ?>
  <?php if($rows->num_rows===0): ?>
    <div class="empty"><div class="ei">📅</div><p>No bookings yet.<br><a href="<?= $B ?>/receiver/browse.php" style="color:var(--accent);">Browse services →</a></p></div>
  <?php else: while($b=$rows->fetch_assoc()):
    $total=(float)$b['cost']+(float)$b['extra_charges']; ?>
    <div class="bitem">
      <div style="display:flex;gap:1rem;align-items:center;flex:1;">
        <div style="font-size:2rem;flex-shrink:0;"><?= $icons[$b['ltype']] ?></div>
        <div class="bitem-info">
          <h4><?= clean($b['lname']) ?></h4>
          <p>📍 <?= clean($b['lloc']) ?> · by <?= clean($b['pname']) ?></p>
          <p>📅 <?= $b['booking_date'] ?> · ⏱ <?= clean($b['duration']) ?></p>
          <p><?= payLabel($b['payment_mode'],$b['upi_app']) ?> · 🧾 <?= clean($b['receipt_no']) ?></p>
          <?php if($b['pickup_drop']): ?><p style="color:var(--accent);font-size:.78rem;">✅ Pickup &amp; Drop included</p><?php endif; ?>
          <?php if((float)$b['extra_charges']>0): ?><p style="color:#fca5a5;font-size:.78rem;">⚠️ Extra: ₹<?= number_format((float)$b['extra_charges'],2) ?></p><?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0;">
        <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
        <div style="color:var(--accent);font-weight:800;font-size:1.05rem;margin-top:.4rem;">₹<?= number_format($total,2) ?></div>
        <div style="color:var(--muted);font-size:.72rem;margin-top:.2rem;"><?= $b['payment_status']==='paid'?'✅ Paid':'⏳ Pending' ?></div>
        <a href="<?= $B ?>/receiver/receipt.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:.5rem;display:inline-block;">🧾 Receipt</a>
      </div>
    </div>
  <?php endwhile; endif; ?>
</div></div>
<?php require_once '../includes/footer.php'; ?>
