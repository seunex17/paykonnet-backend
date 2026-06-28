<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <x-inertia::head />
    <meta name="title" content="Paykonet — Move Money at the Speed of Life" />
    <meta name="description" content="Nigeria's #1 digital wallet. Send money, pay bills, buy airtime, fund bets, and spend globally with USD & NGN virtual cards. Instant data loans. 2M+ users trust Paykonet." />
    <meta name="keywords" content="digital wallet Nigeria, send money Nigeria, virtual card Nigeria, USD virtual card, NGN virtual card, airtime top-up, pay bills online, betting wallet funding, data loan, fintech Nigeria, Paykonet" />
    <meta name="robots" content="index, follow" />
    <meta name="author" content="Paykonet" />
    <meta name="theme-color" content="#7C3AED" />
    <link rel="canonical" href="https://paykonet.com" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://paykonet.com" />
    <meta property="og:title" content="Paykonet — Move Money at the Speed of Life" />
    <meta property="og:description" content="Nigeria's #1 digital wallet. Send money, pay bills, buy airtime, fund bets, and spend globally with USD & NGN virtual cards. Instant data loans. 2M+ users trust Paykonet." />
    <meta property="og:image" content="{{ asset('logo/paykonet.png') }}" />
    <meta property="og:locale" content="en_NG" />
    <meta property="og:site_name" content="Paykonet" />

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@paykonet" />
    <meta name="twitter:url" content="https://paykonet.com" />
    <meta name="twitter:title" content="Paykonet — Move Money at the Speed of Life" />
    <meta name="twitter:description" content="Nigeria's #1 digital wallet. Send money, pay bills, buy airtime, fund bets, and spend globally with USD & NGN virtual cards. Instant data loans." />
    <meta name="twitter:image" content="{{ asset('logo/paykonet.png') }}" />
</head>
<body>
    <x-inertia::app />
</body>
</html>
