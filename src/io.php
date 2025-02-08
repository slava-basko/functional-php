<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\IO;

/**
 * Race conditions safe file write.
 *
 * ```
 * $io = write_file(0666, '/path/to/file.txt', 'content');
 * $io(); // Content write into file at this moment.
 * ```
 *
 * @param int $chmod
 * @param string $file
 * @param mixed $content
 * @return callable|IO
 * @phpstan-return ($file is null ? (callable(string $file, mixed $content=):($content is null ? callable(mixed $content):\Basko\Functional\Functor\IO<callable> : \Basko\Functional\Functor\IO<callable>|\Basko\Functional\Functor\IO<callable>)) : ($content is null ? callable(mixed $content):\Basko\Functional\Functor\IO<callable> : \Basko\Functional\Functor\IO<callable>))
 */
function write_file($chmod, $file = null, $content = null)
{
    InvalidArgumentException::assertInteger($chmod, __FUNCTION__, 1);

    if (\func_num_args() === 1) {
        return partial(write_file, $chmod);
    } elseif (\func_num_args() === 2) {
        return partial(write_file, $chmod, $file);
    }

    InvalidArgumentException::assertString($file, __FUNCTION__, 2);

    /** @var string $file */
    return IO::of(function () use ($chmod, $file, $content) {
        $dir = \dirname($file);

        $tmp = @\tempnam($dir, 'wsw'); // @ to suppress notice for system temp dir fallback

        if ($tmp === false) {
            throw new \Exception(\sprintf('Could not create temporary file in directory "%s"', $dir), 1);
        }

        if (\dirname($tmp) !== \realpath($dir)) {
            \unlink($tmp);

            throw new \Exception(\sprintf('Could not create temporary file in directory "%s"', $dir), 2);
        }

        if (\file_put_contents($tmp, $content) === false) {
            \unlink($tmp);

            throw new \Exception(\sprintf('Could not write content to the file "%s"', $file), 3);
        }

        if (\chmod($tmp, $chmod & ~\umask()) === false) {
            \unlink($tmp);

            throw new \Exception(\sprintf('Could not change chmod of the file "%s"', $file), 4);
        }

        // On windows try again if rename was not successful but target file is writable.
        while (\rename($tmp, $file) === false) {
            if (\is_writable($file) && \stripos(PHP_OS, 'WIN') === 0) {
                continue;
            }

            \unlink($tmp);

            throw new \Exception(\sprintf(
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
 * ```
 * $io = read_file('/path/to/file.txt');
 * $content = $io(); // Content read from file at this moment.
 * ```
 *
 * @param string $file
 * @return \Basko\Functional\Functor\IO<callable>
 */
function read_file($file)
{
    InvalidArgumentException::assertNotEmptyString($file, __FUNCTION__, 1);

    return IO::of(function () use ($file) {
        if (!\file_exists($file)) {
            throw new \Exception(\sprintf('File "%s" does not exist', $file), 1);
        }

        $handle = \fopen($file, 'rb');

        if (!\is_resource($handle)) {
            throw new \Exception(\sprintf('Can not open file "%s"', $file), 2);
        }

        /**
         * @param $file
         * @return false|string
         * @throws \Exception
         */
        $read = function ($file) use (&$handle, &$contents) {
            $contents = '';

            if (\flock($handle, LOCK_SH)) {
                \clearstatcache(true, $file);

                $contents = \fread($handle, \filesize($file) ?: 1);

                \flock($handle, LOCK_UN);
            } else {
                throw new \Exception(\sprintf('flock() failed on "%s"', $file), 3);
            }

            return $contents;
        };

        try {
            return $read($file);
        } finally {
            \fclose($handle);
        }
    });
}

/**
 * @param string $url
 * @param array<string, mixed> $postData
 * @param array<string, mixed> $params
 * @return \Basko\Functional\Functor\IO<callable>
 */
function read_url($url, array $postData = [], array $params = [])
{
    InvalidArgumentException::assertNotEmptyString($url, __FUNCTION__, 1);

    return IO::of(function () use ($url, $postData, $params) {
        $ch = \curl_init($url);

        if ($postData) {
            \curl_setopt($ch, \CURLOPT_POST, true);
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $postData);
        }

        foreach ($params as $param => $value) {
            $param = \strtoupper($param);
            if (!str_starts_with('CURLOPT_', $param)) {
                $param = 'CURLOPT_' . $param;
            }

            if (\defined($param)) {
                \curl_setopt($ch, \constant($param), $value);
            }
        }

        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_HEADER, true);

        $result = (string)\curl_exec($ch);
        $httpCode = (int)\curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $headerSize = (int)\curl_getinfo($ch, \CURLINFO_HEADER_SIZE);

        $lastError = '';
        if ($lastErrorNumber = \curl_errno($ch)) {
            $lastError = \curl_error($ch);
        }

        $errorBody = '';
        $headers = [];
        if ($lastErrorNumber) {
            if (in_array($lastErrorNumber, [\CURLE_OPERATION_TIMEOUTED, \CURLE_OPERATION_TIMEDOUT])) {
                $errorBody = 'TIMEOUT';
            } else {
                $errorBody = $lastError;
            }
        } else {
            $headerText = \trim(\str_replace("\r", '', \substr($result, 0, $headerSize)));

            foreach (\explode("\n", $headerText) as $i => $line) {
                if ($i === 0) {
                    $headers['http_code'] = \trim($line);
                } else {
                    list ($key, $value) = \explode(': ', $line);
                    $headers[\trim($key)] = \trim($value);
                }
            }

            $result = (string)substr($result, $headerSize);
            if (!preg_match('/^2\d\d$/D', (string)$httpCode)) {
                $errorBody = $result ?: 'HTTP CODE ' . $httpCode;
            }
        }

        \curl_close($ch);

        return [$httpCode, $headers, \strlen($errorBody) ? $errorBody : $result];
    });
}
