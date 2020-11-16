--TEST--
Test for showing information when asking for verbose output
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/cli-php-tag.md -v');

--EXPECTF--
Loaded Parsers:
Codelicia\Xulieta\Parser\MarkdownParser
Codelicia\Xulieta\Parser\RstParser

Loaded Validators:
Codelicia\Xulieta\Validator\PhpValidator

Finding documentation files on tests/assets/cli-php-tag.md


     Everything is OK!
