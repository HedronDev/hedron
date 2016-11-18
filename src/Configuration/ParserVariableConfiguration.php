<?php

/**
 * @file
 * Contains \Worx\CI\Configuration\ParserVariableConfiguration.
 */

namespace Worx\CI\Configuration;

class ParserVariableConfiguration {

  protected $oldRevision;

  protected $newRevision;

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
    $this->referenceName = $referenceName;
    $this->branch = $branch;
  }

  public function getOldRevision() {
    return $this->oldRevision;
  }

  public function getNewRevision() {
    return $this->newRevision;
  }

  public function getReferenceName() {
    return $this->referenceName;
  }

  public function getBranch() {
    return $this->branch;
  }

}
