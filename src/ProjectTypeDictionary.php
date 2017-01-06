<?php

namespace Hedron;

use EclipseGc\Plugin\Dictionary\PluginDictionaryInterface;
use EclipseGc\Plugin\Traits\PluginDictionaryTrait;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;
use Hedron\Factory\ProjectTypeFactoryResolver;

class ProjectTypeDictionary implements PluginDictionaryInterface {
  use PluginDictionaryTrait;

  /**
   * ParserDictionary constructor.
   */
  public function __construct(\Traversable $namespaces) {
    $this->discovery = new AnnotatedPluginDiscovery($namespaces, 'ProjectType', 'Hedron\ProjectTypeInterface', 'Hedron\Annotation\ProjectType');
    $this->factoryResolver = new ProjectTypeFactoryResolver();
    $this->factoryClass = 'Hedron\Factory\ProjectTypeFactory';
    $this->pluginType = 'project_type';
  }

}
