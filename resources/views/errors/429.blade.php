<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too Many Requests</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 2rem; }
        .card { max-width: 640px; margin: 3rem auto; padding: 2rem; border: 1px solid #334155; border-radius: 12px; background: #111827; }
        a { color: #38bdf8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Too Many Requests</h1>
        <p>{{ $message ?? 'You have sent too many requests. Please wait a moment and try again.' }}</p>
        <p><a href="/">Return to the app</a></p>
    </div>
</body>
</html>
