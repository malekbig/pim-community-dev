<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PublishPausedJobsToQueue
{
    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
        private readonly GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function publish(): void
    {
        $jobExecutionIds = $this->getPausedJobExecutionIds->all();

        foreach ($jobExecutionIds as $jobExecutionId) {
            $jobExecutionMessage = PausedJobExecutionMessage::createJobExecutionMessage($jobExecutionId, []);
            $this->logger->notice('Publishing job to queue', [
                'job_execution_id' => $jobExecutionId,
            ]);
            try {
                $this->jobExecutionQueue->publish($jobExecutionMessage);
                $this->logger->notice('Job successfully published', [
                    'job_execution_id' => $jobExecutionId,
                ]);
            } catch (\Exception $exception) {
                $this->logger->error('An error occurred trying to publish paused job execution', [
                    'job_execution_id' => $jobExecutionId,
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
