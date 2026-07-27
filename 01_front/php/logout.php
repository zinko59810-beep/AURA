<?php

session_start();

$_SESSION = [];

// Session အားလုံးဖျက်
session_destroy();

// HOME Page ပြန်ပို့
header("Location: /AURA/home");
exit;