<?php

namespace Worx\CI\Parser;

use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "docker_compose",
 *   priority = "950"
 * )
 */
class DockerCompose extends BaseParser {

  public function parse(GitPostReceiveHandler $handler) {
    $parse = FALSE;
    foreach ($handler->getCommittedFiles() as $file_name) {
      if (strpos($file_name, 'docker/') === 0) {
        $parse = TRUE;
        break;
      }
    }
    $configuration = $this->getConfiguration();
    $environment = $this->getEnvironment();
    $clientDir = "{$environment->getClient()}-{$configuration->getBranch()}";
    if (!$parse && !file_exists("{$environment->getDockerDirectory()}/$clientDir") && !file_exists("{$environment->getGitDirectory()}/docker/docker-compose.yml")) {
      $parse = TRUE;
    }
    if ($parse) {
      $commands = [];
      if ($environment->getHost() != 'localhost') {
        $commands[] = "ssh root@{$environment->getHost()}";
      }
      if (file_exists("{$environment->getDockerDirectory()}/$clientDir")) {
        $commands[] = "rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cd {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "docker-compose down";
        $commands[] = "docker-compose build";
        $commands[] = "docker-compose up -d";
      }
      else {
        $commands[] = "mkdir {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cp -r {$environment->getGitDirectory()}/$clientDir/docker/. {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cd {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "docker-compose up -d";
      }
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

}
