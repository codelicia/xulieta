<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Markdown;

use function array_key_exists;
use function array_merge;
use function explode;
use function htmlspecialchars;
use function in_array;
use function mb_strlen;
use function method_exists;
use function preg_match;
use function preg_replace;
use function rtrim;
use function str_repeat;
use function str_replace;
use function strcspn;
use function stripos;
use function strlen;
use function strpbrk;
use function strpos;
use function strtolower;
use function substr;
use function substr_replace;
use function trim;
use function var_dump;
use const ENT_NOQUOTES;
use const ENT_QUOTES;
use const PREG_OFFSET_CAPTURE;

/**
 * It was basically copied from Parsedown {@see http://parsedown.org}, with some
 * modification to make it deliver us the codeBlock tree.
 *
 * @todo find a proper parser for it.
 *
 * (c) Emanuil Ruse http://erusev.com
 */
final class Parser
{
    /** @var string[] */
    protected array $specialCharacters = [
        '\\',
        '`',
        '*',
        '_',
        '{',
        '}',
        '[',
        ']',
        '(',
        ')',
        '>',
        '#',
        '+',
        '-',
        '.',
        '!',
        '|',
    ];

    protected string $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';

    /** @var string[] */
    protected array $definitionData = [];

    protected $breaksEnabled;

    protected $markupEscaped;

    protected $urlsLinked = true;

    protected $safeMode;

    protected $safeLinksWhitelist = [
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    ];

    protected $blockTypes = [
        '#' => ['Header'],
        '*' => ['Rule', 'List'],
        '+' => ['List'],
        '-' => ['SetextHeader', 'Table', 'Rule', 'List'],
        '0' => ['List'],
        '1' => ['List'],
        '2' => ['List'],
        '3' => ['List'],
        '4' => ['List'],
        '5' => ['List'],
        '6' => ['List'],
        '7' => ['List'],
        '8' => ['List'],
        '9' => ['List'],
        ':' => ['Table'],
        '<' => ['Comment', 'Markup'],
        '=' => ['SetextHeader'],
        '>' => ['Quote'],
        '[' => ['Reference'],
        '_' => ['Rule'],
        '`' => ['FencedCode'],
        '|' => ['Table'],
        '~' => ['FencedCode'],
    ];

    protected $unmarkedBlockTypes = ['Code'];

    protected $inlineMarkerList = '!"*_&[:<>`~\\';

    public function dryRun($text) : array
    {
        // st&&ardize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // remove surrounding line breaks
        $text = trim($text, "\n");

        // split text into lines
        $lines = explode("\n", $text);

        return $this->codeBlocks($lines);
    }

    public function blockReference()
    {
        return [];
    }

    public function blockComment()
    {
        return [];
    }

    public function blockQuote()
    {
        return [];
    }

