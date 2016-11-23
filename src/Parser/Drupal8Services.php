<?php

namespace Worx\CI\Parser;

use Symfony\Component\Yaml\Yaml;
use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "drupal_8_services",
 *   project_type = "drupal",
 *   priority = "8"
 * )
 */
class Drupal8Services extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler) {
    $settings_path = "{$this->getSiteDirectoryPath()}/sites/default";
    // @todo identify multisite
    if (file_exists("$settings_path/default.services.yml")) {
      // @todo add a foreach loop around something like:
      // $environment->getSites() so that we can write the default to each site
      // in a multisite install.
      copy("$settings_path/default.services.yml", "$settings_path/services.yml");
      chmod("$settings_path/services.yml", 0775);

      // @todo resepct the previous today about getSites() and also allow a
      // yaml file in the repository to be used to configure $settings.
      if (file_exists("{$this->getGitDirectoryPath()}/services.yml")) {
        $current_services = Yaml::parse(file_get_contents("$settings_path/services.yml"));
        $services_partial = Yaml::parse(file_get_contents("{$this->getGitDirectoryPath()}/services.yml"));
        file_put_contents("$settings_path/services.yml", Yaml::dump($this->mergeArray($current_services, $services_partial), 10, 4, Yaml::DUMP_OBJECT_AS_MAP));
      }
    }
  }

  protected function mergeArray(array ...$arrays) {
    $result = array();
    foreach ($arrays as $array) {
      foreach ($array as $key => $value) {
        // If our current result for this key is an array and the value is an
        // array recursively merge.
        if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
          $result[$key] = $this->mergeArray($result[$key], $value);
        }
        // Otherwise, use the latter value, overriding any previous value.
        else {
          $result[$key] = $value;
        }
      }
    }
    return $result;
  }

}
