@props(['title' => 'Sign in'])

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SFG Medical secure sign in interface">
    <title>{{ $title }} | SFG Medical</title>
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
</head>
<body>
    {{ $slot }}
    <script>{!! file_get_contents(resource_path('js/app.js')) !!}</script>
</body>
</html>
