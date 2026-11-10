<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; margin: 0; }
        .slide { page-break-after: always; padding: 36px 48px; }
        .slide:last-child { page-break-after: auto; }
        .meta { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 12px; }
        h1 { font-size: 26px; margin: 0 0 16px; }
        .body { font-size: 14px; line-height: 1.55; white-space: pre-wrap; }
        .footer { position: fixed; bottom: 18px; right: 36px; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
@foreach($slides as $i => $slide)
<div class="slide">
    <div class="meta">{{ $presentation->subject ?: 'Presentasi' }} · Slide {{ $i + 1 }}/{{ count($slides) }}</div>
    <h1>{{ $slide['title'] }}</h1>
    <div class="body">{{ $slide['body'] }}</div>
</div>
@endforeach
<div class="footer">{{ $presentation->title }} — SIMS Studio Presentasi</div>
</body>
</html>
