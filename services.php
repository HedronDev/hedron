<?php

use Hedron\ParserDictionary;
use Hedron\ProjectTypeDictionary;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

$container->register('dictionary.parser', ParserDictionary::class)
  ->addArgument('%namespaces%');

$container->register('dictionary.project.type', ProjectTypeDictionary::class)
  ->addArgument('%namespaces%')
  ->addArgument('%environment%')
  ->addArgument('%configuration%')
  ->addArgument('%file.system%');

$container->setDefinition('event_dispatcher', new Definition(ContainerAwareEventDispatcher::class, [new Reference('service_container')]));
