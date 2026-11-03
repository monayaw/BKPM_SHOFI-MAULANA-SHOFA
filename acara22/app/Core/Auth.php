<?php

class Auth
{
    // Untuk praktikum lokal. Pada aplikasi nyata, simpan secret di environment variable.
    private const SECRET = 'kunci_rahasia_workshop_2026';

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }

    public static function generateToken(array $payload): string
    {
        $headerJson = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $header = self::base64urlEncode($headerJson ?: '{}');

        // Masa berlaku default 1 jam. Untuk tugas mandiri, ubah 3600 menjadi 300.
        $payload['iat'] = time();
        $payload['exp'] = time() + 300;
        $payloadJson = json_encode($payload);
        $payloadEnc = self::base64urlEncode($payloadJson ?: '{}');

        $signature = self::base64urlEncode(
            hash_hmac('sha256', $header . '.' . $payloadEnc, self::SECRET, true)
        );

        return $header . '.' . $payloadEnc . '.' . $signature;
    }

    public static function validateToken(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;
        $headerRaw = self::base64urlDecode($header);
        $payloadRaw = self::base64urlDecode($payload);

        if ($headerRaw === false || $payloadRaw === false) {
            return false;
        }

        $headerData = json_decode($headerRaw, true);
        $data = json_decode($payloadRaw, true);

        if (!is_array($headerData) || ($headerData['alg'] ?? '') !== 'HS256' ||
            !is_array($data) || !isset($data['exp']) || !is_numeric($data['exp'])) {
            return false;
        }

        $valid = self::base64urlEncode(
            hash_hmac('sha256', $header . '.' . $payload, self::SECRET, true)
        );

        if (!hash_equals($valid, $signature) || (int) $data['exp'] <= time()) {
            return false;
        }

        return $data;
    }

    public static function requireAuth(): array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = '';

        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) === 'authorization') {
                $authHeader = (string) $value;
                break;
            }
        }

        // Fallback untuk konfigurasi Apache tertentu.
        if ($authHeader === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = (string) $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!preg_match('/^Bearer\s+(\S+)$/i', trim($authHeader), $matches)) {
            Response::json(401, false, 'Token tidak ditemukan. Kirim header Authorization: Bearer <token>.');
        }

        $payload = self::validateToken($matches[1]);
        if ($payload === false) {
            Response::json(401, false, 'Token tidak valid atau kedaluwarsa.');
        }

        return $payload;
    }
}
