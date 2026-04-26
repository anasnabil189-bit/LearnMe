<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\OrganizationCode;
use App\Models\Organization;
use App\Models\User;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymob;

    public function __construct(PaymobService $paymob)
    {
        $this->paymob = $paymob;
    }

    /**
     * Start the payment process.
     */
    public function initiatePayment(Request $request)
    {
        Log::info('Payment Initiation Attempt: ', $request->all());

        $request->validate([
            'plan' => 'required|in:individual,family',
            'method' => 'required|in:card,wallet,fawry',
            'phone_number' => ['required', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'organization_code' => 'nullable|string'
        ]);

        $user = auth()->user();
        
        // Update user's phone number from the checkout form
        if ($request->filled('phone_number')) {
            $user->update(['phone_number' => $request->phone_number]);
        }

        if (!$user->phone_number) {
            return redirect()->back()->with('error', 'Please provide a valid Egyptian phone number to proceed with payment.');
        }

        // 1. Determine Pricing
        $prices = [
            'individual' => 319,
            'family' => 1399
        ];
        $amount = $prices[$request->plan];
        $organizationId = null;

        // 2. Applied Discount Logic
        if ($request->plan === 'individual' && $request->filled('organization_code')) {
            $orgCode = OrganizationCode::where('code', $request->organization_code)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first();

            if ($orgCode) {
                $org = $orgCode->organization;
                $userDomain = substr(strrchr($user->email, "@"), 1);
                $allowedDomains = array_map('trim', explode(',', $org->allowed_domains));

                if (in_array($userDomain, $allowedDomains)) {
                    $discount = ($amount * ($org->discount_percentage / 100));
                    $amount -= $discount;
                    $organizationId = $org->id;
                    $orgCode->increment('used_count');
                }
            }
        }

        $amountCents = $amount * 100;

        // 3. Paymob Integration Flow
        try {
            $paymentData = $this->paymob->initiatePayment(
                $amount,
                $user,
                $request->plan,
                $request->method,
                $request->organization_code
            );

            // Fawry Kiosk Payment - show reference code to user
            if (isset($paymentData['fawry_code'])) {
                return redirect()->route('user.dashboard')->with('fawry_code', $paymentData['fawry_code'])
                    ->with('info', 'تم إنشاء طلب الدفع! يرجى الدفع عند أي منفذ فوري باستخدام الكود أدناه.');
            }

            if (isset($paymentData['redirect_url'])) {
                return redirect()->away($paymentData['redirect_url']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('error', 'Failed to initialize payment.');
    }

    /**
     * Handle Paymob Success Redirection (Immediate Activation).
     */
    public function handleSuccess(Request $request)
    {
        $data = $request->all();

        // Log for debugging
        Log::info('Paymob Success Redirect Received', $data);

        // Basic verification: If success is true and we have an order ID
        if ($request->success === 'true' && $request->has('order')) {
            $transaction = Transaction::where('paymob_order_id', $request->order)->first();

            if ($transaction && $transaction->status !== 'success') {
                $transaction->update([
                    'paymob_transaction_id' => $request->id,
                    'status' => 'success',
                    'raw_response' => $data
                ]);

                // Activate Subscription
                $user = $transaction->user;
                if ($transaction->plan === 'school') {
                    if ($user->school) {
                        $user->school->update([
                            'subscription_start' => now(),
                            'subscription_end' => now()->addYear(),
                        ]);
                    }
                } else {
                    $user->update([
                        'subscription_tier' => $transaction->plan,
                        'subscription_expires_at' => now()->addYear()
                    ]);
                }

                return redirect()->route('user.dashboard')->with('success', 'تم تفعيل اشتراكك بنجاح! مرحباً بك في باقة ' . ucfirst($transaction->plan));
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'عملية ناجحة! يمكنك الآن البدء في استخدام مميزات الموقع.');
    }

    /**
     * Handle Paymob Callback (Webhook).
     */
    public function handleCallback(Request $request)
    {
        $data = $request->all();

        // Log the callback for debugging
        Log::info('Paymob Callback Received', $data);

        // HMAC Validation
        if (!$this->paymob->validateHMAC($data)) {
            Log::error('Paymob HMAC Validation Failed');
            return response()->json(['error' => 'Invalid HMAC'], 400);
        }

        $orderId = $data['order']['id'];
        $success = $data['success'];
        $transaction = Transaction::where('paymob_order_id', $orderId)->first();

        if ($transaction && $transaction->status !== 'success') {
            $transaction->update([
                'paymob_transaction_id' => $data['id'],
                'status' => $success === 'true' || $success === true ? 'success' : 'failed',
                'raw_response' => $data
            ]);

            if ($transaction->status === 'success') {
                $user = $transaction->user;

                if ($transaction->plan === 'school') {
                    // Do not auto-activate, keep it pending for admin
                    if ($user->school) {
                        $user->school->update([
                            'subscription_start' => now(),
                            'subscription_end' => now()->addYear(),
                        ]);
                    }
                } else {
                    // Activate Individual/Family Tier
                    $user->update([
                        'subscription_tier' => $transaction->plan,
                        'subscription_expires_at' => now()->addYear()
                    ]);
                }
            }
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Get integration ID from config based on method.
     */
    private function getIntegrationId($method)
    {
        return match ($method) {
            'card' => config('services.paymob.card_integration_id'),
            'wallet' => config('services.paymob.wallet_integration_id'),
            'fawry' => config('services.paymob.fawry_integration_id'),
            default => config('services.paymob.card_integration_id'),
        };
    }
}
