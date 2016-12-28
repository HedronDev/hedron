<?php

namespace Hedron\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
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
    $config = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $commandStack->addCommand("rsync -av --exclude=docker --exclude=.git {$environment->getGitDirectory()}/{$config->getBranch()}/ {$this->getDataDirectoryPath()}");
    $commandStack->execute();
  }

}
