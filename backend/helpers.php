<?php
// ============================================================
//  Spendwise — Shared Helpers (JWT, Response, Auth)
// ============================================================

require_once __DIR__ . '/config.php';

// ---- CORS Headers ----
function setCorsHeaders(): void {
    header('Access-Control-Allow-Origin: '  . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// ---- JSON Response Helpers ----
function sendSuccess(array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => true, ...$data]);
    exit;
}

function sendError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// ---- Read JSON Request Body ----
function getBody(): array {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ============================================================
//  JWT — Simple implementation (no external library needed)
// ============================================================
function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwtCreate(array $payload): string {
    $header    = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['exp'] = time() + JWT_EXPIRY;
    $payload['iat'] = time();
    $body      = base64UrlEncode(json_encode($payload));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    return "$header.$body.$signature";
}

function jwtVerify(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $body, $sig] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;

    $payload = json_decode(base64UrlDecode($body), true);
    if (!$payload || $payload['exp'] < time()) return null;

    return $payload;
}

// ---- Auth Middleware ----
// XAMPP/Apache on Windows does not always pass Authorization header
// via $_SERVER['HTTP_AUTHORIZATION'] — so we check multiple sources.
function requireAuth(): array {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

    // Fallback: getallheaders() works on most Apache/XAMPP setups
    if (empty($authHeader) && function_exists('getallheaders')) {
        foreach (getallheaders() as $key => $val) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $val;
                break;
            }
        }
    }

    if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        sendError('Unauthorised — please log in.', 401);
    }

    $token   = substr($authHeader, 7);
    $payload = jwtVerify($token);
    if (!$payload) {
        sendError('Token invalid or expired — please log in again.', 401);
    }
    return $payload;
}
