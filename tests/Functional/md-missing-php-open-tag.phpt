--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/missing-open-tag.md');

--EXPECTF--
Finding documentation files on tests/assets/missing-open-tag.md


     Everything is OK!
