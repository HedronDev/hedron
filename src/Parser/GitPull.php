<?php

namespace Hedron\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
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
    $clientDir = $this->getGitDirectoryPath();
    if ($this->fileSystem->exists($clientDir)) {
      $commandStack->addCommand("unset GIT_DIR");
      $commandStack->addCommand("git -C $clientDir pull");
    }
    else {
      $commandStack->addCommand("git clone --branch {$configuration->getBranch()} {$environment->getGitRepository()} $clientDir");
    }
    $commandStack->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $dir = $this->getGitDirectoryPath();
    $commandStack->addCommand("rm -Rf $dir");
    $commandStack->execute();
  }

}

