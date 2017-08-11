<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

interface LoginManagerInterface
{
    /**
     * Handles manual login of a user (e.g. after registration)
     *
     * @param UserInterface $user
     * @param Request|null $request
     * @param Response|null $response
     */
    public function login(UserInterface $user, Request $request = null, Response $response = null);
}
