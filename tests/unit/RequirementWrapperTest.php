<?php

declare(strict_types=1);

namespace Nawarian\Requirements;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;
use stdClass;

class RequirementWrapperTest extends TestCase
{
    public function testWrapperTurnsCallIntoPromise(): void
    {
        $service = new class {
            public function getById(int $id): int
            {
                return $id;
            }
        };

        /** @var PromiseInterface $call */
        $call = wrap($service)->getById(1);

        $result = null;
        $call->then(function (int $return) use (&$result) {
            $result = $return;
        });

        $this->assertInstanceOf(PromiseInterface::class, $call);
        $this->assertEquals(1, $result);
    }

    public function testWrapperThrowsExceptionWhenMethodDoesntExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method "stdClass::getById()" does not exist.');

        wrap(new stdClass())->getById(1);
    }
}
