<?php
/**
 * public/admin/logout.php
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;

Session::set('admin_id', null);
Session::set('admin_name', null);
// Agar Session::destroy() poore session ko clear karta hai (user session bhi),
// to sirf admin_* keys unset karna behtar hai jab tak admin/user sessions alag na ho.

header('Location: index.php');
exit;