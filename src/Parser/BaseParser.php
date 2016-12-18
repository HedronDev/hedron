<?php

/**
 * @file
 * Contains \Worx\CI\Parser\BaseParser.
 */

namespace Worx\CI\Parser;

use EclipseGc\Plugin\PluginDefinitionInterface;
use Worx\CI\Command\CommandStackInterface;
use Worx\CI\Configuration\EnvironmentVariables;
use Worx\CI\Configuration\ParserVariableConfiguration;
use Worx\CI\File\FileSystemInterface;
use Worx\CI\FileParserInterface;
use Worx\CI\GitPostReceiveHandler;

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
   * @var \Worx\CI\Configuration\EnvironmentVariables
   */
  protected $environment;

  /**
   * The git repository configuration.
   *
   * @var \Worx\CI\Configuration\ParserVariableConfiguration
   */
  protected $configuration;

  /**
   * The file system.
   *
   * @var \Worx\CI\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * BaseParser constructor.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param \EclipseGc\Plugin\PluginDefinitionInterface $definition
   *   The plugin definition.
   * @param \Worx\CI\Configuration\EnvironmentVariables $environment
   * @param \Worx\CI\Configuration\ParserVariableConfiguration $configuration
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

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getEnvironment() {
    return $this->environment;
  }

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
   * @return \Worx\CI\File\FileSystemInterface
   */
  protected function getFileSystem() {
    return $this->fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {}

}
