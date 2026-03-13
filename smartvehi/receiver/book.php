<?php
require_once '../includes/config.php';
requireRole('receiver');
$user=getCurrentUser(); $db=getDB(); $B=baseUrl();
$id=(int)($_GET['id']??0);
$s=$db->prepare('SELECT l.*,u.full_name AS pname FROM listings l JOIN users u ON l.user_id=u.id WHERE l.id=? AND l.is_active=1');
$s->bind_param('i',$id); $s->execute();
$l=$s->get_result()->fetch_assoc();
if(!$l) redirect('receiver/browse.php');
$error=''; $today=date('Y-m-d');
$hr=(float)($l['price_hour']??$l['price_basic']??$l['rent_hour']??0);
$dr=(float)($l['price_day']??$l['price_full']??$l['rent_day']??0);
$icons=['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $custName=clean($_POST['customer_name']??'');
    $custPhone=clean($_POST['customer_phone']??'');
    $date=$_POST['booking_date']??'';
    $dur=clean($_POST['duration']??'');
    $notes=clean($_POST['notes']??'');
    $payMode=clean($_POST['payment_mode']??'');
    $upiApp=clean($_POST['upi_app']??'');
    $pickupDrop=(int)($_POST['pickup_drop']??0);
    $validModes=['credit_card','debit_card','upi','cash'];
    if(!$custName||!$custPhone||!$date||!$dur||!$payMode){$error='Please fill in all required fields.';}
    elseif(!in_array($payMode,$validModes,true)){$error='Select a valid payment mode.';}
    elseif($date<$today){$error='Booking date cannot be in the past.';}
    else{
        $cost=calcCost($l,$dur);
        if($pickupDrop&&$l['type']==='washing') $cost+=(float)($l['pickup_drop_fee']??100);
        $receiptNo=genReceipt();
        $uid=$user['id'];
        $s2=$db->prepare('INSERT INTO bookings (listing_id,user_id,customer_name,customer_phone,booking_date,duration,notes,cost,payment_mode,upi_app,pickup_drop,receipt_no) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
        $s2->bind_param('iisssssdsssi',$id,$uid,$custName,$custPhone,$date,$dur,$notes,$cost,$payMode,$upiApp,$pickupDrop,$receiptNo);
        // Correct: i i s s s s s d s s i s = 12
        $s2=$db->prepare('INSERT INTO bookings (listing_id,user_id,customer_name,customer_phone,booking_date,duration,notes,cost,payment_mode,upi_app,pickup_drop,receipt_no) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
        $s2->bind_param('iisssssdsssi',$id,$uid,$custName,$custPhone,$date,$dur,$notes,$cost,$payMode,$upiApp,$pickupDrop,$receiptNo);
        if($s2->execute()){
            $_SESSION['last_booking_id']=$db->insert_id;
            redirect('receiver/receipt.php?id='.$db->insert_id);
        } else {$error='Booking failed — '.$db->error;}
    }
}
$pageTitle='Book Service'; require_once '../includes/header.php'; ?>

<div class="page fu" style="padding-top:5.5rem;">
<div class="container" style="max-width:520px;">

  <!-- Listing summary -->
  <div class="card-sm" style="display:flex;gap:1rem;align-items:center;margin-bottom:1.5rem;">
    <div style="font-size:2.3rem;flex-shrink:0;"><?= $icons[$l['type']] ?></div>
    <div>
      <div style="font-weight:700;font-size:.97rem;"><?= clean($l['name']) ?></div>
      <div style="color:var(--muted);font-size:.79rem;">📍 <?= clean($l['location']) ?> · by <?= clean($l['pname']) ?></div>
    </div>
  </div>

  <div class="card">
    <h2 style="font-size:1.45rem;font-weight:800;margin-bottom:.3rem;">Book Service</h2>
    <p style="color:var(--muted);font-size:.86rem;margin-bottom:1.3rem;">Fill details, choose payment &amp; confirm.</p>
    <?php if($error): ?><div class="alert alert-error">⚠️ <?= clean($error) ?></div><?php endif; ?>

    <form method="POST" id="bookForm">
      <div class="form-row">
        <div class="fg"><label>Your Name *</label><input type="text" name="customer_name" value="<?= clean($user['full_name']) ?>" required></div>
        <div class="fg"><label>Phone Number *</label><input type="tel" name="customer_phone" value="<?= clean($user['phone']??'') ?>" required></div>
      </div>
      <div class="form-row">
        <div class="fg"><label>Booking Date *</label><input type="date" name="booking_date" min="<?= $today ?>" value="<?= $today ?>" required></div>
        <div class="fg"><label>Duration *</label>
          <select name="duration" id="dur" onchange="updCost()">
            <option>1 Hour</option><option>3 Hours</option><option>6 Hours</option>
            <option selected>1 Day</option><option>3 Days</option><option>1 Week</option>
          </select>
        </div>
      </div>
      <?php if($l['type']==='washing'): ?>
      <div class="fg">
        <label style="display:flex;align-items:center;gap:.7rem;cursor:pointer;">
          <input type="checkbox" name="pickup_drop" id="pdcheck" value="1" onchange="updCost()" style="width:auto;margin:0;">
          <span>Add Pickup &amp; Drop service (+₹<?= number_format((float)$l['pickup_drop_fee'],2) ?>)</span>
        </label>
      </div>
      <?php endif; ?>
      <div class="fg"><label>Special Requests</label><textarea name="notes" placeholder="Any special instructions…"></textarea></div>

      <!-- COST DISPLAY -->
      <div class="cost-box">
        <div class="clbl">Estimated Total</div>
        <div class="cval" id="cost-display">₹<?= number_format($hr,2) ?></div>
        <div style="font-size:.75rem;color:var(--muted);margin-top:.3rem;" id="cost-breakdown"></div>
      </div>

      <!-- PAYMENT MODE -->
      <div style="margin-bottom:1.2rem;">
        <label style="display:block;font-size:.74rem;color:var(--muted);font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.7rem;">Payment Mode *</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
          <?php
          $payOpts=[
            ['credit_card','💳','Credit Card'],
            ['debit_card','🏧','Debit Card'],
            ['upi','📲','UPI'],
            ['cash','💵','Cash on Delivery'],
          ];
          foreach($payOpts as [$val,$icon,$lbl]): ?>
          <div class="pay-opt" data-val="<?= $val ?>" onclick="selPay(this)"
               style="background:var(--surface2);border:2px solid var(--border);border-radius:12px;padding:.9rem 1rem;cursor:pointer;text-align:center;transition:var(--ease);">
            <div style="font-size:1.6rem;margin-bottom:.3rem;"><?= $icon ?></div>
            <div style="font-size:.8rem;font-weight:600;"><?= $lbl ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="payment_mode" id="pay_mode_input" required>

        <!-- UPI sub-options -->
        <div id="upi-section" style="display:none;margin-top:.9rem;">
          <label style="display:block;font-size:.74rem;color:var(--muted);font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem;">Choose UPI App</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
            <?php foreach([['gpay','Google Pay','💚'],['phonepe','PhonePe','💜'],['paytm','Paytm','💙'],['bhim','BHIM UPI','🇮🇳']] as [$uval,$ulbl,$uico]): ?>
            <div class="upi-opt" data-uval="<?= $uval ?>" onclick="selUpi(this)"
                 style="background:var(--surface2);border:2px solid var(--border);border-radius:10px;padding:.6rem;cursor:pointer;font-size:.82rem;font-weight:600;text-align:center;transition:var(--ease);">
              <?= $uico ?> <?= $ulbl ?>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="upi_app" id="upi_app_input">
        </div>

        <!-- Card details hint -->
        <div id="card-section" style="display:none;margin-top:.9rem;">
          <div class="alert alert-warn" style="font-size:.82rem;">💳 Card payment will be collected securely at the time of service.</div>
        </div>
        <!-- Cash hint -->
        <div id="cash-section" style="display:none;margin-top:.9rem;">
          <div class="alert alert-warn" style="font-size:.82rem;">💵 Pay cash directly to the service provider when you arrive.</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="submitBtn">✓ Confirm &amp; Pay</button>
      <a href="<?= $B ?>/receiver/browse.php" class="btn btn-outline btn-block" style="margin-top:.8rem;">← Cancel</a>
    </form>
  </div>
</div></div>

<script>
const HR=<?= $hr ?>, DR=<?= $dr ?>;
const PD_FEE=<?= (float)($l['pickup_drop_fee']??0) ?>;
const TYPE='<?= $l['type'] ?>';
const COSTS={'1 Hour':HR,'3 Hours':HR*3,'6 Hours':HR*6,'1 Day':DR,'3 Days':DR*3,'1 Week':DR*7};

function updCost(){
    const dur=document.getElementById('dur').value;
    let base=COSTS[dur]||HR;
    let extra=0;
    const pd=document.getElementById('pdcheck');
    if(pd&&pd.checked) extra=PD_FEE;
    const total=base+extra;
    document.getElementById('cost-display').textContent='₹'+total.toFixed(2);
    let bd='Base: ₹'+base.toFixed(2);
    if(extra>0) bd+=' + Pickup/Drop: ₹'+extra.toFixed(2);
    document.getElementById('cost-breakdown').textContent=bd;
}
updCost();

function selPay(el){
    document.querySelectorAll('.pay-opt').forEach(e=>{
        e.style.borderColor='var(--border)';e.style.background='var(--surface2)';});
    el.style.borderColor='var(--accent)';el.style.background='rgba(0,229,184,.08)';
    const val=el.dataset.val;
    document.getElementById('pay_mode_input').value=val;
    document.getElementById('upi-section').style.display=val==='upi'?'block':'none';
    document.getElementById('card-section').style.display=(val==='credit_card'||val==='debit_card')?'block':'none';
    document.getElementById('cash-section').style.display=val==='cash'?'block':'none';
}
function selUpi(el){
    document.querySelectorAll('.upi-opt').forEach(e=>{
        e.style.borderColor='var(--border)';e.style.background='var(--surface2)';e.style.color='';});
    el.style.borderColor='var(--accent3)';el.style.background='rgba(124,92,252,.1)';el.style.color='#a78bfa';
    document.getElementById('upi_app_input').value=el.dataset.uval;
}
document.getElementById('bookForm').addEventListener('submit',function(e){
    if(!document.getElementById('pay_mode_input').value){
        e.preventDefault();alert('Please select a payment mode.');return;}
    if(document.getElementById('pay_mode_input').value==='upi'&&!document.getElementById('upi_app_input').value){
        e.preventDefault();alert('Please select a UPI app.');return;}
});
</script>
<?php require_once '../includes/footer.php'; ?>
