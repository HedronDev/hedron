<?php

namespace Hedron;

use EclipseGc\Plugin\Dictionary\PluginDictionaryInterface;
use EclipseGc\Plugin\Traits\PluginDictionaryTrait;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\Factory\ProjectTypeFactoryResolver;
use Hedron\File\FileSystem;

class ProjectTypeDictionary implements PluginDictionaryInterface {
  use PluginDictionaryTrait;

  /**
   * The project environment settings.
   *
   * @var \Hedron\Configuration\EnvironmentVariables
   */
  protected $environment;

  /**
   * The parser configuration.
   *
   * @var \Hedron\Configuration\ParserVariableConfiguration
   */
  protected $configuration;

  /**
   * The file system.
   *
   * @var \Hedron\File\FileSystem
   */
  protected $fileSystem;

  /**
   * ParserDictionary constructor.
   */
  public function __construct(\Traversable $namespaces, EnvironmentVariables $environment, ParserVariableConfiguration $configuration, FileSystem $fileSystem) {
    $this->environment = $environment;
    $this->configuration = $configuration;
    $this->fileSystem = $fileSystem;
    $this->discovery = new AnnotatedPluginDiscovery($namespaces, 'ProjectType', 'Hedron\ProjectTypeInterface', 'Hedron\Annotation\ProjectType');
    $this->factoryResolver = new ProjectTypeFactoryResolver();
    $this->factoryClass = 'Hedron\Factory\ProjectTypeFactory';
    $this->pluginType = 'project_type';
  }

  /**
   * Get a project plugin for the current environment settings.
   *
   * @return \Hedron\ProjectTypeInterface
   */
  public function getCurrentProject() {
    return $this->createInstance($this->environment->getProjectType(), $this->environment, $this->configuration, $this->fileSystem);
  }

}