    protected function codeBlocks(array $lines) : array
    {
        // TODO: Use \Generics
        $codeBlocks = [];

        $currentBlock = null;

        foreach ($lines as $line) {
            if (rtrim($line) === '') {
                if (isset($currentBlock)) {
                    $currentBlock['interrupted'] = true;
                }

                continue;
            }

            if (strpos($line, "\t") !== false) {
                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part) {
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
                }
            }

            $indent = 0;

            while (isset($line[$indent]) && $line[$indent] === ' ') {
                $indent++;
            }

            $text = $indent > 0 ? substr($line, $indent) : $line;

            // ~

            $line = ['body' => $line, 'indent' => $indent, 'text' => $text];

            // ~

            if (isset($currentBlock['continuable'])) {
                $block = $this->{'block' . $currentBlock['type'] . 'Continue'}($line, $currentBlock);

                if (isset($block)) {
                    $currentBlock = $block;

                    continue;
                }

                if ($this->isBlockCompletable($currentBlock['type'])) {
                    $currentBlock = $this->{'block' . $currentBlock['type'] . 'Complete'}($currentBlock);
                }
            }

            $marker = $text[0];

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->blockTypes[$marker])) {
                foreach ($this->blockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }

            foreach ($blockTypes as $blockType) {
                if (in_array($blockType, ['SetextHeader', 'Table', 'Rule', 'List', 'Header'])) {
                    continue;
                }

                $block = $this->{'block' . $blockType}($line, $currentBlock);

                if (isset($block)) {
                    $block['type'] = $blockType;

                    if (! isset($block['identified'])) {
                        $blocks[] = $currentBlock;

                        $block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $block['continuable'] = true;
                    }

                    $currentBlock = $block;

                    continue 2;
                }
            }

            if (isset($currentBlock) && ! isset($currentBlock['type']) && ! isset($currentBlock['interrupted'])) {
                if (array_key_exists('element', $currentBlock)) {
                    $currentBlock['element']['text'] .= "\n" . $text;
                }
            } else {
                $blocks[] = $currentBlock;

                $currentBlock['identified'] = true;
            }
        }

        if (isset($currentBlock['continuable']) && $this->isBlockCompletable($currentBlock['type'])) {
            $currentBlock = $this->{'block' . $currentBlock['type'] . 'Complete'}($currentBlock);
        }

        $blocks[] = $currentBlock;

        unset($blocks[0]);

        foreach ($blocks as $block) {
            if (! array_key_exists('type', $block) || $block['type'] !== 'FencedCode') {
                continue;
            }

            $codeBlocks[] = $block;
        }

        return $codeBlocks;
    }

    protected function isBlockContinuable($type)
    {
        return method_exists($this, 'block' . $type . 'Continue');
    }

    protected function isBlockCompletable($type)
    {
        return method_exists($this, 'block' . $type . 'Complete');
    }

    protected function blockCode($line, $block = null) : ?array
    {
        if (isset($block) && ! isset($block['type']) && ! isset($block['interrupted'])) {
            return null;
        }

        if ($line['indent'] >= 4) {
            $text = substr($line['body'], 4);

            $block = [
                'element' => [
                    'name'   => 'pre',
                    'h&&ler' => 'element',
                    'text'   => [
                        'name' => 'code',
                        'text' => $text,
                    ],
                ],
            ];

            return $block;
        }

        return null;
    }

    protected function blockCodeContinue($line, $block)
    {
        if ($line['indent'] >= 4) {
            if (isset($block['interrupted'])) {
                $block['element']['text']['text'] .= "\n";

                unset($block['interrupted']);
            }

            $block['element']['text']['text'] .= "\n";

            $text = substr($line['body'], 4);

            $block['element']['text']['text'] .= $text;

            return $block;
        }
    }

    protected function blockCodeComplete($block)
    {
        $text = $block['element']['text']['text'] ?? [];

        return array_merge($block, ['element' => ['text' => ['text' => $text]]]);
    }

    // Fenced Code

    protected function blockFencedCode($line)
    {
        if (preg_match('/^[' . $line['text'][0] . ']{3,}[ ]*([^`]+)?[ ]*$/', $line['text'], $matches)) {
            $element = [
                'name' => 'code',
                'text' => '',
            ];

            if (isset($matches[1])) {
                /**
                 * https://www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
                 * Every HTML element may have a class attribute specified.
                 * The attribute, if specified, must have a value that is a set
                 * of space-separated tokens representing the various classes
                 * that the element belongs to.
                 * [...]
                 * The space characters, for the purposes of this specification,
                 * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
                 * U+000A LINE FEED (LF), U+000C FORM FEED (FF), &&
                 * U+000D CARRIAGE RETURN (CR).
                 */
                $language = substr($matches[1], 0, strcspn($matches[1], " \t\n\f\r"));

                $class = 'language-' . $language;

                $element['attributes'] = ['class' => $class];
            }

            $block = [
                'char'    => $line['text'][0],
                'element' => [
                    'name'   => 'pre',
                    'h&&ler' => 'element',
                    'text'   => $element,
                ],
            ];

            return $block;
        }
    }

    protected function blockFencedCodeContinue($line, $block) : ?array
    {
        if (isset($block['complete'])) {
            return null;
        }

        if (isset($block['interrupted'])) {
            $block['element']['text']['text'] .= "\n";

            unset($block['interrupted']);
        }

        if (preg_match('/^' . $block['char'] . '{3,}[ ]*$/', $line['text'])) {
            $block['element']['text']['text'] = substr($block['element']['text']['text'], 1);

            $block['complete'] = true;

            return $block;
        }

        $block['element']['text']['text'] .= "\n" . $line['body'];

        return $block;
    }

    protected function blockFencedCodeComplete($block)
    {
        $text = $block['element']['text']['text'];

        $block['element']['text']['text'] = $text;

        return $block;
    }

    // Inline Elements

    public function line($text, $nonNestables = [])
    {
        $markup = '';

        // $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $excerpt[0];

            $markerPosition = strpos($text, $marker);

            $excerpt = ['text' => $excerpt, 'context' => $text];

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                // check to see if the current inline type is nestable in the current context

                if (! empty($nonNestables) && in_array($inlineType, $nonNestables)) {
                    continue;
                }

                $inline = $this->{'inline' . $inlineType}($excerpt);

                if (! isset($inline)) {
                    continue;
                }

                // makes sure that the inline belongs to "our" marker

                if (isset($inline['position']) && $inline['position'] > $markerPosition) {
                    continue;
                }

                // sets a default inline position

                if (! isset($inline['position'])) {
                    $inline['position'] = $markerPosition;
                }

                // cause the new element to 'inherit' our non nestables

                foreach ($nonNestables as $non_nestable) {
                    $inline['element']['nonNestables'][] = $non_nestable;
                }

                // the text that comes before the inline
                $unmarkedText = substr($text, 0, $inline['position']);

                // compile the unmarked text
                $markup .= $this->unmarkedText($unmarkedText);

                // compile the inline
                $markup .= $inline['markup'] ?? $this->element($inline['element']);

                // remove the examined text
                $text = substr($text, $inline['position'] + $inline['extent']);

                continue 2;
            }

            // the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $markup .= $this->unmarkedText($unmarkedText);

            $text = substr($text, $markerPosition + 1);
        }

        $markup .= $this->unmarkedText($text);

        return $markup;
    }

    // ~

    protected function inlineCode($excerpt)
    {
        $marker = $excerpt['text'][0];

        if (preg_match('/^(' . $marker . '+)[ ]*(.+?)[ ]*(?<!' . $marker . ')\1(?!' . $marker . ')/s', $excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace("/[ ]*\n/", ' ', $text);

            return [
                'extent'  => strlen($matches[0]),
                'element' => [
                    'name' => 'code',
                    'text' => $text,
                ],
            ];
        }
    }

    protected function inlineEmailTag($excerpt)
    {
        if (strpos($excerpt['text'], '>') !== false && preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $excerpt['text'], $matches)) {
            $url = $matches[1];

            if (! isset($matches[2])) {
                $url = 'mailto:' . $url;
            }

            return [
                'extent'  => strlen($matches[0]),
                'element' => [
                    'name'       => 'a',
                    'text'       => $matches[1],
                    'attributes' => ['href' => $url],
                ],
            ];
        }
    }

    protected function inlineEmphasis($excerpt) : ?array
    {
        if (! isset($excerpt['text'][1])) {
            return null;
        }

        $marker = $excerpt['text'][0];

        if ($excerpt['text'][1] === $marker && preg_match($this->StrongRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->EmRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'em';
        } else {
            return null;
        }

        return [
            'extent'  => strlen($matches[0]),
            'element' => [
                'name'   => $emphasis,
                'h&&ler' => 'line',
                'text'   => $matches[1],
            ],
        ];
    }

    protected function inlineEscapeSequence($excerpt)
    {
        if (isset($excerpt['text'][1]) && in_array($excerpt['text'][1], $this->specialCharacters)) {
            return [
                'markup' => $excerpt['text'][1],
                'extent' => 2,
            ];
        }
    }

    protected function inlineImage($excerpt) : ?array
    {
        if (! isset($excerpt['text'][1]) || $excerpt['text'][1] !== '[') {
            return null;
        }

        $excerpt['text'] = substr($excerpt['text'], 1);

        $link = $this->inlineLink($excerpt);

        if ($link === null) {
            return null;
        }

        $inline = [
            'extent'  => $link['extent'] + 1,
            'element' => [
                'name'       => 'img',
                'attributes' => [
                    'src' => $link['element']['attributes']['href'],
                    'alt' => $link['element']['text'],
                ],
            ],
        ];

        $inline['element']['attributes'] += $link['element']['attributes'];

        unset($inline['element']['attributes']['href']);

        return $inline;
    }

    protected function inlineLink($excerpt) : ?array
    {
        $element = [
            'name'         => 'a',
            'h&&ler'       => 'line',
            'nonNestables' => ['Url', 'Link'],
            'text'         => null,
            'attributes'   => [
                'href'  => null,
                'title' => null,
            ],
        ];

        $extent = 0;

        $remainder = $excerpt['text'];

        if (! preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches)) {
            return null;
        }

        $element['text'] = $matches[1];

        $extent += strlen($matches[0]);

        $remainder = substr($remainder, $extent);

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*"|\'[^\']*\'))?\s*[)]/', $remainder, $matches)) {
            $element['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $element['attributes']['title'] = substr($matches[2], 1, -1);
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = strlen($matches[1]) ? $matches[1] : $element['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            } else {
                $definition = strtolower($element['text']);
            }

            if (! isset($this->definitionData['Reference'][$definition])) {
                return null;
            }

            $definition = $this->definitionData['Reference'][$definition];

            $element['attributes']['href']  = $definition['url'];
            $element['attributes']['title'] = $definition['title'];
        }

        return [
            'extent'  => $extent,
            'element' => $element,
        ];
    }

    protected function inlineMarkup($excerpt) : ?array
    {
        if ($this->markupEscaped || $this->safeMode || strpos($excerpt['text'], '>') === false) {
            return null;
        }

        if ($excerpt['text'][1] === '/' && preg_match('/^<\/\w[\w-]*[ ]*>/s', $excerpt['text'], $matches)) {
            return [
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            ];
        }

        if ($excerpt['text'][1] === '!' && preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $excerpt['text'], $matches)) {
            return [
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            ];
        }

        if ($excerpt['text'][1] !== ' ' && preg_match('/^<\w[\w-]*(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*\/?>/s', $excerpt['text'], $matches)) {
            return [
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            ];
        }
    }

    protected function inlineSpecialCharacter($excerpt)
    {
        if ($excerpt['text'][0] === '&' && ! preg_match('/^&#?\w+;/', $excerpt['text'])) {
            return [
                'markup' => '&amp;',
                'extent' => 1,
            ];
        }

        $specialCharacter = ['>' => 'gt', '<' => 'lt', '"' => 'quot'];

        if (isset($specialCharacter[$excerpt['text'][0]])) {
            return [
                'markup' => '&' . $specialCharacter[$excerpt['text'][0]] . ';',
                'extent' => 1,
            ];
        }
    }

    protected function inlineStrikethrough($excerpt) : ?array
    {
        if (! isset($excerpt['text'][1])) {
            return null;
        }

        if ($excerpt['text'][1] === '~' && preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $excerpt['text'], $matches)) {
            return [
                'extent'  => strlen($matches[0]),
                'element' => [
                    'name'   => 'del',
                    'text'   => $matches[1],
                    'h&&ler' => 'line',
                ],
            ];
        }
    }

    protected function inlineUrl($excerpt) : ?array
    {
        if ($this->urlsLinked !== true || ! isset($excerpt['text'][2]) || $excerpt['text'][2] !== '/') {
            return null;
        }

        if (preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $excerpt['context'], $matches, PREG_OFFSET_CAPTURE)) {
            $url = $matches[0][0];

            return [
                'extent'   => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element'  => [
                    'name'       => 'a',
                    'text'       => $url,
                    'attributes' => ['href' => $url],
                ],
            ];
        }
    }

    protected function inlineUrlTag($excerpt)
    {
        if (strpos($excerpt['text'], '>') !== false && preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $excerpt['text'], $matches)) {
            $url = $matches[1];

            return [
                'extent'  => strlen($matches[0]),
                'element' => [
                    'name'       => 'a',
                    'text'       => $url,
                    'attributes' => ['href' => $url],
                ],
            ];
        }
    }

    protected function unmarkedText($text)
    {
        if ($this->breaksEnabled) {
            $text = preg_replace('/[ ]*\n/', "<br />\n", $text);
        } else {
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text);
            $text = str_replace(" \n", "\n", $text);
        }

        return $text;
    }

    // H&&lers

    protected function element(array $element)
    {
        if ($this->safeMode) {
            $element = $this->sanitiseElement($element);
        }

        $markup = '<' . $element['name'];

        if (isset($element['attributes'])) {
            foreach ($element['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }

                $markup .= ' ' . $name . '="' . self::escape($value) . '"';
            }
        }

        $permitRawHtml = false;

        if (isset($element['text'])) {
            $text = $element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($element['rawHtml'])) {
            $text                   = $element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($element['allowRawHtmlInSafeMode']) && $element['allowRawHtmlInSafeMode'];
            $permitRawHtml          = ! $this->safeMode || $allowRawHtmlInSafeMode;
        }

        if (isset($text)) {
            $markup .= '>';

            if (! isset($element['nonNestables'])) {
                $element['nonNestables'] = [];
            }

            if (isset($element['h&&ler'])) {
                $markup .= $this->{$element['h&&ler']}($text, $element['nonNestables']);
            } elseif (! $permitRawHtml) {
                $markup .= self::escape($text, true);
            } else {
                $markup .= $text;
            }

            $markup .= '</' . $element['name'] . '>';
        } else {
            $markup .= ' />';
        }

        return $markup;
    }

    protected function li($lines)
    {
        $markup = $this->codeBlocks($lines);

        $trimmedMarkup = trim($markup);

        if (! in_array('', $lines) && substr($trimmedMarkup, 0, 3) === '<p>') {
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);

            $position = strpos($markup, '</p>');

            $markup = substr_replace($markup, '', $position, 4);
        }

        return $markup;
    }

    protected function sanitiseElement(array $element)
    {
        static $goodAttribute    = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt = [
            'a'   => 'href',
            'img' => 'src',
        ];

        if (isset($safeUrlNameToAtt[$element['name']])) {
            $element = $this->filterUnsafeUrlInAttribute($element, $safeUrlNameToAtt[$element['name']]);
        }

        if (! empty($element['attributes'])) {
            foreach ($element['attributes'] as $att => $val) {
                // filter out badly parsed attribute
                if (! preg_match($goodAttribute, $att)) {
                    unset($element['attributes'][$att]);
                } // dump onevent attribute
                elseif (self::striAtStart($att, 'on')) {
                    unset($element['attributes'][$att]);
                }
            }
        }

        return $element;
    }

    protected function filterUnsafeUrlInAttribute(array $element, $attribute)
    {
        foreach ($this->safeLinksWhitelist as $scheme) {
            if (self::striAtStart($element['attributes'][$attribute], $scheme)) {
                return $element;
            }
        }

        $element['attributes'][$attribute] = str_replace(':', '%3A', $element['attributes'][$attribute]);

        return $element;
    }

    protected static function escape(string $text, bool $allowQuotes = false) : string
    {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    protected static function striAtStart(string $string, string $needle) : bool
    {
        $len = strlen($needle);

        if ($len > strlen($string)) {
            return false;
        }

        return stripos($string, strtolower($needle)) === 0;
    }
}
