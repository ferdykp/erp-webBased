<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'BeamApp')</title>
    <script>
        window.formatSmartNumber = function (value, maxDecimals = 6, fallback = '-') {
            if (value === null || value === undefined || value === '') return fallback;
            const number = Number(value);
            if (!Number.isFinite(number)) return fallback;
            const decimals = Math.max(0, Math.min(Number(maxDecimals) || 0, 12));
            return new Intl.NumberFormat('en-US', {
                useGrouping: false,
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals,
            }).format(number);
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
