<?php

namespace Basko\Functional\Exception\IO;

class ChmodException extends IOException
{
    /**
     * @param string $file
     * @return self
     */
    public static function unableToChangeChmod($file)
    {
        return new self(sprintf('Could not change chmod of the file "%s"', $file));
    }
}
