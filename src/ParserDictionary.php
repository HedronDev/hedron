<?php

namespace Hedron;

use EclipseGc\Plugin\Dictionary\PluginDictionaryInterface;
use EclipseGc\Plugin\Traits\PluginDictionaryTrait;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;
use Hedron\Factory\ParserFactoryResolver;

class ParserDictionary implements PluginDictionaryInterface {
  use PluginDictionaryTrait;

  /**
   * ParserDictionary constructor.
   */
  public function __construct(\Traversable $namespaces) {
    $this->discovery = new AnnotatedPluginDiscovery($namespaces, 'Parser', 'Hedron\FileParserInterface', 'Hedron\Annotation\Parser');
    $this->factoryResolver = new ParserFactoryResolver();
    $this->factoryClass = 'Hedron\Factory\ParserFactory';
    $this->pluginType = 'post_receive_parser';
  }

}
