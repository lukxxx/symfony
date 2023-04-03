<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TryController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    #[Route('/movies', name: 'movies')]
    public function index(): Response
    {
        // findAll() - SELECT * FROM movies
        // find() - SELECT * FROM movies WHERE id = 5
        // findBy() - SELECT * FROM movies ORDER BY id DESC
        // findOneBy() - SELECT * FROM movies WHERE id = 6 AND title = "TheMovieDB" ODER BY id DESC
        // count() - SELECT COUNT() FROM movies WHERE id = 1

        $repository = $this->em->getRepository(Movie::class);
        $movies = $repository->findAll();

        return $this->render("movies/index.html.twig", [
            "movies" => $movies
        ]);
    }

    #[Route("/movies/create", name: "create_movie")]
    public function create(Request $request): Response {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $newMovie = $form->getData();
            $imagePath = $form->get("imagePath")->getData();
            if($imagePath){
                $newFilename = uniqid(). '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter("kernel.project_dir") . "/public/uploads",
                        $newFilename
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newMovie->setImagePath("/uploads/".$newFilename);
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute("movies");
        }

        return $this->render("movies/create.html.twig", [
            "form" => $form->createView()
        ]);
    }

    #[Route("movies/edit/{id}", name: "edit_movie")]
    public function edit($id, Request $request): Response
    {
        $repository = $this->em->getRepository(Movie::class);
        $movie = $repository->find($id);

        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get("imagePath")->getData();

        if($form->isSubmitted() && $form->isValid()){
            if($imagePath){
                if($movie->getImagePath() !== null){
                    if(!file_exists($this->getParameter("kernel.project_dir") . $movie->getImagePath())){
                        $this->getParameter("kernel.project_dir") . $movie->getImagePath();

                        $newFilename = uniqid() . "." . $imagePath->guessExtension();



                        try {
                            $imagePath->move(
                                $this->getParameter("kernel.project_dir") . "/public/uploads",
                                $newFilename
                            );
                        } catch (FileException $e) {
                            return new Response($e->getMessage());
                        }

                        $movie->setImagePath("/uploads/" . $newFilename);
                        $this->em->flush();
                        return $this->redirectToRoute("movies");
                    }
                }
            } else {
                $movie->setTitle($form->get("title")->getData());
                $movie->setReleaseYear($form->get("releaseYear")->getData());
                $movie->setDescription($form->get("description")->getData());

                $this->em->flush();
                return $this->redirect("/movies");
            }
        }

        return $this->render("movies/edit.html.twig", [
           "movie" => $movie,
           "form" => $form->createView()
        ]);
    }

    #[Route('/movies/{id}', methods: ["GET"], name: 'try')]
    public function show($id): Response
    {
        // findAll() - SELECT * FROM movies
        // find() - SELECT * FROM movies WHERE id = 5
        // findBy() - SELECT * FROM movies ORDER BY id DESC
        // findOneBy() - SELECT * FROM movies WHERE id = 6 AND title = "TheMovieDB" ODER BY id DESC
        // count() - SELECT COUNT() FROM movies WHERE id = 1

        $repository = $this->em->getRepository(Movie::class);
        $movie = $repository->find($id);

        return $this->render("movies/show.html.twig", [
            "movie" => $movie
        ]);
    }
}
