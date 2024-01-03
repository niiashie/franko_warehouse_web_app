<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Personal Website - Code With Dary
    </title>
    <link 
        rel="stylesheet" 
        href="style.css"
    />
    <link 
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;900&display=swap" 
        rel="stylesheet"
    />
    <link 
        rel="stylesheet" 
        href="//use.fontawesome.com/releases/v5.0.7/css/all.css"
    />
    <link rel="stylesheet" href={{ asset('css/app2.css') }} />
</head>
<body>
    <header>
        <!-- Navigation -->
        @include('layouts.header')
    </header>

    @yield('content')

    <!-- Footer -->
    @include('layouts.footer')
</body>
</html>