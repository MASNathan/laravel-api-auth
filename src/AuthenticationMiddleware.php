<?php

namespace MASNathan\LaravelApiAuth;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class AuthenticationMiddleware
{
    /** @var mixed */
    protected $user;

    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        if (!config('api_auth.providers')) {
            return $next($request);
        }

        /** @var AuthenticationProvider $provider */
        foreach (config('api_auth.providers') as $type => $providerClass) {
            $provider = App::make($providerClass);

            if (!$provider instanceof AuthenticationProvider) {
                throw new \Exception('Authentication providers must implement MASNathan\\LaravelApiAuth\\AuthenticationProvider');
            }

            $user = $provider->authenticate($request);

            if ($user !== false) {
                $this->setUserResolver($request, $type, $user);

                return $next($request);
            }
        }

        throw new HttpResponseException(Response::make('', 403));
    }

    /**
     * @param Request $request
     * @param string  $type
     * @param mixed   $user
     */
    protected function setUserResolver($request, string $type, $user)
    {
        $request->setUserResolver(function () use ($type, $user) {
            return new class($type, $user) {
                /** @var string */
                protected $type;

                /** @var mixed */
                protected $user;

                public function __construct(string $type, $user)
                {
                    $this->user = $user;
                    $this->type = $type;
                }

                public function __call($name, $arguments)
                {
                    return call_user_func_array([$this->user, $name], $arguments);
                }

                public function __get($name)
                {
                    return $this->user->$name;
                }

                public function __set($name, $value)
                {
                    $this->user->$name = $value;
                }

                public function getAuthenticationType(): ?string
                {
                    return $this->type;
                }

                public function instance()
                {
                    return $this->user;
                }
            };
        });
    }
}
