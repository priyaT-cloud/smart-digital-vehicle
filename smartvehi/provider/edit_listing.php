<?php
require_once '../includes/config.php';
requireRole('provider');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$id=(int)($_GET['id']??0);
$s=$db->prepare('SELECT * FROM listings WHERE id=? AND user_id=?');
$s->bind_param('ii',$id,$user['id']); $s->execute();
$l=$s->get_result()->fetch_assoc();
if(!$l) redirect('provider/dashboard.php?tab=my');
$error=''; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=clean($_POST['name']??''); $location=clean($_POST['location']??''); $desc=clean($_POST['description']??'');
    if(!$name||!$location){$error='Name and location required.';}
    else{
        $imagePath=$l['image'];
        if(!empty($_FILES['image']['tmp_name'])&&$_FILES['image']['error']===UPLOAD_ERR_OK){
            $allowed=['image/jpeg','image/png','image/webp','image/gif'];
            $ftype=mime_content_type($_FILES['image']['tmp_name']);
            if(in_array($ftype,$allowed,true)&&$_FILES['image']['size']<=5*1024*1024){
                $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
                $fn='uploads/'.uniqid('img_',true).'.'.$ext;
                $dest=dirname(__DIR__).DIRECTORY_SEPARATOR.$fn;
                if(move_uploaded_file($_FILES['image']['tmp_name'],$dest)) $imagePath=$fn;
            }
        }
        if($l['type']==='parking'){
            $ph=(float)($_POST['price_hour']??0); $pd=(float)($_POST['price_day']??0);
            $sl=(int)($_POST['total_slots']??0); $vt=clean($_POST['vehicle_type']??'Both');
            $lf=(float)($_POST['late_fee_hour']??50);
            // name=s loc=s desc=s img=s ph=d pd=d sl=i vt=s lf=d id=i → ssssddisdi
            $s=$db->prepare('UPDATE listings SET name=?,location=?,description=?,image=?,price_hour=?,price_day=?,total_slots=?,vehicle_type=?,late_fee_hour=? WHERE id=?');
            $s->bind_param('ssssddisdi',$name,$location,$desc,$imagePath,$ph,$pd,$sl,$vt,$lf,$id);
        } elseif($l['type']==='washing'){
            $pb=(float)($_POST['price_basic']??0); $pf=(float)($_POST['price_full']??0);
            $svo=clean($_POST['services_offered']??''); $pdf=(float)($_POST['pickup_drop_fee']??100);
            // name=s loc=s desc=s img=s pb=d pf=d svo=s pdf=d id=i → ssssddsd i
            $s=$db->prepare('UPDATE listings SET name=?,location=?,description=?,image=?,price_basic=?,price_full=?,services_offered=?,pickup_drop_fee=? WHERE id=?');
            $s->bind_param('ssssddsdi',$name,$location,$desc,$imagePath,$pb,$pf,$svo,$pdf,$id);
        } else {
            $rt=clean($_POST['rental_type']??''); $vm=clean($_POST['vehicle_model']??'');
            $rh=(float)($_POST['rent_hour']??0); $rd=(float)($_POST['rent_day']??0);
            $ft=clean($_POST['fuel_type']??'Petrol'); $lf=(float)($_POST['late_fee_rental']??100);
            $s=$db->prepare('UPDATE listings SET name=?,location=?,description=?,image=?,rental_type=?,vehicle_model=?,rent_hour=?,rent_day=?,fuel_type=?,late_fee_rental=? WHERE id=?');
            $s->bind_param('ssssssddsd i',$name,$location,$desc,$imagePath,$rt,$vm,$rh,$rd,$ft,$lf,$id);
            $s=$db->prepare('UPDATE listings SET name=?,location=?,description=?,image=?,rental_type=?,vehicle_model=?,rent_hour=?,rent_day=?,fuel_type=?,late_fee_rental=? WHERE id=?');
            $s->bind_param('ssssssddsdi',$name,$location,$desc,$imagePath,$rt,$vm,$rh,$rd,$ft,$lf,$id);
        }
        if($s->execute()){
            $s2=$db->prepare('SELECT * FROM listings WHERE id=?'); $s2->bind_param('i',$id); $s2->execute();
            $l=$s2->get_result()->fetch_assoc(); $success='Updated successfully!';
        } else $error='Update failed.';
    }
}
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$pageTitle='Edit Listing'; require_once '../includes/header.php'; ?>
<div class="page fu" style="padding-top:5.5rem;">
<div class="container" style="max-width:780px;">
  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.8rem;">
    <a href="<?= $B ?>/provider/dashboard.php?tab=my" class="nbtn">← Back</a>
    <h2 style="font-size:1.5rem;font-weight:800;"><?= $icons[$l['type']] ?> Edit Listing</h2>
  </div>
  <?php if($error): ?><div class="alert alert-error">⚠️ <?= clean($error) ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
  <div class="card">
    <form method="POST" enctype="multipart/form-data">
      <div class="form-row">
        <div class="fg"><label>Name *</label><input type="text" name="name" value="<?= clean($l['name']) ?>" required></div>
        <div class="fg"><label>Location *</label><input type="text" name="location" value="<?= clean($l['location']) ?>" required></div>
      </div>
      <?php if($l['type']==='parking'): ?>
        <div class="form-row">
          <div class="fg"><label>Price/Hour (₹)</label><input type="number" name="price_hour" step="0.01" min="0" value="<?= (float)$l['price_hour'] ?>"></div>
          <div class="fg"><label>Price/Day (₹)</label><input type="number" name="price_day" step="0.01" min="0" value="<?= (float)$l['price_day'] ?>"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Total Slots</label><input type="number" name="total_slots" min="1" value="<?= (int)$l['total_slots'] ?>"></div>
          <div class="fg"><label>Vehicle Type</label><select name="vehicle_type"><?php foreach(['2-Wheeler','4-Wheeler','Both'] as $o): ?><option <?= $l['vehicle_type']===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="fg"><label>Late Fee/Hour (₹)</label><input type="number" name="late_fee_hour" step="0.01" value="<?= (float)$l['late_fee_hour'] ?>"></div>
      <?php elseif($l['type']==='washing'): ?>
        <div class="form-row">
          <div class="fg"><label>Basic Price (₹)</label><input type="number" name="price_basic" step="0.01" min="0" value="<?= (float)$l['price_basic'] ?>"></div>
          <div class="fg"><label>Full Service (₹)</label><input type="number" name="price_full" step="0.01" min="0" value="<?= (float)$l['price_full'] ?>"></div>
        </div>
        <div class="fg"><label>Services Offered</label><input type="text" name="services_offered" value="<?= clean($l['services_offered']??'') ?>"></div>
        <div class="fg"><label>Pickup &amp; Drop Fee (₹)</label><input type="number" name="pickup_drop_fee" step="0.01" value="<?= (float)$l['pickup_drop_fee'] ?>"></div>
      <?php else: ?>
        <div class="form-row">
          <div class="fg"><label>Vehicle Category</label><select name="rental_type"><?php foreach(['2-Wheeler (Bike/Scooter)','3-Wheeler (Auto)','4-Wheeler (Car)'] as $o): ?><option <?= $l['rental_type']===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?></select></div>
          <div class="fg"><label>Vehicle Model</label><input type="text" name="vehicle_model" value="<?= clean($l['vehicle_model']??'') ?>"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Rent/Hour (₹)</label><input type="number" name="rent_hour" step="0.01" min="0" value="<?= (float)$l['rent_hour'] ?>"></div>
          <div class="fg"><label>Rent/Day (₹)</label><input type="number" name="rent_day" step="0.01" min="0" value="<?= (float)$l['rent_day'] ?>"></div>
        </div>
        <div class="form-row">
          <div class="fg"><label>Fuel Type</label><select name="fuel_type"><?php foreach(['Petrol','Electric','Diesel','CNG'] as $o): ?><option <?= $l['fuel_type']===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?></select></div>
          <div class="fg"><label>Late Fee/Hour (₹)</label><input type="number" name="late_fee_rental" step="0.01" value="<?= (float)$l['late_fee_rental'] ?>"></div>
        </div>
      <?php endif; ?>
      <div class="fg"><label>Description</label><textarea name="description"><?= clean($l['description']??'') ?></textarea></div>
      <div class="fg">
        <label>Update Image (leave blank to keep current)</label>
        <?php if($l['image']): ?><div style="margin-bottom:.7rem;"><img src="<?= $B ?>/<?= clean($l['image']) ?>" style="max-height:120px;border-radius:8px;"></div><?php endif; ?>
        <input type="file" name="image" accept="image/*" style="background:var(--bg);border:1px solid var(--border2);border-radius:8px;padding:.5rem .8rem;width:100%;color:var(--muted);">
      </div>
      <div style="display:flex;gap:.8rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">✅ Save Changes</button>
        <a href="<?= $B ?>/provider/dashboard.php?tab=my" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div></div>
<?php require_once '../includes/footer.php'; ?>
