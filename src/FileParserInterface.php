<?php

namespace Worx\CI;

use EclipseGc\Plugin\PluginInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Worx\CI\Command\CommandStackInterface;

interface FileParserInterface extends PluginInterface {

  /**
   * @param \Worx\CI\GitPostReceiveHandler $handler
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack);

}
