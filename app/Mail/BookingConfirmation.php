<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Booking Konsultasi Berhasil')
            ->view('emails.booking-confirmation')
            ->with([
                'patientName' => $this->booking->patient->name,
                'doctorName' => $this->booking->doctor->user->name,
                'specialization' => $this->booking->doctor->specialization->name,
                'bookingDate' => $this->booking->booking_date->format('d F Y'),
                'timeSlot' => $this->booking->time_slot,
                'complaint' => $this->booking->complaint,
            ]);
    }
}