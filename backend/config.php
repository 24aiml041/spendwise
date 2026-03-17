<?php

define('DB_HOST',     'localhost');
define('DB_NAME',     'spendwise');
define('DB_USER',     'root');       // your MySQL username
define('DB_PASS',     '');           // your MySQL password
define('DB_CHARSET',  'utf8mb4');

// JWT Secret — change this to a long random string in production
define('JWT_SECRET',  'spendwise_super_secret_key_change_me');
define('JWT_EXPIRY',  86400); // 24 hours in seconds

// CORS — set to your frontend URL in production
define('ALLOWED_ORIGIN', '*');

// Base URL for API
const BASE_URL = 'http://localhost/spendwise-php/backend/api';

// ============================================================
//  Database Connection (PDO)
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
