<?php
// ============================================================
// includes/config.php
// ============================================================
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smartvehi');
define('SITE_NAME', 'SmartVehi');

if (session_status() === PHP_SESSION_NONE) session_start();

function baseUrl(): string {
    static $base = null;
    if ($base !== null) return $base;
    $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $parts  = explode('/', trim(dirname($script), '/'));
    $path   = '';
    foreach ($parts as $seg) {
        $path .= '/' . $seg;
        if (strtolower($seg) === 'smartvehi') break;
    }
    $base = $proto . '://' . $host . $path;
    return $base;
}

function getDB(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('<div style="font-family:sans-serif;max-width:600px;margin:3rem auto;
             background:#1a0000;color:#ff8080;padding:2rem;border-radius:12px;border:1px solid #ff4444;">
             <h2>⚠️ Database Connection Failed</h2>
             <p>' . htmlspecialchars($conn->connect_error) . '</p>
             <ul style="margin-top:1rem;line-height:2">
               <li>Start MySQL in XAMPP Control Panel</li>
               <li>Import database.sql in phpMyAdmin</li>
               <li>Check DB_USER/DB_PASS in includes/config.php</li>
             </ul></div>');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function clean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB(); $id = (int)$_SESSION['user_id'];
    $s  = $db->prepare('SELECT * FROM users WHERE id=?');
    $s->bind_param('i', $id); $s->execute();
    return $s->get_result()->fetch_assoc() ?: null;
}
function redirect(string $path): void {
    if (strncmp($path, 'http', 4) !== 0)
        $path = baseUrl() . '/' . ltrim($path, '/');
    header('Location: ' . $path); exit;
}
function requireLogin(): void { if (!isLoggedIn()) redirect('login.php'); }
function requireRole(string $role): void {
    requireLogin();
    $u = getCurrentUser();
    if (!$u || $u['role'] !== $role)
        redirect($role === 'provider' ? 'receiver/browse.php' : 'provider/dashboard.php');
}
function stars(float $avg, int $count): string {
    if ($count === 0) return '<span style="color:var(--muted)">No reviews</span>';
    $f = (int)round($avg);
    return str_repeat('⭐',$f).str_repeat('☆',5-$f)
         .' <span style="color:var(--muted);font-size:.8rem">'.number_format($avg,1).' ('.$count.')</span>';
}
function calcCost(array $l, string $dur): float {
    $h = (float)($l['price_hour'] ?? $l['price_basic'] ?? $l['rent_hour'] ?? 0);
    $d = (float)($l['price_day']  ?? $l['price_full']  ?? $l['rent_day']  ?? 0);
    return match($dur) {
        '1 Hour'  => $h,   '3 Hours' => $h*3,
        '6 Hours' => $h*6, '1 Day'   => $d,
        '3 Days'  => $d*3, '1 Week'  => $d*7,
        default   => $h,
    };
}
function genReceipt(): string {
    return 'SV-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
}
function payLabel(string $mode, ?string $app=null): string {
    return match($mode) {
        'credit_card' => '💳 Credit Card',
        'debit_card'  => '💳 Debit Card',
        'upi'         => '📲 UPI (' . ($app ?? 'UPI') . ')',
        'cash'        => '💵 Cash on Delivery',
        default       => $mode,
    };
}
?>
