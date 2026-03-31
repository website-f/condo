<?php

namespace FSPoster\App\Providers\Factories;

use FSPoster\App\Providers\Context\UserContext;

class UserFactory
{
    public function make(): UserContext
    {
        $user = \wp_get_current_user();
        return new UserContext($user->ID, $user->user_login, $user->user_email, $user->user_url);
    }
}
