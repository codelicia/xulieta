<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\External;

use Codelicia\Xulieta\External\Markinho;
use Codelicia\Xulieta\ValueObject\SampleCode;
use PHPUnit\Framework\TestCase;

final class MarkinhoTest extends TestCase
{
    /**
     * @test
     * @dataProvider markdownProvider
     */
    public function itShouldExtractBlockCodeFromMarkdown(string $markdown, array $expectedCodeBlock) : void
    {
        self::assertEquals($expectedCodeBlock, Markinho::extractCodeBlocks('fake-file.md', $markdown));
    }

    public function markdownProvider() : array
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
                ',
                'expectedCodeBlock' => [
                    new SampleCode('fake-file.md', '', 5, "x = 1"),
                    new SampleCode('fake-file.md', '', 11, "y = 2;\nz = 10;"),
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
