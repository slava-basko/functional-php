<?php

namespace Basko\Functional\Exception\IO;

class WriteContentException extends IOException
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToWriteContent($file)
    {
        return new self(sprintf('Could not write content to the file "%s"', $file));
    }
}
