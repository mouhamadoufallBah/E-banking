<?php

namespace Moohamad\EBankingApi\Core;
use DateTime;


const SECRET = '9i8$F!vH@iZvRzVp0#uGh%UwE9kPcE1!0!M3P0k$sJ8tQy^uP7N!QmX7N#zLrN#P!';

class Jwt
{
    public function generate(array $header, array $payload, string $secret, int $validity = 86400): string
    {

        if ($validity > 0) {
            $now = new DateTime();
            $expiration = $now->getTimestamp() + $validity;
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $expiration;
        }

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        $secret = base64_encode(SECRET);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        $jwt = $base64Header . '.' . $base64Payload . '.' . $signature;

        return $jwt;
    }

    public function check(string $tokken, string $secret): bool
    {
        $header = $this->getHeader($tokken);
        $payload = $this->getPayload($tokken);

        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $tokken === $verifToken;
    }
 
    public function getHeader(string $tokken)
    {
        $array = explode('.', $tokken);

        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }
    public function getPayload(string $tokken)
    {
        $array = explode('.', $tokken);

        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    public function isExpired($tokken): bool
    {
        $payload = $this->getPayload($tokken);

        $now = new DateTime();

        return $payload['exp'] < $now->getTimestamp();
    }

    public function isValid($tokken): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $tokken
        ) == 1;
    }
}


