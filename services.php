<?php

use Hedron\ParserDictionary;

$container->register('dictionary.parser', ParserDictionary::class)
  ->addArgument('%namespaces%');
