<?php
namespace App\Razorpay;

use Razorpay\Api\Api;

class RazorpayService
{
    protected $api;

    private $keyId = "rzp_live_RQX1UGlbhpIGPP";  
    private $keySecret = "FLSCVpG4dCiddQF4EZkGQfKw";
    private $webhookSecret = "SV9h@tWw56Exh7Z";
    

    public function __construct()
    {
        $this->api = new Api($this->keyId, $this->keySecret);
    }

    // ====================== PLANS ======================

    /**
     * Create a Razorpay subscription plan
     * @param string $name
     * @param int $amount (in INR)
     * @param string $currency
     * @param int $intervalCount
     * @param string $period
     */
    public function createPlan($name, $amount, $currency = 'INR', $intervalCount = 1, $period = 'monthly')
    {
        try {
            $plan = $this->api->plan->create([
                'period' => $period,
                'interval' => $intervalCount,
                'item' => [
                    'name' => $name,
                    'amount' => $amount * 100,
                    'currency' => $currency
                ]
            ]);
            return $plan;
        } catch (\Exception $e) {
            error_log('Razorpay Plan Creation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch a plan by ID
     */
    public function fetchPlan($planId)
    {
        try {
            return $this->api->plan->fetch($planId);
        } catch (\Exception $e) {
            error_log('Razorpay Plan Fetch Error: ' . $e->getMessage());
            return false;
        }
    }

    // ====================== SUBSCRIPTIONS ======================

    /**
     * Create a subscription for a plan
     * @param string $planId
     * @param int $customerNotify (0/1)
     * @param int $totalCount (number of billing cycles)
     */
    public function createSubscription($planId, $customerNotify = 1, $totalCount = 12, $userId = null)
{
    $razorpay_notes = [];
    if ($userId) {
        $razorpay_notes['internal_user_id'] = $userId;
    }
    
    $subscription = $this->api->subscription->create([
        'plan_id' => $planId,
        'total_count' => $totalCount,
        'notes' => $razorpay_notes, // Include the notes
        // ... other parameters ...
    ]);
    return $subscription;
}

    // public function createSubscription($planId, $customerNotify = 1, $totalCount = 12,$userid)
    // {
    //     try {
    //         $subscription = $this->api->subscription->create([
    //             'plan_id' => $planId,
    //             'customer_notify' => $customerNotify,
    //             'total_count'     => $totalCount
    //         ]);
    //         return $subscription;
    //     } catch (\Exception $e) {
    //         error_log('Razorpay Subscription Creation Error: ' . $e->getMessage());
    //         return false;
    //     }
    // }

    /**
     * Fetch a subscription
     */
    public function fetchSubscription($subscriptionId)
    {
        try {
            return $this->api->subscription->fetch($subscriptionId);
        } catch (\Exception $e) {
            error_log('Razorpay Subscription Fetch Error: ' . $e->getMessage());
            return false;
        }
    }

    // ====================== PAYMENTS ======================

    /**
     * Create an order (one-time payment)
     */
    public function createOrder($amount, $currency = 'INR')
    {
        try {
            $receiptId = $this->generateUniqueReceiptId();
            $order = $this->api->order->create([
                'receipt' => $receiptId,
                'amount' => $amount * 100,
                'currency' => $currency,
                'payment_capture' => 1
            ]);
            return $order;
        } catch (\Exception $e) {
            error_log('Razorpay Order Creation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Capture a payment
     */
    public function capturePayment($paymentId, $amount)
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment->capture(['amount' => $amount * 100]);
        } catch (\Exception $e) {
            error_log('Razorpay Payment Capture Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch a payment
     */
    public function fetchPayment($paymentId)
    {
        try {
            return $this->api->payment->fetch($paymentId);
        } catch (\Exception $e) {
            error_log('Razorpay Fetch Payment Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment($paymentId, $amount = null)
    {
        try {
            $refund = $this->api->payment->fetch($paymentId)->refund([
                'amount' => $amount ? $amount * 100 : null
            ]);
            return $refund;
        } catch (\Exception $e) {
            error_log('Razorpay Refund Error: ' . $e->getMessage());
            return false;
        }
    }

    // ====================== WEBHOOKS ======================

    /**
     * Verify payment signature
     */
    public function verifySignature($attributes)
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            error_log('Razorpay Signature Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook($payload, $signature): bool
    {
        try {
            $this->api->utility->verifyWebhookSignature($payload, $signature, $this->webhookSecret);
            return true;
        } catch (\Exception $e) {
            error_log('Razorpay Webhook Verification Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse webhook JSON payload
     */
    public function parseWebhookEvent($payload): array
    {
        return json_decode($payload, true);
    }
}
