<?php

namespace Hedron\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "docker_compose_ps",
 *   priority = "0"
 * )
 */
class DockerComposePS extends BaseParser {

  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $commandStack->addCommand("cd {$this->getDockerDirectoryPath()}");
    $commandStack->addCommand("docker-compose ps");
    $commandStack->execute();
  }

}
