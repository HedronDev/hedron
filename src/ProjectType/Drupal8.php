<?php

namespace Hedron\ProjectType;

use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use Hedron\Annotation\ProjectType;
use Hedron\ParserDictionary;

/**
 * @ProjectType(
 *   pluginId = "drupal8",
 *   label = "Drupal 8"
 * )
 */
class Drupal8 extends ProjectTypeBase {

  /**
   * {@inheritdoc}
   */
  public static function getFileParsers(ParserDictionary $dictionary) : PluginDefinitionSet {
    $parsers = [
      'git_pull',
      'ensure_shared_volumes',
      'composer_drupal_8',
      'drupal_8_services',
      'drupal_8_settings',
      'docker_compose',
      'docker_compose_ps',
    ];
    $definitions = [];
    foreach ($parsers as $parser) {
      $definitions[] = $dictionary->getDefinition($parser);
    }
    return new PluginDefinitionSet(... $definitions);
  }

}
