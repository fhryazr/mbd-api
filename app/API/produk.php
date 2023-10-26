<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // TABEL CUSTOMER
    // get
    $app->get('/produk', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL GetProduk()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader('Content-Type', 'application/json');
    });

    // get by id
    $app->get('/produk/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        // Ambil id_pembeli dari parameter URL
        $id_produk_param = $args['id'];

        // Buat query untuk memanggil stored procedure
        $query = $db->prepare('CALL GetProdukById(:id_produk_param)');
        $query->bindParam(':id_produk_param', $id_produk_param, PDO::PARAM_STR);
        $query->execute();

        // Ambil hasil dari stored procedure
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Ubah hasil menjadi JSON
        if ($results) {
            $response->getBody()->write(json_encode($results));
        } else {
            $response->getBody()->write(json_encode(['error' => 'Produk tidak ditemukan']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // post satu produk
    $app->post('/produk', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $parsedBody = $request->getParsedBody();

        // Extract data
        $id_produk = $parsedBody['id_produk'];
        $nama_produk = $parsedBody['nama_produk'];
        $harga_produk = $parsedBody['harga_produk'];
        $stok_produk = $parsedBody['stok_produk'];

        // Prepare and execute the stored procedure
        $query = $db->prepare('CALL CreateProduk(:id_produk, :nama_produk, :harga_produk, :stok_produk)');
        $query->bindParam(':id_produk', $id_produk, PDO::PARAM_STR);
        $query->bindParam(':nama_produk', $nama_produk, PDO::PARAM_STR);
        $query->bindParam(':harga_produk', $harga_produk, PDO::PARAM_INT);
        $query->bindParam(':stok_produk', $stok_produk, PDO::PARAM_INT);

        try {
            $query->execute();
            $response->getBody()->write(json_encode(['message' => 'Produk telah dibuat']));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // post multi produk
    $app->post('/produk/multi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // Get the request body as JSON
        $products_data = json_decode($request->getBody()->getContents(), true);

        $data_json = json_encode($products_data);

        // Prepare and execute the stored procedure for adding multiple products
        $query = $db->prepare('CALL TambahMultiProdukBaru(:products_data)');
        $query->bindParam(':products_data', $data_json, PDO::PARAM_STR);

        try {
            $query->execute();

            // Create a response object and write the JSON response
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['message' => 'Multiple products added successfully']));

            return $response;
        } catch (PDOException $e) {
            // Create a response object for the error
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response->withStatus(500); // Set the HTTP status code for the error
        }
    });

    // put produk
    $app->put('/produk/{id}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $currentId = $args['id'];
        $nama_produk = $parsedBody['nama_produk'];
        $harga_produk = $parsedBody['harga_produk'];
        $stok_produk = $parsedBody['stok_produk'];
        $db = $this->get(PDO::class);

        // Buat query untuk memanggil stored procedure
        $query = $db->prepare('CALL UpdateProduk(:id_produk, :nama_produk, :harga_produk, :stok_produk)');
        $query->bindParam(':id_produk', $currentId, PDO::PARAM_STR);
        $query->bindParam(':nama_produk', $nama_produk, PDO::PARAM_STR);
        $query->bindParam(':harga_produk', $harga_produk, PDO::PARAM_INT);
        $query->bindParam(':stok_produk', $stok_produk, PDO::PARAM_INT);

        try {
            $query->execute();
            $response->getBody()->write(json_encode(['message' => 'Produk dengan ID '.$currentId.' telah diperbarui']));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // delete user
    $app->delete('/produk/{id}', function (Request $request, Response $response, $args) {
        $currentId = $args['id'];
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL DeleteProduk(:new_id_produk)');
            $query->bindParam(':new_id_produk', $currentId, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Data tidak ditemukan']));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Produk dengan ID '.$currentId.' telah dihapus dari database']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Database error: '.$e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });
};
