<?php

declare(strict_types=1);

namespace Day;

function command(string $command): Day
{
    return new Day($command);
}
