<?php

namespace Worx\CI\Parser;

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
  public function parse(GitPostReceiveHandler $handler) {
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    $commands = [];
    if (file_exists("{$environment->getGitDirectory()}/$clientDir")) {
      $commands[] = "unset GIT_DIR";
      $commands[] = "git -C {$environment->getGitDirectory()}/$clientDir pull";
    }
    else {
      $commands[] = "git clone --branch {$configuration->getBranch()} {$environment->getGitRepository()} {$environment->getGitDirectory()}/$clientDir";
    }
    $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
  }

}
