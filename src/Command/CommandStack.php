<?php

namespace Hedron\Command;

use Symfony\Component\Console\Output\OutputInterface;

class CommandStack implements CommandStackInterface {

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * @var array
   */
  protected $commands = [];

  /**
   * CommandHandler constructor.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface to append command output to.
   */
  public function __construct(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * {@inheritdoc}
   */
  public function addCommand(string $command) {
    $this->commands[] = $command;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $this->output->writeln('<info>' . shell_exec(implode('; ', $this->commands)) . '</info>');
    $this->commands = [];
  }
}