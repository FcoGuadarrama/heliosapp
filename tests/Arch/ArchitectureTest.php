<?php

arch('domain should not use http layer')
    ->expect('App\Domain')
    ->not->toUse('App\Http');

arch('domain should not use jobs')
    ->expect('App\Domain')
    ->not->toUse('App\Jobs');
