--TEST--
Report a warning in case of missing PHP open tag
and show the validator that reported the error
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.rst -vvv');

--EXPECTF--
Loaded OutputFormatters:
Codelicia\Xulieta\Output\Stdout
Codelicia\Xulieta\Output\Checkstyle

Loaded Parsers:
Codelicia\Xulieta\Parser\MarkdownParser
Codelicia\Xulieta\Parser\RstParser

Loaded Validators:
Codelicia\Xulieta\Validator\PhpValidator

Finding documentation files on tests/assets/immediately-open-tag.rst

 --> tests/assets/immediately-open-tag.rst
 1 |   echo "Hello World!"
 2 |
   | |
     = note: Syntax error, unexpected EOF, expecting ';' on line 3
       >>  by: Codelicia\Xulieta\Validator\PhpValidator


     Operation failed!
