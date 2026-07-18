<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recipe->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; color: #111827; }
        .section { margin-top: 16px; }
        pre { white-space: pre-wrap; font-family: Consolas, monospace; }
    </style>
</head>
<body>
    <h1>{{ $recipe->title }}</h1>
    <p><strong>Conversion Unit:</strong> {{ $recipe->target_unit }}</p>
    <p><strong>Date Saved:</strong> {{ $recipe->created_at->format('F j, Y') }}</p>

    <div class="section">
        <h3>Original Recipe</h3>
        <pre>{{ $recipe->original_recipe }}</pre>
    </div>

    <div class="section">
        <h3>Converted Recipe</h3>
        <pre>{{ $recipe->converted_recipe }}</pre>
    </div>
</body>
</html>
