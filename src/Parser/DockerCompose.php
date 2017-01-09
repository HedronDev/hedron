<?php

namespace Hedron\Parser;

use Symfony\Component\Filesystem\Filesystem;
use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "docker_compose"
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
    if (!$parse && !$this->getFileSystem()->exists("{$this->getDockerDirectoryPath()}") && $this->getFileSystem()->exists("{$environment->getGitDirectory()}/$clientDir/docker/docker-compose.yml")) {
      $parse = TRUE;
    }
    if ($parse) {
      if ($environment->getHost() != 'local') {
        $commandStack->addCommand("ssh root@{$environment->getHost()}");
      }
      // Rebuild
      if ($this->getFileSystem()->exists("{$this->getDockerDirectoryPath()}")) {
        $commandStack->addCommand("rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$this->getDockerDirectoryPath()}");
        $commandStack->execute();
        if (!$this->getFileSystem()->exists("{$this->getDockerDirectoryPath()}/.env")) {
          $this->createEnv();
        }
        $commandStack->addCommand("cd {$this->getDockerDirectoryPath()}");
        $commandStack->addCommand("docker-compose down");
        $commandStack->addCommand("docker-compose build");
        $commandStack->addCommand("docker-compose up -d");
      }
      // Create
      else {
        $commandStack->addCommand("mkdir -p {$this->getDockerDirectoryPath()}");
        $commandStack->execute();
        if (!$this->getFileSystem()->exists("{$this->getDockerDirectoryPath()}/.env")) {
          $this->createEnv();
        }
        $commandStack->addCommand("cp -r {$environment->getGitDirectory()}/$clientDir/docker/. {$this->getDockerDirectoryPath()}");
        $commandStack->addCommand("cd {$this->getDockerDirectoryPath()}");
        $commandStack->addCommand("docker-compose up --build -d");
      }
      $commandStack->execute();
    }
  }

  protected function createEnv() {
    $environment_file = "{$this->getDockerDirectoryPath()}/.env";
    $contents = "WEB={$this->getDataDirectoryPath()}\nSQL={$this->getSqlDirectoryPath()}";
    $this->getFileSystem()->putContents($environment_file, $contents);
  }

  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $dir = $this->getDockerDirectoryPath();
    $commandStack->addCommand("cd $dir");
    $commandStack->addCommand("docker-compose down");
    $commandStack->addCommand("docker-compose rm -v");
    $commandStack->addCommand("rm -Rf $dir");
    $commandStack->execute();
  }


}
