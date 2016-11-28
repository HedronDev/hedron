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
   * The project types to avoid working against.
   *
   * This is only really useful for parsers of project_type "all". It allows
   * the parser to specify any project types it should not work against as
   * opposed to projects it should be working against.
   *
   * @var array
   */
  protected $exclude = [];

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
   * Retrieves a list of project types this parser should not work against.
   *
   * @return array
   */
  public function getExclusions() {
    return $this->exclude;
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
