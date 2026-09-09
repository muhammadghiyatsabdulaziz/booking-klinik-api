<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    // POST /api/bookings — pasien bikin booking baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required',
            'complaint' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cek slot masih kosong (double-check terakhir sebelum simpan)
        $exists = Booking::where('doctor_id', $request->doctor_id)
            ->where('booking_date', $request->booking_date)
            ->where('time_slot', $request->time_slot)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Slot ini sudah dibooking pasien lain, silakan pilih slot lain',
            ], 409);
        }

        $booking = Booking::create([
            'patient_id' => $request->user()->id,
            'doctor_id' => $request->doctor_id,
            'booking_date' => $request->booking_date,
            'time_slot' => $request->time_slot,
            'complaint' => $request->complaint,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'booking' => $booking->load(['doctor.user', 'doctor.specialization']),
        ], 201);
    }

    // GET /api/bookings/my-bookings — riwayat booking milik pasien yang login
    public function myBookings(Request $request)
    {
        $bookings = Booking::with(['doctor.user', 'doctor.specialization'])
            ->where('patient_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // PATCH /api/bookings/{id}/cancel — pasien batalkan booking miliknya
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('patient_id', $request->user()->id)
            ->firstOrFail();

        if ($booking->status === 'completed') {
            return response()->json([
                'message' => 'Booking yang sudah selesai tidak bisa dibatalkan',
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking berhasil dibatalkan',
            'booking' => $booking,
        ]);
    }

    // GET /api/doctor/bookings — daftar booking masuk untuk dokter yang login
    public function doctorBookings(Request $request)
    {
        $doctor = $request->user()->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'User ini bukan dokter'], 403);
        }

        $bookings = Booking::with('patient')
            ->where('doctor_id', $doctor->id)
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // PATCH /api/doctor/bookings/{id}/status — dokter update status booking
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor = $request->user()->doctor;
        $booking = Booking::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        $booking->update([
            'status' => $request->status,
            'notes' => $request->notes ?? $booking->notes,
        ]);

        return response()->json([
            'message' => 'Status booking berhasil diupdate',
            'booking' => $booking,
        ]);
    }
}