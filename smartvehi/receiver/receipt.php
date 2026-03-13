<?php
require_once '../includes/config.php';
requireRole('receiver');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$id=(int)($_GET['id']??$_SESSION['last_booking_id']??0);
$s=$db->prepare('SELECT b.*,l.name AS lname,l.location AS lloc,l.type AS ltype,l.image AS limage,
  l.vehicle_model,l.rental_type,l.fuel_type,l.price_hour,l.price_day,l.price_basic,l.price_full,l.rent_hour,l.rent_day,
  u.full_name AS pname,u.phone AS pphone,u2.full_name AS rname
  FROM bookings b
  JOIN listings l ON b.listing_id=l.id
  JOIN users u ON l.user_id=u.id
  JOIN users u2 ON b.user_id=u2.id
  WHERE b.id=? AND b.user_id=?');
$s->bind_param('ii',$id,$user['id']); $s->execute();
$b=$s->get_result()->fetch_assoc();
if(!$b) redirect('receiver/my_bookings.php');
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$total=(float)$b['cost']+(float)$b['extra_charges'];
$pageTitle='Receipt '.$b['receipt_no']; require_once '../includes/header.php'; ?>

<div class="page fu" style="padding-top:5.5rem;">
<div class="container" style="max-width:560px;">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;" class="no-print">
    <a href="<?= $B ?>/receiver/my_bookings.php" class="nbtn">← My Bookings</a>
    <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Print Receipt</button>
  </div>

  <!-- RECEIPT BOX -->
  <div class="receipt-box" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r2);overflow:hidden;">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,var(--accent),var(--accent3));padding:1.8rem 2rem;color:#000;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
        <div>
          <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;color:#000;">SmartVehi</h2>
          <p style="font-size:.82rem;opacity:.75;">Smart Vehicle Service Platform</p>
        </div>
        <div style="text-align:right;">
          <div style="font-size:.72rem;opacity:.7;font-weight:600;letter-spacing:.05em;">RECEIPT</div>
          <div style="font-size:1.2rem;font-weight:800;"><?= clean($b['receipt_no']) ?></div>
          <div style="font-size:.78rem;opacity:.75;"><?= date('d M Y, H:i',strtotime($b['created_at'])) ?></div>
        </div>
      </div>
    </div>

    <!-- Vehicle image (for rentals) -->
    <?php if($b['ltype']==='rental'&&$b['limage']): ?>
    <div style="width:100%;height:200px;overflow:hidden;">
      <img src="<?= $B ?>/<?= clean($b['limage']) ?>" alt="<?= clean($b['lname']) ?>"
           style="width:100%;height:100%;object-fit:cover;">
    </div>
    <?php elseif($b['limage']): ?>
    <div style="width:100%;height:140px;overflow:hidden;">
      <img src="<?= $B ?>/<?= clean($b['limage']) ?>" alt="<?= clean($b['lname']) ?>"
           style="width:100%;height:100%;object-fit:cover;">
    </div>
    <?php endif; ?>

    <!-- Body -->
    <div style="padding:1.6rem 2rem;">

      <!-- Service info -->
      <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:1.3rem;padding-bottom:1.3rem;border-bottom:1px solid var(--border);">
        <div style="font-size:2.5rem;"><?= $icons[$b['ltype']] ?></div>
        <div>
          <div style="font-weight:700;font-size:1.05rem;"><?= clean($b['lname']) ?></div>
          <div style="color:var(--muted);font-size:.82rem;">📍 <?= clean($b['lloc']) ?></div>
          <div style="margin-top:.3rem;"><span class="badge badge-<?= $b['ltype'] ?>"><?= strtoupper($b['ltype']) ?></span></div>
        </div>
      </div>

      <!-- Details grid -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.3rem;">
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Customer</div>
          <div style="font-weight:600;font-size:.9rem;"><?= clean($b['customer_name']) ?></div>
          <div style="color:var(--muted);font-size:.8rem;"><?= clean($b['customer_phone']) ?></div>
        </div>
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Provider</div>
          <div style="font-weight:600;font-size:.9rem;"><?= clean($b['pname']) ?></div>
          <div style="color:var(--muted);font-size:.8rem;"><?= clean($b['pphone']) ?></div>
        </div>
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Booking Date</div>
          <div style="font-weight:600;font-size:.9rem;"><?= date('d M Y',strtotime($b['booking_date'])) ?></div>
        </div>
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Duration</div>
          <div style="font-weight:600;font-size:.9rem;"><?= clean($b['duration']) ?></div>
        </div>
        <?php if($b['ltype']==='rental'): ?>
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Vehicle</div>
          <div style="font-weight:600;font-size:.9rem;"><?= clean($b['vehicle_model']??'') ?></div>
          <div style="color:var(--muted);font-size:.8rem;"><?= clean($b['rental_type']??'') ?> · <?= clean($b['fuel_type']??'') ?></div>
        </div>
        <?php endif; ?>
        <?php if($b['pickup_drop']): ?>
        <div>
          <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Pickup &amp; Drop</div>
          <div style="font-weight:600;font-size:.9rem;color:var(--accent);">✅ Included</div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Payment -->
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:1rem 1.2rem;margin-bottom:1.3rem;">
        <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;">Payment Summary</div>
        <div style="display:flex;justify-content:space-between;font-size:.87rem;margin-bottom:.35rem;">
          <span style="color:var(--muted);">Base Cost</span><span>₹<?= number_format((float)$b['cost'],2) ?></span>
        </div>
        <?php if((float)$b['extra_charges']>0): ?>
        <div style="display:flex;justify-content:space-between;font-size:.87rem;margin-bottom:.35rem;">
          <span style="color:#fca5a5;">Extra Charges (late)</span><span style="color:#fca5a5;">+₹<?= number_format((float)$b['extra_charges'],2) ?></span>
        </div>
        <?php endif; ?>
        <div style="height:1px;background:var(--border);margin:.6rem 0;"></div>
        <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.1rem;">
          <span>Total</span><span style="color:var(--accent);">₹<?= number_format($total,2) ?></span>
        </div>
        <div style="margin-top:.6rem;font-size:.82rem;color:var(--muted);">
          <?= payLabel($b['payment_mode'],$b['upi_app']) ?>
          <span style="margin-left:.7rem;color:<?= $b['payment_status']==='paid'?'var(--accent)':'#fbbf24' ?>;">
            <?= $b['payment_status']==='paid'?'✅ Paid':'⏳ Pending' ?>
          </span>
        </div>
      </div>

      <?php if($b['notes']): ?>
      <div style="margin-bottom:1.3rem;">
        <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Special Requests</div>
        <div style="font-size:.87rem;color:var(--muted);font-style:italic;">"<?= clean($b['notes']) ?>"</div>
      </div>
      <?php endif; ?>

      <!-- Status -->
      <div style="text-align:center;padding:1rem;background:rgba(0,229,184,.06);border:1px solid rgba(0,229,184,.15);border-radius:10px;">
        <div style="font-size:.72rem;color:var(--muted);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;">Booking Status</div>
        <span class="badge badge-<?= $b['status'] ?>" style="font-size:.82rem;padding:.3rem .9rem;"><?= strtoupper($b['status']) ?></span>
        <div style="font-size:.78rem;color:var(--muted);margin-top:.6rem;">
          Show this receipt to the service provider to avail your service.
        </div>
      </div>

      <!-- Footer -->
      <div style="text-align:center;padding-top:1.2rem;margin-top:1.2rem;border-top:1px solid var(--border);font-size:.75rem;color:var(--muted);">
        SmartVehi · BCA Project 2026 · Priya.T (23IABCA120)<br>
        Receipt No: <?= clean($b['receipt_no']) ?> · Generated: <?= date('d M Y H:i') ?>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:.8rem;margin-top:1.3rem;flex-wrap:wrap;" class="no-print">
    <a href="<?= $B ?>/receiver/browse.php" class="btn btn-outline">← Browse More</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Receipt</button>
  </div>
</div></div>

<?php require_once '../includes/footer.php'; ?>
