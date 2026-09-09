<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Request $request, $bookingId)
    {
        $booking = Booking::with(['doctor.user', 'doctor.specialization', 'patient'])
            ->where('id', $bookingId)
            ->where('patient_id', $request->user()->id)
            ->firstOrFail();

        $payment = Payment::where('booking_id', $booking->id)->first();

        if (!$payment) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->doctor->consultation_fee,
                'status' => 'pending',
            ]);
        }

        $params = [
            'transaction_details' => [
                'order_id' => 'BOOKING-' . $booking->id . '-' . time(),
                'gross_amount' => (int) $booking->doctor->consultation_fee,
            ],
            'customer_details' => [
                'first_name' => $booking->patient->name,
                'email' => $booking->patient->email,
                'phone' => $booking->patient->phone ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'BOOKING-' . $booking->id,
                    'price' => (int) $booking->doctor->consultation_fee,
                    'quantity' => 1,
                    'name' => 'Booking ' . $booking->doctor->specialization->name . ' - ' . $booking->doctor->user->name,
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'snap_token' => $snapToken,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat token pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        $notif = new \Midtrans\Notification();

        $orderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;

        preg_match('/BOOKING-(\d+)-/', $orderId, $matches);
        $bookingId = $matches[1] ?? null;

        if (!$bookingId) {
            return response()->json(['message' => 'Invalid order_id'], 400);
        }

        $payment = Payment::where('booking_id', $bookingId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus == 'accept' || $fraudStatus == null) {
                $payment->update([
                    'status' => 'paid',
                    'method' => $notif->payment_type,
                    'transaction_id' => $notif->transaction_id,
                    'paid_at' => now(),
                ]);

                $booking = $payment->booking;
                $booking->update(['status' => 'confirmed']);
                $booking->load(['doctor.user', 'doctor.specialization', 'patient']);

                try {
                    Mail::to($booking->patient->email)->send(new BookingConfirmation($booking));
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim email booking: ' . $e->getMessage());
                }
            }
        } elseif ($transactionStatus == 'pending') {
            $payment->update(['status' => 'pending']);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }
}