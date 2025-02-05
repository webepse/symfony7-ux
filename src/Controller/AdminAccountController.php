<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AdminAccountController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_account_login')]
    public function index(AuthenticationUtils $utils): Response
    {

        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        $loginError = null;

        //dump($error);

        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // l'ereur est due à la limitation de tentative de connexion
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }

        $user = $this->getUser();
        if($user)
        {
            if(in_array('ROLE_ADMIN', $user->getRoles()))
            {
                return $this->redirectToRoute('admin_dashboard_index');
            }
        }
        
        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

       /**
     * Permet de se déconnecter
     *
     * @return void
     */
    #[Route("/admin/logout", name: "admin_account_logout")]
    public function logout(): void
    {

    }

    #[Route('/admin', name: 'admin_dashboard_index')]
    public function dashboard(): Response
    {
        
        return $this->render('admin/dashboard/index.html.twig', [
          
        ]);
    }
}
