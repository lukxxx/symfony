<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/articles', name: 'articles')]
    public function index(ArticleRepository $repository, Request $request, PaginatorInterface $paginationInterface): Response
    {
        $paginator = $repository->paginate($request->query->getInt("page", 1));
        $paginator->setTemplate("pagination/tailwindcss_pagination.html.twig");

        return $this->render("articles/index.html.twig", [
            "articleCard" => $paginator
        ]);
    }

    #[Route("/articles/create", name: "createArticle")]
    public function create(Request $request): Response
    {
        $immutable = new \DateTimeImmutable('-1 year');
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $newArticle = $form->getData();
            $newArticleTitle = $newArticle->getTitle();
            $newArticleTitle = strtolower(trim(preg_replace('/[\s-]+/', "-", preg_replace('/[^A-Za-z0-9-]+/', "-", preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $newArticleTitle))))), "-"));
            $imagePath = $form->get("imagePath")->getData();
            if ($imagePath) {
                $newFilename = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter("kernel.project_dir") . "/public/uploads", $newFilename
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newArticle->setImagePath("/uploads/" . $newFilename);
            }
            $newArticle->setTitle($newArticle->getTitle());
            $newArticle->setCreatedAt($immutable);
            $newArticle->setSlug($newArticleTitle);
            $this->em->persist($newArticle);
            $this->em->flush();

            return $this->redirectToRoute("articles");
        }
        return $this->render("articles/create.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    #[Route("articles/edit/{id}", name: "edit_movie")]
    public function edit($id, Request $request): Response
    {
        $repository = $this->em->getRepository(Article::class);
        $article = $repository->find($id);

        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);
        $imagePath = $form->get("imagePath")->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($article->getImagePath() !== null) {
                    if (!file_exists($this->getParameter("kernel.project_dir") . $article->getImagePath())) {
                        $this->getParameter("kernel.project_dir") . $article->getImagePath();

                        $newFilename = uniqid() . "." . $imagePath->guessExtension();


                        try {
                            $imagePath->move(
                                $this->getParameter("kernel.project_dir") . "/public/uploads", $newFilename
                            );
                        } catch (FileException $e) {
                            return new Response($e->getMessage());
                        }

                        $article->setImagePath("/uploads/" . $newFilename);
                        $this->em->flush();
                        return $this->redirectToRoute("articles");
                    }
                }
            } else {
                $article->setTitle($form->get("title")->getData());
                $article->setAuthor($form->get("author")->getData());
                $article->setDescription($form->get("description")->getData());

                $this->em->flush();
                return $this->redirect("/articles");
            }
        }

        return $this->render("articles/edit.html.twig", [
            "movie" => $article,
            "form" => $form->createView(),
        ]);
    }

    #[Route("/articles/delete/{id}", name: "delete_movie", methods: ["GET", "DELETE"])]
    public function delete($id): Response
    {
        //GET ,,
        $repository = $this->em->getRepository(Article::class);
        $article = $repository->find($id);

        $commentsRepository = $this->em->getRepository(Comment::class);
        $comments = $commentsRepository->findBy(["article" => $article], );

        for ($i = 0; $i < sizeof($comments); $i++) {
            $this->em->remove($comments[$i]);
        }

        $this->em->remove($article);
        $this->em->flush();

        return $this->redirectToRoute("articles");
    }

    #[Route('/articles/{slug}', name: 'articleShow', methods: ["GET", "POST"])]
    public function show($slug, Request $request): Response
    {
        //GET CURRENT DATE AND TIME
        $immutable = new \DateTimeImmutable('-1 year');

        //GET ARTICLE BASED ON ID
        $articleRepository = $this->em->getRepository(Article::class);
        $article = $articleRepository->findBy(["slug" => $slug]);
        $articleId = $article[0]->getId();

        //CREATE FORM FOR ADDING COMMENTS
        $comments = new Comment();
        $form = $this->createForm(CommentFormType::class, $comments);
        $form->handleRequest($request);

        //GET ALL COMMENTS RELATED TO CURRENT ARTICLE
        $commentsRepository = $this->em->getRepository(Comment::class);
        $comments = $commentsRepository->findBy(["article" => $articleId]);

        //IF COMMENT IS BEING ADDED RUN THIS IF STATEMENT
        if ($form->isSubmitted() && $form->isValid()) {
            //GET DATA FROM COMMENT FORM
            $newComment = $form->getData();
            $authorName = $form->get("authorName")->getData();
            $content = $form->get("content")->getData();

            //SET DATA FOR NEW COMMENT
            $newComment->setCreatedAt($immutable);
            $newComment->setContent($content);
            $newComment->setAuthorName($authorName);
            $newComment->setArticle($article[0]);

            //PERSIST IN DATABASE
            $this->em->persist($newComment);
            $this->em->flush();

            //REFRESH THE PAGE
            return $this->redirect("/articles/" . $slug);
        }

        //RENDER THE ARTICLE DETAIL PAGE
        return $this->render("articles/show.html.twig", [
            "article" => $article[0],
            "comments" => $comments,
            "form" => $form->createView(),
        ]);
    }
}