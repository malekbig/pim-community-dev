<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Step execution archivist
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepExecutionArchivist implements EventSubscriberInterface
{
    /** @var ArchiverInterface[] */
    protected array $archivers = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::STEP_EXECUTION_COMPLETED => 'onStepExecutionCompleted',
        ];
    }

    /**
     * Register an archiver
     *
     * @param ArchiverInterface $archiver
     *
     * @throws \InvalidArgumentException
     */
    public function registerArchiver(ArchiverInterface $archiver): void
    {
        if (array_key_exists($archiver->getName(), $this->archivers)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'There is already a registered archiver named "%s": %s',
                    $archiver->getName(),
                    get_class($this->archivers[$archiver->getName()])
                )
            );
        }

        $this->archivers[$archiver->getName()] = $archiver;
    }

    /**
     * Delegate archiving to the registered archivers
     */
    public function onStepExecutionCompleted(StepExecutionEvent $event): void
    {
        foreach ($this->archivers as $archiver) {
            if ($archiver->supports($event->getStepExecution())) {
                $archiver->archive($event->getStepExecution());
            }
        }
    }

    /**
     * Get the archives generated by the archivers
     *
     * @param JobExecution $jobExecution
     *
     * @return array
     */
    public function getArchives(JobExecution $jobExecution, bool $deep = false): iterable
    {
        $result = [];

        if (!$jobExecution->isRunning()) {
            foreach ($this->archivers as $archiver) {
                $result[$archiver->getName()] = $archiver->getArchives($jobExecution, $deep);
            }
        }

        return $result;
    }

    /**
     * Tells if there were at least 2 files archived for a given job execution
     */
    public function hasAtLeastTwoArchives(JobExecution $jobExecution): bool
    {
        if (!$jobExecution->isRunning()) {
            $count = 0;
            foreach ($this->archivers as $archiver) {
                foreach ($archiver->getArchives($jobExecution, true) as $archive) {
                    if (++$count >= 2) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get an archive of an archiver
     *
     * @param JobExecution $jobExecution
     * @param string $archiver
     * @param string $key
     *
     * @return resource
     * @throws \InvalidArgumentException
     *
     */
    public function getArchive(JobExecution $jobExecution, $archiver, $key)
    {
        if (!isset($this->archivers[$archiver])) {
            throw new \InvalidArgumentException(
                sprintf('Archiver "%s" is not registered', $archiver)
            );
        }

        return $this->archivers[$archiver]->getArchive($jobExecution, $key);
    }
}