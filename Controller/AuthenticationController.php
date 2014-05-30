<?php

namespace CiscoSystems\DirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends Controller
{
    /**
     * Controller method for displaying the login page
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction( Request $request )
    {
        return $this->render( 'CiscoSystemsDirectoryBundle:Authentication:login.html.twig', array(
        ));
    }
}
