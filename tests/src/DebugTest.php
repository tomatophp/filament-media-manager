<?php

it('will not use debugging functions', function () {
    expect(['dd', 'dump', 'ray'])->each->not->toBeUsed();
});
