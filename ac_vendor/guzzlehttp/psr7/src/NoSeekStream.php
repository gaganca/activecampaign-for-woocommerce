<?php
namespace AcVendor\GuzzleHttp\Psr7;

use AcVendor\Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that prevents a stream from being seeked
 */
class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }

    public function isSeekable()
    {
        return false;
    }
}