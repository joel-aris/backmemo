<?php

declare(strict_types=1);

if (! function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        $result = preg_split('/'.$pattern.'/u', $string, $limit);

        return $result === false ? false : $result;
    }
}

if (! function_exists('validika_hash')) {
    function validika_hash(string ...$parts): string
    {
        return hash('sha256', implode('|', $parts));
    }
}

if (! function_exists('validika_signature')) {
    function validika_signature(string $payload): string
    {
        $key = (string) config('app.validika_signing_key', env('VALIDIKA_SIGNING_KEY', 'change-me'));

        return hash_hmac('sha256', $payload, $key);
    }
}
