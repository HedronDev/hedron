<?php

namespace Hedron\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Hedron\Command\CommandStackInterface;
use Hedron\Configuration\EnvironmentVariables;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\File\FileSystemInterface;
use Hedron\FileParserInterface;
use Hedron\GitPostReceiveHandler;

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
   * The environment configuration.
   *
   * @var \Hedron\Configuration\EnvironmentVariables
   */
  protected $environment;

  /**
   * The git repository configuration.
   *
   * @var \Hedron\Configuration\ParserVariableConfiguration
   */
  protected $configuration;

  /**
   * The file system.
   *
   * @var \Hedron\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * BaseParser constructor.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param \EclipseGc\Plugin\PluginDefinitionInterface $definition
   *   The plugin definition.
   * @param \Hedron\Configuration\EnvironmentVariables $environment
   *   The environment object
   * @param \Hedron\Configuration\ParserVariableConfiguration $configuration
   *   The configuration from the git post-receive hook.
   * @param \Hedron\File\FileSystemInterface $fileSystem
   *   The file system object.
   */
  public function __construct(string $pluginId, PluginDefinitionInterface $definition, EnvironmentVariables $environment, ParserVariableConfiguration $configuration, FileSystemInterface $fileSystem) {
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $definition;
    $this->environment = $environment;
    $this->configuration = $configuration;
    $this->fileSystem = $fileSystem;
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
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment() {
    return $this->environment;
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
    return "{$this->getEnvironment()->getClient()}-{$this->getConfiguration()->getBranch()}";
  }

  /**
   * The absolute path of the client site data directory.
   *
   * @return string
   *   The absolute path of the client site data directory.
   */
  protected function getSiteDirectoryPath() {
    return "{$this->getEnvironment()->getDockerDirectory()}/{$this->getClientDirectoryName()}/{$this->getEnvironment()->getDataDirectory()}";
  }

  /**
   * The absolute path of the git directory.
   *
   * @return string
   *   The absolute path of the git directory.
   */
  protected function getGitDirectoryPath() {
    return "{$this->getEnvironment()->getGitDirectory()}/{$this->getClientDirectoryName()}";
  }

  /**
   * The file system object.
   *
   * @return \Hedron\File\FileSystemInterface
   */
  protected function getFileSystem() {
    return $this->fileSystem;
  }

}
