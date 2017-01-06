<?php

/**
 * @file
 * Contains \Hedron\ProjectType\ProjectTypeBase.
 */

namespace Hedron\ProjectType;

use EclipseGc\Plugin\PluginDefinitionInterface;
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
   * Drupal8 constructor.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param \EclipseGc\Plugin\PluginDefinitionInterface $definition
   *   The plugin definition.
   */
  public function __construct(string $pluginId, PluginDefinitionInterface $definition) {
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId(): string {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition(): PluginDefinitionInterface {
    return $this->pluginDefinition;
  }

}
