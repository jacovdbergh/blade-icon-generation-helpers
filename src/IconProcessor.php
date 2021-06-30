<?php

namespace Codeat3\BladeIconGeneration;

use PhpCsFixer\Finder;
use Illuminate\Support\Str;

class IconProcessor
{
    protected $file;

    public function __construct($filepath, $config = [])
    {
        // echo $filepath;
        $this->file = new \SplFileInfo($filepath);
        // print_r($this->file->getPath());die();
        // $finder = new Finder();
        // $this->file = $finder->files()->
    }

    // abstract protected function copy();

    // public function process()
    // {
    //     echo 'starting process....';
    //     $this->copy();
    // }

    // protected function beforeGenericOptimization($str) {
    //     return $str;
    // }

    // protected function optimize($str, $outline)
    // {
    //     $str = (new CollapseWhitespace())->apply($str);

    //     $tempStr = $this->beforeGenericOptimization($str);
    //     if(is_string($tempStr)) {
    //         $str = $tempStr;
    //     }

    //     $str = preg_replace(array_keys($this->replacePatterns), array_values($this->replacePatterns), $str);

    //     // remove width and height only from svg tag & not the internal one's
    //     $str = $this->removeWidthHeight($str);

    //     if(is_array($outline)) {
    //         $str = $this->optimizeCustomSvg($str, $outline);
    //     }

    //     // optimize for outline
    //     if ($outline === true) {
    //         $str = $this->optimizeOutlineSvg($str);
    //     }

    //     // optimize for solid
    //     if ($outline === false) {
    //         $str = $this->optimizeSolidSvg($str);
    //     }
    //     $str = (new CollapseWhitespace())->apply($str);

    //     return $str;
    // }

    // private function removeWidthHeight($str)
    // {

    //     $str = (new CollapseWhitespace())->apply($str);

    //     $crawler = new Crawler($str);
    //     $svg = $crawler->filter('svg')->first();

    //     $width = $svg->attr('width');
    //     $height = $svg->attr('height');

    //     if (! empty($width) && stristr($width, 'px') === false) {
    //         $str = str_replace('width="'.$width.'"', '', $str);
    //         $str = str_replace("width='${width}'", '', $str);
    //     }

    //     if (! empty($height) && stristr($height, 'px') === false) {
    //         $str = str_replace('height="'.$height.'"', '', $str);
    //         $str = str_replace("height='${height}'", '', $str);
    //     }

    //     return $str;
    // }

    // private function optimizeCustomSvg($str, $attrs)
    // {
    //     $crawler = new Crawler($str);
    //     $svg = $crawler->filter('svg')->first();

    //     foreach ($attrs as $key => $value) {
    //         $str = $this->addAttribute($str, $key, $value);
    //     }

    //     return $str;
    // }

    // private function optimizeOutlineSvg($str)
    // {
    //     $crawler = new Crawler($str);
    //     $svg = $crawler->filter('svg')->first();

    //     if ($svg->attr('fill') !== 'none') {
    //         $str = $this->addAttribute($str, 'fill', 'none');
    //     }
    //     if ($svg->attr('stroke') !== 'currentColor') {
    //         $str = $this->addAttribute($str, 'stroke', 'currentColor');
    //     }

    //     return $str;
    // }

    // private function optimizeSolidSvg($str)
    // {
    //     $crawler = new Crawler($str);
    //     $svg = $crawler->filter('svg')->first();

    //     if ($svg->attr('fill') !== 'currentColor') {
    //         $str = $this->addAttribute($str, 'fill', 'currentColor');
    //     }

    //     return $str;
    // }

    // private function addAttribute($str, $key, $value)
    // {
    /*     preg_match('/<svg.*?>/', $str, $matches);
        */
    //     if (count($matches) > 0 && isset($matches[0])) {
    //         $source = $matches[0];
    //         $replacement = str_replace('>', " $key=\"$value\">", $source);
    //         $str = str_replace($source, $replacement, $str);
    //     }

    //     return $str;
    // }

    // protected function getSourcePath(): string
    // {
    //     return __DIR__.'/../../'.$this->meta['temp-path'].'/'.$this->meta['clone-dir'];
    // }

    protected function getDestinationPath($dir = ''): string
    {
        $str = $this->file->getPath();
        if (! empty($dir)) {
            $str = $str.'/'.$dir;
        }

        return Str::finish($str, DIRECTORY_SEPARATOR);
    }

    // protected function cleanUp($filesystem)
    // {
    //     if ($filesystem->exists($this->getDestinationPath())) {
    //         $filesystem->remove($this->getDestinationPath());
    //     }

    //     $filesystem->mkdir($this->getDestinationPath());
    // }

    protected function getExtension()
    {
        return $this->file->getExtension();
    }

    public function getDestinationFileName($suffix = '', $dir = '')
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
