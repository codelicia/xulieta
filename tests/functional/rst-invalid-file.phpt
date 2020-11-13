--TEST--
Report error when the RST file cannot be parsed
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/invalid-file.rst');

--EXPECTF--
Finding documentation files on tests/assets/invalid-file.rst

Could not parse the file "tests/assets/invalid-file.rst"

     Operation failed!
