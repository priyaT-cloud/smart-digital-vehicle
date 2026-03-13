<?php
require_once '../includes/config.php';
requireRole('provider');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$service=$_SESSION['chosen_service']??'parking';
$tab=$_GET['tab']??'add';
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$labels=['parking'=>'Parking Zone','washing'=>'Washing Center','rental'=>'Rental Vehicle'];

// ── DELETE listing ────────────────────────────────────────
if(isset($_GET['delete'])){
    $lid=(int)$_GET['delete'];
    $s=$db->prepare('DELETE FROM listings WHERE id=? AND user_id=?');
    $s->bind_param('ii',$lid,$user['id']); $s->execute();
    redirect('provider/dashboard.php?tab=my&msg=deleted');
}

// ── MARK TAKEN ────────────────────────────────────────────
if(isset($_GET['mark_taken'])){
    $lid=(int)$_GET['mark_taken'];
    $dur=clean($_GET['dur']??'1 Day');
    $hours=['1 Hour'=>1,'3 Hours'=>3,'6 Hours'=>6,'1 Day'=>24,'3 Days'=>72,'1 Week'=>168];
    $h=$hours[$dur]??24;
    $returnDue=date('Y-m-d H:i:s',strtotime("+{$h} hours"));
    $s=$db->prepare('UPDATE listings SET is_taken=1,taken_at=NOW(),return_due=? WHERE id=? AND user_id=?');
    $s->bind_param('sii',$returnDue,$lid,$user['id']); $s->execute();
    // Update booking status
    $s2=$db->prepare('UPDATE bookings SET status="taken" WHERE listing_id=? AND status="confirmed" ORDER BY created_at DESC LIMIT 1');
    $s2->bind_param('i',$lid); $s2->execute();
    redirect('provider/dashboard.php?tab=tracking&msg=marked_taken');
}

// ── MARK RETURNED ─────────────────────────────────────────
if(isset($_GET['mark_returned'])){
    $lid=(int)$_GET['mark_returned'];
    // Get listing
    $s=$db->prepare('SELECT * FROM listings WHERE id=? AND user_id=?');
    $s->bind_param('ii',$lid,$user['id']); $s->execute();
    $listing=$s->get_result()->fetch_assoc();
    if($listing && $listing['is_taken']){
        $extra=0;
        if($listing['return_due'] && strtotime('now')>strtotime($listing['return_due'])){
            $hoursLate=ceil((time()-strtotime($listing['return_due']))/3600);
            $feePerHr=(float)($listing['late_fee_hour']??$listing['late_fee_rental']??50);
            $extra=$hoursLate*$feePerHr;
        }
        $s2=$db->prepare('UPDATE listings SET is_taken=0,taken_at=NULL,return_due=NULL WHERE id=?');
        $s2->bind_param('i',$lid); $s2->execute();
        // Update booking
        $s3=$db->prepare('UPDATE bookings SET status="completed",extra_charges=? WHERE listing_id=? AND status="taken" ORDER BY created_at DESC LIMIT 1');
        $s3->bind_param('di',$extra,$lid); $s3->execute();
    }
    redirect('provider/dashboard.php?tab=tracking&msg=returned');
}

$uid=$user['id'];
// Stats
$totalListings=(int)$db->query("SELECT COUNT(*) FROM listings WHERE user_id=$uid")->fetch_row()[0];
$totalBookings=(int)$db->query("SELECT COUNT(*) FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.user_id=$uid")->fetch_row()[0];
$totalEarnings=(float)$db->query("SELECT COALESCE(SUM(b.cost+b.extra_charges),0) FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.user_id=$uid AND b.payment_status='paid'")->fetch_row()[0];
$totalReviews=(int)$db->query("SELECT COUNT(*) FROM reviews r JOIN listings l ON r.listing_id=l.id WHERE l.user_id=$uid")->fetch_row()[0];

$msgs=['deleted'=>'✅ Listing deleted.','added'=>'✅ Listing published!','updated'=>'✅ Listing updated!',
       'missing'=>'⚠️ Name and location are required.','error'=>'⚠️ Save failed.',
       'marked_taken'=>'✅ Vehicle marked as taken. Timer started!','returned'=>'✅ Vehicle marked as returned.'];
$msg=isset($_GET['msg'],$msgs[$_GET['msg']])?$msgs[$_GET['msg']]:'';

$pageTitle='Provider Dashboard'; require_once '../includes/header.php'; ?>

