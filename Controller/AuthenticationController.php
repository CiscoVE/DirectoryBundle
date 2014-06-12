<?php

namespace CiscoSystems\DirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthenticationController extends Controller
{
    /**
     * Controller method for displaying the login page
     *
     * @Route("/login", name="CiscoSystemsDirectoryBundle_login")
     * @Template()
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction( Request $request )
    {
        $this->get( 'logger' )->info( 'cisco.ldap: controller received POST request' );
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return array(
            'last_username' => $lastUsername,
            'error'         => $error,
        );
    }

    /**
     * @Route("/login-check", name="CiscoSystemsDirectoryBundle_logincheck")
     */
    public function loginCheckAction()
    {
        // The security layer will intercept this request
        return new Response( 'this should not be displayed' );
    }

    /**
     * @Route("/logout", name="CiscoSystemsDirectoryBundle_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
        return new Response( 'this should not be displayed' );
    }
}
