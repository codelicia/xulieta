<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;
use Symfony\Component\Console\Output\OutputInterface;

use function htmlspecialchars;

final class Checkstyle implements OutputFormatter
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->output->writeln('<?xml version="1.0" encoding="UTF-8"?>');
        $this->output->writeln('<checkstyle>');
    }

    public function addViolation(Violation $violation): void
    {
        $this->output->writeln('  <file name="' . htmlspecialchars($violation->file()) . '">');

        $error  = '    ';
        $error .= '<error';
        $error .= ' line="' . $violation->absoluteLine() . '"';
        $error .= ' column="1"';
        $error .= ' severity="error"';
        $error .= ' message="Codelicia/Xulieta: ' . htmlspecialchars($violation->message()) . '"';
        $error .= ' source="Codelicia/Xulieta"';
        $error .= '/>';

        $this->output->writeln($error);
        $this->output->writeln('  </file>');
    }

    public function __destruct()
    {
        echo '</checkstyle>';
    }

    public function writeln(string $text): void
    {
        // Intentionally left empty
    }

    public static function canResolve(string $style): bool
    {
        return $style === 'checkstyle';
    }
}
