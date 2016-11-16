<?php

namespace Worx\CI;

use EclipseGc\Plugin\PluginInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface FileParserInterface extends PluginInterface {

  /**
   * @param array $intersect
   * @param array $committed
   * @param array $all
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  public function parse(array $intersect, array $committed, array $all, OutputInterface $output);

}
