<?php

declare(strict_types=1);

use Slim\App;

return function (App $app) {
    // TABEL CUSTOMER
    $pembeliRoutes = require __DIR__.'/API/pembeli.php';
    $pembeliRoutes($app);

    // TABEL Produk
    $produkRoutes = require __DIR__.'/API/produk.php';
    $produkRoutes($app);

    // Tabel
    $transaksiRoutes = require __DIR__.'/API/transaksi.php';
    $transaksiRoutes($app);
};
