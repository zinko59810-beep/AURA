<?php

session_start();

$_SESSION = [];

// Session အားလုံးဖျက်
session_destroy();

// Login Page ပြန်ပို့
header("Location: /AURA/auth");
exit;