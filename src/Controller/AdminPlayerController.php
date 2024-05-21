<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminPlayerController extends AbstractController
{
     /**
     * Permet d'afficher l'ensemble des teams
     *
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/admin/players/{page<\d+>?1}', name: 'admin_players_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(Player::class) 
                ->setPage($page)
                ->setLimit(10);
       

        return $this->render('admin/player/index.html.twig', [
           'pagination' => $pagination
        ]);
    }

    #[Route("/admin/players/create", name: "admin_players_create")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        // partie traitement du formulaire
        if($form->isSubmitted() && $form->isValid())
        {

            // gestion de l'image
            $file = $form['picture']->getData();
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
                $player->setPicture($newFilename);

            }

            $manager->persist($player);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le joueur a été enregistré avec succés"
            );

            return $this->redirectToRoute("admin_players_index");
        } 


       

        return $this->render("admin/player/create.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    #[Route("/admin/players/{id}/edit", name: "admin_players_edit")]
    public function edit(): Response
    {
        return $this->render("admin/player/edit.html.twig",[

        ]);
    }

    #[Route("/admin/players/{id}/delete", name: "admin_players_delete")]
    public function delete(Player $player, EntityManagerInterface $manager): Response
    {
        $this->addFlash(
            "success",
            "Le joueur <strong>".$player->getfirstName()." ".$player->getLastName()."</strong> a bien été supprimé"
        );
        $manager->remove($player);
        $manager->flush();
        return $this->redirectToRoute('admin_players_index');
    }
}
