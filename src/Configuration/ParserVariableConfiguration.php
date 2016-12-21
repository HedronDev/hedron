<?php

namespace Hedron\Configuration;

class ParserVariableConfiguration {

  protected $oldRevision;

  protected $newRevision;

  /**
   * Determines if parsers should even be fired.
   *
   * @var bool
   */
  protected $execute;

  protected $referenceName;

  protected $branch;

  /**
   * ParserVariableConfiguration constructor.
   *
   * @param $oldRevision
   * @param $newRevision
   * @param $referenceName
   */
  public function __construct($oldRevision, $newRevision, $referenceName, $branch) {
    $this->oldRevision = $oldRevision;
    $this->newRevision = $newRevision;
    $this->execute = $newRevision != '0000000000000000000000000000000000000000';
    $this->referenceName = $referenceName;
    $this->branch = $branch;
  }

  public function getOldRevision() {
    return $this->oldRevision;
  }

  public function getNewRevision() {
    return $this->newRevision;
  }

  public function execute() {
    return $this->execute;
  }

  public function getReferenceName() {
    return $this->referenceName;
  }

  public function getBranch() {
    return $this->branch;
  }

}
