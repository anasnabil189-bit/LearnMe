<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $baseUrl = 'https://accept.paymob.com/api';
    protected $apiKey;
    protected $hmacSecret;

    public function __construct()
    {
        $this->apiKey = config('services.paymob.api_key');
        $this->hmacSecret = config('services.paymob.hmac_secret');
    }

    /**
     * Complete initiation flow to get the redirect URL.
     */
    public function initiatePayment($amount, $user, $plan, $method, $orgCode = null)
    {
        $amountCents = $amount * 100;
        $merchantOrderId = 'ORD_' . time() . '_' . $user->id;

        // 1. Auth
        $token = $this->authenticate();
        if (!$token) throw new \Exception('Paymob Authentication failed');

        // 2. Order
        $orderId = $this->createOrder($token, $amountCents, 'EGP', $merchantOrderId);
        if (!$orderId) throw new \Exception('Paymob Order Creation failed');

        // 3. Payment Key
        $integrationId = $this->getIntegrationId($method);
        $nameParts = explode(' ', $user->name);
        $paymentToken = $this->getPaymentKey($token, $orderId, $amountCents, $integrationId, [
            'first_name' => $nameParts[0],
            'last_name' => count($nameParts) > 1 ? end($nameParts) : 'User',
            'email' => $user->email,
            'phone_number' => $user->phone_number,
        ]);

        if (!$paymentToken) throw new \Exception('Paymob Payment Key generation failed');

        // 4. Create Transaction Record
        \App\Models\Transaction::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => $amount,
            'payment_method' => $method,
            'paymob_order_id' => $orderId,
            'status' => 'pending',
        ]);

        // 5. Prepare Redirect URL
        $redirectUrl = '';
        if ($method === 'wallet') {
            $redirectUrl = $this->prepareWalletPayment($paymentToken, $user->phone_number);
        } elseif ($method === 'fawry') {
            $fawryData = $this->prepareFawryPayment($paymentToken);
            if ($fawryData) {
                return [
                    'order_id' => $orderId,
                    'payment_token' => $paymentToken,
                    'fawry_code' => $fawryData['bill_reference'],
                    'redirect_url' => route('user.dashboard') // Redirect to dashboard to show code
                ];
            }
            throw new \Exception('Failed to generate Fawry payment code.');
        } else {
            $iframeId = config('services.paymob.iframe_id');
            $redirectUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
        }

        return [
            'order_id' => $orderId,
            'payment_token' => $paymentToken,
            'redirect_url' => $redirectUrl
        ];
    }

    private function getIntegrationId($method)
    {
        return match($method) {
            'card'   => config('services.paymob.card_integration_id'),
            'wallet' => config('services.paymob.wallet_integration_id'),
            'fawry'  => config('services.paymob.fawry_integration_id'),
            default  => config('services.paymob.card_integration_id'),
        };
    }

    /**
     * Get Authentication Token from Paymob.
     */
    public function authenticate()
    {
        $maxRetries = 2;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(30)
                    ->retry(2, 100)
                    ->post("{$this->baseUrl}/auth/tokens", [
                        'api_key' => $this->apiKey,
                    ]);

                if ($response->successful()) {
                    return $response->json()['token'];
                }

                $errorData = $response->json();
                $message = $errorData['detail'] ?? ($errorData['message'] ?? 'Unknown Error');
                throw new \Exception('Paymob API Error: ' . $message);
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt > $maxRetries) {
                    Log::error('Paymob Authentication Failed after retries', [
                        'error' => $e->getMessage(),
                        'base_url' => $this->baseUrl
                    ]);
                    throw new \Exception('Payment system is currently unreachable. Please try again in a few moments. (Error: ' . $e->getMessage() . ')');
                }
                usleep(500000); // Wait 0.5s before retry
            }
        }
    }

    /**
     * Register an Order on Paymob.
     */
    public function createOrder($token, $amountCents, $currency = 'EGP', $merchantOrderId = null)
    {
        try {
            $response = Http::timeout(30)->withToken($token)->post("{$this->baseUrl}/ecommerce/orders", [
                'auth_token' => $token,
                'delivery_needed' => false,
                'amount_cents' => (int) $amountCents,
                'currency' => $currency,
                'merchant_order_id' => $merchantOrderId,
                'items' => [],
            ]);

            if ($response->successful()) {
                return $response->json()['id'];
            }

            Log::error('Paymob Order Creation Failed', ['response' => $response->json()]);
            throw new \Exception('Paymob Order Error: Failed to create order.');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Generate Payment Key.
     */
    public function getPaymentKey($token, $orderId, $amountCents, $integrationId, $billingData, $currency = 'EGP')
    {
        try {
            $response = Http::timeout(30)->withToken($token)->post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token' => $token,
                'amount_cents' => $amountCents,
                'expiration' => 3600, // 1 hour
                'order_id' => $orderId,
                'billing_data' => [
                    'first_name' => $billingData['first_name'] ?? 'N/A',
                    'last_name' => $billingData['last_name'] ?? 'N/A',
                    'email' => $billingData['email'] ?? 'N/A',
                    'phone_number' => $billingData['phone_number'] ?? 'N/A',
                    'apartment' => 'NA',
                    'floor' => 'NA',
                    'street' => 'NA',
                    'building' => 'NA',
                    'shipping_method' => 'NA',
                    'postal_code' => 'NA',
                    'city' => 'NA',
                    'country' => 'NA',
                    'state' => 'NA',
                ],
                'currency' => $currency,
                'integration_id' => $integrationId,
                'redirection_url' => config('app.url') . '/payments/success'
            ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }

            Log::error('Paymob Payment Key Failed', ['response' => $response->json()]);
            throw new \Exception('Paymob Payment Key Error: Failed to generate payment key.');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Initiate Wallet Payment (Vodafone Cash, etc.)
     */
    public function prepareWalletPayment($paymentToken, $phoneNumber)
    {
        try {
            // Ensure phone is 11 digits starting with 0
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            $response = Http::timeout(30)->post("{$this->baseUrl}/acceptance/payments/pay", [
                'source' => [
                    'identifier' => $phoneNumber,
                    'subtype' => 'WALLET',
                ],
                'payment_token' => $paymentToken,
            ]);

            $json = $response->json();
            $url = $json['redirect_url'] ?? ($json['redirection_url'] ?? ($json['iframe_redirection_url'] ?? null));

            // In wallet payments, pending: true means the transaction is ready to be paid
            $isSuccessful = ($json['success'] ?? false) == true || ($json['pending'] ?? false) == true || ($json['pending'] ?? false) == 'true';

            if ($response->successful() && ($isSuccessful || $url)) {
                if (!$url) {
                    throw new \Exception("Paymob link generation failed. Response: " . json_encode($json));
                }
                return $url;
            }

            // Error handling
            $errorMessage = $json['data']['message'] ?? ($json['detail'] ?? 'Unknown Wallet Error');
            Log::error('Paymob Wallet Initiation Failed', [
                'status' => $response->status(),
                'response' => $json
            ]);
            
            throw new \Exception("Paymob says: " . $errorMessage);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Initiate Fawry (Kiosk) Payment
     */
    public function prepareFawryPayment($paymentToken)
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/acceptance/payments/pay", [
                'source' => [
                    'identifier' => 'AGGREGATOR',
                    'subtype' => 'AGGREGATOR',
                ],
                'payment_token' => $paymentToken,
            ]);

            $json = $response->json();

            // Fawry returns HTTP 200 but with pending=true if successful
            $isPending = ($json['pending'] === 'true' || $json['pending'] === true);
            $billReference = $json['data']['bill_reference'] ?? null;

            if ($response->successful() && $isPending && $billReference) {
                return [
                    'bill_reference' => $billReference,
                ];
            }

            // Expose the reason it failed
            $errorMessage = $json['data']['message'] ?? ($json['detail'] ?? 'No referrence code returned by Paymob.');
            Log::error('Paymob Fawry Initiation Failed', [
                'status' => $response->status(),
                'response' => $json
            ]);
            
            throw new \Exception("Fawry Error: " . $errorMessage);
        } catch (\Exception $e) {
            // Re-throw so standard error handling displays it
            throw $e;
        }
    }

    /**
     * Validate HMAC from Paymob Callback.
     */
    public function validateHMAC($data)
    {
        $string = $data['amount_cents'] .
                  $data['created_at'] .
                  $data['currency'] .
                  $data['error_occured'] .
                  $data['has_parent_transaction'] .
                  $data['id'] .
                  $data['integration_id'] .
                  $data['is_3d_secure'] .
                  $data['is_auth'] .
                  $data['is_capture'] .
                  $data['is_refunded'] .
                  $data['is_standalone_payment'] .
                  $data['is_voided'] .
                  $data['order']['id'] .
                  $data['owner'] .
                  $data['pending'] .
                  $data['source_data']['pan'] .
                  $data['source_data']['sub_type'] .
                  $data['source_data']['type'] .
                  $data['success'];

        $hmac = hash_hmac('sha512', $string, $this->hmacSecret);

        return $hmac === $data['hmac'];
    }
}
