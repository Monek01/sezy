<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#004aad">
    <title inertia>{{ config('app.name', 'SEZY') }}</title>
    <meta name="description" content="SEZY - Plateforme e-commerce multi-catégories au Bénin et pour la diaspora. Mode, cosmétiques, alimentation. Paiement Mobile Money MTN, Moov, Wave.">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-gray-50">
    @inertia
</body>
</html>
