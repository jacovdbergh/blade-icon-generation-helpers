<?php

namespace Codeat3\BladeIconGeneration;

use PhpCsFixer\Finder;
use Illuminate\Support\Str;

class IconProcessor
{
    protected $file;

    public function __construct($filepath, $config = [])
    {
        $this->file = new \SplFileInfo($filepath);
    }
    protected function getDestinationPath($dir = ''): string
    {
        $str = $this->file->getPath();
        if (! empty($dir)) {
            $str = $str.'/'.$dir;
        }

        return Str::finish($str, DIRECTORY_SEPARATOR);
    }

    protected function getExtension()
    {
        return $this->file->getExtension();
    }

    public function getDestinationFilePath($suffix = '', $dir = '')
    {
        return $this->getDestinationPath($dir)
                .Str::slug($this->getFileName())
                .$suffix
                .'.'
                .$this->getExtension();
    }

    protected function getFileName()
    {
        return $this->file->getBasename('.svg');
    }
}
