<?php
$title = "Lista obiektów sportowych";

ob_start(); // rozpocznij buforowanie treści
?>
    <h2 class="text-2xl font-semibold mb-4">Dostępne obiekty</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    </div>
<?php
$content = ob_get_clean(); // zakończ buforowanie i zapisz w $content
include __DIR__ . '/../layout.php'; // załaduj główny layout
