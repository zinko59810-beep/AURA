<?php

// DB接続ファイル

//本番表 は　
// ・DB名　　　　：sys1_26_aura
// ・DBユーザー　：sys1_26_aura

// define('DB_USERNAME','sys1_26_aura');
// define('DSN','mysql:host=localhost;dbname=sys1_26_aura;charset=utf8mb4');
define('DB_USERNAME','aura');
define('DB_PASSWORD','aura');
define('DSN','mysql:host=localhost;dbname=student_db;charset=utf8mb4');

// DB connect လုပ်တဲ့ function
function db_connect(): PDO
{

// new PDO(...) PHP နဲ့ MySQL ချိတ်တဲ့ object
      $dbh = new PDO(
        DSN,
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
       
    );
     return $dbh;
}




