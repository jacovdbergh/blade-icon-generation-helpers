<?php

namespace Codeat3\BladeIconGeneration;

use DOMDocument;
use Illuminate\Support\Str;
use InlineStyle\InlineStyle;
use RenatoMarinho\LaravelPageSpeed\Middleware\CollapseWhitespace;
use Codeat3\BladeIconGeneration\Exceptions\InvalidFileExtensionException;

class IconProcessor
{
    protected $file;

    protected $sourceFile;

    protected $filepath;

    protected $svgDoc;

    protected $svgLine;

    protected $config;

    protected $attributesToRemove = [
        'width',
        'height',
        'class',
        'style',
        'id',
    ];

    public function __construct($filepath, $config = [], $sourceFile = null)
    {
        $this->sourceFile = $sourceFile;
        $this->filepath = $filepath;
        $this->file = new \SplFileInfo($filepath);

        $this->config = $config;

        $this->checkIfValidFile();

        $this->svgDoc = new DOMDocument();
        $this->svgDoc->load($filepath);
    }

    private function checkIfValidFile()
    {
        if (
            $this->config['blacklisted-ext'] ?? false
            && is_array($this->config['blacklisted-ext'])
        ) {
            if (in_array($this->file->getExtension(), $this->config['blacklisted-ext'])) {
                throw new InvalidFileExtensionException();
            }
        }

        if (
            $this->config['whitelisted-pattern'] ?? false
            && is_array($this->config['whitelisted-pattern'])
        ) {
            foreach ($this->config['whitelisted-pattern'] as $pattern) {
                if (! preg_match('/'.$pattern.'/', $this->file->getFilename())) {
                    throw new InvalidFileExtensionException();
                }
            }
        }

        // if (
        //     $this->config['whitelisted-files'] ?? false
        //     && is_array($this->config['whitelisted-files'])
        // ) {
        //     if (! in_array(
        //         str_replace($this->config['output-suffix'] ?? '', '', $this->file->getBasename()),
        //         $this->config['whitelisted-files']
        //     )
        //     ) {
        //         var_dump($this->config['output-suffix']);
        //         var_dump($this->file->getBasename());
        //         var_dump($this->config['whitelisted-files']);
        //         var_dump(str_replace($this->config['output-suffix'] ?? '', '', $this->file->getBasename()));
        //         var_dump(! in_array(
        //             str_replace($this->config['output-suffix'] ?? '', '', $this->file->getBasename()),
        //             $this->config['whitelisted-files']
        //         ));
        //         die('EXCEPTON');

        //         throw new InvalidFileExtensionException();
        //     }
        // }
    }

    public function save($filenameCallable = null)
    {
        $destinationPath = $this->getDestinationFilePath($filenameCallable);

        if ($this->filepath !== $destinationPath) {
            rename($this->filepath, $destinationPath);
        }

        if ($this->svgLine === null) {
            $this->svgLine = $this->getSvgAsString();
        }

        $this->svgLine = preg_replace('/\<\?xml.*\?\>/', '', $this->svgLine);
        $this->svgLine = (new CollapseWhitespace())->apply($this->svgLine);
        file_put_contents($destinationPath, $this->svgLine);
    }

    public function preOptimization()
    {
        return $this;
    }

    public function postOptimizationAsString(callable $callable)
    {
        $this->svgLine = $callable($this->getSvgAsString());

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

    public function optimize($pre = null, $post = null)
    {
        // remove unwanted attributes id, class, width, height
        $svgEL = $this->svgDoc->getElementsByTagName('svg')[0];

        if (is_callable($pre)) {
            $pre($svgEL);
        }

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

        if (is_callable($post)) {
            $post($svgEL);
        }

        return $this;
    }

    protected function getSvgAsString()
    {
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

    protected function getFileName($filenameCallable = null)
    {
        $name = $this->file->getBasename('.svg');

        if (is_callable($filenameCallable)) {
            return $filenameCallable($name, $this->file);
        }

        return $name;
    }

    public function getDestinationFilePath($filenameCallable = '', $suffix = '', $dir = '')
    {
        return $this->getDestinationPath($dir)
                .Str::slug($this->getFileName($filenameCallable))
                .$suffix
                .'.'
                .$this->getExtension();
    }

    public function convertStyleToInline()
    {
        $htmlDoc = new InlineStyle($this->svgDoc);
        $htmlDoc->applyStylesheet($htmlDoc->extractStylesheets());
        $this->svgDoc = $htmlDoc->getDomObject();

        return $this;
    }
}
