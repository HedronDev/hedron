<?php

namespace Worx\CI\Factory;

use EclipseGc\Plugin\Factory\FactoryInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;

class ParserFactory implements FactoryInterface {

  public function createInstance(PluginDefinitionInterface $definition, ...$constructors) {
    $class = $definition->getClass();
    return new $class($definition->getPluginId(), ...$constructors);
  }

}
