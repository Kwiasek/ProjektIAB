<?php
$title = "Rejestracja";

ob_start();
?>



<form action="/auth/register" method="post" class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-3xl font-bold text-center">Zarejestruj się</h2>
    <span class="block my-2 text-sm text-gray-600 text-center">I zacznij rezerwować swoje ulubione obiekty sportowe.</span>
    <div class="my-4 flex gap-4">
        <div class="w-full">
            <label for="fname" class="block text-gray-700 mb-1 font-medium">Imię</label>
            <input type="text" name="fname" id="fname" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
        </div>
        <div class="w-full">
            <label for="lname" class="block text-gray-700 mb-1 font-medium">Nazwisko</label>
            <input type="text" name="lname" id="lname" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
        </div>
    </div>

    <div class="mb-4">
        <label for="phone" class="block text-gray-700 mb-1 font-medium">Numer telefonu</label>
        <input type="tel" name="phone" id="phone" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label for="email" class="block text-gray-700 mb-1 font-medium">E-mail</label>
        <input type="email" name="email" id="email" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label for="login" class="block text-gray-700 mb-1 font-medium">Login</label>
        <input type="text" name="login" id="login" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label for="password" class="block text-gray-700 mb-1 font-medium">Hasło</label>
        <input type="password" name="password" id="password" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="block text-gray-700 mb-1 font-medium">Powtórz hasło</label>
        <input type="password" name="password_confirm" id="password_confirm" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label class="flex items-center justify-between">
            <span class="block text-gray-700 mb-1 font-medium">
                Jesteś właścicielem obiektów sportowych?
            </span>
            <input type="checkbox" name="owner" id="owner" value="" class="sr-only peer">
            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-600 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
        </label>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-3"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg cursor-pointer mt-2">
        Zarejestruj się
    </button>

    <p class="text-center text-sm mt-4">
        Masz już konto? <a href="/login" class="text-blue-600 hover:underline">Zaloguj się</a>
    </p>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
