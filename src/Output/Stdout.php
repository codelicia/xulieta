<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;
use Symfony\Component\Console\Output\OutputInterface;

use function explode;
use function max;
use function round;
use function str_pad;
use function strlen;

use const PHP_EOL;
use const STR_PAD_LEFT;

final class Stdout implements OutputFormatter
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function addViolation(Violation $violation): void
    {
        $this->output->writeln(' --> ' . $violation->file());

        $linesAround   = 5;
        $code          = $violation->code()->code();
        $lines         = explode(PHP_EOL, $code);
        $i             = 0;
        $errorOccurred = false;
        $startLine     = max(0, $violation->violationLine() - $linesAround);
        $endLine       = $violation->violationLine() + $linesAround;
        foreach ($lines as $line) {
            $i++;

            if (! ($i >= $startLine && $i <= $endLine)) {
                continue;
            }

            if ($errorOccurred) {
                $text = empty($line) ? $line : ' ' . $line;
                $this->output->writeln(str_pad((string) $i, 2, ' ', STR_PAD_LEFT) . ' | <fg=red>|</>' . $text);
            } else {
                $text = empty($line) ? $line : '   ' . $line;
                $this->output->writeln(str_pad((string) $i, 2, ' ', STR_PAD_LEFT) . ' |' . $text);
            }

            if ($i !== $violation->violationLine()) {
                continue;
            }

            $errorOccurred   = true;
            $middleOfTheLine = (int) round(strlen($line) / 2);
            $this->output->writeln('   |  <fg=red>_' . str_pad('^', $middleOfTheLine, '_', STR_PAD_LEFT) . '</>');
        }

        $this->output->writeln([
            '   | <fg=red>|</>',
            '     <fg=red>=</> note: <fg=yellow>' . $violation->message() . '</>',
            '',
        ]);
    }

    public function writeln(string $text): void
    {
        $this->output->writeln($text);
    }
}
