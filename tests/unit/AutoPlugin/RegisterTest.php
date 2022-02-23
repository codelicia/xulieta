<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\AutoPlugin;

use Codelicia\Xulieta\AutoPlugin\Register;
use Composer\Installer\PackageEvent;
use PHPUnit\Framework\TestCase;

final class RegisterTest extends TestCase
{
    /** @test */
    public function itShouldOnlyRunOnDevMode(): void
    {
        $event = $this->createMock(PackageEvent::class);
        $event->expects(self::once())
            ->method('isDevMode')
            ->willReturn(false);

        $event->expects(self::never())
            ->method('getOperation');

        Register::scan($event);

        $this->assertTrue(true);
    }
}
