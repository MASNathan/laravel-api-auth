<?php

namespace MASNathan\LaravelApiAuth;

use Illuminate\Http\Request;

interface AuthenticationProvider
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function authenticate(Request $request);
}
