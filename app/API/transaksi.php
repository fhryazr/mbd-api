<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // GET all transaksi
    $app->get('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // Execute the stored procedure
        $query = $db->query('CALL GetTransaksi()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Write the JSON response
        $response->getBody()->write(json_encode($results));

        return $response->withHeader('Content-Type', 'application/json');
    });

    // GET transaksi by ID
    $app->get('/transaksi/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        // Get the id_transaksi from the URL parameter
        $idTransaksiParam = $args['id'];

        // Execute the stored procedure with the parameter
        $query = $db->prepare('CALL GetTransaksiByID(:id_transaksi_param)');
        $query->bindParam(':id_transaksi_param', $idTransaksiParam, PDO::PARAM_INT);
        $query->execute();

        // Fetch the results
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Write the JSON response
        if ($results) {
            $response->getBody()->write(json_encode($results));
        } else {
            $response->getBody()->write(json_encode(['error' => 'Transaksi not found']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // GET all detail transaksi
    $app->get('/transaksi-detail', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // Execute the stored procedure
        $query = $db->query('CALL GetDetailTransaksi()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Write the JSON response
        $response->getBody()->write(json_encode($results));

        return $response->withHeader('Content-Type', 'application/json');
    });

    // GET detail transaksi by ID
    $app->get('/transaksi-detail/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        // Get the id_transaksi from the URL parameter
        $idTransaksiParam = $args['id'];

        // Execute the stored procedure with the parameter
        $query = $db->prepare('CALL GetDetailTransaksiByID(:id_transaksi_param)');
        $query->bindParam(':id_transaksi_param', $idTransaksiParam, PDO::PARAM_INT);
        $query->execute();

        // Fetch the results
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Write the JSON response
        if ($results) {
            $response->getBody()->write(json_encode($results));
        } else {
            $response->getBody()->write(json_encode(['error' => 'Detail transaksi not found']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // POST multi transaksi barang
    $app->post('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // Get the request body as JSON
        $transaksiData = json_decode($request->getBody()->getContents(), true);

        // Extract data from the request
        $pembeli_id = $transaksiData['pembeli_id'];
        $tgl_transaksi = $transaksiData['tgl_transaksi'];
        $JSON_TEXT = $transaksiData['JSON_TEXT'];

        // JSON-encode the $barang variable separately
        $jsonBarang = json_encode($JSON_TEXT);

        // Prepare and execute the stored procedure
        $query = $db->prepare('CALL CreateTransaksiWithDetails2(:pembeli_id, :tgl_transaksi, :JSON_TEXT)');
        $query->bindParam(':pembeli_id', $pembeli_id, PDO::PARAM_STR);
        $query->bindParam(':tgl_transaksi', $tgl_transaksi, PDO::PARAM_STR);
        $query->bindParam(':JSON_TEXT', $jsonBarang, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);

        try {
            $query->execute();

            // Create a response object and write the JSON response
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['message' => 'Transaksi created successfully']));

            return $response;
        } catch (PDOException $e) {
            // Create a response object for the error
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response->withStatus(500); // Set the HTTP status code for the error
        }
    });

    // DELETE transaksi
    $app->delete('/transaksi/{id}', function ($request, $response, $args) {
        $db = $this->get(PDO::class);

        $id_transaksi = $args['id'];

        // Prepare and execute the stored procedure for deleting a transaksi
        $query = $db->prepare('CALL HapusTransaksi(:id_transaksi)');
        $query->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);

        try {
            $query->execute();

            // Create a response object and write the JSON response
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['message' => 'Transaksi deleted successfully']));

            return $response;
        } catch (PDOException $e) {
            // Create a response object for the error
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response->withStatus(500); // Set the HTTP status code for the error
        }
    });
};
