<?php

declare(strict_types=1);

(static fn () => require __DIR__ . '/../../vendor/autoload.php')();

return static function (string $params): void {
    $basePath = dirname(__DIR__, 2);

    system(sprintf('php %s/bin/xulieta check:erromeu %s', $basePath, $params));
};
