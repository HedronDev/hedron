<?php

namespace Hedron\Test\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Hedron\Command\CommandStackInterface;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\File\FileSystem;
use Hedron\GitPostReceiveHandler;

class FileParserInterfaceTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \Hedron\Configuration\ParserVariableConfiguration
   */
  protected $config;

  /**
   * @var \Hedron\Configuration\EnvironmentVariables
   */
  protected $environment;

  protected function setUp() {
    $config = $this->prophesize(ParserVariableConfiguration::class);
    $config->getBranch()->willReturn('foo');
    $this->config = $config->reveal();
    $environment = $this->prophesize(EnvironmentVariables::class);
    $environment->getGitDirectory()->willReturn("git_dir");
    $environment->getClient()->willReturn("client_name");
    $environment->getDockerDirectory()->willReturn("docker_dir");
    $environment->getDataDirectory()->willReturn("data_dir");
    $environment->getGitRepository()->willReturn("some_git_repo");
    $environment->getHost()->willReturn('localhost');
    $this->environment = $environment->reveal();
  }

  /**
   * @dataProvider testParserProvider
   */
  public function testParser(string $class, string $method, string $plugin_id, GitPostReceiveHandler $handler, FileSystem $fileSystem, int $executions, array $commands = []) {
    $commandStack = $this->prophesize(CommandStackInterface::class);
    foreach ($commands as $command) {
      $commandStack->addCommand($command)->shouldBeCalled();
    }
    if ($executions) {
      $commandStack->execute()->shouldBeCalledTimes($executions);
    }
    else {
      $commandStack->execute()->shouldNotBeCalled();
    }
    $definition = $this->prophesize(PluginDefinitionInterface::class);
    $parser = new $class($plugin_id, $definition->reveal(), $this->environment, $this->config, $fileSystem);
    $parser->{$method}($handler, $commandStack->reveal());
  }

  public function testParserProvider() {
    $providers = [];
    // Simple fileSystem for when no checks are needed.
    $fileSystem = $this->prophesize(FileSystem::class);
    $handler = $this->prophesize(GitPostReceiveHandler::class);

    // Rsync parse().
    $providers[] = ['\Hedron\Parser\Rsync', 'parse', 'rsync', $handler->reveal(), $fileSystem->reveal(), 1, [
      "rsync -av --exclude=docker --exclude=.git git_dir/client_name-foo/ docker_dir/client_name-foo/data_dir"
    ]];
    // Rsync destroy
    $providers[] = ['\Hedron\Parser\Rsync', 'destroy', 'rsync', $handler->reveal(), $fileSystem->reveal(), 0, []];

    // New; git clone
    $newGitFileSystem = $this->prophesize(FileSystem::class);
    $newGitFileSystem->exists("git_dir/client_name-foo")->willReturn(FALSE);
    $providers[] = ['\Hedron\Parser\GitPull', 'parse', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "git clone --branch foo some_git_repo git_dir/client_name-foo"
    ]];
    // Destroy with the same settings.
    $providers[] = ['\Hedron\Parser\GitPull', 'destroy', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "rm -Rf git_dir/client_name-foo"
    ]];

    // Existing; git pull.
    $existingGitFileSystem = $this->prophesize(FileSystem::class);
    $existingGitFileSystem->exists("git_dir/client_name-foo")->willReturn(TRUE);
    $providers[] = ['\Hedron\Parser\GitPull', 'parse', 'git_pull', $handler->reveal(), $existingGitFileSystem->reveal(), 1, [
      "unset GIT_DIR",
      "git -C git_dir/client_name-foo pull"
    ]];
    // Destroy with the same settings.
    $providers[] = ['\Hedron\Parser\GitPull', 'destroy', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "rm -Rf git_dir/client_name-foo"
    ]];

    // Docker Compose parse
    // Docker dir has already been built and no new docker files were committed
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir exists so does docker-compose; no new docker committed files
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(TRUE);
    $dcfileSystem->exists("git_dir/client_name-foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir does not exist but neither does docker-compose.yml
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/client_name-foo/docker/docker-compose.yml")->willReturn(FALSE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir doesn't exist but docker-compose does.
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/client_name-foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 1, [
      "mkdir docker_dir/client_name-foo",
      "cp -r git_dir/client_name-foo/docker/. docker_dir/client_name-foo",
      "cd docker_dir/client_name-foo",
      "docker-compose up -d",
    ]];

    // Docker dir not built; new docker files were committed
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(FALSE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn(['docker/foo']);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 1, [
      "mkdir docker_dir/client_name-foo",
      "cp -r git_dir/client_name-foo/docker/. docker_dir/client_name-foo",
      "cd docker_dir/client_name-foo",
      "docker-compose up -d",
    ]];

    // Docker dir built; new docker files were committed
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-foo")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn(['docker/foo']);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 1, [
      "rsync -av --delete git_dir/client_name-foo/docker/ docker_dir/client_name-foo",
      "cd docker_dir/client_name-foo",
      "docker-compose down",
      "docker-compose build",
      "docker-compose up -d",
    ]];

    return $providers;
  }

}
