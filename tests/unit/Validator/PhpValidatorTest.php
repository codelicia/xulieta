<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\Validator;

use Codelicia\Xulieta\Validator\PhpValidator;
use Codelicia\Xulieta\ValueObject\SampleCode;
use PHPUnit\Framework\TestCase;

final class PhpValidatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider violationProvider
     */
    public function itShouldDetectViolationsOnPhpCode(bool $shouldHaveViolation, string $phpCode) : void
    {
        $sampleCode = new SampleCode('file.php', 'php', 0, $phpCode);
        $subjectUnderTest = new PhpValidator();
        self::assertEquals($shouldHaveViolation, $subjectUnderTest->hasViolation($sampleCode));
    }

    public function violationProvider() : array
    {
        return [
            'empty' => [
                'shouldHaveViolation' => false,
                'phpCode' => '',
            ],
            'echo hi' => [
                'shouldHaveViolation' => false,
                'phpCode' => 'echo "hi";',
            ],
            'with open tag' => [
                'shouldHaveViolation' => false,
                'phpCode' => '<?php echo "hi";',
            ],
            'with open tag multiline' => [
                'shouldHaveViolation' => false,
                'phpCode' => "<?php\necho 'hi';\necho 'bye';",
            ],
            'missing semicolon' => [
                'shouldHaveViolation' => true,
                'phpCode' => 'echo "hi"',
            ],
            'with open missing semicolon' => [
                'shouldHaveViolation' => true,
                'phpCode' => '<?php echo "hi"',
            ],
            'missing semicolon multiline' => [
                'shouldHaveViolation' => true,
                'phpCode' => "<?php\necho 'hi'\necho 'bye';",
            ],
        ];
    }
}
