--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.md');

--EXPECTF--
Finding documentation files on tests/assets/immediately-open-tag.md

 --> tests/assets/immediately-open-tag.md
 1 |   echo 'Hello World!'
   | |
     = note: Syntax error, unexpected EOF, expecting ';' on line 2


     Operation failed!
