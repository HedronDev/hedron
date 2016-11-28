<?php

namespace Worx\CI\Filter;

use EclipseGc\Plugin\Filter\PluginDefinitionFilterInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;

class ProjectTypeFilter implements PluginDefinitionFilterInterface {

  /**
   * The type of project against which to filter.
   *
   * @var string
   */
  protected $projectType;

  /**
   * ProjectTypeFilter constructor.
   */
  public function __construct(string $project_type) {
    $this->projectType = $project_type;
  }

  /**
   * {@inheritdoc}
   */
  public function filter(PluginDefinitionInterface $definition): bool {
    /** @var \Worx\CI\Annotation\Parser $definition */
    $project_type = $definition->getProjectType();
    if (in_array($this->projectType, $definition->getExclusions())) {
      return FALSE;
    }
    if ($project_type == $this->projectType || $project_type == 'all') {
      return TRUE;
    }
    return FALSE;
  }

}
