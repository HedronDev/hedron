<?php

namespace Hedron\Factory;

use EclipseGc\Plugin\Factory\FactoryInterface;
use EclipseGc\Plugin\Factory\FactoryResolverInterface;

class ProjectTypeFactoryResolver implements FactoryResolverInterface {

  public function getFactoryInstance(string $factoryClass): FactoryInterface {
    return new ProjectTypeFactory();
  }

}
