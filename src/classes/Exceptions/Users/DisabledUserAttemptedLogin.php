<?php

namespace dhope0000\LXDClient\Exceptions\Users;

class DisabledUserAttemptedLogin extends \Exception
{
    public function __construct($username)
    {
        parent::__construct("Cant login right now - contact admin");
    }
}
