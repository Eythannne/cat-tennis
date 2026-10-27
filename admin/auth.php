<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

csrf_check();

require_once '../src/init.php';
require_once __DIR__ . '/_layout.php';