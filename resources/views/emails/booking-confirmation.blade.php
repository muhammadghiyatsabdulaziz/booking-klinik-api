<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 8px; }
        .header { color: #2563eb; font-size: 20px; font-weight: bold; margin-bottom: 20px; }
        .detail-row { margin-bottom: 10px; }
        .label { color: #6b7280; font-size: 13px; }
        .value { font-size: 15px; font-weight: 500; }
        .footer { margin-top: 20px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">✅ Booking Konsultasi Berhasil</div>

        <p>Halo <strong>{{ $patientName }}</strong>,</p>
        <p>Booking konsultasi kamu telah berhasil dikonfirmasi dengan detail berikut:</p>

        <div class="detail-row">
            <div class="label">Dokter</div>
            <div class="value">{{ $doctorName }} ({{ $specialization }})</div>
        </div>

        <div class="detail-row">
            <div class="label">Tanggal</div>
            <div class="value">{{ $bookingDate }}</div>
        </div>

        <div class="detail-row">
            <div class="label">Jam</div>
            <div class="value">{{ $timeSlot }}</div>
        </div>

        @if($complaint)
        <div class="detail-row">
            <div class="label">Keluhan</div>
            <div class="value">{{ $complaint }}</div>
        </div>
        @endif

        <div class="footer">
            Email ini dikirim otomatis oleh sistem Booking Klinik. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>