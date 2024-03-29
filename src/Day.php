<?php

declare(strict_types=1);

namespace Day;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Day
{
    protected Collection $script;

    protected ?string $dir = null;

    protected OutputFormatter $formatter;

    protected $tmpFile;

    public function __construct(protected string $command)
    {
        $this->script = collect();
        $this->formatter = new OutputFormatter(true);
    }

    public function fromDir(string $dir)
    {
        $this->dir = $dir;

        return $this;
    }

    public function enter()
    {
        $this->type('');

        return $this;
    }

    public function waitFor(string $text, int $timeout = 5)
    {
        $this->script->push('set timeout '.$timeout);

        $text = addslashes($this->formatter->format($text));

        $this->script->push(<<<EXPECT
        expect {
            -ex "$text" { }
            timeout { puts "Not found: $text"; exit 2 }
        }
        EXPECT);

        return $this;
    }

    public function question(string $text, string|int|null $answer = null, int $timeout = 5)
    {
        $this->waitFor($text, $timeout);
        $this->waitFor('>', $timeout);

        $this->type($answer);

        return $this;
    }

    public function waitForConfirmation(string $text, bool $answer = true, int $timeout = 5)
    {
        $this->waitFor($text, $timeout);
        $this->waitFor('>', $timeout);

        $this->type($answer ? 'y' : 'n');

        return $this;
    }

    public function confirm(string $text, int $timeout = 5)
    {
        $this->waitForConfirmation($text, true, $timeout);

        return $this;
    }

    public function deny(string $text, int $timeout = 5)
    {
        $this->waitForConfirmation($text, false, $timeout);

        return $this;
    }

    public function type(?string $text = null)
    {
        $text ??= '';

        $this->script->push(sprintf('send -- "%s\r"', addslashes($text)));

        return $this;
    }

    public function exec()
    {
        $this->script = collect([
            <<<'BEFORE'
            #!/usr/bin/expect
            expect_before {
                eof { exit 1 }
            }
            BEFORE,
            'spawn '.$this->command,
        ])
            ->concat($this->script)
            ->push('expect eof');

        $this->tmpFile = tmpfile();

        fwrite($this->tmpFile, $this->script->implode(PHP_EOL));

        chdir($this->dir ?? base_path());

        $path = stream_get_meta_data($this->tmpFile)['uri'];

        chmod($path, 0755);

        $proc = popen($path, 'r');

        while (! feof($proc)) {
            $output = fread($proc, 4096);
            echo $output;
            @flush();
        }

        pclose($proc);
        fclose($this->tmpFile);
    }

    public function __destruct()
    {
        if (isset($this->tmpFile) && is_resource($this->tmpFile)) {
            @fclose($this->tmpFile);
        }
    }
}
