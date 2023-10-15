<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\IO;
use Exception;

/**
 * Race conditions safe file write.
 *
 * ```php
 * $io = write_file(0666, '/path/to/file.txt', 'content');
 * $io(); // Content write into file at this moment.
 * ```
 *
 * @param int $chmod
 * @param string $file
 * @param mixed $content
 * @return callable|IO
 */
function write_file($chmod, $file = null, $content = null)
{
    InvalidArgumentException::assertInteger($chmod, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) === 1) {
        return partial(write_file, $chmod);
    } elseif (count($args) === 2) {
        return partial(write_file, $chmod, $file);
    }

    InvalidArgumentException::assertString($file, __FUNCTION__, 2);

    return IO::of(function () use ($chmod, $file, $content) {
        $dir = dirname($file);

        $tmp = @tempnam($dir, 'wsw'); // @ to suppress notice for system temp dir fallback

        if ($tmp === false) {
            throw new Exception(sprintf('Could not create temporary file in directory "%s"', $dir), 1);
        }

        if (dirname($tmp) !== realpath($dir)) {
            unlink($tmp);

            throw new Exception(sprintf('Could not create temporary file in directory "%s"', $dir), 2);
        }

        if (file_put_contents($tmp, $content) === false) {
            unlink($tmp);

            throw new Exception(sprintf('Could not write content to the file "%s"', $file), 3);
        }

        if (chmod($tmp, $chmod & ~umask()) === false) {
            unlink($tmp);

            throw new Exception(sprintf('Could not change chmod of the file "%s"', $file), 4);
        }

        // On windows try again if rename was not successful but target file is writable.
        while (rename($tmp, $file) === false) {
            if (is_writable($file) && stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            unlink($tmp);

            throw new Exception(sprintf(
                'Could not move file "%s" to location "%s": '
                . 'either the source file is not readable, or the destination is not writable',
                $tmp,
                $file
            ), 5);
        }

        return true;
    });
}

define('Basko\Functional\write_file', __NAMESPACE__ . '\\write_file');

/**
 * Read file contents.
 *
 * ```php
 * $io = read_file('/path/to/file.txt');
 * $content = $io(); // Content read from file at this moment.
 * ```
 *
 * @param string $file
 * @return IO
 */
function read_file($file)
{
    InvalidArgumentException::assertNotEmptyString($file, __FUNCTION__, 1);

    return IO::of(function () use ($file) {
        if (!file_exists($file)) {
            throw new Exception(sprintf('File "%s" does not exist', $file), 1);
        }

        $handle = fopen($file, 'rb');

        if (!is_resource($handle)) {
            throw new Exception(sprintf('Can not open file "%s"', $file), 2);
        }

        /**
         * @param $file
         * @return false|string
         * @throws \Exception
         */
        $read = function ($file) use (&$handle, &$contents) {
            $contents = '';

            if (flock($handle, LOCK_SH)) {
                clearstatcache(true, $file);

                $contents = fread($handle, filesize($file) ?: 1);

                flock($handle, LOCK_UN);
            } else {
                throw new Exception(sprintf('flock() failed on "%s"', $file), 3);
            }

            return $contents;
        };

        try {
            return $read($file);
        } finally {
            fclose($handle);
        }
    });
}
