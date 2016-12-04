<?php

namespace Worx\CI\Parser;

use Worx\CI\Command\CommandStackInterface;
use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer",
 *   project_type = "php",
 *   priority = "10"
 * )
 */
class ComposerJson extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    if (array_search('composer.json', $handler->getIntersectFiles()) !== FALSE) {
      $configuration = $this->getConfiguration();
      $environment = $this->getEnvironment();
      $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
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
