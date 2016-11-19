<?php

namespace Worx\CI\Parser;

use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer_drupal_8",
 *   priority = "9"
 * )
 */
class ComposerDrupal8 extends BaseParser {

  public function parse(GitPostReceiveHandler $handler) {
    $commands = [];
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    $site_directory = "{$environment->getDockerDirectory()}/$clientDir/{$environment->getDataDirectory()}";
    $composer_file = "$site_directory/composer.json";
    if (file_exists($composer_file)) {
      $composer_file = file_get_contents($composer_file);
      $composer_file = json_decode($composer_file);
      if (!empty($composer_file->require->{'drupal/drupal'}) && file_exists("$site_directory/vendor/drupal/drupal")) {
        $commands[] = "rsync -av --exclude=.git --exclude=vendor --exclude=modules --exclude=themes --delete-after $site_directory/vendor/drupal/drupal/ $site_directory";
        $commands[] = "cd $site_directory";
        $commands[] = "composer update";
      }
    }
    $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
  }

}
