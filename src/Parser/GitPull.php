<?php

namespace Worx\CI\Parser;

use Worx\CI\Command\CommandStackInterface;
use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "git_pull",
 *   priority = "1000"
 * )
 */
class GitPull extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    if (file_exists("{$environment->getGitDirectory()}/$clientDir")) {
      $commandStack->addCommand("unset GIT_DIR");
      $commandStack->addCommand("git -C {$environment->getGitDirectory()}/$clientDir pull");
    }
    else {
      $commandStack->addCommand("git clone --branch {$configuration->getBranch()} {$environment->getGitRepository()} {$environment->getGitDirectory()}/$clientDir");
    }
    $commandStack->execute();
  }

}
