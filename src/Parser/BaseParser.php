<?php

namespace Hedron\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Hedron\Command\CommandStackInterface;
use Hedron\FileParserInterface;
use Hedron\GitPostReceiveHandler;
use Hedron\ProjectTypeInterface;

/**
 * A base implementation for any parser plugin to extend.
 */
abstract class BaseParser implements FileParserInterface {

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin definition.
   *
   * @var \EclipseGc\Plugin\PluginDefinitionInterface
   */
  protected $pluginDefinition;

  /**
   * The project plugin for the current environment settings.
   *
   * @var \Hedron\ProjectTypeInterface
   */
  protected $project;

  /**
   * BaseParser constructor.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param \EclipseGc\Plugin\PluginDefinitionInterface $definition
   *   The plugin definition.
   * @param \Hedron\ProjectTypeInterface $project
   *   The project plugin for the current environment settings.
   */
  public function __construct(string $pluginId, PluginDefinitionInterface $definition, ProjectTypeInterface $project) {
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $definition;
    $this->project = $project;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId(): string {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition(): PluginDefinitionInterface {
    return $this->pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->project->getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment() {
    return $this->project->getEnvironment();
  }

  /**
   * {@inheritdoc}
   */
  public function getProject() {
    return $this->project;
  }

  /**
   * {@inheritdoc}
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {}

  /**
   * The client directory in {client}-{branch} format.
   *
   * @return string
   *   The client directory name.
   */
  protected function getClientDirectoryName() {
    return strtolower($this->getEnvironment()->getClient()) . '-' . strtolower($this->getEnvironment()->getName()) . '-' . $this->getConfiguration()->getBranch();
  }

  /**
   * The absolute path of the git directory.
   *
   * @return string
   *   The absolute path of the git directory.
   */
  protected function getGitDirectoryPath() {
    return "{$this->getEnvironment()->getGitDirectory()}/{$this->getConfiguration()->getBranch()}";
  }

  /**
   * The absolute path of the client site data directory.
   *
   * @return string
   *   The absolute path of the client site data directory.
   */
  protected function getDataDirectoryPath() {
    $data_dir = $this->getEnvironment()->getDataDirectory();
    $config = $this->getConfiguration();
    return str_replace('{branch}', $config->getBranch(), $data_dir);
  }

  protected function getSqlDirectoryPath() {
    $data_dir = $this->getEnvironment()->getDataDirectory();
    $config = $this->getConfiguration();
    return str_replace(['{branch}', 'web'], [$config->getBranch(), 'sql'], $data_dir);
  }

  protected function getDockerDirectoryPath() {
    $data_dir = $this->getEnvironment()->getDockerDirectory();
    $config = $this->getConfiguration();
    $environment = $this->getEnvironment();
    return $data_dir . DIRECTORY_SEPARATOR . $environment->getClient() . '-' . $environment->getName() . '-' . $config->getBranch();
  }

  /**
   * The file system object.
   *
   * @return \Hedron\File\FileSystemInterface
   */
  protected function getFileSystem() {
    return $this->project->getFileSystem();
  }

}
