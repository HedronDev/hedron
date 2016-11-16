<?php

namespace Worx\CI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class GitPostReceiveHandler extends Application
{
  private $output;
  private $input;

  protected $oldRevision;

  protected $newRevision;

  protected $referenceName;

  protected $branch;

  /**
   * @var \Worx\CI\FileParserInterface[]
   */
  protected $fileParsers;

  /**
   * PostReceiveHandler constructor.
   *
   * @param string $parameters
   * @param \Worx\CI\FileParserInterface[] $fileParsers
   */
  public function __construct($parameters, FileParserInterface ...$fileParsers)
  {
    list($oldrev, $newrev, $refname) = explode(' ', $parameters);
    list(,, $branch) = explode('/', $refname);
    $this->oldRevision = $oldrev;
    $this->newRevision = $newrev;
    $this->referenceName = $refname;
    $this->branch = $branch;
    $this->artifactDirectory = '~/opt/artifact/' . $branch;
    $this->fileParsers = $fileParsers;
    parent::__construct('Git Post Receive handler', '0.0.1');
  }

  /**
   * {@inheritdoc}
   */
  public function doRun(InputInterface $input, OutputInterface $output)
  {
    $this->input = $input;
    $this->output = $output;
    //$output->writeln('<info>' . shell_exec("git diff {$this->oldRevision}..{$this->newRevision}") . '</info>');
    $committedFiles = $this->extractCommittedFiles();
    $allFiles = $this->extractTopLevelFiles();
    $intersectFiles = array_intersect_assoc($committedFiles, $allFiles);
    $output->writeln('<info>' . print_r($intersectFiles, TRUE) . '</info>');
    $this->parseFiles($intersectFiles, $committedFiles, $allFiles);
  }

  /**
   * Gets a list of files that were committed in this revision.
   *
   * @return array
   */
  protected function extractCommittedFiles()
  {
    return explode(PHP_EOL, trim(shell_exec("git diff-tree --no-commit-id --name-only -r {$this->newRevision}")));
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

  public function parseFiles(array $intersect, array $committed, array $all)
  {
    foreach ($this->fileParsers as $parser) {
      $parser->parse($intersect, $committed, $all, $this->output);
    }
  }

}