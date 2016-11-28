<?php

/**
 * @file
 * Contains \Worx\CI\Bootstrap.
 */

namespace Worx\CI;

use Composer\Autoload\ClassLoader;
use EclipseGc\Plugin\Filter\PluginDefinitionFilterInterface;
use Symfony\Component\Yaml\Yaml;
use Worx\CI\Configuration\EnvironmentVariables;
use Worx\CI\Configuration\ParserVariableConfiguration;
use Worx\CI\Exception\MissingEnvironmentConfigurationException;

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
   * Extracts data from the post-receive hook as configuration for easy use.
   *
   * @param string $input
   *   Input to the post-receive git hook
   *
   * @return \Worx\CI\Configuration\ParserVariableConfiguration
   *   A simple configuration object.
   */
  public static function getConfiguration(string $input) {
    list($oldrev, $newrev, $refname) = explode(' ', $input);
    list(,, $branch) = explode('/', $refname);
    return new ParserVariableConfiguration($oldrev, $newrev, $refname, $branch);
  }

  /**
   * Bootstraps the environment variables.
   *
   * @return \Worx\CI\Configuration\EnvironmentVariables
   *   The environment variables from yaml.
   *
   * @throws \Worx\CI\Exception\MissingEnvironmentConfigurationException
   *   If the yaml file is missing, throws this exception.
   */
  public static function getEnvironmentVariables() {
    $dir = shell_exec('pwd');
    $environment_file = file_get_contents($dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'environment.yml');
    if (!$environment_file) {
      throw new MissingEnvironmentConfigurationException("The environment configuration is missing, please contact your administrator.");
    }
    return new EnvironmentVariables(Yaml::parse($environment_file));
  }

  /**
   * Gets an array of valid parser plugins for the given filters.
   *
   * @param \Worx\CI\Configuration\EnvironmentVariables $environment
   *   The environment configuration.
   * @param \Worx\CI\Configuration\ParserVariableConfiguration $configuration
   *   The git repository configuration.
   * @param \Worx\CI\ParserDictionary $dictionary
   *   The parser plugin dictionary
   * @param \EclipseGc\Plugin\Filter\PluginDefinitionFilterInterface[] ...$filters
   *   The list of filters to apply.
   *
   * @return \Worx\CI\FileParserInterface[]
   *   The valid parser plugins.
   */
  public static function getValidParsers(EnvironmentVariables $environment, ParserVariableConfiguration $configuration, ParserDictionary $dictionary, PluginDefinitionFilterInterface ...$filters) {
    $plugins = [];
    foreach ($dictionary->getFilteredDefinitions(...$filters) as $pluginDefinition) {
      $plugins[] = $dictionary->createInstance($pluginDefinition->getPluginId(), $pluginDefinition, $environment, $configuration);
    }
    usort($plugins, '\Worx\CI\Bootstrap::sortPlugins');
    return $plugins;
  }

  /**
   * Sorts FileParserInterface objects by their priority.
   *
   * @param \Worx\CI\FileParserInterface $a
   *   The first parser.
   * @param \Worx\CI\FileParserInterface $b
   *   The second parser.
   *
   * @return bool
   */
  public static function sortPlugins(FileParserInterface $a, FileParserInterface $b) {
    return $a->getPluginDefinition()->getPriority() < $b->getPluginDefinition()->getPriority();
  }

}
