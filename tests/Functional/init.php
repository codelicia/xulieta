<?php

declare(strict_types=1);

use function Psl\Env\current_dir;
use function Psl\Str\format;

(static fn () => require __DIR__ . '/../../vendor/autoload.php')();

return static function (string $params): void {
    system(format('php %s/bin/xulieta check:erromeu %s', current_dir(), $params));
};
