<?php

/**
 * @file
 * Contains \Worx\CI\Parser\ComposerWP.
 */

namespace Worx\CI\Parser;
use Worx\CI\Command\CommandStackInterface;
use Worx\CI\GitPostReceiveHandler;
use Worx\CI\Tools\ComposerHelperTrait;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer_wp",
 *   project_type = "wp",
 *   priority = "10"
 * )
 */
class ComposerWP extends BaseParser {
  use ComposerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $commands = [];
    $git_directory = $this->getGitDirectoryPath();
    $site_directory = $this->getSiteDirectoryPath();
    $composer_file = "$site_directory/composer.json";
    $git_composer = "$git_directory/composer.json";
    $version = FALSE;
    $new = FALSE;
    $removals = [];
    if (file_exists($git_composer)) {
      $composer_content = file_get_contents($git_composer);
      $git_composer = json_decode($composer_content);
      if (!empty($git_composer->require->{'johnpbloch/wordpress'})) {
        $version = $git_composer->require->{'johnpbloch/wordpress'};
      }
    }
    if (array_search('composer.json', $handler->getCommittedFiles()) !== FALSE) {
      // Starting a new set of commands since we need the new composer.json
      // file provided by drupal/drupal before we can continue with merging
      // the old composer.json.
      $commands[] = "cd $site_directory";
      if ($git_composer) {
        $composer_content = file_get_contents($composer_file);
        $new_composer = json_decode($composer_content);
        $removals = $this->mergeComposerJsonFiles($composer_file, $git_composer, $new_composer, [$this, 'alterComposerFile']);
      }
      // If we don't have a vendor dir yet, run install.
      if (!file_exists("$site_directory/vendor")) {
        $commands[] = "composer install";
      }
      // Otherwise, just update for the changes made to the composer file.
      else {
        $commands[] = "composer update --lock";
      }
      // Update modules directory with changes from the git repository.
      // @todo only do this if something changed in /modules
      if (file_exists("$git_directory/modules")) {
        $commands[] = "rsync -av $git_directory/modules/ $site_directory/modules";
      }
      // Update themes directory with changes from the git repository.
      // @todo only do this if something changed in /themes
      if (file_exists("$git_directory/themes")) {
        $commands[] = "rsync -av $git_directory/themes/ $site_directory/themes";
      }
      // @todo add profiles
      foreach ($removals as $package) {
        $commands[] = "composer remove $package";
      }
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

}

