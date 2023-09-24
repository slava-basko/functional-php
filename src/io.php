<?php

namespace Basko\Functional;

use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\IO;

/**
 * Race conditions safe file write.
 *
 * ```php
 * write_file(0666, '/path/to/file.txt', 'content');
 * ```
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

    return IO::of(function () use ($chmod, $file, $content) {
        $dir = dirname($file);

        $tmp = @tempnam($dir, 'wsw'); // @ to suppress notice for system temp dir fallback

        if ($tmp === false) {
            return Either::left(sprintf('Could not create temporary file in directory "%s"', $dir));
        }

        if (dirname($tmp) !== realpath($dir)) {
            unlink($tmp);

            return Either::left(sprintf('Could not create temporary file in directory "%s"', $dir));
        }

        if (file_put_contents($tmp, $content) === false) {
            unlink($tmp);

            return Either::left(sprintf('Could not write content to the file "%s"', $file));
        }

        if (chmod($tmp, $chmod & ~umask()) === false) {
            unlink($tmp);

            return Either::left(sprintf('Could not change chmod of the file "%s"', $file));
        }

        // On windows try again if rename was not successful but target file is writable.
        while (rename($tmp, $file) === false) {
            if (is_writable($file) && stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            unlink($tmp);

            return Either::left(sprintf(
                'Could not move file "%s" to location "%s": '
                . 'either the source file is not readable, or the destination is not writable',
                $tmp,
                $file
            ));
        }

        return Either::right(true);
    });
}

define('Basko\Functional\write_file', __NAMESPACE__ . '\\write_file', false);
