<?php

namespace Worx\CI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Worx\CI\Command\CommandStack;
use Worx\CI\Command\CommandStackFactoryInterface;
use Worx\CI\Configuration\ParserVariableConfiguration;

class GitPostReceiveHandler extends Application
{
  protected $output;
  protected $input;

  /**
   * @var \Worx\CI\Configuration\ParserVariableConfiguration
   */
  protected $configuration;

  /**
   * @var \Worx\CI\Command\CommandStackFactoryInterface
   */
  protected $commandStackFactory;

  /**
   * @var \Worx\CI\FileParserInterface[]
   */
  protected $fileParsers;

  protected $committedFiles;

  protected $allFiles;

  protected $intersectFiles;

  /**
   * PostReceiveHandler constructor.
   *
   * @param \Worx\CI\Configuration\ParserVariableConfiguration $configuration
   * @param \Worx\CI\Command\CommandStackFactoryInterface $commandStackFactory
   * @param \Worx\CI\FileParserInterface[] $fileParsers
   */
  public function __construct(ParserVariableConfiguration $configuration, CommandStackFactoryInterface $commandStackFactory, FileParserInterface ...$fileParsers)
  {
    $this->configuration = $configuration;
    $this->commandStackFactory = $commandStackFactory;
    $this->fileParsers = $fileParsers;
    parent::__construct('Git Post Receive handler', '0.0.2');
  }

  /**
   * {@inheritdoc}GitPostReceiveHandler
   */
  public function doRun(InputInterface $input, OutputInterface $output)
  {
    $this->input = $input;
    $this->output = $output;
    //$output->writeln('<info>' . shell_exec("git diff {$this->oldRevision}..{$this->newRevision}") . '</info>');
    $this->committedFiles = $this->extractCommittedFiles();
    $this->allFiles = $this->extractTopLevelFiles();
    $this->intersectFiles = array_intersect($this->allFiles, $this->committedFiles);
    if ($this->getConfiguration()->execute()) {
      $this->parseFiles();
    }
  }

  /**
   * Gets a list of files that were committed in this revision.
   *
   * @return array
   */
  protected function extractCommittedFiles()
  {
    return explode(PHP_EOL, trim(shell_exec("git diff-tree --no-commit-id --name-only -r {$this->configuration->getNewRevision()}")));
  }

  /**
   * Gets a list of all files in the repository.
   *
   * @return array
   */
  protected function extractTopLevelFiles()
  {
    return explode(PHP_EOL, trim(shell_exec("git ls-tree --full-tree --name-only HEAD")));
  }

  public function parseFiles()
  {
    foreach ($this->fileParsers as $parser) {
      $parser->parse($this, $this->commandStackFactory->create($this->getOutput()));
    }
  }

  public function getCommittedFiles()
  {
    return $this->committedFiles;
  }

  public function getTopLevelFiles()
  {
    return $this->allFiles;
  }

  public function getIntersectFiles()
  {
    return $this->intersectFiles;
  }

  /**
   * @return OutputInterface
   */
  public function getOutput()
  {
    return $this->output;
  }

  /**
   * @return \Worx\CI\Configuration\ParserVariableConfiguration
   */
  public function getConfiguration()
  {
    return $this->configuration;
  }

}
