<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
session_open();
if (csrf_ok($_GET['t'] ?? null)) {
    $_SESSION = [];
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/']);
    session_destroy();
}
redirect('/admin/login');
