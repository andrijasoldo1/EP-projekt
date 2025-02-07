<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pravni Dokument</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 40px; padding: 40px; }
        h1, h2, h3 { text-align: center; }
        p { font-size: 14px; line-height: 1.5; }
        hr { border: 1px solid #000; margin: 15px 0; }
        .section-title { font-weight: bold; text-decoration: underline; font-size: 16px; }
        .signature { margin-top: 40px; text-align: left; }
        .signature p { margin-bottom: 5px; }
        .signature-line { border-bottom: 1px solid black; width: 300px; display: inline-block; margin-top: 10px; }
        .qr-code { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

    <h1>Pravni Dokument</h1>
    <h3>Generirano od strane AI odvjetnika</h3>

    <p><strong>Vrsta dokumenta:</strong> {{ ucfirst($conversation->document_type ?? 'Dokument') }}</p>
    <p><strong>Tema:</strong> {{ $conversation->topic ?? 'N/A' }}</p>
    <p><strong>Datum generiranja:</strong> {{ $date ?? now()->format('d.m.Y') }}</p>
    <hr>

    <!-- Dinamične sekcije -->
    @if(!empty($data))
        @foreach ($data as $title => $content)
            <h3 class="section-title">{{ $title }}</h3>
            <p>{!! nl2br(e($content)) !!}</p>
        @endforeach
    @else
        <p><em>Nema dostupnih podataka za prikaz.</em></p>
    @endif

    <!-- QR kod za verifikaciju dokumenta -->
    @if(isset($qrCode))
        <div class="qr-code">
            <h3 class="section-title">Verifikacija dokumenta</h3>
            <p>Skenirajte QR kod kako biste provjerili autentičnost dokumenta:</p>
            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR kod za verifikaciju">
        </div>
    @endif

    <!-- Korisnički digitalni potpis -->
    @if(isset($signatureData) && !empty($signatureData))
        <h3 class="section-title">Korisnički potpis</h3>
        <p>Ovaj dokument je potpisan digitalno:</p>
        <img src="{{ $signatureData }}" alt="Potpis korisnika" width="200">
    @endif

    <!-- Klasični potpis -->
    <div class="signature">
        <h3 class="section-title">Potpis i datum</h3>
        <p><strong>Datum:</strong> {{ $date ?? '__________ (datum)' }}</p>
        <p><strong>Potpis:</strong></p>
        <div class="signature-line"></div>
    </div>

</body>
</html>
