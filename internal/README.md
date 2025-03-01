# How to debug doc_generator.php
```bash
php -dxdebug.mode=debug -dxdebug.client_host=127.0.0.1 -dxdebug.client_port=9003 -dxdebug.start_with_request=yes internal/doc_generator.php
```

# How to run PHPStan with debug mode
```bash
php -dxdebug.mode=debug -dxdebug.client_host=127.0.0.1 -dxdebug.client_port=9003 -dxdebug.start_with_request=yes vendor/bin/phpstan --configuration=phpstan.neon --memory-limit=-1 --debug --xdebug
```
