<?php

namespace Hedron;

use EclipseGc\Plugin\PluginInterface;
use Hedron\Command\CommandStackInterface;

interface FileParserInterface extends PluginInterface {

  /**
   * @param \Hedron\GitPostReceiveHandler $handler
   * @param \Hedron\Command\CommandStackInterface $commandStack
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

  /**
   * @param \Hedron\GitPostReceiveHandler $handler
   * @param \Hedron\Command\CommandStackInterface $commandStack
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

}
