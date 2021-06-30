<?php

namespace Codeat3\BladeIconGeneration;

use DOMDocument;
use Illuminate\Support\Str;

class IconProcessor
{
    protected $file;
    protected $filepath;
    protected $svgDoc;
    protected $svgLine;
    protected $config;

    protected $attributesToRemove = [
        'width',
        'height',
        'class',
        'style',
    ];

    public function __construct($filepath, $config = [])
    {
        $this->filepath = $filepath;
        $this->file = new \SplFileInfo($filepath);

        $this->config = $config;

        $this->svgDoc = new DOMDocument();
        $this->svgDoc->formatOutput = false;
        $this->svgDoc->load($filepath);
    }

    public function save()
    {
        $destinationPath = $this->getDestinationFilePath();

        if ($this->filepath !== $destinationPath) {
            rename($this->filepath, $destinationPath);
        }

        $this->svgLine = str_replace(PHP_EOL, '', $this->svgLine);
        file_put_contents($destinationPath, $this->svgLine);
    }

    public function preOptimization()
    {
        return $this;
    }

    public function postOptimization()
    {
        return $this;
    }

    private function optimizeOutlineSvg(&$svgEL)
    {
        if ($svgEL->getAttribute('fill') !== 'none') {
            $svgEL->setAttribute('fill', 'none');
        }

        if ($svgEL->getAttribute('stroke') !== 'currentColor') {
            $svgEL->setAttribute('stroke', 'currentColor');
        }
    }

    private function optimizeSolidSvg(&$svgEL)
    {
        if ($svgEL->getAttribute('fill') !== 'currentColor') {
            $svgEL->setAttribute('fill', 'currentColor');
        }
    }

    private function addCustomAttributes(&$svgEL, $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $svgEL->setAttribute($key, $value);
        }
    }

    public function optimize()
    {
        // remove unwanted attributes id, class, width, height
        $svgEL = $this->svgDoc->getElementsByTagName('svg')[0];
        foreach ($this->attributesToRemove as $attribute) {
            $svgEL->removeAttribute($attribute);
        }

        if ($this->config['is-outline'] ?? false) {
            $this->optimizeOutlineSvg($svgEL);
        }

        if ($this->config['is-solid'] ?? false) {
            $this->optimizeSolidSvg($svgEL);
        }

        if (
            $this->config['custom-attributes'] ?? false
            && is_array($this->config['custom-attributes'])
        ) {
            $this->addCustomAttributes($svgEL, $this->config['custom-attributes']);
        }

        $this->svgLine = $this->getSvgAsString();

        return $this;
    }

    protected function getSvgAsString() {
        return $this->svgDoc->saveXML();
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

    protected function getFileName()
    {
        return $this->file->getBasename('.svg');
    }

    public function getDestinationFilePath($suffix = '', $dir = '')
    {
        return $this->getDestinationPath($dir)
                .Str::slug($this->getFileName())
                .$suffix
                .'.'
                .$this->getExtension();
    }
}
