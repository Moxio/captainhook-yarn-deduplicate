<?php

namespace Moxio\CaptainHook\YarnDeduplicate;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Cli\Processor;
use SebastianFeldmann\Cli\Processor\ProcOpen;
use SebastianFeldmann\Git\Repository;

class YarnDuplicationCheckAction implements Action  {
    /** @var Processor */
    private $processor;

    public function __construct(Processor $processor = null) {
        $this->processor = $processor ?? new ProcOpen();
    }

    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $indexOperator = $repository->getIndexOperator();
        if (!in_array("yarn.lock", $indexOperator->getStagedFiles(), true)) {
            return;
        }

        $yarnDeduplicateBin = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/yarn-deduplicate");
        $yarnDeduplicateResult = $this->processor->run($yarnDeduplicateBin . " --list --fail");

        if ($yarnDeduplicateResult->isSuccessful() === false) {
            if ($yarnDeduplicateResult->getCode() === 1) {
                $baseMessage = "Duplicate packages found in yarn.lock; run yarn-deduplicate to fix this:";
                throw new ActionFailed($baseMessage . PHP_EOL . $yarnDeduplicateResult->getStdOut());
            } else {
                $baseMessage = "Failed to check yarn.lock for duplicate packages using yarn-deduplicate:";
                throw new \RuntimeException($baseMessage . PHP_EOL . $yarnDeduplicateResult->getStdErr());
            }
        }
    }
}
