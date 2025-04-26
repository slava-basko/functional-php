<?php

use Basko\FunctionalTest\Helpers\Value;
use Basko\Functional as f;

return function () {
    $v = new Value('a');
    return f\partial($v->concatWith2(...), 'b');
};
