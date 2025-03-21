<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;
use Override;
use Psl\IO;
use Psl\Math;
use Psl\Str;
use Symfony\Component\Console\Output\OutputInterface;

use const PHP_EOL;

/**
 * @psalm-suppress UnusedClass
 * @psalm-suppress PossiblyUnusedMethod
 */
final readonly class Stdout implements OutputFormatter
{
    public function __construct(private OutputInterface $output)
    {
    }

    #[Override]
    public function addViolation(Violation $violation): void
    {
        IO\write_line(Str\format(' --> %s', $violation->code->file));

        $linesAround   = 5;
        $code          = $violation->code->code;
        $lines         = Str\split($code, PHP_EOL);
        $i             = 0;
        $errorOccurred = false;
        $startLine     = Math\max([0, $violation->violationLine - $linesAround]);
        $endLine       = Math\sum([$violation->violationLine, $linesAround]);
        foreach ($lines as $line) {
            $i++;

            if (! ($i >= $startLine && $i <= $endLine)) {
                continue;
            }

            if ($errorOccurred) {
                $text = empty($line) ? $line : ' ' . $line;
                $this->writeln(Str\pad_left((string) $i, 2, ' ') . ' | <fg=red>|</>' . $text);
            } else {
                $text = empty($line) ? $line : '   ' . $line;
                $this->writeln(Str\pad_left((string) $i, 2, ' ') . ' |' . $text);
            }

            if ($i !== $violation->violationLine) {
                continue;
            }

            $errorOccurred = true;
            /** @psalm-var int<0, max> $middleOfTheLine */
            $middleOfTheLine = (int) Math\round(Str\length($line) / 2);
            $this->writeln('   |  <fg=red>_' . Str\pad_left('^', $middleOfTheLine, '_') . '</>');
        }

        $this->output->writeln([
            '   | <fg=red>|</>',
            '     <fg=red>=</> note: <fg=yellow>' . $violation->message . '</>',
        ]);

        $this->output->writeln(
            '     <fg=red>  >> </> by: <fg=yellow>' . $violation->validatedBy . '</>',
            OutputInterface::VERBOSITY_VERBOSE,
        );

        IO\write_line('');
    }

    #[Override]
    public function writeln(string $text): void
    {
        $this->output->writeln($text);
    }

    #[Override]
    public static function canResolve(string $style): bool
    {
        return $style === 'stdout';
    }
}
