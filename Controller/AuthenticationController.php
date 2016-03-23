<?php

namespace CiscoSystems\DirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
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
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

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
