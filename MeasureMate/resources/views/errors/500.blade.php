<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something Went Wrong</title>
    <style>
        body { font-family: Arial, sans-serif; background: #050A0C; color: #F5F7FA; margin: 0; display: grid; place-items: center; min-height: 100vh; }
        .card { background: #121B20; border: 1px solid #26353D; border-radius: 16px; padding: 32px; max-width: 480px; text-align: center; box-shadow: 0 18px 40px rgba(0,0,0,0.35); }
        .code { font-size: 56px; font-weight: 800; color: #00E5FF; margin-bottom: 8px; }
        .btn { display: inline-block; margin-top: 16px; padding: 10px 14px; border-radius: 999px; background: #00E5FF; color: #041015; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <h1>Something Went Wrong</h1>
        <p>{{ $message ?? 'Something went wrong. Please try again.' }}</p>
        <a class="btn" href="{{ url('/') }}">Return to MeasureMate</a>
    </div>
</body>
</html>
