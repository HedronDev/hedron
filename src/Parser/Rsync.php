<?php

namespace Worx\CI\Parser;

use Worx\CI\Command\CommandStackInterface;
use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "rsync",
 *   exclude = {
 *     "drupal"
 *   },
 *   priority = "900"
 * )
 */
class Rsync extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    $commandStack->addCommand("rsync -av --exclude=docker --exclude=.git {$environment->getGitDirectory()}/$clientDir/ {$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}");
    $commandStack->execute();
  }

}
