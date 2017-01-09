<?php

namespace Hedron\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "ensure_shared_volumes"
 * )
 */
class EnsureSharedVolumes extends BaseParser {

  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    if (!$this->getFileSystem()->exists($this->getDataDirectoryPath())) {
      $commandStack->addCommand("mkdir -p {$this->getDataDirectoryPath()}");
    }
    if (!$this->getFileSystem()->exists($this->getSqlDirectoryPath())) {
      $commandStack->addCommand("mkdir -p {$this->getSqlDirectoryPath()}");
    }
    $commandStack->execute();
  }

  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $commandStack->addCommand("rm -Rf {$this->getDataDirectoryPath()}");
    $commandStack->addCommand("rm -Rf {$this->getSqlDirectoryPath()}");
    $commandStack->execute();
  }

}
