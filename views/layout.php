<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Rezerwacja obiektów sportowych' ?></title>
    <link href="./css/output.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
    <nav class="flex px-8 py-3 border-b border-b-blue-300 align-middle justify-between">
        <a href="/" class="text-3xl font-bold text-blue-500">SportSpot</a>
        <div class="flex items-center gap-6 font-medium">
            <a href="/">Lista obiektów</a>
            <a href="/">Moje Rezerwacje</a>
            <a href="/">Dołącz do rezerwacji</a>
            <?php if (isset($_SESSION['user'])) : ?>
                <a href="/logout" class="px-4 py-2 bg-red-200 text-red-500 rounded-lg">Wyloguj się</a>
            <?php else : ?>
                <a href="/login" class="px-4 py-2 bg-blue-200 text-blue-500 rounded-lg">Zaloguj się</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container mx-auto mt-8 p-4">
        <?= $content ?? '' ?>
    </main>
    <script src="./js/main.js"></script>
</body>
</html>