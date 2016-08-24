<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SwitchUserListener implements ListenerInterface
{
    private $securityContext;
    private $tokenStorage;
    private $provider;
    private $userChecker;
    private $providerKey;
    private $accessDecisionManager;
    private $usernameParameter;
    private $role;
    private $logger;
    private $dispatcher;

    /**
     * Constructor.
     */
    public function __construct(
            TokenStorage $tokenStorage,
            UserProviderInterface $provider,
            UserCheckerInterface $userChecker,
            $providerKey,
            AccessDecisionManagerInterface $accessDecisionManager,
            LoggerInterface $logger = null,
            $usernameParameter = '_switch_user',
            $role = 'ROLE_ALLOWED_TO_SWITCH',
            EventDispatcherInterface $dispatcher = null
    ) {
        if ( empty( $providerKey ) )
        {
            throw new \InvalidArgumentException( '$providerKey must not be empty.' );
        }
        $this->tokenStorage = $tokenStorage;
        $this->provider = $provider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->usernameParameter = $usernameParameter;
        $this->role = $role;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle( GetResponseEvent $event )
    {
        $request = $event->getRequest();
        if ( !$request->get( $this->usernameParameter ) ) return;
        if ( '_exit' == $request->get( $this->usernameParameter ) )
        {
            $this->attemptExitUser( $request );
        }
        else
        {
            try
            {
                $this->attemptSwitchUser( $request );
            }
            catch ( AuthenticationException $e )
            {
                throw new \LogicException( sprintf( 'Switch User failed: "%s"', $e->getMessage() ) );
            }
        }
        $request->server->set( 'QUERY_STRING', '' );
        $response = new RedirectResponse( $request->getUri(), 302 );
        $event->setResponse( $response );
    }

    /**
     * Attempts to switch to another user.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface
     */
    private function attemptSwitchUser( Request $request )
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ( !$user->hasRole( $this->role ))
        {
            throw new AccessDeniedException();
        }
        $username = strtolower( $request->get( $this->usernameParameter ));
        $session = $request->getSession();
        if ( $session->get( 'originalUserId' ))
        {
            if ( $user->getUsername() == $username ) return;
        }
        if ( null !== $this->logger )
        {
            $this->logger->info(sprintf( 'Attempt to switch to user "%s"', $username ) );
        }
        $userSwitchedTo = $this->provider->loadUserByUsername( $username );
        $session->set( 'originalUserId', $user->getId() );
        $token->setUser( $userSwitchedTo );
        if ( null !== $this->dispatcher )
        {
            $switchEvent = new SwitchUserEvent( $request, $userSwitchedTo );
            $this->dispatcher->dispatch( SecurityEvents::SWITCH_USER, $switchEvent );
        }
        return $token;
    }

    /**
     * Attempts to exit from an already switched user.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface
     */
    private function attemptExitUser( Request $request )
    {
        $session = $request->getSession();
        $originalUserId = $session->get( 'originalUserId' );
        if ( !$originalUserId )
        {
            throw new AuthenticationCredentialsNotFoundException(  'Could not find original User object.' );
        }
        $token = $this->tokenStorage->getToken();
        $session->set( 'originalUserId', null );
        $userSwitchedFrom = $this->provider->loadUserById( $originalUserId );
        if ( null !== $this->dispatcher )
        {
            $switchEvent = new SwitchUserEvent( $request, $userSwitchedFrom );
            $this->dispatcher->dispatch( SecurityEvents::SWITCH_USER, $switchEvent );
        }
        $token->setUser( $userSwitchedFrom );
        return $token;
    }
}
