<?php

namespace Hedron\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "composer"
 * )
 */
class ComposerJson extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    if (array_search('composer.json', $handler->getIntersectFiles()) !== FALSE) {
      $environment = $this->getEnvironment();
      $clientDir = $this->getClientDirectoryName();
      $commandStack->addCommand("cd {$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}");
      if (file_exists("{$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}/composer.lock")) {
        $commandStack->addCommand("composer update");
      }
      else {
        $commandStack->addCommand("composer install");
      }
      $commandStack->execute();
    }
  }

}
