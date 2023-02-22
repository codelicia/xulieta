--TEST--
Report a warning in case of missing PHP open tag
and show the validator that reported the error
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.md -vvv');

--EXPECTF--
Loaded OutputFormatters:
Codelicia\Xulieta\Output\Stdout
Codelicia\Xulieta\Output\Checkstyle

Loaded Parsers:
Codelicia\Xulieta\Parser\MarkdownParser
Codelicia\Xulieta\Parser\RstParser

Loaded Validators:
Codelicia\Xulieta\Validator\PhpValidator

Finding documentation files on tests/assets/immediately-open-tag.md

 --> tests/assets/immediately-open-tag.md
 1 |   echo 'Hello World!'
   | |
     = note: Syntax error, unexpected EOF, expecting ';' on line 2
       >>  by: Codelicia\Xulieta\Validator\PhpValidator


     Operation failed!
