<?php

namespace Hedron;

use EclipseGc\Plugin\PluginInterface;
use Hedron\Command\CommandStackInterface;

/**
 * Creates, updates or destroy an environment based upon changes in the code.
 */
interface FileParserInterface extends PluginInterface {

  /**
   * The configuration object.
   *
   * @return \Hedron\Configuration\ParserVariableConfiguration
   */
  public function getConfiguration();

  /**
   * The environment object.
   *
   * @return \Hedron\Configuration\EnvironmentVariables
   */
  public function getEnvironment();

  /**
   * Parses the changes in the handler and executes appropriate commands.
   *
   * @param \Hedron\GitPostReceiveHandler $handler
   *   The post-receive git hook handler
   * @param \Hedron\Command\CommandStackInterface $commandStack
   *   The command stack interface.
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

  /**
   * An environmental tear down operation to undo parser specific operations.
   *
   * @param \Hedron\GitPostReceiveHandler $handler
   *   The post-receive git hook handler
   * @param \Hedron\Command\CommandStackInterface $commandStack
   *   The command stack interface.
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

}
