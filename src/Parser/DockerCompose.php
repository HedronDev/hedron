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
    $clientDir = $this->getClientDirectoryName();
    if (!$parse && !$this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir") && $this->fileSystem->exists("{$environment->getGitDirectory()}/$clientDir/docker/docker-compose.yml")) {
      $parse = TRUE;
    }
    if ($parse) {
      if ($environment->getHost() != 'local') {
        $commandStack->addCommand("ssh root@{$environment->getHost()}");
      }
      // Rebuild
      if ($this->fileSystem->exists("{$environment->getDockerDirectory()}/$clientDir")) {
        $commandStack->addCommand("rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("docker-compose down");
        $commandStack->addCommand("docker-compose build");
        $commandStack->addCommand("docker-compose up -d");
      }
      // Create
      else {
        $commandStack->addCommand("mkdir {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("cp -r {$environment->getGitDirectory()}/$clientDir/docker/. {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("docker-compose up -d");
      }
      $commandStack->execute();
    }
  }

  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $dir = $this->getEnvironment()->getDockerDirectory() . DIRECTORY_SEPARATOR . $this->getClientDirectoryName();
    $commandStack->addCommand("cd $dir");
    $commandStack->addCommand("docker-compose down");
    $commandStack->addCommand("docker-compose rm -v");
    $commandStack->addCommand("rm -Rf $dir");
    $commandStack->execute();
  }


}
