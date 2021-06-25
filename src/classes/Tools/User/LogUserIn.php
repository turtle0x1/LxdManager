<?php

namespace dhope0000\LXDClient\Tools\User;

use dhope0000\LXDClient\Model\Users\FetchUserDetails;
use dhope0000\LXDClient\Model\Users\InsertToken;
use dhope0000\LXDClient\Tools\Utilities\StringTools;
use Symfony\Component\HttpFoundation\Session\Session;
use dhope0000\LXDClient\Model\Users\AllowedProjects\FetchAllowedProjects;
use dhope0000\LXDClient\Model\InstanceSettings\GetSetting;
use dhope0000\LXDClient\Constants\InstanceSettingsKeys;
use dhope0000\LXDClient\Tools\Ldap\Ldap;
use dhope0000\LXDClient\Exceptions\Users\CantFindUserException;
use dhope0000\LXDClient\Exceptions\Users\PasswordIncorrectException;
use dhope0000\LXDClient\Exceptions\Users\DisabledUserAttemptedLogin;

class LogUserIn
{
    public function __construct(
        FetchUserDetails $fetchUserDetails,
        InsertToken $insertToken,
        Session $session,
        FetchAllowedProjects $fetchAllowedProjects,
        GetSetting $getSetting,
        Ldap $ldap
    ) {
        $this->fetchUserDetails = $fetchUserDetails;
        $this->insertToken = $insertToken;
        $this->session = $session;
        $this->fetchAllowedProjects = $fetchAllowedProjects;
        $this->getSetting = $getSetting;
        $this->ldap = $ldap;
    }

    public function login(string $username, string $password)
    {
        $isLdapUser = !is_null($this->fetchUserDetails->fetchLdapId($username));
        $passwordHash = $this->fetchUserDetails->fetchHash($username);

        $server = $this->getSetting->getSettingLatestValue(InstanceSettingsKeys::LDAP_SERVER);

        $haveLdapServer = !empty($server);
        $havePasswordHash = !empty($passwordHash);

        //NOTE This will throw in "error" if the ldap server param has been unset
        // and the user is trying to login with an email address!
        if (!$haveLdapServer && !$havePasswordHash) {
            throw new CantFindUserException($username);
        }

        // Local user
        if ($havePasswordHash && !$isLdapUser) {
            if (password_verify($password, $passwordHash) !== true) {
                throw new PasswordIncorrectException($username);
            }
        } elseif ($isLdapUser || $haveLdapServer) {
            try {
                $ldapconn = $this->ldap->getAdminBoundConnection($server);
            } catch (\Throwable $e) {
                throw new \Exception("Can't login right now - contact admin", 1);
            }
            // Will throw if cant find user / cant bind & gives us back a
            // username that we should be able to fetch from DB
            $username = $this->ldap->verifyAccount($ldapconn, $username, $password);
        } elseif ($isLdapUser || !$haveLdapServer) {
            throw new \Exception("Can't login right now - contact admin", 1);
        }

        $userId = $this->fetchUserDetails->fetchId($username);

        $isLoginDisabled = $this->fetchUserDetails->isLoginDisabled($userId);

        if ($isLoginDisabled) {
            throw new DisabledUserAttemptedLogin($username);
        }

        $allowedProjects = $this->fetchAllowedProjects->fetchAll($userId);

        $isAdmin = $this->fetchUserDetails->isAdmin($userId);

        if (empty($allowedProjects) && !$isAdmin) {
            throw new \Exception("No servers / projects assigned, contact your admin!", 1);
        }

        $token = StringTools::random(256);

        $this->session->set("userId", $userId);
        $this->session->set("isAdmin", $isAdmin);
        $this->session->set("wsToken", $token);
        $this->insertToken->insert($userId, $token);

        header("Location: /");

        return true;
    }
}
