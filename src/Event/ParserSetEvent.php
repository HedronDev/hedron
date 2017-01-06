<?php

namespace Hedron\Event;

use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use Symfony\Component\EventDispatcher\Event;

class ParserSetEvent extends Event {

  /**
   * The set of parsers to evaluate or manipulate.
   *
   * @var PluginDefinitionSet
   */
  protected $set;

  /**
   * Set the parser plugin definition set.
   *
   * @param \EclipseGc\Plugin\Discovery\PluginDefinitionSet $set
   */
  public function setParserDefinitionSet(PluginDefinitionSet $set) {
    $this->set = $set;
  }

  /**
   * Get the parser plugin definition set.
   *
   * @return \EclipseGc\Plugin\Discovery\PluginDefinitionSet
   */
  public function getParserDefinitionSet() : PluginDefinitionSet {
    return $this->set;
  }

}
