<?php

namespace FSPoster\App\Providers\Context;

class UserContext
{
    public int $id;
    public string $userLogin;
    public string $userEmail;
    public string $userUrl;
    public function __construct(
         int $id = 0,
         string $userLogin = '',
         string $userEmail = '',
         string $userUrl = ''
    ) {
        $this->id = $id;
        $this->userLogin = $userLogin;
        $this->userEmail = $userEmail;
        $this->userUrl = $userUrl;
    }
}
