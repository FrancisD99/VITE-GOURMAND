<?php

class JWTHandler
{
    private $secret = 'your-secret-key-change-this';
    private $algorithm = 'HS256';
    private $expiration = 300; // 5 minutes

    public function __construct($secret = null, $expiration = null)
    {
        if ($secret) {
            $this->secret = $secret;
        }
        if ($expiration) {
            $this->expiration = $expiration;
        }
    }

    // Encoder un JWT
    public function encode($payload)
    {
        $header = $this->base64url_encode(json_encode([
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ]));

        $payload['exp'] = time() + $this->expiration;
        $payload['iat'] = time();

        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = $this->base64url_encode(hash_hmac('sha256', "$header.$payload_encoded", $this->secret, true));

        return "$header.$payload_encoded.$signature";
    }

    // Décoder et valider un JWT
    public function decode($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // Vérifier la signature
        $valid_signature = $this->base64url_encode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        
        if (!hash_equals($signature, $valid_signature)) {
            return false;
        }

        // Décoder le payload
        $decoded_payload = json_decode($this->base64url_decode($payload), true);

        // Vérifier l'expiration
        if (isset($decoded_payload['exp']) && $decoded_payload['exp'] < time()) {
            return false;
        }

        return $decoded_payload;
    }

    // Vérifier si un token est valide
    public function isValid($token)
    {
        return $this->decode($token) !== false;
    }

    // Encoder en base64url
    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Décoder depuis base64url
    private function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }

    // Récupérer un token depuis les headers
    public static function getTokenFromRequest()
    {
        // Essayer différentes méthodes pour obtenir le token
        
        // Méthode 1: getallheaders() (Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $matches = [];
                    if (preg_match('/Bearer\s+(.+)/', $value, $matches)) {
                        return $matches[1];
                    }
                }
            }
        }
        
        // Méthode 2: $_SERVER (Nginx/IIS)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                return $matches[1];
            }
        }
        
        // Méthode 3: Vérifier REDIRECT_HTTP_AUTHORIZATION (certains serveurs)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.+)/', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}
