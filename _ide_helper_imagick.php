<?php

/**
 * Imagick IDE Helper for PHP 8.2
 * This file provides IDE autocompletion for Imagick extension
 */

class Imagick implements Iterator, Countable
{
    const LAYERMETHOD_FLATTEN = 12;
    const ALPHACHANNEL_REMOVE = 8;
    const COMPRESSION_JPEG = 7;
    
    public function __construct(mixed $files = null) {}
    public function setResolution(float $x_resolution, float $y_resolution): bool {}
    public function readImage(string $filename): bool {}
    public function setImageFormat(string $format): bool {}
    public function setImageCompressionQuality(int $quality): bool {}
    public function setImageBackgroundColor(mixed $background): bool {}
    public function setImageAlphaChannel(int $mode): bool {}
    public function mergeImageLayers(int $layer_method): Imagick {}
    public function writeImage(string $filename = null): bool {}
    public function clear(): bool {}
    public function destroy(): bool {}
    public function getImageWidth(): int {}
    public function getImageHeight(): int {}
    public function current(): Imagick {}
    public function key(): int {}
    public function next(): void {}
    public function rewind(): void {}
    public function valid(): bool {}
    public function count(): int {}
}

class ImagickException extends Exception {}
