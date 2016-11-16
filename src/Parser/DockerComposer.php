<?php

namespace Worx\CI\Parser;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "docker_compose",
 *   priority = "100"
 * )
 */
class DockerComposer extends BaseParser {

  public function parse(array $intersect, array $committed, array $all, OutputInterface $output) {
    if (array_search('docker-compose.yml', $intersect)) {
      $output->writeln('<info>Do docker stuff here.</info>');
    }
  }

}
