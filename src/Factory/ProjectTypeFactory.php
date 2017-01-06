<?php

namespace Hedron\Factory;

use EclipseGc\Plugin\Factory\FactoryInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;

class ProjectTypeFactory implements FactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createInstance(PluginDefinitionInterface $definition, ...$constructors) {
    $class = $definition->getClass();
    return new $class($definition->getPluginId(), ...$constructors);
  }

}
