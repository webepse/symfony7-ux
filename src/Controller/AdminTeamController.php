<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminTeamController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des teams
     *
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/admin/teams/{page<\d+>?1}', name: 'admin_teams_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(Team::class) 
                ->setPage($page)
                ->setLimit(10);
       

        return $this->render('admin/team/index.html.twig', [
           'pagination' => $pagination
        ]);
    }

    #[Route("/admin/teams/create", name: "admin_teams_create")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        // partie traitement du formulaire
        if($form->isSubmitted() && $form->isValid())
        {

            // gestion de l'image
            $file = $form['logo']->getData();
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $team->setLogo($newFilename);

            }

            $manager->persist($team);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'équipe a été enregistrée avec succés"
            );

            return $this->redirectToRoute("admin_teams_index");
        } 


       

        return $this->render("admin/team/create.html.twig",[
            'myForm' => $form->createView()
        ]);
    }


    #[Route("/admin/teams/{id}/edit", name: "admin_teams_edit")]
    public function edit(): Response
    {
        return $this->render("admin/team/edit.html.twig",[

        ]);
    }

    #[Route("/admin/teams/{id}/delete", name: "admin_teams_delete")]
    public function delete(Team $ad, EntityManagerInterface $manager): Response
    {
        return $this->redirectToRoute('admin_teams_index');
    }
}
