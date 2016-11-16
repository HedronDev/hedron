<?php

/**
 * @file
 * Contains \Worx\CI\Bootstrap.
 */

namespace Worx\CI;

use Composer\Autoload\ClassLoader;
use EclipseGc\Plugin\Filter\PluginDefinitionFilterInterface;

class Bootstrap {

  /**
   * Extracts a traversable object of namespaces and their directories.
   *
   * @param \Composer\Autoload\ClassLoader $classLoader
   *   The classloader from which to extract namespaces and directories.
   *
   * @return \Traversable
   *   The traversable list of namespaces.
   */
  public static function extractNamespaces(ClassLoader $classLoader) {
    $namespaces = [];
    foreach ($classLoader->getPrefixes() as $namespace => $directories) {
      $namespaces[$namespace] = $directories[0];
    }
    foreach ($classLoader->getPrefixesPsr4() as $namespace => $directories) {
      $namespaces[$namespace] = $directories[0];
    }
    return new \ArrayIterator($namespaces);
  }

  /**
   * Gets an array of valid parser plugins for the given filters.
   *
   * @param \Worx\CI\ParserDictionary $dictionary
   *   The parser plugin dictionary
   * @param \EclipseGc\Plugin\Filter\PluginDefinitionFilterInterface[] ...$filters
   *   The list of filters to apply.
   *
   * @return FileParserInterface[]
   *   The valid parser plugins.
   */
  public static function getValidParsers(ParserDictionary $dictionary, PluginDefinitionFilterInterface ...$filters) {
    $plugins = [];
    foreach ($dictionary->getFilteredDefinitions(...$filters) as $pluginDefinition) {
      $plugins[] = $dictionary->createInstance($pluginDefinition->getPluginId(), $pluginDefinition);
    }
    return $plugins;
  }

}