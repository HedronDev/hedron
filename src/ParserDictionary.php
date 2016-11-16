<?php

namespace Worx\CI;

use EclipseGc\Plugin\Dictionary\PluginDictionaryInterface;
use EclipseGc\Plugin\Traits\PluginDictionaryTrait;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;
use Worx\CI\Factory\ParserFactoryResolver;

class ParserDictionary implements PluginDictionaryInterface {
  use PluginDictionaryTrait;

  /**
   * ParserDictionary constructor.
   */
  public function __construct(\Traversable $namespaces) {
    $this->discovery = new AnnotatedPluginDiscovery($namespaces, 'Parser', 'Worx\CI\FileParserInterface', 'Worx\CI\Annotation\Parser');
    $this->factoryResolver = new ParserFactoryResolver();
    $this->factoryClass = 'Worx\CI\Factory\ParserFactory';
    $this->pluginType = 'git_file_parser';
  }

}
