<?php

session_start();

// Session အားလုံးဖျက်
session_destroy();

// Login Page ပြန်ပို့
header("Location: auth.php");
exit;