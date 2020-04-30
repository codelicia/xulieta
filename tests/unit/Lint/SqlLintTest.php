<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\Lint;

use Codelicia\Xulieta\Lint\SqlLint;
use PHPUnit\Framework\TestCase;

final class SqlLintTest extends TestCase
{
    /**
     * @test
     * @dataProvider violationProvider
     */
    public function itShouldDetectViolationsOnSql(bool $shouldHaveViolation, string $sql) : void
    {
        $subjectUnderTest = new SqlLint();
        self::assertEquals($shouldHaveViolation, $subjectUnderTest->hasViolation($sql));
    }

    public function violationProvider() : array
    {
        return [
            'empty' => [
                'shouldHaveViolation' => false,
                'sql' => '',
            ],
            'simple select' => [
                'shouldHaveViolation' => false,
                'sql' => 'select * from test',
            ],
            'select missing fields' => [
                'shouldHaveViolation' => true,
                'sql' => 'select from test',
            ],
            'missing select' => [
                'shouldHaveViolation' => true,
                'sql' => 'from test',
            ],
            'not a query' => [
                'shouldHaveViolation' => true,
                'sql' => 'banana is not a ;query',
            ],
        ];
    }
}
