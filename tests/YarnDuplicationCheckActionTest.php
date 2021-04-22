<?php
declare(strict_types=1);

namespace Moxio\CaptainHook\YarnDeduplicate\Test;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Exception\ActionFailed;
use Moxio\CaptainHook\YarnDeduplicate\YarnDuplicationCheckAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Cli\Command\Result;
use SebastianFeldmann\Cli\Processor;
use SebastianFeldmann\Git\Operator\Index;
use SebastianFeldmann\Git\Repository;

class YarnDuplicationCheckActionTest extends TestCase {
    /** @var MockObject&Processor */
    private $processor;
    /** @var YarnDuplicationCheckAction */
    private $yarn_duplication_check_action;
    /** @var MockObject|Config */
    private $config;
    /** @var MockObject|Repository */
    private $repository;
    /** @var MockObject|Index */
    private $index_operator;

    protected function setUp(): void {
        $this->processor = $this->createMock(Processor::class);
        $this->yarn_duplication_check_action = new YarnDuplicationCheckAction($this->processor);

        $this->config = $this->createMock(Config::class);
        $this->repository = $this->createMock(Repository::class);
        $this->index_operator = $this->createMock(Index::class);
        $this->repository->expects($this->any())
            ->method("getIndexOperator")
            ->willReturn($this->index_operator);
    }

    public function testReturnsWhenYarnLockFileWasNotChanged(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFiles")
            ->willReturn([]);

        $io = new NullIO();
        $config_action = new Config\Action(YarnDuplicationCheckAction::class);

        $this->expectNotToPerformAssertions();
        $this->yarn_duplication_check_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testReturnsWhenYarnLockFileWasChangedAndYarnDeduplicateProcessWasSuccessful() {
        $this->index_operator->expects($this->any())
            ->method("getStagedFiles")
            ->willReturn([ "yarn.lock" ]);

        $io = new NullIO();
        $config_action = new Config\Action(YarnDuplicationCheckAction::class);

        $expected_cmd = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/yarn-deduplicate --list --fail");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($this->equalTo($expected_cmd))
            ->willReturn(new Result($expected_cmd, 0));

        $this->yarn_duplication_check_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testThrowsActionFailedWhenYarnLockFileWasChangedAndYarnDeduplicateProcessWasNotSuccesful() {
        $this->index_operator->expects($this->any())
            ->method("getStagedFiles")
            ->willReturn([ "yarn.lock" ]);

        $io = new NullIO();
        $config_action = new Config\Action(YarnDuplicationCheckAction::class);

        $expected_cmd = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/yarn-deduplicate --list --fail");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($this->equalTo($expected_cmd))
            ->willReturn(new Result($expected_cmd, 1));

        $this->expectException(ActionFailed::class);
        $this->yarn_duplication_check_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testThrowsRuntimeExceptionWhenYarnDeduplicateProcessReturnsWithUnexpectedErrorCode() {
        $this->index_operator->expects($this->any())
            ->method("getStagedFiles")
            ->willReturn([ "yarn.lock" ]);

        $io = new NullIO();
        $config_action = new Config\Action(YarnDuplicationCheckAction::class);

        $expected_cmd = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/yarn-deduplicate --list --fail");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($this->equalTo($expected_cmd))
            ->willReturn(new Result($expected_cmd, 5));

        $this->expectException(\RuntimeException::class);
        $this->yarn_duplication_check_action->execute($this->config, $io, $this->repository, $config_action);
    }
}
