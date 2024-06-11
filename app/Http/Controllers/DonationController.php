<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donation;


class DonationController extends Controller
{

    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
    }

    //
    public function index()
    {
        $donations = Donation::orderBy('id', 'DESC')->paginate(5);
        return view('welcome', compact('donations'));
    }

    public function create()
    {
        return view('donation');
    }

    public function store(Request $request)
    {
        \DB::transaction(function () use ($request) {
            $donation = Donation::create([
                'donor_name' => $request->donor_name,
                'donation_code' => "SANBOX-" . uniqid(),
                'donor_email' => $request->donor_email,
                'donation_type' => $request->donation_type,
                'amount' => floatval($request->amount),
                'note' => $request->note
            ]);

            $payload = [
                'transaction_details' => [
                    'order_id' => $donation->donation_code,
                    'gross_amount' => $donation->amount,
                ],
                'customer_details' => [
                    'first_name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                ],
                'item_details' => [
                    [
                        'id' => $donation->donation_type,
                        'price' => $donation->amount,
                        'quantity' => 1,
                        'name' => ucwords(str_replace('_', ' ', $donation->donation_type))
                    ]
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            $donation->snap_token = $snapToken;
            $donation->save();

            $this->response['snap_token'] = $snapToken;
        });

        return response()->json($this->response);
    }

    public function notification()
    {
        $notif = new \Midtrans\Notification();
        \DB::transaction(function () use ($notif) {
            $transactionStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraudStatus = $notif->fraud_status;
            $donation = Donation::where('donation_code', $orderId)->first();

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challange') {
                        $donation->setStatusPending();
                    } else {
                        $donation->setStatusSuccess();
                    }
                }
            } elseif ($transactionStatus == 'sattlement') {
                $donation->status = 'success';
            } elseif ($transactionStatus == 'pending') {
                $donation->status = 'pending';
            } elseif ($transactionStatus == 'deny') {
                $donation->status = 'deny';
            } elseif ($transactionStatus == 'expire') {
                $donation->status = 'expire';
            } elseif ($transactionStatus == 'cancel') {
                $donation->status = 'cancel';
            }
        });

        return;
    }
}
