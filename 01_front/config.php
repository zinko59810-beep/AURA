<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aura";

$conn = new mysqli($host, $user, $pass, $dbname);

if($conn->connect_error){
    die("Connection Failed");
}

?>