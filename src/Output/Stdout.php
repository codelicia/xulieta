<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;
use Symfony\Component\Console\Output\OutputInterface;
use const PHP_EOL;

final class Stdout implements OutputFormatter
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function addViolation(Violation $violation) : void
    {
        $this->writeln('<error>Wrong code on file: ' . $violation->code()->file() . '</error>');
        $this->writeln($violation->message() . PHP_EOL);
        $this->writeln($violation->code()->code());
    }

    public function writeln(string $text) : void
    {
        $this->output->writeln($text);
    }
}
