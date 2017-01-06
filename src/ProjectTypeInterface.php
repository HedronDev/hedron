<?php

namespace Hedron;

use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use EclipseGc\Plugin\PluginInterface;

interface ProjectTypeInterface extends PluginInterface {

  const COLLECT_PARSER_SET = 'project.type.parser.set';

  /**
   * @param \Hedron\ParserDictionary $dictionary
   *
   * @return \EclipseGc\Plugin\Discovery\PluginDefinitionSet
   */
  public static function getFileParsers(ParserDictionary $dictionary) : PluginDefinitionSet ;

}
