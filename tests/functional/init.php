<?php

declare(strict_types=1);

(static fn () => require __DIR__ . '/../../vendor/autoload.php')();

return static function (string $params): void {
    system(Psl\Str\format('php %s/bin/xulieta check:erromeu %s', Psl\Env\current_dir(), $params));
};
