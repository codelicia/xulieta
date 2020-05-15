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
    public function itShouldDetectViolationsOnSql(
        string $sql,
        bool $shouldHaveViolation,
        string $violationList
    ) : void {
        $subjectUnderTest = new SqlLint();
        self::assertEquals($shouldHaveViolation, $subjectUnderTest->hasViolation($sql));
        self::assertEquals($violationList, $subjectUnderTest->getViolation($sql));
    }

    public function violationProvider() : array
    {
        return [
            'empty' => [
                'sql' => '',
                'shouldHaveViolation' => false,
                'violationList' => '',
            ],
            'simple select' => [
                'sql' => 'select * from test',
                'shouldHaveViolation' => false,
                'violationList' => '',
            ],
            'select missing fields' => [
                'sql' => 'select from test',
                'shouldHaveViolation' => true,
                'violationList' => 'An expression was expected.',
            ],
            'select with missing close parenthesis' => [
                'sql' => 'SELECT , FROM User',
                'shouldHaveViolation' => true,
                'violationList' => "An expression was expected.\nUnexpected token.",
            ],
            'missing select' => [
                'sql' => 'from test',
                'shouldHaveViolation' => true,
                'violationList' => 'Unrecognized statement type.',
            ],
            'not a query' => [
                'sql' => 'banana is not a ;query',
                'shouldHaveViolation' => true,
                'violationList' => "Unexpected beginning of statement.\nUnrecognized statement type.",
            ],
        ];
    }
}
