<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Rezerwacja obiektów sportowych' ?></title>
    <link href="./css/output.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
    <main class="container mx-auto mt-8 p-4">
        <?= $content ?? '' ?>
    </main>
    <script src="./js/main.js"></script>
</body>
</html>