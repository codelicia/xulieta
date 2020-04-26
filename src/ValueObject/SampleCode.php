<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final class SampleCode
{
    private string $language;

    private string $code;

    public function __construct(string $language, string $code)
    {
        $this->language = $language;
        $this->code     = $code;
    }

    public function language() : string
    {
        return $this->language;
    }

    public function code() : string
    {
        return $this->code;
    }
}
