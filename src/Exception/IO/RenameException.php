<?php

namespace Basko\Functional\Exception\IO;

class RenameException extends IOException
{
    /**
     * @param string $source
     * @param string $target
     * @return self
     */
    public static function unableToMoveFile($source, $target)
    {
        return new self(sprintf(
            'Could not move file "%s" to location "%s": '
            . 'either the source file is not readable, or the destination is not writable',
            $source,
            $target
        ));
    }
}
