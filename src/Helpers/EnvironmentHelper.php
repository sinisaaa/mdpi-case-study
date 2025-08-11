<?php

declare(strict_types=1);

namespace App\Helpers;

final class EnvironmentHelper {

    /** @var string  */
    public const ENV_PROD = 'prod';

    /**
     * @return bool
     */
    public static function isInProdMode(): bool
    {
        $env = getenv('APP_ENV');

        return $env === self::ENV_PROD;
    }

}
