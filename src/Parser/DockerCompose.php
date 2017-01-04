<?php

namespace Hedron\Parser;

use Symfony\Component\Filesystem\Filesystem;
use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "docker_compose",
 *   priority = "950"
 * )
 */
class DockerCompose extends BaseParser {

  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $parse = FALSE;
    foreach ($handler->getCommittedFiles() as $file_name) {
      if (strpos($file_name, 'docker/') === 0) {
        $parse = TRUE;
        break;
      }
    }
    $environment = $this->getEnvironment();
    $clientDir = $this->getConfiguration()->getBranch();
    if (!$parse && !$this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir") && $this->fileSystem->exists("{$environment->getGitDirectory()}/$clientDir/docker/docker-compose.yml")) {
      $parse = TRUE;
    }
    if ($parse) {
      // We're going to parse, so let's make sure the web & sql volumes exists.
      if (!$this->fileSystem->exists($this->getDataDirectoryPath())) {
        $commandStack->addCommand("mkdir -p {$this->getDataDirectoryPath()}");
      }
      if (!$this->fileSystem->exists($this->getSqlDirectoryPath())) {
        $commandStack->addCommand("mkdir -p {$this->getSqlDirectoryPath()}");
      }
      if ($environment->getHost() != 'local') {
        $commandStack->addCommand("ssh root@{$environment->getHost()}");
      }
      // Rebuild
      if ($this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir")) {
        $commandStack->addCommand("rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->execute();
        if (!$this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir/.env")) {
          $this->createEnv();
        }
        $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("docker-compose down");
        $commandStack->addCommand("docker-compose build");
        $commandStack->addCommand("docker-compose up -d");
      }
      // Create
      else {
        $commandStack->addCommand("mkdir -p {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->execute();
        if (!$this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir/.env")) {
          $this->createEnv();
        }
        $commandStack->addCommand("cp -r {$environment->getGitDirectory()}/$clientDir/docker/. {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("docker-compose up -d");
      }
      $commandStack->execute();
    }
  }

  protected function createEnv() {
    $environment = $this->getEnvironment();
    $clientDir = $this->getConfiguration()->getBranch();
    $environment_file = "{$environment->getDockerDirectory()}/$clientDir/.env";
    $contents = "WEB={$this->getDataDirectoryPath()}\nSQL={$this->getSqlDirectoryPath()}";
    $this->fileSystem->putContents($environment_file, $contents);
  }

  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $dir = $this->getDockerDirectoryPath();
    $commandStack->addCommand("cd $dir");
    $commandStack->addCommand("docker-compose down");
    $commandStack->addCommand("docker-compose rm -v");
    $commandStack->addCommand("rm -Rf $dir");
    $commandStack->addCommand("rm -Rf {$this->getDataDirectoryPath()}");
    $commandStack->addCommand("rm -Rf {$this->getSqlDirectoryPath()}");
    $commandStack->execute();
  }


}
