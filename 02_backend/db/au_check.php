<?php
//ログイン確認用。ログインが必要な画面の一番上で require_once する。
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['emp_id'])) {
    header('Location: ../public/login.php');
    exit;
}