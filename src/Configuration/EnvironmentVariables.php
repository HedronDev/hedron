<?php

/**
 * @file
 * Contains \Worx\CI\Configuration\EnvironmentVariables.
 */

namespace Worx\CI\Configuration;

use Worx\CI\Exception\InvalidEnvironmentConfigurationException;

class EnvironmentVariables {

  protected $projectType;

  protected $host;

  protected $gitDirectory;

  protected $gitRepository;

  protected $client;

  protected $dockerDirectory;

  protected $dataDirectory;

  /**
   * EnvironmentVariables constructor.
   */
  public function __construct(array $configuration) {
    $this->validate($configuration);
    $this->projectType = $configuration['projectType'];
    $this->host = $configuration['host'];
    $this->gitDirectory = $configuration['gitDirectory'];
    $this->gitRepository = $configuration['gitRepository'];
    $this->client = $configuration['client'];
    $this->dockerDirectory = $configuration['dockerDirectory'];
    $this->dataDirectory = $configuration['dataDirectory'];
  }

  protected function validate(array $configuration) {
    $expectations = [
      'projectType',
      'host',
      'gitDirectory',
      'gitRepository',
      'client',
      'dockerDirectory',
      'dataDirectory',
    ];
    $message = "The environment must have a configured %s entry.";
    foreach ($expectations as $expectation) {
      if (!isset($configuration[$expectation]) || !is_string($configuration[$expectation])) {
        // @todo add validation specific to the individual variables.
        throw new InvalidEnvironmentConfigurationException(sprintf($message, $expectation));
      }
    }
  }

  public function getProjectType() {
    return $this->projectType;
  }

  public function getHost() {
    return $this->host;
  }

  public function getGitDirectory() {
    return $this->gitDirectory;
  }

  public function getGitRepository() {
    return $this->gitRepository;
  }

  public function getClient() {
    return $this->client;
  }

  public function getDockerDirectory() {
    return $this->dockerDirectory;
  }

  public function getDataDirectory() {
    return $this->dataDirectory;
  }

}
