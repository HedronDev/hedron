<?php

namespace Hedron\Factory;

use EclipseGc\Plugin\Factory\FactoryInterface;
use EclipseGc\Plugin\Factory\FactoryResolverInterface;

class ParserFactoryResolver implements FactoryResolverInterface {

  public function getFactoryInstance(string $factoryClass): FactoryInterface {
    return new ParserFactory();
  }

}
