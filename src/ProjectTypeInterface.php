<?php

namespace Hedron;

use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use EclipseGc\Plugin\PluginInterface;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\File\FileSystem;

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

  /**
   * Get the post-receive configuration.
   *
   * @return \Hedron\Configuration\ParserVariableConfiguration
   */
  public function getConfiguration() : ParserVariableConfiguration ;

  /**
   * Get the project environment settings.
   *
   * @return \Hedron\Configuration\EnvironmentVariables
   */
  public function getEnvironment() : EnvironmentVariables ;

  /**
   * Get the file system.
   *
   * @return \Hedron\File\FileSystem
   */
  public function getFileSystem() : FileSystem ;

}
