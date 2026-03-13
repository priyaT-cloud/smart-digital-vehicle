<?php
require_once '../includes/config.php';
requireRole('provider');
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('provider/dashboard.php');
$user=getCurrentUser(); $db=getDB();
$service=trim($_POST['type']??'');
if(!in_array($service,['parking','washing','rental'],true)) redirect('provider/dashboard.php');
$name=clean($_POST['name']??''); $location=clean($_POST['location']??''); $desc=clean($_POST['description']??'');
if(!$name||!$location) redirect('provider/dashboard.php?tab=add&msg=missing');

$imagePath=null;
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

$ok=false;
if($service==='parking'){
    // Cols: user_id(i) type(s) name(s) location(s) description(s) image(s)
    //       price_hour(d) price_day(d) total_slots(i) vehicle_type(s) late_fee_hour(d)
    // Type string: i s s s s s d d i s d  = "isssssddisd" (11)
    $ph=(float)($_POST['price_hour']??0);
    $pd=(float)($_POST['price_day']??0);
    $sl=(int)($_POST['total_slots']??0);
    $vt=clean($_POST['vehicle_type']??'Both');
    $lf=(float)($_POST['late_fee_hour']??50);
    $s=$db->prepare('INSERT INTO listings (user_id,type,name,location,description,image,price_hour,price_day,total_slots,vehicle_type,late_fee_hour) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $s->bind_param('isssssddisd',$user['id'],$service,$name,$location,$desc,$imagePath,$ph,$pd,$sl,$vt,$lf);
    $ok=$s->execute();

} elseif($service==='washing'){
    // Cols: user_id(i) type(s) name(s) location(s) description(s) image(s)
    //       price_basic(d) price_full(d) services_offered(s) pickup_drop_fee(d)
    // Type string: i s s s s s d d s d  = "isssssddsd" (10)
    $pb=(float)($_POST['price_basic']??0);
    $pf=(float)($_POST['price_full']??0);
    $svo=clean($_POST['services_offered']??'');
    $pdf=(float)($_POST['pickup_drop_fee']??100);
    $s=$db->prepare('INSERT INTO listings (user_id,type,name,location,description,image,price_basic,price_full,services_offered,pickup_drop_fee) VALUES(?,?,?,?,?,?,?,?,?,?)');
    $s->bind_param('isssssddsd',$user['id'],$service,$name,$location,$desc,$imagePath,$pb,$pf,$svo,$pdf);
    $ok=$s->execute();

} elseif($service==='rental'){
    // Cols: user_id(i) type(s) name(s) location(s) description(s) image(s)
    //       rental_type(s) vehicle_model(s) rent_hour(d) rent_day(d) fuel_type(s) late_fee_rental(d)
    // Type string: i s s s s s s s d d s d  = "isssssssddsd" (12)
    $rt=clean($_POST['rental_type']??'');
    $vm=clean($_POST['vehicle_model']??'');
    $rh=(float)($_POST['rent_hour']??0);
    $rd=(float)($_POST['rent_day']??0);
    $ft=clean($_POST['fuel_type']??'Petrol');
    $lf=(float)($_POST['late_fee_rental']??100);
    $s=$db->prepare('INSERT INTO listings (user_id,type,name,location,description,image,rental_type,vehicle_model,rent_hour,rent_day,fuel_type,late_fee_rental) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
    $s->bind_param('isssssssddsd',$user['id'],$service,$name,$location,$desc,$imagePath,$rt,$vm,$rh,$rd,$ft,$lf);
    $ok=$s->execute();
}
redirect($ok?'provider/dashboard.php?tab=my&msg=added':'provider/dashboard.php?tab=add&msg=error');
