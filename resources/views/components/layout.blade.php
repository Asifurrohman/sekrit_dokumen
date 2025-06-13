<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    <title>Halaman {{ $title }} || Analisis Sentimen</title>
</head>
<body class="font-figtree bg-slate-100">
    <div class="flex w-full">
        <x-sidebar />
        <div class="w-full md:flex-1">
            <x-navbar />
            <main class="m-4 bg-white shadow-sm p-4">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>