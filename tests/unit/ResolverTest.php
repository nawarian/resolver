<?php

declare(strict_types=1);

namespace Nawarian\Requirements;

use Generator;
use PHPUnit\Framework\TestCase;
use function React\Promise\reject;
use function React\Promise\resolve;
use RuntimeException;

class ResolverTest extends TestCase
{
    private Resolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new Resolver();
    }

    /**
     * @dataProvider resolverResolvesPromisesOnlyProvider
     *
     * @param Generator $resolvable
     * @param string $message
     */
    public function testResolverResolvesPromisesOnly(Generator $resolvable, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $this->resolver->resolve($resolvable);
    }

    public function resolverResolvesPromisesOnlyProvider(): array
    {
        $yieldsAString = function(): Generator  {
            yield 'a';
        };

        $yieldsAnObject = function(): Generator {
            yield (object) ['a' => 'b'];
        };

        return [
            [$yieldsAString(), 'Yielded value must be a Promise, "string" given'],
            [$yieldsAnObject(), 'Yielded value must be a Promise, "stdClass" given'],
        ];
    }

    public function testResolverWillResolvePromisesAndReturnToGenerator(): void
    {
        $resolvable = function(): Generator {
            $resolved = yield resolve('Resolved Value');

            return $resolved;
        };
        $generator = $resolvable();

        $this->resolver->resolve($generator);
        $this->assertEquals('Resolved Value', $generator->getReturn());
    }

    public function testResolverResolvesArraysOfPromises(): void
    {
        $resolvable = function(): Generator {
            list($first, $second) = yield [
                resolve(10),
                resolve(20),
            ];

            return $first + $second;
        };
        $generator = $resolvable();

        $this->resolver->resolve($generator);

        $this->assertEquals(30, $generator->getReturn());
    }

    public function testPromiseRejectionTriggersAnException(): void
    {
        $resolvable = function(): Generator {
            try {
                yield reject('My Rejection');
            } catch (RuntimeException $e) {
                return $e->getMessage();
            }
        };
        $generator = $resolvable();

        $this->resolver->resolve($generator);
        $this->assertEquals('My Rejection', $generator->getReturn());
    }
}
