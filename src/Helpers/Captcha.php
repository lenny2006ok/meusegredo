<?php
namespace MeuSegredo\Helpers;

class Captcha {
    public static function verify($response) {
        $config = require __DIR__ . '/../../config/env.php';
        $secretKey = $config['TURNSTILE_SECRET_KEY'] ?? '';

        if (empty($secretKey)) {
            return true;
        }

        if (empty($response)) {
            return false;
        }

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $ip = \MeuSegredo\Core\Security::getClientIP();

        $data = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $ip
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return false;
        }

        $json = json_decode($result, true);
        return isset($json['success']) && $json['success'] === true;
    }
}
