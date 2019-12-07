<?php

declare(strict_types=1);

namespace Nawarian\Requirements;

use Generator;
use React\Promise\PromiseInterface;
use function React\Promise\all;
use RuntimeException;

class Resolver
{
    public function resolve(Generator $resolvable): void
    {
        /** @var PromiseInterface|array<PromiseInterface> $promiseOrPromises */
        foreach ($resolvable as $promiseOrPromises) {
            if ($this->yieldedValueIsInvalid($promiseOrPromises)) {
                throw new RuntimeException(
                    sprintf(
                        'Yielded value must be a Promise, "%s" given',
                        $this->getVariableType($promiseOrPromises)
                    )
                );
            }

            if (true === is_array($promiseOrPromises)) {
                $promiseOrPromises = all($promiseOrPromises);
            }

            $promiseOrPromises->then(
                function ($result) use ($resolvable) {
                    $resolvable->send($result);
                },
                function ($error) use ($resolvable) {
                    $resolvable->throw(new RuntimeException($error));
                }
            );
        }
    }

    private function yieldedValueIsInvalid($promiseOrPromises): bool
    {
        return false === $promiseOrPromises instanceof PromiseInterface && false === is_array($promiseOrPromises);
    }

    private function getVariableType($promiseOrPromises): string
    {
        return is_object($promiseOrPromises) ? get_class($promiseOrPromises) : gettype($promiseOrPromises);
    }
}
