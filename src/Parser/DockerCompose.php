<?php

namespace Worx\CI\Parser;

use Symfony\Component\Filesystem\Filesystem;
use Worx\CI\Command\CommandStackInterface;
use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
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
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    if (!$parse && !file_exists("{$environment->getDockerDirectory()}/$clientDir") && !file_exists("{$environment->getGitDirectory()}/docker/docker-compose.yml")) {
      $parse = TRUE;
    }
    if ($parse) {
      if ($environment->getHost() != 'localhost') {
        $commandStack->addCommand("ssh root@{$environment->getHost()}");
      }
      if (file_exists("{$environment->getDockerDirectory()}/$clientDir")) {
        $commandStack->addCommand("rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir");
        $commandStack->addCommand("docker-compose down");
        $commandStack->addCommand("docker-compose build");
        $commandStack->addCommand("docker-compose up -d");
      }
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
