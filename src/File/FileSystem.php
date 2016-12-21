<?php

namespace Hedron\File;

class FileSystem implements FileSystemInterface {

  public function exists(string $fileName) {
    return file_exists($fileName);
  }

  public function getContents(string $fileName) {
    $content = file_get_contents($fileName);
    return $content;
  }

  public function putContents(string $filename, $data) {
    return file_put_contents($filename, $data);
  }



}