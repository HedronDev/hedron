<?php

namespace Worx\CI\Parser;

use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer_drupal_8",
 *   project_type = "drupal",
 *   priority = "10"
 * )
 */
class ComposerDrupal8 extends BaseParser {

  public function parse(GitPostReceiveHandler $handler) {
    $commands = [];
    $git_directory = $this->getGitDirectoryPath();
    $site_directory = $this->getSiteDirectoryPath();
    $composer_file = "$site_directory/composer.json";
    $git_composer = "$git_directory/composer.json";
    $version = FALSE;
    $new = FALSE;
    if (file_exists($git_composer)) {
      $composer_content = file_get_contents($git_composer);
      $original_composer = json_decode($composer_content);
      if (!empty($original_composer->require->{'drupal/drupal'})) {
        $version = $original_composer->require->{'drupal/drupal'};
      }
    }
    // Create a drupal install if it doesn't exist locally yet.
    if (!file_exists($composer_file)) {
      $new = TRUE;
      // composer create-project will not work in a directory with any
      // content so we remove the files which were rsynced into the directory
      // and remake the directory before building our project.
      $commands[] = "rm -Rf $site_directory";
      $commands[] = "mkdir $site_directory";
      $commands[] = "cd $site_directory";
      if ($version) {
        $commands[] = "composer create-project drupal/drupal:$version --no-interaction --prefer-dist --no-install .";
      }
      else {
        $commands[] = "composer create-project drupal/drupal --no-interaction --prefer-dist --no-install .";
      }
      // The lock file will prevent new composerable dependencies from being
      // installed after our git working directory is re-merged with this
      // directory structure, so we remove it since it will be regenerated
      // by a later composer install call.
      $commands[] = "rm composer.lock";
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
    if (array_search('composer.json', $handler->getCommittedFiles()) !== FALSE) {
      // If we have an original composer file and this is not a new build of
      // the site, we might need to get a new version of Drupal core.
      if ($original_composer && !$new) {
        // If no version was specified, see if the running version is newest.
        if (!$version) {

        }
      }
      // Starting a new set of commands since we need the new composer.json
      // file provided by drupal/drupal before we can continue with merging
      // the old composer.json.
      $commands = [];
      $commands[] = "cd $site_directory";
      if ($original_composer) {
        $this->mergeComposerJsonFiles($composer_file, $original_composer);
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
      if (file_exists("$git_directory/modules")) {
        $commands[] = "rsync -av $git_directory/modules/ $site_directory/modules";
      }
      // Update themes directory with changes from the git repository.
      if (file_exists("$git_directory/themes")) {
        $commands[] = "rsync -av $git_directory/themes/ $site_directory/themes";
      }
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

  /**
   * Merges the original composer file with the new file provided by drupal.
   *
   * This ensures that the composer.json file that is going to be installed
   * will have all the appropriate requirements documented by the git
   * repository that is kept up to date by the client.
   *
   * @param string $composer_file
   *   The absolute path to the composer file.
   * @param \stdClass $original_composer
   *   The original composer.json file before it was removed as a php object.
   *
   * @return int
   */
  protected function mergeComposerJsonFiles(string $composer_file, \stdClass $original_composer) {
    $composer_content = file_get_contents($composer_file);
    $drupal_composer = json_decode($composer_content);
    foreach ($original_composer as $key => $values) {
      $is_array = is_array($values);
      // Don't add the drupal/drupal dependency to the new composer.json.
      if ($key == 'require') {
        unset($values->{'drupal/drupal'});
      }
      $values = (array) $values;
      if (isset($drupal_composer->{$key})) {
        $drupal_values = (array) $drupal_composer->{$key};
        $values = array_merge($drupal_values, $values);
      }
      $drupal_composer->{$key} = $is_array ? $values : (object) $values;
    }
    return file_put_contents($composer_file, json_encode($drupal_composer, JSON_PRETTY_PRINT));
  }
}
