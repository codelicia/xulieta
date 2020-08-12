--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.md');

--EXPECTF--
Finding documentation files on tests/assets/immediately-open-tag.md

Wrong code on file: tests/assets/immediately-open-tag.md
Syntax error, unexpected EOF, expecting ';' on line 2

echo 'Hello World!'

     Operation failed!
