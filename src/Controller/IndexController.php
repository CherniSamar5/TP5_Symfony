<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;




class IndexController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/IndexController.php',
        ]);
    }

    #[Route('/home', name: 'article_list')]
    public function home(ManagerRegistry $doctrine)
    {
        $articles = $doctrine->getRepository(Article::class)->findAll();
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }
        

    #[Route('/article/save' , name: 'save_article')]
    public function save(ManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        $article = new Article();
        $article->setNom('Article 3');
        $article->setPrix(3000);
        $entityManager->persist($article);
        $entityManager->flush();
        return new Response('Article enregisté avec id '.$article->getId());
    }



    #[Route('/article/new', name:'new_article')]
    public function new(Request $request,ManagerRegistry $doctrine):Response
    {
        $article = new Article();
        $form = $this->createFormBuilder($article)
        ->add('nom', TextType::class)
        ->add('prix', TextType::class)
        ->add('save', SubmitType::class, array('label' => 'Créer'))
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
            }
            return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/article/{id}', name:'article_show')]
    public function show($id,ManagerRegistry $doctrine): Response {
        $article = $doctrine->getRepository(Article::class)->find($id);
        return $this->render('articles/show.html.twig', array('article' =>$article));
    }


    #[Route('/article/edit/{id}', name: 'edit_article')]
    public function edit(Request $request, $id, ManagerRegistry $doctrine)
    {
        $article = new Article();
        $article = $doctrine->getRepository(Article::class)->find($id);
        $form = $this->createFormBuilder($article)
        ->add('nom', TextType::class)
        ->add('prix', TextType::class)
        ->add('save', SubmitType::class, array('label' => 'Modifier'))->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
        }
        return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
    }


    #[Route('/article/delete/{id}', name: 'delete_article')]
    public function delete(Request $request, $id, ManagerRegistry $doctrine)
    {
        $article = $doctrine->getRepository(Article::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($article);
        $entityManager->flush();
        $response = new Response();
        $response->send();
        return $this->redirectToRoute('article_list');
    }
}
