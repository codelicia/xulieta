--TEST--
Report a warning using checkstyle in case of missing PHP open tag
--FILE--
<?php

$checkRunner = require __DIR__ . '/init.php';

$checkRunner('tests/assets/syntax-error-checkstyle.md --output=checkstyle');

--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="tests/assets/syntax-error-checkstyle.md">
    <error line="7" column="1" severity="error" message="Codelicia/Xulieta: Syntax error, unexpected '}', expecting ';' on line 5" source="Codelicia/Xulieta"/>
  </file>
</checkstyle>
