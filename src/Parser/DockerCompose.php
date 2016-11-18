<?php

namespace Worx\CI\Parser;

use Worx\CI\GitPostReceiveHandler;

/**
 * @Worx\CI\Annotation\Parser(
 *   pluginId = "docker_compose",
 *   priority = "100"
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
    if ($parse) {
      $configuration = $this->getConfiguration();
      $environment = $this->getEnvironment();
      $clientDir = "{$environment->getClient()}_{$configuration->getBranch()}";
      $commands = [];
      if ($environment->getHost() != 'localhost') {
        $commands[] = "ssh root@{$environment->getHost()}";
        $commands[] = "cd {$environment->getGitDirectory()}";
      }
      if (file_exists("{$environment->getDockerDirectory()}/$clientDir")) {
        $commands[] = "unset GIT_DIR";
        $commands[] = "git -C {$environment->getGitDirectory()}/$clientDir pull";
        $commands[] = "rsync -av --delete {$environment->getGitDirectory()}/$clientDir/docker/ {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cd {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "docker-compose down";
        $commands[] = "docker-compose build";
        $commands[] = "docker-compose up -d";
      }
      else {
        $commands[] = "git clone --branch {$configuration->getBranch()} {$environment->getGitRepository()} $clientDir";
        $commands[] = "cd $clientDir";
        $commands[] = "mkdir {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cp -r {$environment->getDockerDirectory()}/$clientDir/docker/. {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "cd {$environment->getDockerDirectory()}/$clientDir";
        $commands[] = "ls";
        $commands[] = "docker-compose up -d";
      }
      echo print_r($commands, TRUE);
      $handler->getOutput()->writeln('<info>' . shell_exec(implode('; ', $commands)) . '</info>');
    }
  }

}
