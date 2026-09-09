<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    // GET /api/doctors — daftar semua dokter, bisa difilter by spesialisasi
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialization']);

        if ($request->has('specialization_id')) {
            $query->where('specialization_id', $request->specialization_id);
        }

        $doctors = $query->get();

        return response()->json($doctors);
    }

    // GET /api/doctors/{id} — detail satu dokter
    public function show($id)
    {
        $doctor = Doctor::with(['user', 'specialization', 'schedules'])->findOrFail($id);

        return response()->json($doctor);
    }

    // GET /api/doctors/{id}/available-slots?date=2026-09-01 — slot kosong di tanggal tertentu
    public function availableSlots(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $doctor = Doctor::findOrFail($id);
        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l')); // contoh: 'monday'

        // Cek apakah dokter praktik di hari itu
        $schedule = $doctor->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json([
                'message' => 'Dokter tidak praktik di hari ini',
                'available_slots' => [],
            ]);
        }

        // Cek apakah tanggal ini termasuk hari libur/cuti dokter
        $isException = $doctor->scheduleExceptions()
            ->where('date', $date->format('Y-m-d'))
            ->exists();

        if ($isException) {
            return response()->json([
                'message' => 'Dokter cuti/libur di tanggal ini',
                'available_slots' => [],
            ]);
        }

        // Generate semua slot berdasarkan jam kerja & durasi slot
        $slots = [];
        $current = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);

        while ($current->lt($end)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($schedule->slot_duration_minutes);
        }

        // Ambil slot yang sudah dibooking di tanggal itu
        $bookedSlots = $doctor->bookings()
            ->where('booking_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time_slot')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        // Slot kosong = semua slot dikurangi yang sudah dibooking
        $availableSlots = array_values(array_diff($slots, $bookedSlots));

        return response()->json([
            'doctor_id' => $doctor->id,
            'date' => $date->format('Y-m-d'),
            'available_slots' => $availableSlots,
        ]);
    }
}