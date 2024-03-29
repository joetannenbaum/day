<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Day\Day;

use function Day\command;

(new Day('bellows launch'))
    // command('bellows launch')
    ->fromDir(__DIR__.'/../../bellows-tester')
    ->question('Which server would you like to use', 'blip-tester')
    ->question('App Name', 'Bellows Test')
    ->exec();
