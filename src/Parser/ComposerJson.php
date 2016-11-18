<?php

namespace Worx\CI\Parser;

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
  public function parse(GitPostReceiveHandler $handler) {
    if (array_search('composer.json', $handler->getIntersectFiles()) !== FALSE) {
      $configuration = $this->getConfiguration();
      $environment = $this->getEnvironment();
      $clientDir = "{$environment->getClient()}_{$configuration->getBranch()}";
      $commands = [];
      $commands[] = "unset GIT_DIR";
      $commands[] = "git -C {$environment->getGitDirectory()}/$clientDir pull";
      $commands[] = "rsync -av --exclude=docker --exclude=.git {$environment->getGitDirectory()}/$clientDir/ {$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}";
      $commands[] = "cd {$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}";
      if (file_exists("{$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}/composer.lock")) {
        $commands[] = "composer update";
      }
      else {
        $commands[] = "composer install";
      }
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

}
