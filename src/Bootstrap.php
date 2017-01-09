<?php

namespace Hedron;

use Composer\Autoload\ClassLoader;
use Hedron\Event\ParserSetEvent;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\Exception\MissingEnvironmentConfigurationException;
use Hedron\File\FileSystemInterface;

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
   * @return \Hedron\Configuration\ParserVariableConfiguration
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
   * @return \Hedron\Configuration\EnvironmentVariables
   *   The environment variables from yaml.
   *
   * @throws \Hedron\Exception\MissingEnvironmentConfigurationException
   *   If the yaml file is missing, throws this exception.
   */
  public static function getEnvironmentVariables() {
    $dir = shell_exec('pwd');
    $dir_parts = explode(DIRECTORY_SEPARATOR, $dir);
    $project = trim(array_pop($dir_parts));
    $client = trim(array_pop($dir_parts));
    $dir = $dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'project' . DIRECTORY_SEPARATOR . $client . DIRECTORY_SEPARATOR . $project;
    $environment_file = file_get_contents($dir . DIRECTORY_SEPARATOR . 'environment.yml');
    if (!$environment_file) {
      throw new MissingEnvironmentConfigurationException("The environment configuration is missing, please contact your administrator.");
    }
    return new EnvironmentVariables(Yaml::parse($environment_file));
  }

  /**
   * Iterate through all namespace dirs and add services to the container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container builder.
   * @param \Traversable $namespaces
   *   The namespaces.
   */
  public static function collectServices(ContainerBuilder $container, \Traversable $namespaces) {
    $service_directories = [];
    foreach ($namespaces as $directory) {
      // $directory will correspond to the src dir, so up one level.
      $service_directories[] = $directory . DIRECTORY_SEPARATOR . '..';
    }
    $loader = new PhpFileLoader($container, new FileLocator($service_directories));
    $loader->load('services.php');
  }

  /**
   * Get a project plugin for the current environment settings.
   *
   * @param \Hedron\ProjectTypeDictionary $projectTypeDictionary
   *   The project type dictionary.
   *
   * @return \Hedron\ProjectTypeInterface
   *   The current project type plugin.
   */
  public static function getProject(ProjectTypeDictionary $projectTypeDictionary) {
    return $projectTypeDictionary->getCurrentProject();
  }

  /**
   * Gets an array of valid parser plugins for the project type.
   *
   * @param \Hedron\ProjectTypeInterface $project
   *   The project plugin for the current environment settings.
   * @param \Hedron\ParserDictionary $parserDictionary
   *   The parser dictionary.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   *
   * @return \Hedron\FileParserInterface[]
   *   The valid parser plugins.
   */
  public static function getValidParsers(ProjectTypeInterface $project, ParserDictionary $parserDictionary, EventDispatcherInterface $dispatcher) {
    $parserSet = $project::getFileParsers($parserDictionary);
    $event = new ParserSetEvent($project, $parserSet);
    $dispatcher->dispatch(ProjectTypeInterface::COLLECT_PARSER_SET, $event);
    $plugins = [];
    foreach ($event->getParserDefinitionSet() as $parserDefinition) {
      $plugins[] = $parserDictionary->createInstance($parserDefinition->getPluginId(), $parserDefinition, $project);
    }
    return $plugins;
  }

}
