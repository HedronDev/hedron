<?php

namespace Worx\CI\Parser;

use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "rsync",
 *   project_type = "php",
 *   priority = "900"
 * )
 */
class Rsync extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler) {
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    $commands = [];
    $commands[] = "rsync -av --exclude=docker --exclude=.git {$environment->getGitDirectory()}/$clientDir/ {$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}";
    $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
  }

}
