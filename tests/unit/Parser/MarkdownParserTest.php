<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\Parser;

use Codelicia\Xulieta\Parser\MarkdownParser;
use Codelicia\Xulieta\ValueObject\SampleCode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class MarkdownParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider markdownProvider
     */
    public function itShouldExtractBlockCodeFromMarkdown(string $markdown, array $expectedCodeBlock): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->expects(self::once())->method('getContents')->willReturn($markdown);
        $file->expects(self::atLeast(1))->method('getPathname')->willReturn('fake-file.md');

        self::assertEquals($expectedCodeBlock, (new MarkdownParser())->getAllSampleCodes($file));
    }

    public function markdownProvider(): array
    {
        return [
            'simple block' => [
                'markdown' => '
# Markdown

with a simple block

```
x = 1
```

## Multi line

```
y = 2;
z = 10;
```

some more text
```sql
SELECT * FROM Account LIMIT 2
```
                ',
                'expectedCodeBlock' => [
                    new SampleCode('fake-file.md', '', 5, 'x = 1'),
                    new SampleCode('fake-file.md', '', 11, "y = 2;\nz = 10;"),
                    new SampleCode('fake-file.md', 'sql', 17, 'SELECT * FROM Account LIMIT 2'),
                ],
            ],
            'code block' => [
                'markdown' => "
# Markdown

with a code block

```php
echo 'hi';
echo 'bye';
```
                ",
                'expectedSampleCode' => [
                    new SampleCode('fake-file.md', 'php', 5, "echo 'hi';\necho 'bye';"),
                ],
            ],
        ];
    }
}
