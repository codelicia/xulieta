--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/immediately-open-tag.rst');

--EXPECTF--
Finding documentation files on tests/assets/immediately-open-tag.rst

 --> tests/assets/immediately-open-tag.rst
 1 |   echo "Hello World!"
 2 |
   | |
     = note: Syntax error, unexpected EOF, expecting ';' on line 3


     Operation failed!
