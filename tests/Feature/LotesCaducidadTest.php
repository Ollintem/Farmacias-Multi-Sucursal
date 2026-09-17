<?php

it('loads the lotes and expiry page', function () {
    $this->get(route('lotes.index'))->assertOk();
});
