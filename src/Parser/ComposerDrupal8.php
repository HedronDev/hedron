<?php

namespace Hedron\Parser;

use Composer\Semver\Comparator;
use Hedron\Command\CommandStackInterface;
use Hedron\Exception\MissingComposerException;
use Hedron\GitPostReceiveHandler;
use Hedron\Tools\ComposerHelperTrait;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "composer_drupal_8",
 *   project_type = "drupal",
 *   priority = "10"
 * )
 */
class ComposerDrupal8 extends BaseParser {
  use ComposerHelperTrait;

  protected $version = FALSE;

  protected $new = FALSE;

  /**
   * {@inheritdoc}
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $commandStack->addCommand("find {$this->getDataDirectoryPath()} -mindepth 1 -exec rm -rf {} \\;");
    $commandStack->execute();
  }


  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $git_directory = $this->getGitDirectoryPath();
    $site_directory = $this->getDataDirectoryPath();
    $file_system = $this->getFileSystem();
    $composer_file = "$site_directory/composer.json";
    $git_composer = $this->getGitComposerFile("$git_directory/composer.json");
    // Create a drupal install if it doesn't exist locally yet.
    $this->composeDrupalDrupal($composer_file, $commandStack);
    // Executing this set of commands since we need the new composer.json
    // file provided by drupal/drupal before we can continue with merging
    // the old composer.json.
    $commandStack->execute();

    // If this is a new install or the composer file has changed there are a
    // number of things which might need to be accomplished based upon the
    // content of the composer.json file that was committed.
    if ($this->new || array_search('composer.json', $handler->getCommittedFiles()) !== FALSE) {
      $this->composeDrupalCore($git_composer);
      if (!$file_system->exists("$site_directory/composer.original")) {
        // @todo print a more helpful message to the user.
        throw new MissingComposerException("The copy of the original composer.json file appears to be missing. Please consult a system administrator.");
      }
      $new_composer = json_decode($file_system->getContents("$site_directory/composer.original"));
      $changes = $this->mergeComposerJsonFiles($composer_file, $git_composer, $new_composer, [$this, 'alterComposerFile']);
      $this->getInstallCommand($commandStack);
      $subdirectories = [
        'modules',
        'themes',
        'profiles'
      ];
      foreach ($subdirectories as $subdirectory) {
        $this->rsyncSubDirectory($subdirectory, $commandStack);
      }
      $this->installComposerDependencies($changes['install'], $commandStack);
      if (!$this->new) {
        $this->updateComposerDependencies($changes['update'], $commandStack);
        $this->removeComposerDependencies($changes['remove'], $commandStack);
      }
      $commandStack->execute();
    }
  }

  protected function getGitComposerFile(string $gitComposerFile) {
    $gitComposer = NULL;
    $file_system = $this->getFileSystem();
    if ($file_system->exists($gitComposerFile)) {
      $gitComposer = json_decode($file_system->getContents($gitComposerFile));
      if (!empty($gitComposer->require->{'drupal/drupal'})) {
        $this->version = $gitComposer->require->{'drupal/drupal'};
      }
    }
    if (!$gitComposer) {
      throw new MissingComposerException("The git repository does not appear to have a committed composer.json file.");
    }
    return $gitComposer;
  }

  protected function composeDrupalDrupal($composerFile, CommandStackInterface $commandStack) {
    $site_directory = $this->getDataDirectoryPath();
    $file_system = $this->getFileSystem();
    if (!$file_system->exists($composerFile)) {
      $this->new = TRUE;
      // composer create-project will not work in a directory with any
      // content so we remove the files which were rsynced into the directory
      // and remake the directory before building our project.
      $commandStack->addCommand("rm -Rf $site_directory");
      $commandStack->addCommand("mkdir -p $site_directory");
      $commandStack->addCommand("cd $site_directory");
      if ($this->version) {
        $commandStack->addCommand("composer create-project drupal/drupal:{$this->version} --no-interaction --prefer-dist --no-install .");
      }
      else {
        $commandStack->addCommand("composer create-project drupal/drupal --no-interaction --prefer-dist --no-install .");
      }
      $commandStack->addCommand("cp composer.json composer.original");
      // The lock file will prevent new composerable dependencies from being
      // installed after our git working directory is re-merged with this
      // directory structure, so we remove it since it will be regenerated
      // by a later composer install call.
      //$commandStack->addCommand("rm composer.lock");
    }
  }

  protected function composeDrupalCore($git_composer) {
    // If we have an original composer file and this is not a new build of
    // the site, we might need to get a new version of Drupal core.
    // If no version was specified, see if the running version is newest.
    if ($git_composer && !$this->new && $this->version && Comparator::lessThan((string) $this->getCurrentDrupalCoreVersion(), (string) $this->version)) {
      print "Versions: {$this->version}, {$this->getCurrentDrupalCoreVersion()}" . PHP_EOL;
      print "Version comparison " . var_export(Comparator::lessThan($this->getCurrentDrupalCoreVersion(), $this->version), TRUE) . PHP_EOL;
      $git_composer->require->{'drupal/core'} = $this->version;
    }
  }

  protected function getInstallCommand(CommandStackInterface $commandStack) {
    $site_directory = $this->getDataDirectoryPath();
    $file_system = $this->getFileSystem();
    // If we don't have a vendor dir yet, run install.
    if (!$file_system->exists("$site_directory/vendor")) {
      $commandStack->addCommand("cd $site_directory");
      $commandStack->addCommand("composer install");
    }
  }

  protected function rsyncSubDirectory(string $subdirectory, CommandStackInterface $commandStack) {
    $git_directory = $this->getGitDirectoryPath();
    $site_directory = $this->getDataDirectoryPath();
    $file_system = $this->getFileSystem();
    // Update modules directory with changes from the git repository.
    // @todo only do this if something changed in /$subdirectory
    if ($file_system->exists("$git_directory/$subdirectory")) {
      $commandStack->addCommand("rsync -av $git_directory/$subdirectory/ $site_directory/$subdirectory");
    }
  }

  protected function removeComposerDependencies(array $removals, CommandStackInterface $commandStack) {
    $commandStack->addCommand("cd {$this->getDataDirectoryPath()}");
    foreach ($removals as $package) {
      $commandStack->addCommand("composer remove $package");
    }
  }

  protected function updateComposerDependencies(array $update, CommandStackInterface $commandStack) {
    if ($update) {
      $commandStack->addCommand("cd {$this->getDataDirectoryPath()}");
      $commandStack->addCommand("composer update");
    }
  }

  protected function installComposerDependencies(array $install, CommandStackInterface $commandStack) {
    $commandStack->addCommand("cd {$this->getDataDirectoryPath()}");
    foreach ($install as $package => $version) {
      if ($version) {
        $commandStack->addCommand("composer require $package $version");
      }
      else {
        $commandStack->addCommand("composer require $package");
      }
    }
  }

  /**
   * Uses tokens to extract the current version of drupal/core.
   *
   * @return string
   */
  protected function getCurrentDrupalCoreVersion() {
    $drupal_class = "{$this->getDataDirectoryPath()}/core/lib/Drupal.php";
    $file_system = $this->getFileSystem();
    if ($file_system->exists($drupal_class)) {
      $drupal_class = $file_system->getContents($drupal_class);
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
   * @see \Hedron\Tools\ComposerHelperTrait::mergeComposerObjects
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
