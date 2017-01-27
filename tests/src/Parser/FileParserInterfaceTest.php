<?php

namespace Hedron\Test\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Hedron\Command\CommandStackInterface;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\File\FileSystem;
use Hedron\File\FileSystemInterface;
use Hedron\GitPostReceiveHandler;
use Hedron\ProjectTypeInterface;

class FileParserInterfaceTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \Hedron\Configuration\ParserVariableConfiguration
   */
  protected $config;

  /**
   * @var \Hedron\Configuration\EnvironmentVariables
   */
  protected $environment;

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $projectType;

  protected function setUp() {
    /** @var ParserVariableConfiguration $config */
    $config = $this->prophesize(ParserVariableConfiguration::class);
    $config->getBranch()->willReturn('foo');
    /** @var EnvironmentVariables $environment */
    $environment = $this->prophesize(EnvironmentVariables::class);
    $environment->getGitDirectory()->willReturn("git_dir");
    $environment->getName()->willReturn("project_name");
    $environment->getClient()->willReturn("client_name");
    $environment->getDockerDirectory()->willReturn("docker_dir");
    $environment->getDataDirectory()->willReturn("{branch}/web");
    $environment->getGitRepository()->willReturn("some_git_repo");
    $environment->getHost()->willReturn('local');
    /** @var ProjectTypeInterface $project_type */
    $project_type = $this->prophesize(ProjectTypeInterface::class);
    $project_type->getConfiguration()->willReturn($config->reveal());
    $project_type->getEnvironment()->willReturn($environment->reveal());
    $this->projectType = $project_type;
  }

  /**
   * @dataProvider testParserProvider
   */
  public function testParser(string $class, string $method, string $plugin_id, GitPostReceiveHandler $handler, FileSystemInterface $fileSystem, int $executions, array $commands = []) {
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
    /** @var ProjectTypeInterface $projectType */
    $projectType = $this->projectType;
    $projectType->getFileSystem()->willReturn($fileSystem);
    $definition = $this->prophesize(PluginDefinitionInterface::class);
    $parser = new $class($plugin_id, $definition->reveal(), $projectType->reveal());
    $parser->{$method}($handler, $commandStack->reveal());
  }

  public function testParserProvider() {
    $providers = [];
    // Simple fileSystem for when no checks are needed.
    $fileSystem = $this->prophesize(FileSystem::class);
    $handler = $this->prophesize(GitPostReceiveHandler::class);

    // Rsync parse().
    $providers[] = ['\Hedron\Parser\Rsync', 'parse', 'rsync', $handler->reveal(), $fileSystem->reveal(), 1, [
      "rsync -av --exclude=docker --exclude=.git git_dir/foo/ foo/web"
    ]];
    // Rsync destroy
    $providers[] = ['\Hedron\Parser\Rsync', 'destroy', 'rsync', $handler->reveal(), $fileSystem->reveal(), 0, []];

    // New; git clone
    $newGitFileSystem = $this->prophesize(FileSystem::class);
    $newGitFileSystem->exists("git_dir/foo")->willReturn(FALSE);
    $providers[] = ['\Hedron\Parser\GitPull', 'parse', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "git clone --branch foo some_git_repo git_dir/foo"
    ]];
    // Destroy with the same settings.
    $providers[] = ['\Hedron\Parser\GitPull', 'destroy', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "rm -Rf git_dir/foo"
    ]];

    // Existing; git pull.
    $existingGitFileSystem = $this->prophesize(FileSystem::class);
    $existingGitFileSystem->exists("git_dir/foo")->willReturn(TRUE);
    $providers[] = ['\Hedron\Parser\GitPull', 'parse', 'git_pull', $handler->reveal(), $existingGitFileSystem->reveal(), 1, [
      "unset GIT_DIR",
      "git -C git_dir/foo pull"
    ]];
    // Destroy with the same settings.
    $providers[] = ['\Hedron\Parser\GitPull', 'destroy', 'git_pull', $handler->reveal(), $newGitFileSystem->reveal(), 1, [
      "rm -Rf git_dir/foo"
    ]];

    // Docker Compose parse
    // Docker dir has already been built and no new docker files were committed
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir exists so does docker-compose; no new docker committed files
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(TRUE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir does not exist but neither does docker-compose.yml
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(FALSE);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 0, []];

    // Docker dir doesn't exist but docker-compose does.
    // Data web dir doesn't exist
    // Data sql dir doesn't exist
    // .env file doesn't exist
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("foo/web")->willReturn(FALSE);
    $dcfileSystem->exists("foo/sql")->willReturn(FALSE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo/.env")->willReturn(FALSE);
    $dcfileSystem->putContents("docker_dir/client_name-project_name-foo/.env", "HEDRON_WEB_VOL=foo/web\nHEDRON_SQL_VOL=foo/sql")->willReturn(1);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 2, [
      "mkdir -p docker_dir/client_name-project_name-foo",
      "cp -r git_dir/foo/docker/. docker_dir/client_name-project_name-foo",
      "cd docker_dir/client_name-project_name-foo",
      "docker-compose up --build -d",
    ]];

    // Docker dir doesn't exist but docker-compose does.
    // Data web dir exists
    // Data sql dir doesn't exist
    // .env file doesn't exist
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("foo/web")->willReturn(TRUE);
    $dcfileSystem->exists("foo/sql")->willReturn(FALSE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo/.env")->willReturn(FALSE);
    $dcfileSystem->putContents("docker_dir/client_name-project_name-foo/.env", "HEDRON_WEB_VOL=foo/web\nHEDRON_SQL_VOL=foo/sql")->willReturn(1);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 2, [
      "mkdir -p docker_dir/client_name-project_name-foo",
      "cp -r git_dir/foo/docker/. docker_dir/client_name-project_name-foo",
      "cd docker_dir/client_name-project_name-foo",
      "docker-compose up --build -d",
    ]];

    // Docker dir doesn't exist but docker-compose does.
    // Data web dir exists
    // Data sql dir exists
    // .env file doesn't exist
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("foo/web")->willReturn(TRUE);
    $dcfileSystem->exists("foo/sql")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo/.env")->willReturn(FALSE);
    $dcfileSystem->putContents("docker_dir/client_name-project_name-foo/.env", "HEDRON_WEB_VOL=foo/web\nHEDRON_SQL_VOL=foo/sql")->willReturn(1);
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 2, [
      "mkdir -p docker_dir/client_name-project_name-foo",
      "cp -r git_dir/foo/docker/. docker_dir/client_name-project_name-foo",
      "cd docker_dir/client_name-project_name-foo",
      "docker-compose up --build -d",
    ]];

    // Docker dir doesn't exist but docker-compose does.
    // Data web dir exists
    // Data sql dir exists
    // .env file exists
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("foo/web")->willReturn(TRUE);
    $dcfileSystem->exists("foo/sql")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("git_dir/foo/docker/docker-compose.yml")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo/.env")->willReturn(TRUE);
    $dcfileSystem->getContents("docker_dir/client_name-project_name-foo/.env")->willReturn('');
    $dcfileSystem->putContents("docker_dir/client_name-project_name-foo/.env", "\nHEDRON_WEB_VOL=foo/web\nHEDRON_SQL_VOL=foo/sql")->willReturn('');
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn([]);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 2, [
      "mkdir -p docker_dir/client_name-project_name-foo",
      "cp -r git_dir/foo/docker/. docker_dir/client_name-project_name-foo",
      "cd docker_dir/client_name-project_name-foo",
      "docker-compose up --build -d",
    ]];

    // Docker dir not built; new docker files were committed
    $dcfileSystem = $this->prophesize(FileSystem::class);
    $dcfileSystem->exists("foo/web")->willReturn(TRUE);
    $dcfileSystem->exists("foo/sql")->willReturn(TRUE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(FALSE);
    $dcfileSystem->exists("docker_dir/client_name-project_name-foo/.env")->willReturn(TRUE);
    $dcfileSystem->getContents("docker_dir/client_name-project_name-foo/.env")->willReturn('');
    $dcfileSystem->putContents("docker_dir/client_name-project_name-foo/.env", "\nHEDRON_WEB_VOL=foo/web\nHEDRON_SQL_VOL=foo/sql")->willReturn('');
    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
    $dchandler->getCommittedFiles()->willReturn(['docker/foo']);
    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 2, [
      "mkdir -p docker_dir/client_name-project_name-foo",
      "cp -r git_dir/foo/docker/. docker_dir/client_name-project_name-foo",
      "cd docker_dir/client_name-project_name-foo",
      "docker-compose up --build -d",
    ]];
//
//    // Docker dir built; new docker files were committed
//    $dcfileSystem = $this->prophesize(FileSystem::class);
//    $dcfileSystem->exists("docker_dir/client_name-project_name-foo")->willReturn(TRUE);
//    $dchandler = $this->prophesize(GitPostReceiveHandler::class);
//    $dchandler->getCommittedFiles()->willReturn(['docker/foo']);
//    $providers[] = ['\Hedron\Parser\DockerCompose', 'parse', 'docker_compose', $dchandler->reveal(), $dcfileSystem->reveal(), 1, [
//      "rsync -av --delete git_dir/foo/docker/ docker_dir/client_name-project_name-foo",
//      "cd docker_dir/client_name-project_name-foo",
//      "docker-compose down",
//      "docker-compose build",
//      "docker-compose up --build -d",
//    ]];

    return $providers;
  }

}
