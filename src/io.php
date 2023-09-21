<?php

namespace Basko\Functional;

use Basko\Functional\Functor\IO;

/**
 * Race conditions safe file write.
 *
 * @param int $chmod
 * @param string $file
 * @param string $content
 * @return callable|IO
 */
function write_file($chmod, $file = null, $content = null)
{
    if (is_null($file) && is_null($content)) {
        return partial(write_file, $chmod);
    } elseif (is_null($content)) {
        return partial(write_file, $chmod, $file);
    }

    return IO::of(function ($flags = 0) use ($chmod, $file, $content) {
        $dir = dirname($file);

        $tmp = @tempnam($dir, 'wsw'); // @ to suppress notice for system temp dir fallback

        if ($tmp === false) {
            throw Exception\IO\RuntimeException::unableToCreateTemporaryFile($dir); // TODO: return Either?
        }

        if (dirname($tmp) !== realpath($dir)) {
            unlink($tmp);
            throw Exception\IO\RuntimeException::unableToCreateTemporaryFile($dir);
        }

        if (file_put_contents($tmp, $content, $flags) === false) {
            unlink($tmp);
            throw Exception\IO\WriteContentException::unableToWriteContent($tmp);
        }

        if (chmod($tmp, $chmod & ~umask()) === false) {
            unlink($tmp);
            throw Exception\IO\ChmodException::unableToChangeChmod($tmp);
        }

        // On windows try again if rename was not successful but target file is writable.
        while (rename($tmp, $file) === false) {
            if (is_writable($file) && stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            unlink($tmp);
            throw Exception\IO\RenameException::unableToMoveFile($tmp, $file);
        }

        return true;
    });
}

define('Basko\Functional\write_file', __NAMESPACE__ . '\\write_file', false);
