<?php

/**
 * @file
 * Contains \Hedron\ProjectType\ProjectTypeBase.
 */

namespace Hedron\ProjectType;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\File\FileSystem;
use Hedron\ProjectTypeInterface;

abstract class ProjectTypeBase implements ProjectTypeInterface {

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin definition.
   *
   * @var \EclipseGc\Plugin\PluginDefinitionInterface
   */
  protected $pluginDefinition;

  /**
   * The parser configuration.
   *
   * @var \Hedron\Configuration\ParserVariableConfiguration
   */
  protected $configuration;

  /**
   * The project environment settings.
   *
   * @var \Hedron\Configuration\EnvironmentVariables
   */
  protected $environment;

  /**
   * The file system.
   *
   * @var \Hedron\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Drupal8 constructor.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param \EclipseGc\Plugin\PluginDefinitionInterface $definition
   *   The plugin definition.
   */
  public function __construct(string $pluginId, PluginDefinitionInterface $definition, EnvironmentVariables $environment, ParserVariableConfiguration $configuration, FileSystem $fileSystem) {
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $definition;
    $this->configuration = $configuration;
    $this->environment = $environment;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() : string {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() : PluginDefinitionInterface {
    return $this->pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() : ParserVariableConfiguration {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment() : EnvironmentVariables {
    return $this->environment;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileSystem() : FileSystem {
    return $this->fileSystem;
  }

}
