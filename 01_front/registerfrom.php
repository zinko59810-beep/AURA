<?php

session_start();
require_once 'config.php';

if(isset($_POST["Register"])){
        $username =$_POST ["Username"];
        $password=password_hash($_POST ["Password"], PASSWORD_DEFAULT);
        $studentid =$_POST ["Student_id"];
        $class =$_POST ["class"];
        $hobby =$_POST["Hobby"];
        $subject =$_POST ["Favourite_Subject"];
        
        $checkstudentid = $conn->query("SELECT Student_id FROM users WHERE Student_id ='$studentid'");

        if($checkstudentid ->num_rows>0){
            $_SESSION ['Regitster_error'] = "Student Id is already exit!";
            $_SESSION ['active_form'] = 'register';
        }else{
            $conn->query("INSERT INTO users (Username,Password,Student_id,class,Hobby,Favourite_Subject) VALUES ('$username', '$password', '$studentid', '$class','$hobby', '$subject' )");
        }
        header("Location: index.php");
        exit();
} 

    if(isset($_POST['Login'])){
        $username = $_POST["Username"];
        $password = $_POST["Password"];
        $result = $conn ->query("SELECT * from users where Student_id= '$studentid'");
        $users =  $result->fetch_assoc();
        if(password_verify($password,$username['password'])){
            $_SESSION['Username'] = $username['Username'];
            $_SESSION['Password'] = $password['Password'];

            if($username['class'] === 'Html/css/javascript'){
                header("Location: front.php");
            }elseif($username['class'] === 'php'){
                header("Lcation: backend.php");
            }elseif($username['class'] === 'java'){
                header("Location: java.php");
            }elseif($username['class'] === 'database'){
                header("Location: database.php");
            }else{
                header("Location: general.php");
            }
            exit();
        }
    }
    $_SESSION['login_error'] ='Incorrect Username or Password!';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();

?>