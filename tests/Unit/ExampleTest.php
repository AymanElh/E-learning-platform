<?php

function sum(int $a, int $b)
{
    return $a+$b;
}

test('sum', function () {
    $value = sum(1, 2);

    expect($value)->toBe(3); // Assert that the value is 3...
});
