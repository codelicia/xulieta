--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/syntax-error.md');

--EXPECTF--
Finding documentation files on tests/assets/syntax-error.md

 --> tests/assets/syntax-error.md
 1 |   <?php
 2 |
 3 |   if (true) {
 4 |       echo 'Hello World!'
 5 |   }
   |  _^
   | |
     = note: Syntax error, unexpected '}', expecting ';' on line 5


     Operation failed!
