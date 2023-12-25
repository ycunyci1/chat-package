<?php
namespace Dd1\Chat\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class JwtService
{
    /**
     * Generate a JWT from the user ID and claims.
     */
    public static function generateJwt($userId): string
    {
        // Конфигурация JWT
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(config('centrifugo.secret')));

        // Текущее время
        $now = new \DateTimeImmutable();

        // Создание токена
        $token = $config->builder()
            ->issuedAt($now) // (iat) Время, когда токен был создан
            ->expiresAt($now->modify('+1 hour')) // (exp) Время, когда токен истекает
            ->relatedTo($userId) // (sub) Идентификатор пользователя/клиента
            ->getToken($config->signer(), $config->signingKey()); // Получение токена

        return $token->toString();
    }
}
