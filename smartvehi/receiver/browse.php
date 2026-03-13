<?php
require_once '../includes/config.php';
requireRole('receiver');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$service=$_SESSION['chosen_service']??'parking';
$search=trim($_GET['search']??'');
$sort=$_GET['sort']??'newest';
$typeEsc=$db->real_escape_string($service);
$where="l.type='$typeEsc' AND l.is_active=1";
if($search!==''){$s=$db->real_escape_string($search);$where.=" AND (l.name LIKE '%$s%' OR l.location LIKE '%$s%')";}
$order=match($sort){'price-low'=>'COALESCE(l.price_hour,l.price_basic,l.rent_hour,0) ASC','price-high'=>'COALESCE(l.price_hour,l.price_basic,l.rent_hour,0) DESC','rating'=>'avg_rating DESC',default=>'l.created_at DESC'};
$rows=$db->query("SELECT l.*,u.full_name AS pname,ROUND(AVG(r.rating),1) AS avg_rating,COUNT(r.id) AS rcnt FROM listings l JOIN users u ON l.user_id=u.id LEFT JOIN reviews r ON r.listing_id=l.id WHERE $where GROUP BY l.id ORDER BY $order");
$myBC=(int)$db->query("SELECT COUNT(*) FROM bookings WHERE user_id={$user['id']}")->fetch_row()[0];
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$pageTitle='Find '.ucfirst($service); require_once '../includes/header.php'; ?>
<div class="page fu" style="padding-top:5.5rem;">
<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;"><?= $icons[$service] ?> Find <?= ucfirst($service) ?></h2>
      <div style="color:var(--muted);font-size:.87rem;margin-top:.2rem;"><?= clean($user['full_name']) ?> · <?= $rows->num_rows ?> result(s)</div>
    </div>
    <div style="display:flex;gap:.7rem;flex-wrap:wrap;">
      <a href="<?= $B ?>/receiver/my_bookings.php" class="nbtn">📅 My Bookings (<?= $myBC ?>)</a>
      <a href="<?= $B ?>/service.php?role=receiver" class="nbtn">Change Service</a>
    </div>
  </div>
  <form method="GET">
    <div class="filter-bar">
      <input type="text" name="search" placeholder="🔍 Search by name or location…" value="<?= clean($search) ?>">
      <select name="sort">
        <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest First</option>
        <option value="price-low" <?= $sort==='price-low'?'selected':'' ?>>Price: Low → High</option>
        <option value="price-high" <?= $sort==='price-high'?'selected':'' ?>>Price: High → Low</option>
        <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Highest Rated</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Search</button>
      <?php if($search||$sort!=='newest'): ?><a href="browse.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
    </div>
  </form>
  <?php if($rows->num_rows===0): ?>
    <div class="empty"><div class="ei">🔍</div><p>No <?= $service ?> services found.<?= $search?' Try a different term.':' Check back soon!' ?></p></div>
  <?php else: ?>
  <div class="grid3">
  <?php while($l=$rows->fetch_assoc()):
    $price=match($l['type']){'parking'=>'₹'.(float)$l['price_hour'].'/hr · ₹'.(float)$l['price_day'].'/day','washing'=>'Basic: ₹'.(float)$l['price_basic'].' · Full: ₹'.(float)$l['price_full'],'rental'=>'₹'.(float)$l['rent_hour'].'/hr · ₹'.(float)$l['rent_day'].'/day',default=>'—'};
    $extra=match($l['type']){'parking'=>(int)$l['total_slots'].' slots · '.clean($l['vehicle_type']??''),'washing'=>clean($l['services_offered']??''),'rental'=>clean($l['rental_type']??'').' · '.clean($l['vehicle_model']??'').' · '.clean($l['fuel_type']??''),default=>''};
    $isTaken=$l['is_taken']&&in_array($l['type'],['parking','rental']); ?>
  <div class="lcard">
    <div class="lcard-img">
      <?php if($l['image']): ?><img src="<?= $B ?>/<?= clean($l['image']) ?>" alt=""><?php else: echo $icons[$l['type']]; endif; ?>
      <?php if($isTaken): ?><div class="taken-overlay">🔴 TAKEN</div><?php endif; ?>
    </div>
    <div class="lcard-body">
      <span class="badge badge-<?= $l['type'] ?>"><?= strtoupper($l['type']) ?></span>
      <div class="lcard-title" style="margin-top:.45rem;"><?= clean($l['name']) ?></div>
      <div class="lcard-meta">📍 <?= clean($l['location']) ?> · by <?= clean($l['pname']) ?></div>
      <div class="lcard-extra"><?= $extra ?></div>
      <div class="lcard-price"><?= $price ?></div>
      <div class="lcard-rating"><?= stars((float)$l['avg_rating'],(int)$l['rcnt']) ?></div>
      <div class="lcard-actions">
        <?php if(!$isTaken): ?>
          <a href="<?= $B ?>/receiver/book.php?id=<?= $l['id'] ?>" class="lcard-btn lcard-btn-p">Book Now</a>
        <?php else: ?>
          <span class="lcard-btn" style="background:rgba(239,68,68,.1);color:#f87171;cursor:default;">Unavailable</span>
        <?php endif; ?>
        <a href="<?= $B ?>/receiver/listing.php?id=<?= $l['id'] ?>" class="lcard-btn lcard-btn-s">Details</a>
        <a href="<?= $B ?>/receiver/reviews.php?id=<?= $l['id'] ?>" class="lcard-btn lcard-btn-s" style="flex:.5;color:var(--gold);">⭐</a>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div></div>
<?php require_once '../includes/footer.php'; ?>
