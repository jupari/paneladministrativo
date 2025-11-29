<?php

// Define el path base de Laravel
//define('LARAVEL_START', microtime(true));

// Cargar el autoload de Laravel
//require __DIR__ . '/bootstrap/autoload.php'; // Para Laravel < 8
// require __DIR__ . '/vendor/autoload.php'; // Para Laravel 8+

// use Illuminate\Contracts\Console\Kernel;

// // Iniciar Laravel
// $app = require_once __DIR__ . '/bootstrap/app.php';

// // Obtener instancia de la aplicación
// $kernel = $app->make(Kernel::class);

// // Ejecutar los comandos de Artisan
// $kernel->call('cache:clear');
// $kernel->call('config:clear');
// $kernel->call('view:clear');
// $kernel->call('route:clear');

// // Mostrar mensaje de éxito
// echo "✅ Caché de Laravel limpiada correctamente.";

// use Illuminate\Support\Facades\Artisan;

// require __DIR__.'/vendor/autoload.php';
// $app = require_once __DIR__.'/bootstrap/app.php';

// $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// // Comandos que deseas ejecutar
// Artisan::call('config:clear');
// Artisan::call('cache:clear');
// Artisan::call('route:clear');
// Artisan::call('view:clear');
// Artisan::call('optimize:clear');
// Artisan::call('config:cache');
// Artisan::call('route:cache');

// echo "✅ Comandos ejecutados desde el navegador.";


// optimize.php
exec('php artisan config:cache');
exec('php artisan route:cache');
exec('php artisan view:cache');
exec('php artisan event:cache');
echo "✅ Optimización completada.";
?>


