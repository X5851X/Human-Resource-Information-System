<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>HRIS Diskominfostandi Bekasi</title>
  
  <!-- Favicon -->
  <link rel="icon" href="{{ asset('images/logo-diskominfostandi.png') }}" type="image/png">
  <link rel="shortcut icon" href="{{ asset('images/logo-diskominfostandi.png') }}" type="image/png">
  
  @vite('resources/js/main.js')
</head>
<body class="bg-gray-100 text-gray-900">
  <div id="app"></div>
</body>
</html>