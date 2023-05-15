<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\CommandExecutor;

use Akeneo\Platform\Installer\Domain\Event\InstallerEvent;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvents;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCommandExecutor implements CommandExecutorInterface
{
    abstract public function getName(): string;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @param string[] $options
     */
    public function execute(?array $options, bool $withOutput = false): null|OutputInterface
    {
        $command = [
            'command' => $this->getName(),
        ];

        if ($options) {
            $command = \array_merge($command, $options);
        }

        $output = $withOutput ? new BufferedOutput() : new NullOutput();

        try {
            $this->getApplication()->run(new ArrayInput($command), $output);
        } catch (\Exception $e) {
            $output = $e->getMessage();
        }

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, ['output' => $output->fetch()]),
            InstallerEvents::POST_COMMAND_EXECUTED,
        );

        return $withOutput ? $output : null;
    }

    public function getApplication(): Application
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        return $application;
    }
}
