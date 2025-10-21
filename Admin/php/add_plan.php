<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../common/config.php');   // DB + keys
require('../../vendor/autoload.php'); 
use App\Razorpay\RazorpayService;

extract($_POST);

try {
    $razorpay = new RazorpayService();

    // Create plan in Razorpay
    $plan = $razorpay->createPlan($name, $amount, $currency, $interval, $period);

    if (!$plan) {
        throw new Exception("Failed to create plan in Razorpay.");
    }

    // Get plan ID (object property)
    $plan_id = $plan->id;

    // Insert into DB
    $query = mysqli_query($conn,
        "INSERT INTO `plans`(`id`, `name`, `amount`, `currency`, `interval`, `period`, `status`) 
         VALUES ('$plan_id','$name','$amount','$currency','$interval','$period','$status')"
    );

    if ($query) {
        echo '<div class="alert alert-success">
        <strong>Plan Added Successfully!</strong></div>';
        echo '<script>setTimeout(function(){location.href="show_plans.php"},1000)</script>';
    } else {
        throw new Exception("DB Insert Failed: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">
    <strong>Error: '.$e->getMessage().'</strong></div>';
}
