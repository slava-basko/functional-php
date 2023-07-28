<?php

// php -dxdebug.mode=debug -dxdebug.client_host=127.0.0.1 -dxdebug.client_port=9003 -dxdebug.start_with_request=yes internal/doc_generator.php

require_once __DIR__ . '/../vendor/autoload.php'; // composer autoload
use Basko\Functional as f;

//$f = get_defined_functions()['user'][202];
//$r = new ReflectionFunction($f);
//var_dump($r->name);
//exit;

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
    f\map(f\pipe(
        f\construct_with_args(ReflectionFunction::class),
        f\invoker('getDocComment')
    )),
    f\map(f\pipe(
        f\identity,
        f\partial_p('explode', [1 => "@", 3 => 2]),
        f\prop(0),
        f\str_replace([' * ', '/**', ' *'], ''),
        'trim'
    )),
    f\map(function ($value, $key) {
        return f\concat_all('### ', $key, PHP_EOL, $value, PHP_EOL, PHP_EOL);
    }),
    f\partial('file_put_contents', dirname(__DIR__) . '/docs/functions.md')
)(get_defined_functions());
