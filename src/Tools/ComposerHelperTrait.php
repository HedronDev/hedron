<?php

namespace Hedron\Tools;

use Composer\Semver\Comparator;

trait ComposerHelperTrait {

  /**
   * Merges the original composer file with a new composer file.
   *
   * This ensures that the composer.json file that is going to be installed
   * will have all the appropriate requirements documented by the git
   * repository that is kept up to date by the client.
   *
   * @param string $composer_file
   *   The absolute path to the composer file to write.
   * @param \stdClass $original_composer
   *   The original composer.json file before it was removed as a php object.
   * @param \stdClass $new_composer
   *   The new composer.json file as a php object.
   * @param callable $callback
   *   A callback if necessary to customize the composer object further.
   *
   * @return array
   */
  protected function mergeComposerJsonFiles(string $composer_file, \stdClass $original_composer, \stdClass $new_composer, callable $callback = NULL) {
    $new_composer = $this->mergeComposerObjects($original_composer, $new_composer, $callback);
    $composer_content = file_get_contents($composer_file);
    $replace_composer = json_decode($composer_content);
    $changes = $this->calculateRequirementChanges($replace_composer, $new_composer);
    if (file_put_contents($composer_file, json_encode($new_composer, JSON_PRETTY_PRINT)) !== FALSE) {
      return $changes;
    }
  }

  /**
   * Merges two composer objects into one.
   *
   * @param \stdClass $original_composer
   *   The original composer.json file before it was removed as a php object.
   * @param \stdClass $new_composer
   *   The new composer file to generate.
   * @param callable $callback
   *   A callback if necessary to customize the composer object further.
   *
   * @return \stdClass
   *   An object representing the merged composer json files.
   */
  protected function mergeComposerObjects(\stdClass $original_composer, \stdClass $new_composer, callable  $callback = NULL) {
    foreach ($original_composer as $key => $values) {
      $is_array = is_array($values);
      $values = (array) $values;
      if (isset($new_composer->{$key})) {
        $value = (array) $new_composer->{$key};
        $values = array_merge($value, $values);
      }
      $new_composer->{$key} = $is_array ? $values : (object) $values;
    }
    if ($callback) {
      call_user_func($callback, $new_composer);
    }
    return $new_composer;
  }

  /**
   * Calculated requirements that need to be removed from the system.
   *
   * @param $replace_composer
   *   Composer object about to be replaced.
   * @param $new_composer
   *   Composer object doing the replacing.
   *
   * @return array
   *   An array of requirements to install, update or remove.
   */
  protected function calculateRequirementChanges($replace_composer, $new_composer) {
    $changes = [
      'install' => [],
      'update' => [],
      'remove' => [],
    ];
    foreach (['require', 'require-dev'] as $key) {
      $old_requirements = !empty($replace_composer->{$key}) ? $replace_composer->{$key} : [];
      $new_requirements = !empty($new_composer->{$key}) ? $new_composer->{$key} : [];
      foreach ($new_requirements as $requirement => $version) {
        if (empty($old_requirements->{$requirement})) {
          $changes['install'][$requirement] = $version;
        }
        if ((!empty($old_requirements->{$requirement}) && Comparator::greaterThan($version, $old_requirements->{$requirement}))) {
          $changes['update'][$requirement] = $version;
        }
      }
      foreach ($old_requirements as $requirement => $version) {
        if (empty($new_requirements->{$requirement})) {
          $changes['remove'][] = $requirement;
        }
      }
    }
    return $changes;
  }

}
