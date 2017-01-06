<?php

namespace Hedron\Annotation;

use EclipseGc\PluginAnnotation\Definition\AnnotatedPluginDefinition;

/**
 * @Annotation
 */
class ProjectType extends AnnotatedPluginDefinition {

  /**
   * Human readable label of the project type.
   *
   * @var string
   */
  protected $label;

  /**
   * Retrieves the human readable label of the project type.
   *
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

}
