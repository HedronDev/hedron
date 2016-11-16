<?php

namespace Worx\CI\Parser;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "composer",
 *   project_type = "php",
 *   priority = "10"
 * )
 */
class ComposerJson extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function parse(array $intersect, array $committed, array $all, OutputInterface $output) {
    if (array_search('composer.json', $all)) {
      $output->writeln('<info>Do composer.json work.</info>');
    }
  }

}
