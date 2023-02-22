--TEST--
Ignore unsupported language
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/unsupported-language.md');

--EXPECTF--
Finding documentation files on tests/assets/unsupported-language.md


     Everything is OK!
