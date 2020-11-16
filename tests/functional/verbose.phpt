--TEST--
Test for showing information when asking for verbose output
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/cli-php-tag.md -v');

--EXPECTF--
Loaded Plugins:
Codelicia\Xulieta\Plugin\PhpOnRstPlugin
Codelicia\Xulieta\Plugin\PhpOnMarkdownPlugin

Finding documentation files on tests/assets/cli-php-tag.md


     Everything is OK!
