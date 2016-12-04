<?php

/**
 * @file
 * Contains \Worx\CI\File\FileSystemInterface.
 */

namespace Worx\CI\File;

interface FileSystemInterface {

  public function exists(string $fileName);

  /**
   * @param string $fileName
   *
   * @return string
   */
  public function getContents(string $fileName);

  /**
   * @param string $filename
   * @param $data
   *
   * @return int
   */
  public function putContents(string $filename, $data);

}