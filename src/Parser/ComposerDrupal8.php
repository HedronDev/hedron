<?php

namespace Worx\CI\Parser;

use Composer\Semver\Comparator;
use Worx\CI\GitPostReceiveHandler;
use Worx\CI\Tools\ComposerHelperTrait;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer_drupal_8",
 *   project_type = "drupal",
 *   priority = "10"
 * )
 */
class ComposerDrupal8 extends BaseParser {
  use ComposerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler) {
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
      if (!empty($git_composer->require->{'drupal/drupal'})) {
        $version = $git_composer->require->{'drupal/drupal'};
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
      $commands[] = "cp composer.json composer.original";
      // The lock file will prevent new composerable dependencies from being
      // installed after our git working directory is re-merged with this
      // directory structure, so we remove it since it will be regenerated
      // by a later composer install call.
      $commands[] = "rm composer.lock";
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
    if ($new || array_search('composer.json', $handler->getCommittedFiles()) !== FALSE) {
      // If we have an original composer file and this is not a new build of
      // the site, we might need to get a new version of Drupal core.
      if ($git_composer && !$new) {
        // If no version was specified, see if the running version is newest.
        if ($version && $this->compareComposerVersion($this->getCurrentDrupalCoreVersion(), $version)) {
          $git_composer->require->{'drupal/core'} = $version;
        }
      }
      // Starting a new set of commands since we need the new composer.json
      // file provided by drupal/drupal before we can continue with merging
      // the old composer.json.
      $commands = [];
      $commands[] = "cd $site_directory";
      if ($git_composer) {
        $composer_content = file_get_contents("$site_directory/composer.original");
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
      print print_r($this->getConfiguration()->getOldRevision(), TRUE);
      print print_r($this->getConfiguration()->getNewRevision(), TRUE);
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

  /**
   * Uses tokens to extract the current version of drupal/core.
   *
   * @return string
   */
  protected function getCurrentDrupalCoreVersion() {
    $drupal_class = "{$this->getSiteDirectoryPath()}/core/lib/Drupal.php";
    if (file_exists($drupal_class)) {
      $drupal_class = file_get_contents($drupal_class);
      $drupal_class = token_get_all($drupal_class);
      foreach ($drupal_class as $delta => $token) {
        if (is_array($token) && $token[1] == 'const' && is_array($drupal_class[$delta + 2]) && $drupal_class[$delta + 2][0] == T_STRING && $drupal_class[$delta + 2][1] == 'VERSION' && is_array($drupal_class[$delta + 6])) {
          return $drupal_class[$delta + 6][1];
        }
      }
    }
  }

  /**
   * Compare semantic version numbers to tell if new is larger than current.
   *
   * @param string $current_version
   *   The current version in semver format.
   * @param string $new_version
   *   The new version in semver format.
   *
   * @return bool
   *   True if the new version is larger than the current.
   */
  protected function compareComposerVersion(string $current_version, string $new_version) {
    return Comparator::greaterThan($new_version, $current_version);
  }

  /**
   * Alters the composer object.
   *
   * @param \stdClass $composer
   *
   * @see \Worx\CI\Tools\ComposerHelperTrait::mergeComposerObjects
   */
  protected function alterComposerFile(\stdClass $composer) {
    // Don't add the drupal/drupal dependency to the new composer.json.
    unset($composer->require->{'drupal/drupal'});
    // If there's a require entry for drupal/core, unset the composer's replace
    // object.
    if (!empty($composer->require->{'drupal/core'})) {
      unset($composer->replace);
    }

    $composer->repositories = [];
    $composer->repositories[] = (object) [
      "type" => "composer",
      "url" => "https://packagist.drupal-composer.org"
    ];
    $composer->extra->{"installer-paths"} = (object) [
      "modules/contrib/{\$name}" => ["type:drupal-module"],
      "modules/custom/{\$name}" => ["type:drupal-custom-module"],
      "profiles/contrib/{\$name}" => ["type:drupal-profile"],
      "themes/contrib/{\$name}" => ["type:drupal-theme"],
      "themes/custom/{\$name}" => ["type:drupal-custom-theme"]
    ];
  }

}
