<?php

namespace Hedron\Test\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\Configuration\ParserVariableConfiguration;
use Hedron\GitPostReceiveHandler;
use Hedron\Parser\Rsync;

class FileParserInterfaceTest extends \PHPUnit_Framework_TestCase {

  public function testParser() {
    $handler = $this->prophesize(GitPostReceiveHandler::class);
    $commandStack = $this->prophesize(CommandStackInterface::class);
    $config = $this->prophesize(ParserVariableConfiguration::class);
    $config->getBranch()->willReturn('foo');
    $handler->getConfiguration()->willReturn($config->reveal());
//    $commandStack->addMethodProphecy()
    $parser = $this->prophesize(Rsync::class);
    $parser->getConfiguration()->willReturn($config->reveal());
    $parser->parse($handler, $commandStack)->willReturn();
  }

}
