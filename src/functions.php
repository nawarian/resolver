<?php

namespace Nawarian\Requirements;

function wrap(object $target)
{
    return new RequirementWrapper($target);
}
