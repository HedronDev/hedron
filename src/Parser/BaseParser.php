<?php

/**
 * @file
 * Contains \Worx\CI\Parser\BaseParser.
 */

namespace Worx\CI\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Worx\CI\FileParserInterface;

abstract class BaseParser implements FileParserInterface {

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
   * BaseParser constructor.
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
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition(): PluginDefinitionInterface {
    return $this->pluginDefinition;
  }

}