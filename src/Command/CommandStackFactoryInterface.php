<?php

namespace Worx\CI\Command;

interface CommandStackFactoryInterface {

  /**
   * Creates a new CommandStackInterface object.
   *
   * @param mixed ...$constructors
   *
   * @return CommandStackInterface
   */
  public function create(...$constructors);

}