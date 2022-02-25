<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Unit\AutoPlugin;

use Codelicia\Xulieta\AutoPlugin\Register;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvent;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\TestCase;
use StdClass;

final class RegisterTest extends TestCase
{
    /** @test */
    public function itShouldRunOnlyOnDevEnvironment() : void
    {
        $event = $this->createMock(PackageEvent::class);
        $event->expects(self::once())->method('isDevMode')->willReturn(false);
        $event->expects(self::never())->method('getOperation');

        Register::scan($event);

        self::assertTrue(true);
    }

    /** @test */
    public function itShouldFailWhenGetOperationHasNoValidResult() : void
    {
        $event = $this->createMock(PackageEvent::class);
        $event->expects(self::once())->method('isDevMode')->willReturn(true);
        $event->expects(self::once())->method('getOperation')->willReturn(new StdClass);

        $this->expectExceptionMessage('assert($operation instanceof InstallOperation)');

        Register::scan($event);
    }

    /** @test */
    public function itShouldDoNothingWhenNoExtraKeyIsConfigured() : void
    {
        $event = $this->createMock(PackageEvent::class);
        $event->expects(self::once())->method('isDevMode')->willReturn(true);

        $installOperation = $this->createMock(InstallOperation::class);
        $package = $this->createMock(PackageInterface::class);

        $event->expects(self::once())->method('getOperation')->willReturn($installOperation);
        $installOperation->expects(self::once())->method('getPackage')->willReturn($package);

        $package->expects(self::once())->method('getExtra')->willReturn([]);

        $event->expects(self::never())->method('getIO');
        $event->expects(self::never())->method('getComposer');

        Register::scan($event);
    }
}
