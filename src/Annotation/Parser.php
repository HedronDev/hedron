<?php

namespace Worx\CI\Annotation;

use EclipseGc\PluginAnnotation\Definition\AnnotatedPluginDefinition;

/**
 * @Annotation
 */
class Parser extends AnnotatedPluginDefinition {

  /**
   * The category of project against which this parser will work.
   *
   * @var string
   */
  protected $project_type = 'all';

  /**
   * The priority of the parser.
   *
   * @var int
   */
  protected $priority = 0;

  /**
   * Retrieves the type of project.
   *
   * @return string
   */
  public function getProjectType() {
    return $this->project_type;
  }

  /**
   * Retrieves the parser priority.
   *
   * @return int
   */
  public function getPriority() {
    return $this->priority;
  }

}
