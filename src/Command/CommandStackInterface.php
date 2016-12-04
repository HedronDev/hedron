<?php

/**
 * @file
 * Contains \Worx\CI\Command\CommandHandlerInterface.
 */

namespace Worx\CI\Command;

interface CommandStackInterface {

  /**
   * Add commands to the command stack for execution.
   *
   * @param string $command
   *   A shell command to invoke during execute().
   *
   * @return void
   */
  public function addCommand(string $command);

  /**
   * Executes the stack of commands.
   *
   * @return void
   */
  public function execute();

}
