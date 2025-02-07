<?php

$a = [1, 2, 3 ,4];

$result = array_map(function ($x) {
    $new = [$x];
    return [...$new];
}, $a);

var_dump($result);
