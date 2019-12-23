<?php

namespace Moxio\CaptainHook\YarnDeduplicate\Condition;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Condition;
use SebastianFeldmann\Git\Repository;

class YarnDeduplicateInstalled implements Condition
{
    public function isTrue(IO $io, Repository $repository): bool
    {
        $yarnDeduplicateBin = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/yarn-deduplicate");
        return is_file($yarnDeduplicateBin);
    }
}
