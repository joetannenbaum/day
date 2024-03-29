# Day

This package provides Dusk-like integration testing for your terminal commands. Under the hood, it requires the Unix `expect` command in order to function correctly.

> [!WARNING]
> This package is currently in active development. The API is subject to change.

> [!INFORMATION]
> If you're testing Laravel commands this package currently supports only Symfony style commands, it has not been updated for Prompts yet.

## Example

The following would run the `bellows launch` command from the `bellows-tester` directory and answer several questions as they appear in the terminal.

```php
use Day\Day;

use function Day\command;

command('bellows launch')
    ->fromDir(__DIR__ . '/../../bellows-tester')
    ->question('Which server would you like to use', 'bellows-tester')
    ->question('App Name', 'Bellows Test')
    ->deny('Enable quick deploy')
    ->confirm('Launch now')
    ->waitFor('Launched!', 60)
    ->exec();
```
