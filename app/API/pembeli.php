<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // TABEL CUSTOMER
    // get
    $app->get('/pembeli', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);

            // Check if the database connection is successful
            if (!$db) {
                throw new PDOException('Failed to connect to the database.');
            }

            $query = $db->query('CALL GetPembeli()');

            // Check if the query execution is successful
            if (!$query) {
                throw new PDOException('Failed to execute the query.');
            }

            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($results));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response->withHeader('Content-Type', 'application/json');
        }
    });

    // get by id
    $app->get('/pembeli/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = $this->get(PDO::class);

            // Check if the database connection is successful
            if (!$db) {
                throw new PDOException('Failed to connect to the database.');
            }

            // Ambil id_pembeli dari parameter URL
            $id_pembeli_param = $args['id'];

            // Buat query untuk memanggil stored procedure
            $query = $db->prepare('CALL GetPembeliByID(:id_pembeli_param)');
            $query->bindParam(':id_pembeli_param', $id_pembeli_param, PDO::PARAM_STR);
            $query->execute();

            // Ambil hasil dari stored procedure
            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            // Periksa apakah pengguna ditemukan
            if (empty($results)) {
                throw new PDOException('User Not Found');
            }

            // Ubah hasil menjadi JSON
            $response->getBody()->write(json_encode($results));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            if ($e->getMessage() === 'User Not Found') {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            } else {
                $response = $response->withStatus(500);
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            }

            return $response->withHeader('Content-Type', 'application/json');
        }
    });

    // post user
    $app->post('/pembeli', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // Ambil data dari body permintaan POST
        $parsedBody = $request->getParsedBody();
        $id_pembeli_new = $parsedBody['id_pembeli'];
        $nama_new = $parsedBody['nama'];
        $alamat_new = $parsedBody['alamat'];
        $email_new = $parsedBody['email'];

        // Buat query untuk memanggil stored procedure
        $query = $db->prepare('CALL CreatePembeli(:id_pembeli_new, :nama_new, :alamat_new, :email_new)');
        $query->bindParam(':id_pembeli_new', $id_pembeli_new, PDO::PARAM_STR);
        $query->bindParam(':nama_new', $nama_new, PDO::PARAM_STR);
        $query->bindParam(':alamat_new', $alamat_new, PDO::PARAM_STR);
        $query->bindParam(':email_new', $email_new, PDO::PARAM_STR);

        try {
            $query->execute();
            $response->getBody()->write(json_encode(['message' => 'Pembeli telah dibuat']));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // put user
    $app->put('/pembeli/{id}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $currentId = $args['id'];
        $nama_new = $parsedBody['nama'];
        $alamat_new = $parsedBody['alamat'];
        $email_new = $parsedBody['email'];
        $db = $this->get(PDO::class);

        // Validasi input jika diperlukan

        // Buat query untuk memanggil stored procedure
        $query = $db->prepare('CALL UpdatePembeli(:id_pembeli_new, :nama_new, :alamat_new, :email_new)');
        $query->bindParam(':id_pembeli_new', $currentId, PDO::PARAM_STR);
        $query->bindParam(':nama_new', $nama_new, PDO::PARAM_STR);
        $query->bindParam(':alamat_new', $alamat_new, PDO::PARAM_STR);
        $query->bindParam(':email_new', $email_new, PDO::PARAM_STR);

        try {
            $query->execute();
            $response->getBody()->write(json_encode(['message' => 'Pembeli dengan ID '.$currentId.' telah diperbarui']));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    // delete user
    $app->delete('/pembeli/{id}', function (Request $request, Response $response, $args) {
        $currentId = $args['id'];
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL HapusPembeli(:new_id_pembeli)');
            $query->bindParam(':new_id_pembeli', $currentId, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Data tidak ditemukan']));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Pembeli dengan ID '.$currentId.' telah dihapus dari database']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Database error: '.$e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });
};
