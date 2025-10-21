<?php

// Include PHPMailer classes
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_otp_email($email, $otp)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smartleaderflutter@gmail.com';  
        $mail->Password   = 'pbhp hvaw mpyo lilq';      
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('smartleaderflutter@gmail.com', 'Smartleader');
        $mail->addAddress($email);

        // Content
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
	
    function user_signup()
    {
        extract($_POST); 
    
        $validation = ['username' => $username, 'email' => $email, 'phone' => $phone];
        $valid_check = array_search(null, $validation);
        if ($valid_check) {
            $message['result'] = $valid_check . " is Empty";
            echo json_encode($message);
            die();
        }
    
        $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email'");
        if (mysqli_num_rows($check) > 0) {
            $message['result'] = "Email already registered";
            echo json_encode($message);
            die();
        }
    
        $otp = rand(100000, 999999);
    
        if (!$this->send_otp_email($email, $otp)) {
            $message['result'] = "Failed to send OTP";
            echo json_encode($message);
            die();
        }
    
        $insert = mysqli_query($this->conn, "INSERT INTO `signup`(`username`, `email`, `phone`, `otp`, `created_at`) 
        VALUES ('$username','$email','$phone','$otp', NOW())");
    
        if ($insert) {
            $message['result'] = "OTP Sent to your Email";
        } else {
            $message['result'] = "Failed to initiate signup";
        }
    
        echo json_encode($message);
    }

