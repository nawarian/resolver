<?php

declare(strict_types=1);

namespace Nawarian\Requirements;

use InvalidArgumentException;
use React\Promise\Promise;
use Throwable;

class RequirementWrapper
{
    private object $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function __call($name, $arguments)
    {
        if (false === method_exists($this->target, $name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Method "%s::%s()" does not exist.',
                    get_class($this->target),
                    $name
                )
            );
        }

        $defer = new Promise(function (callable $resolve, callable $reject) use ($name, $arguments) {
            try {
                $resolve(call_user_func_array([$this->target, $name], $arguments));
            } catch (Throwable $e) {
                $reject($e);
            }
        });

        return $defer;
    }
}
