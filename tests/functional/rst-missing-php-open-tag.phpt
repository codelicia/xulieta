--TEST--
Report a warning in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/missing-open-tag.rst');

--EXPECTF--
Finding documentation files on tests/assets/missing-open-tag.rst

Snippet missing PHP open tag on file: %A/tests/assets/missing-open-tag.rst

     Everything is OK!
