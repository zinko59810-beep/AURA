<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    isset($_GET['lang']) &&
    in_array($_GET['lang'], ['ja', 'en'], true)
) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLang = $_SESSION['lang'] ?? 'ja';

$lang = require __DIR__ . '/' . $currentLang . '.php';