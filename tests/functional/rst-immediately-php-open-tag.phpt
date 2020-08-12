--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.rst');

--EXPECTF--
Finding documentation files on tests/assets/immediately-open-tag.rst

Wrong code on file: tests/assets/immediately-open-tag.rst
Syntax error, unexpected EOF, expecting ';' on line 3

echo "Hello World!"


     Operation failed!
