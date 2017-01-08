<?php

namespace Hedron;

use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use EclipseGc\Plugin\PluginInterface;

interface ProjectTypeInterface extends PluginInterface {

  const COLLECT_PARSER_SET = 'project.type.parser.set';

  /**
   * Specify a set of definitions to run for this project type.
   *
   * @param \Hedron\ParserDictionary $dictionary
   *   The parser dictionary.
   *
   * @return \EclipseGc\Plugin\Discovery\PluginDefinitionSet
   *   The set of parser definition to run for this project type.
   */
  public static function getFileParsers(ParserDictionary $dictionary) : PluginDefinitionSet ;

}
