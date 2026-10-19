<?php
require_once __DIR__ . '/../backend/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    http_response_code(400);
    exit('Invalid logout request.');
}

Session::signOut();
header('Location: login.php');
exit;