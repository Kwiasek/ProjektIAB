<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Rezerwacja obiektów sportowych' ?></title>
    <link href="/css/output.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 text-gray-800 antialiased scroll-smooth">
    <div id="loader-bar" class="fixed top-0 left-0 h-1 bg-blue-500 w-0 z-50 transition-all duration-300"></div>
    <nav class="flex px-8 py-3 border-b border-b-blue-300 align-middle justify-between sticky top-0 bg-gray-100">
        <a href="/" class="text-3xl font-bold text-blue-500">SportSpot</a>
        <div class="flex items-center gap-6 font-medium text-gray-600">
            <a href="/" class="hover:text-gray-800">Lista obiektów</a>
            <a href="/" class="hover:text-gray-800">Moje rezerwacje</a>
            <a href="/" class="hover:text-gray-800">Dołącz do rezerwacji</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('loader-bar');
            loader.style.width = '0%';
            let progress = 0;
            let interval = setInterval(() => {
                if (progress < 90) {
                    progress += Math.random() * 5
                    loader.style.width = progress + '%';
                }
            }, 200);

            window.addEventListener('load', () => {
                clearInterval(interval)
                loader.style.width = '100%'
                setTimeout(() => loader.style.opacity = '0', 1000);
                setTimeout(() => loader.style.display = 'none', 1300);
            })
        });

        const links = document.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', e => {
                const href = link.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript')) {
                    const loader = document.getElementById('loader-bar');
                    loader.style.display = 'block';
                    loader.style.opacity = '1';
                    loader.style.width = '0%';
                    setTimeout(() => loader.style.width = '50%', 100);
                }
            });
        });
    </script>
</body>
</html>