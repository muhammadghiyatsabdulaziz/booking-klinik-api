<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'str_number',
        'bio',
        'experience_years',
        'consultation_fee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function scheduleExceptions()
    {
        return $this->hasMany(ScheduleException::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}