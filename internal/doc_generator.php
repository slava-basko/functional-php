<?php

// php -dxdebug.mode=debug -dxdebug.client_host=127.0.0.1 -dxdebug.client_port=9003 -dxdebug.start_with_request=yes internal/doc_generator.php

require_once __DIR__ . '/../vendor/autoload.php'; // composer autoload
use Basko\Functional as f;

f\pipe(
    f\prop('user'),
    f\select(f\contains('basko\functional')),
    f\map(f\pipe(
        f\construct_with_args(ReflectionFunction::class),
        f\prop('name')
    )),
    f\converge('array_combine', [
        'array_values',
        f\identity,
    ]),
    f\converge('array_combine', [
        f\map(f\pipe(
            f\identity,
            f\partial_p('explode', [1 => "\\", 3 => 3]),
            f\prop(2)
        )),
        'array_keys',
    ]),
    f\unary('array_flip'),
    f\reject(f\str_starts_with('_')),
    f\unary('array_flip'),
    f\map(f\pipe(
        f\construct_with_args(ReflectionFunction::class),
        f\invoker('getDocComment')
    )),
    f\map(f\pipe(
        f\identity,
        f\partial_p('explode', [1 => "@", 3 => 2]),
        f\prop(0),
        f\str_replace_first('* ```', '* ```php'),
        f\str_replace([' * ', '/**', ' *'], ''),
        'trim'
    )),
    f\map(f\binary(f\flipped(f\partial_p(
        f\ary(f\concat_all, 6),
        [1 => '### ', 3 => PHP_EOL, 5 => PHP_EOL, 6 => PHP_EOL]
    )))),
    f\partial('file_put_contents', dirname(__DIR__) . '/docs/functions.md')
)(get_defined_functions());
