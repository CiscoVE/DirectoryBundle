<?php

namespace CiscoSystems\DirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        return array();
    }

    /**
     * @Route("/login-check", name="CiscoSystemsDirectoryBundle_logincheck")
     */
    public function loginCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="CiscoSystemsDirectoryBundle_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }
}
