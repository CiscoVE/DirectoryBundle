<?php

namespace CiscoSystems\DirectoryBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use CiscoSystems\DirectoryBundle\Security\Authentication\DirectoryUserToken;

class DirectoryAuthenticationListener extends AbstractAuthenticationListener
{
    /**
     * Performs authentication.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     *
     * @throws AuthenticationException if the authentication fails
     */
    protected function attemptAuthentication( Request $request )
    {
        $request = $event->getRequest();
        $token = new DirectoryUserToken( $request->get( "username" ), $request->get( "password" ));
        $authenticatedToken = $this->authenticationManager->authenticate( $token );
        $this->securityContext->setToken( $authenticatedToken );
        // TODO: might want to do something here
        // like, logging or something crazy like that
        return $authenticatedToken;
    }
}
