<?php

namespace Hedron\Command;

class CommandStackFactory implements CommandStackFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function create(...$constructors) {
    return new CommandStack(...$constructors);
  }

}
