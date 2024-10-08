<?php

declare(strict_types=1);

namespace App\UseCase\Command\HandleFailedMessages;

use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class HandleFailedMessagesHandler implements CommandHandlerBase
{
    private Application $console;

    public function __construct(
        private readonly KernelInterface $kernel
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(HandleFailedMessagesCommand $command): void
    {
        $this->initConsoleApplication();

        $limitCount = $command->limitCount;
        $limitTime = $command->limitTime;

        if (!$limitCount) {
            throw new \RuntimeException('Limit count cannot be empty');
        }

        // лимит не больше часа на обработку сообщений
        if ($limitTime > 60 * 60) {
            $limitTime = 3600;
        }

        $input = new ArrayInput([
            'command' => 'messenger:consume',
            'receivers' => ['failed'],
            '--limit' => $limitCount,
            '--time-limit' => $limitTime, // Чтобы процесс не висел очень долго, если выставят большой лимит по количеству
            '--no-reset' => '--no-reset', // чтобы не очищать данные для Worker от Road Runner
        ]);
        $output = new BufferedOutput();

        $this->console->run($input, $output);
    }

    private function initConsoleApplication(): void
    {
        $this->console = new Application($this->kernel); // Если подключить Application через конструктор, то тесты зависают
        $this->console->setAutoExit(false);
    }
}
