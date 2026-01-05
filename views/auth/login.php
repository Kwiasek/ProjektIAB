<?php
$title = "Logowanie";

ob_start();
?>



<form action="/auth/login" method="post" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-3xl font-bold text-center">Zaloguj się</h2>
    <span class="block my-2 text-sm text-gray-600 text-center">I zacznij rezerwować swoje ulubione obiekty sportowe.</span>
    <div class="my-4">
        <label for="login" class="block text-gray-700 mb-1 font-medium">Login</label>
        <input type="text" name="login" id="login" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <div class="mb-4">
        <label for="password" class="block text-gray-700 mb-1 font-medium">Hasło</label>
        <input type="password" name="password" id="password" required class="w-full ring ring-blue-400 focus:outline-blue-600 rounded px-3 py-2 bg-gray-50" />
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-3"><?= htmlspecialchars($_SESSION['error']) ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg cursor-pointer">
        Zaloguj się
    </button>

    <p class="text-center text-sm mt-4">
        Nie masz konta? <a href="/register" class="text-blue-600 hover:underline">Zarejestruj się</a>
    </p>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
