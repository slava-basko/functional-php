<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\IO;

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

    if (is_null($file) && is_null($content)) {
        return partial(write_file, $chmod);
    } elseif (is_null($content)) {
        return partial(write_file, $chmod, $file);
    }

    InvalidArgumentException::assertString($file, __FUNCTION__, 2);

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

/**
 * Read file contents.
 *
 * ```php
 * $io = f\read_file('/path/to/file.txt');
 * $content = $io(); // Content read from file at this moment.
 * ```
 *
 * @param string $file
 * @return IO
 */
function read_file($file)
{
    InvalidArgumentException::assertString($file, __FUNCTION__, 1);

    return IO::of(function () use ($file) {
        if (!file_exists($file)) {
            return Either::left(sprintf('File "%s" does not exist', $file));
        }

        $handle = fopen($file, 'rb');

        if (!is_resource($handle)) {
            return Either::left(sprintf('Can not open file "%s"', $file));
        }

        /**
         * @param string $file
         * @return \Basko\Functional\Functor\Either
         */
        $read = function ($file) use (&$handle, &$contents) {
            $contents = '';

            if (flock($handle, LOCK_SH)) {
                clearstatcache(true, $file);

                $contents = fread($handle, filesize($file) ?: 1);

                flock($handle, LOCK_UN);
            } else {
                return Either::left(sprintf('flock() failed on "%s"', $file));
            }

            return Either::right($contents);
        };

        if (PHP_VERSION_ID >= 70000) {
            try {
                $result = $read($file);
            } catch (\Throwable $throwable) {
                $result = Either::left($throwable);
            } finally {
                fclose($handle);
            }
        } else {
            try {
                $result = $read($file);
            } catch (\Exception $exception) {
                $result = Either::left($exception);
            } finally {
                fclose($handle);
            }
        }

        return $result;
    });
}
