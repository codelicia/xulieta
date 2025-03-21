<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;
use Override;
use Psl\IO;

use function htmlspecialchars;

/**
 * @psalm-suppress UnusedClass
 * @psalm-suppress PossiblyFalseArgument
 * @psalm-suppress PossiblyUnusedMethod
 */
final class Checkstyle implements OutputFormatter
{
    public function __construct()
    {
        IO\write_line('<?xml version="1.0" encoding="UTF-8"?>');
        IO\write_line('<checkstyle>');
    }

    #[Override]
    public function addViolation(Violation $violation): void
    {
        IO\write_line('  <file name="' . htmlspecialchars($violation->code->file) . '">');

        $error  = '    ';
        $error .= '<error';
        $error .= ' line="' . $violation->absoluteLine() . '"';
        $error .= ' column="1"';
        $error .= ' severity="error"';
        $error .= ' message="Codelicia/Xulieta: ' . htmlspecialchars($violation->message) . '"';
        $error .= ' source="Codelicia/Xulieta"';
        $error .= '/>';

        IO\write_line($error);
        IO\write_line('  </file>');
    }

    public function __destruct()
    {
        IO\write_line('</checkstyle>');
    }

    #[Override]
    public function writeln(string $text): void
    {
        // Intentionally left empty
    }

    #[Override]
    public static function canResolve(string $style): bool
    {
        return $style === 'checkstyle';
    }
}
