<?php

use DataLinx\PhpUtils\Fluent\FluentString;

test('fstr works correctly', function () {
    expect(fstr('test'))->toBeInstanceOf(FluentString::class);
});
