<?php

namespace Basko\Functional\Exception\IO;

class RuntimeException extends IOException
{
    /**
     * @param string $dir
     * @return self
     */
    public static function unableToCreateTemporaryFile($dir)
    {
        return new self(sprintf('Could not create temporary file in directory "%s"', $dir));
    }
}
