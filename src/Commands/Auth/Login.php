<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Commands\Auth;

use Illuminate\Support\Carbon;
use LaravelZero\Framework\Commands\Command;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Login extends Command
{
    /**
     * @var string[]
     */
    private static array $passThroughVariables = [
        'APP_ENV',
        'PATH',
        'PHP_CLI_SERVER_WORKERS',
        'PHP_IDE_CONFIG',
        'SYSTEMROOT',
        'XDEBUG_CONFIG',
        'XDEBUG_MODE',
        'XDEBUG_SESSION',
    ];

    protected $description = 'Authenticate with the configured OAuth service provider';

    private bool $serverRunningHasBeenDisplayed = false;

    /**
     * @var array<int, array{0: int, 1: bool|string|Carbon}>
     */
    private array $requestsPool = [];

    public function __construct()
    {
        $this->signature = config('oauth.commands.login');

        parent::__construct();
    }

    public function handle(AbstractProvider $provider): int
    {
        $authorizationUrl = $provider->getAuthorizationUrl();

        (new Process([$this->getUrlOpenCommand(), $authorizationUrl]))->run();

        $process = $this->startProcess();

        while ($process->isRunning()) {
            usleep(1000 * 500);
        }

        return $process->getExitCode() ?? Command::SUCCESS;
    }

    private function startProcess(): Process
    {
        $process = new Process(
            command: $this->serverCommand(),
            cwd: base_path(),
            env: collect($_ENV)->mapWithKeys(
                function ($value, $key) {
                    return in_array($key, self::$passThroughVariables, true) ? [$key => $value] : [$key => false];
                }
            )->all()
        );

        $process->start($this->handleProcessOutput($process));

        return $process;
    }

    /**
     * @return string[]
     */
    private function serverCommand(): array
    {
        $redirectUri = str(config('oauth.auth.redirect_uri'))
            ->replaceStart('http://', '')
            ->replaceStart('https://', '');

        return [
            (new PhpExecutableFinder())->find(includeArgs: false),
            '-S',
            $redirectUri->toString(),
            __DIR__ . '/../../../bin/server.php',
        ];
    }

    /**
     * @return callable(string, string): void
     */
    private function handleProcessOutput(Process $process): callable
    {
        return fn (string $type, string $buffer) => str($buffer)->explode("\n")->each(
            function (string $line) use ($process): void {
                if (str($line)->contains('Development Server (http')) {
                    if ($this->serverRunningHasBeenDisplayed) {
                        return;
                    }

                    $this->components->info('Waiting on authorization code from the browser');
                    $this->comment('  <fg=yellow;options=bold>Press Ctrl+C to cancel login</>');

                    $this->newLine();

                    $this->serverRunningHasBeenDisplayed = true;
                } elseif (str($line)->contains(' Accepted')) {
                    $requestPort = $this->getRequestPortFromLine($line);

                    $this->requestsPool[$requestPort] = [
                        $this->getRequestPortFromLine($line),
                        false,
                    ];
                } elseif (str($line)->contains([' [200]: GET '])) {
                    $requestPort = $this->getRequestPortFromLine($line);

                    $this->requestsPool[$requestPort][1] = $this->getDateFromLine(trim(explode('[200]: GET', $line)[1]));
                } elseif (str($line)->contains(' Closing')) {
                    $requestPort = $this->getRequestPortFromLine($line);

                    if (empty($this->requestsPool[$requestPort])) {
                        return;
                    }

                    unset($this->requestsPool[$requestPort]);

                    $process->stop();
                } elseif (!empty($line)) {
                    $position = strpos($line, '] ');

                    if ($position !== false) {
                        $line = substr($line, $position + 1);
                    }

                    $this->components->warn($line);
                }
            }
        );
    }

    private function getDateFromLine(string $line): Carbon|false
    {
        $regex = env('PHP_CLI_SERVER_WORKERS', 1) > 1
            ? '/^\[\d+]\s\[([a-zA-Z0-9: ]+)\]/'
            : '/^\[([^\]]+)\]/';

        $line = str_replace('  ', ' ', $line);

        preg_match($regex, $line, $matches);

        return Carbon::createFromFormat('D M d H:i:s Y', $matches[1]);
    }

    private function getRequestPortFromLine(string $line): int
    {
        preg_match('/:(\d+)\s(?:(?:\w+$)|(?:\[.*))/', $line, $matches);

        return (int) $matches[1];
    }

    private function getUrlOpenCommand(): string
    {
        // use php match syntax
        return match (PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            default => 'xdg-open',
        };
    }
}