<div class="page fu" style="padding-top:5.5rem;">
<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;"><?= $icons[$service] ?> Provider Dashboard</h2>
      <div style="color:var(--muted);font-size:.87rem;margin-top:.2rem;"><?= clean($user['full_name']) ?> · <?= ucfirst($service) ?> Provider</div>
    </div>
    <a href="<?= $B ?>/service.php?role=provider" class="nbtn">Change Service</a>
  </div>

  <!-- Stats -->
  <div class="stats">
    <div class="stat"><div class="stat-num" style="color:var(--accent)"><?= $totalListings ?></div><div class="stat-lbl">Listings</div></div>
    <div class="stat"><div class="stat-num" style="color:#a78bfa"><?= $totalBookings ?></div><div class="stat-lbl">Bookings</div></div>
    <div class="stat"><div class="stat-num" style="color:var(--accent2)">₹<?= number_format($totalEarnings) ?></div><div class="stat-lbl">Total Earnings</div></div>
    <div class="stat"><div class="stat-num" style="color:var(--gold)"><?= $totalReviews ?></div><div class="stat-lbl">Reviews</div></div>
  </div>

  <?php if($msg): ?><div class="alert <?= str_starts_with($msg,'⚠')?'alert-error':'alert-success' ?>"><?= $msg ?></div><?php endif; ?>

  <div class="tabs">
    <a href="?tab=add"      class="tab <?= $tab==='add'?'active':'' ?>">➕ Add Listing</a>
    <a href="?tab=my"       class="tab <?= $tab==='my'?'active':'' ?>">📋 My Listings</a>
    <a href="?tab=bookings" class="tab <?= $tab==='bookings'?'active':'' ?>">📅 Bookings</a>
    <a href="?tab=tracking" class="tab <?= $tab==='tracking'?'active':'' ?>">📡 Vehicle Tracking</a>
  </div>

  <!-- ═══ ADD LISTING ═══ -->
  <?php if($tab==='add'): ?>
  <div class="card">
    <h3 style="margin-bottom:1.4rem;font-size:1.1rem;"><?= $icons[$service] ?> Add New <?= $labels[$service] ?></h3>
    <form method="POST" action="<?= $B ?>/provider/add_listing.php" enctype="multipart/form-data">
      <input type="hidden" name="type" value="<?= $service ?>">
      <div class="form-row">
        <div class="fg"><label>Name / Title *</label><input type="text" name="name" placeholder="e.g. City Center Parking Zone A" required></div>
        <div class="fg"><label>Location / Address *</label><input type="text" name="location" placeholder="e.g. Anna Nagar, Chennai" required></div>
      </div>
      <?php if($service==='parking'): ?>
        <div class="form-row">
          <div class="fg"><label>Price per Hour (₹)</label><input type="number" name="price_hour" min="0" step="0.01" placeholder="30"></div>
          <div class="fg"><label>Price per Day (₹)</label><input type="number" name="price_day" min="0" step="0.01" placeholder="200"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Total Slots</label><input type="number" name="total_slots" min="1" placeholder="20"></div>
          <div class="fg"><label>Vehicle Type</label>
            <select name="vehicle_type"><option>2-Wheeler</option><option>4-Wheeler</option><option selected>Both</option></select></div>
        </div>
        <div class="fg"><label>Late Fee per Hour (₹)</label><input type="number" name="late_fee_hour" min="0" step="0.01" placeholder="50" value="50"></div>
      <?php elseif($service==='washing'): ?>
        <div class="form-row">
          <div class="fg"><label>Basic Wash Price (₹)</label><input type="number" name="price_basic" min="0" step="0.01" placeholder="150"></div>
          <div class="fg"><label>Full Service Price (₹)</label><input type="number" name="price_full" min="0" step="0.01" placeholder="450"></div>
        </div>
        <div class="fg"><label>Services Offered</label><input type="text" name="services_offered" placeholder="Exterior wash, Interior vacuum, Waxing…"></div>
        <div class="fg"><label>Pickup &amp; Drop Extra Fee (₹)</label><input type="number" name="pickup_drop_fee" min="0" step="0.01" placeholder="100" value="100"></div>
      <?php elseif($service==='rental'): ?>
        <div class="form-row">
          <div class="fg"><label>Vehicle Category</label>
            <select name="rental_type"><option>2-Wheeler (Bike/Scooter)</option><option>3-Wheeler (Auto)</option><option>4-Wheeler (Car)</option></select></div>
          <div class="fg"><label>Vehicle Model</label><input type="text" name="vehicle_model" placeholder="e.g. Honda Activa 6G"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Rent per Hour (₹)</label><input type="number" name="rent_hour" min="0" step="0.01" placeholder="80"></div>
          <div class="fg"><label>Rent per Day (₹)</label><input type="number" name="rent_day" min="0" step="0.01" placeholder="500"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Fuel Type</label>
            <select name="fuel_type"><option>Petrol</option><option>Electric</option><option>Diesel</option><option>CNG</option></select></div>
          <div class="fg"><label>Late Return Fee per Hour (₹)</label><input type="number" name="late_fee_rental" min="0" step="0.01" placeholder="100" value="100"></div>
        </div>
      <?php endif; ?>
      <div class="fg"><label>Description</label><textarea name="description" placeholder="Describe your service…"></textarea></div>
      <div class="fg">
        <label>Upload Image</label>
        <div class="upload-box" onclick="document.getElementById('imgf').click()">
          <div style="font-size:1.9rem;margin-bottom:.4rem;">📷</div>
          <p style="font-size:.87rem;color:var(--muted);">Click to upload — JPG/PNG/WEBP, max 5MB</p>
          <input type="file" id="imgf" name="image" accept="image/*" style="display:none" onchange="prevImg(this)">
          <img id="img-preview" src="" alt="" style="display:none;max-width:100%;max-height:200px;border-radius:8px;margin-top:1rem;object-fit:cover;">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="min-width:200px;">🚀 Publish Listing</button>
    </form>
  </div>

  <!-- ═══ MY LISTINGS ═══ -->
  <?php elseif($tab==='my'):
    $res=$db->query("SELECT l.*,(SELECT ROUND(AVG(rating),1) FROM reviews WHERE listing_id=l.id) AS avg_r,
      (SELECT COUNT(*) FROM bookings WHERE listing_id=l.id) AS bcount
      FROM listings l WHERE l.user_id=$uid ORDER BY l.created_at DESC");
    if($res->num_rows===0): ?>
      <div class="empty"><div class="ei">📭</div><p>No listings yet.<br><a href="?tab=add" style="color:var(--accent)">Add your first listing →</a></p></div>
    <?php else: while($l=$res->fetch_assoc()): ?>
      <div class="mcard">
        <div class="mcard-thumb">
          <?php if($l['image']): ?><img src="<?= $B ?>/<?= clean($l['image']) ?>" alt=""><?php else: echo $icons[$l['type']]; endif; ?>
        </div>
        <div class="mcard-info">
          <div class="mcard-title"><?= clean($l['name']) ?>
            <?php if($l['is_taken']): ?> <span class="badge badge-taken" style="font-size:.65rem;">TAKEN</span><?php endif; ?>
          </div>
          <div class="mcard-sub">📍 <?= clean($l['location']) ?> · <?= $l['avg_r']?'⭐ '.$l['avg_r']:'No reviews' ?> · <?= (int)$l['bcount'] ?> booking(s)</div>
        </div>
        <div class="mcard-actions">
          <a href="<?= $B ?>/provider/edit_listing.php?id=<?= $l['id'] ?>" class="btn-edit">✏️ Edit</a>
          <a href="?tab=my&delete=<?= $l['id'] ?>" class="btn-danger" onclick="return confirm('Delete this listing?')">🗑️ Delete</a>
        </div>
      </div>
    <?php endwhile; endif; ?>

  <!-- ═══ BOOKINGS ═══ -->
  <?php elseif($tab==='bookings'):
    $res=$db->query("SELECT b.*,l.name AS lname,l.type AS ltype FROM bookings b
      JOIN listings l ON b.listing_id=l.id WHERE l.user_id=$uid ORDER BY b.created_at DESC");
    if($res->num_rows===0): ?>
      <div class="empty"><div class="ei">📅</div><p>No bookings received yet.</p></div>
    <?php else: while($b=$res->fetch_assoc()):
      $total=(float)$b['cost']+(float)$b['extra_charges']; ?>
      <div class="bitem">
        <div class="bitem-info">
          <h4><?= $icons[$b['ltype']] ?> <?= clean($b['lname']) ?></h4>
          <p>👤 <?= clean($b['customer_name']) ?> · 📞 <?= clean($b['customer_phone']) ?></p>
          <p>📅 <?= $b['booking_date'] ?> · ⏱ <?= clean($b['duration']) ?></p>
          <p><?= payLabel($b['payment_mode'],$b['upi_app']) ?> · 🧾 <?= clean($b['receipt_no']) ?></p>
          <?php if($b['extra_charges']>0): ?>
            <p style="color:#fca5a5;">⚠️ Extra charges: ₹<?= number_format((float)$b['extra_charges'],2) ?></p>
          <?php endif; ?>
        </div>
        <div style="text-align:right;">
          <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
          <div style="color:var(--accent);font-weight:800;font-size:1rem;margin-top:.4rem;">₹<?= number_format($total,2) ?></div>
          <div style="color:var(--muted);font-size:.72rem;margin-top:.2rem;"><?= $b['payment_status']==='paid'?'✅ Paid':'⏳ Pending' ?></div>
        </div>
      </div>
    <?php endwhile; endif; ?>

  <!-- ═══ VEHICLE TRACKING ═══ -->
  <?php elseif($tab==='tracking'):
    $res=$db->query("SELECT * FROM listings WHERE user_id=$uid AND (type='parking' OR type='rental') ORDER BY created_at DESC");
    if($res->num_rows===0): ?>
      <div class="empty"><div class="ei">📡</div><p>No parking or rental listings to track.</p></div>
    <?php else:
      echo '<div class="alert alert-warn" style="margin-bottom:1.2rem;">📡 <strong>Live Tracking Panel</strong> — Mark vehicles as taken when receiver collects, and returned when they bring it back. Extra charges apply for late returns.</div>';
      while($l=$res->fetch_assoc()):
        $isOverdue=$l['is_taken']&&$l['return_due']&&strtotime('now')>strtotime($l['return_due']);
        $hoursLate=$isOverdue?ceil((time()-strtotime($l['return_due']))/3600):0;
        $extraEst=$isOverdue?$hoursLate*(float)($l['late_fee_hour']??$l['late_fee_rental']??50):0;
    ?>
      <div class="track-card <?= $isOverdue?'overdue-banner':'' ?>">
        <div class="track-status">
          <div class="status-dot <?= $l['is_taken']?($isOverdue?'dot-red':'dot-yellow'):'dot-green' ?>"></div>
          <strong><?= clean($l['name']) ?></strong>
          <span class="badge <?= $l['is_taken']?($isOverdue?'badge-overdue':'badge-taken'):'badge-completed' ?>">
            <?= $l['is_taken']?($isOverdue?'⚠️ OVERDUE':'🟡 TAKEN'):'🟢 AVAILABLE' ?>
          </span>
          <span class="badge badge-<?= $l['type'] ?>"><?= strtoupper($l['type']) ?></span>
        </div>
        <div style="color:var(--muted);font-size:.82rem;margin-bottom:.8rem;">
          📍 <?= clean($l['location']) ?>
          <?php if($l['is_taken']&&$l['taken_at']): ?>
            · 🕐 Taken at: <?= date('d M H:i',strtotime($l['taken_at'])) ?>
            · ⏰ Due: <?= date('d M H:i',strtotime($l['return_due'])) ?>
          <?php endif; ?>
          <?php if($isOverdue): ?>
            <br><span style="color:#fca5a5;font-weight:600;">⚠️ Overdue by <?= $hoursLate ?> hour(s) — Estimated extra charge: ₹<?= number_format($extraEst,2) ?></span>
          <?php endif; ?>
        </div>
        <?php if(!$l['is_taken']): ?>
          <!-- Show mark as taken with duration -->
          <form method="GET" style="display:inline-flex;gap:.7rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="tab" value="tracking">
            <input type="hidden" name="mark_taken" value="<?= $l['id'] ?>">
            <select name="dur" style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:.42rem .8rem;color:var(--text);font-size:.84rem;">
              <option>1 Hour</option><option>3 Hours</option><option>6 Hours</option>
              <option selected>1 Day</option><option>3 Days</option><option>1 Week</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">🚦 Mark as Taken</button>
          </form>
        <?php else: ?>
          <a href="?tab=tracking&mark_returned=<?= $l['id'] ?>" class="btn btn-outline btn-sm"
             onclick="return confirm('Mark as returned? Extra charges will be calculated if overdue.')">
            ✅ Mark as Returned
          </a>
        <?php endif; ?>
      </div>
    <?php endwhile; endif; ?>

  <?php endif; ?>
</div></div>

<script>
function prevImg(input){
    if(!input.files[0])return;
    const r=new FileReader();
    r.onload=e=>{const i=document.getElementById('img-preview');i.src=e.target.result;i.style.display='block';};
    r.readAsDataURL(input.files[0]);
}
</script>
<?php require_once '../includes/footer.php'; ?>
