<?php

namespace Worx\CI;

use EclipseGc\Plugin\PluginInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Worx\CI\Command\CommandStackInterface;

interface FileParserInterface extends PluginInterface {

  /**
   * @param \Worx\CI\GitPostReceiveHandler $handler
   * @param \Worx\CI\Command\CommandStackInterface $commandStack
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

  /**
   * @param \Worx\CI\GitPostReceiveHandler $handler
   * @param \Worx\CI\Command\CommandStackInterface $commandStack
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

}
