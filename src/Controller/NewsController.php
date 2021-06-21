<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\News;
use App\Entity\Comments;
use App\Form\CommentsType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\NewsRepository;
use App\Repository\CommentsRepository;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    protected $auth;

    public function __construct(AuthorizationCheckerInterface $auth) {
        $this->auth = $auth;
    }

    /**
     * @Route("/", name="news")
     */
    public function index(NewsRepository $newsRepository): Response
    {
        $listNews = $newsRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('news/index.html.twig', [
            'listNews' => $listNews,
        ]);
    }

    /**
     * @Route("/news/{id}", name="news_show")
     */
    public function show(News $news, CommentsRepository $commentsRepository, Request $request): Response
    {
        if($this->getUser() == null){
            $news->setViews($news->getViews()+1);
        }
        elseif($this->auth->isGranted('ROLE_ADMIN') == false){
            $news->setViews($news->getViews()+1);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();

        $commentsList = $commentsRepository->findBy(['news' => $news], ['createdAt' => 'DESC']);

        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$this->getUser()){
                return $this->redirectToRoute('app_login');
            }
            $comment = $form->getData();
            $comment->setCreatedAt(new \DateTime('now'));
            $comment->setNews($news);
            $comment->setUser($this->getUser());
            
            $em->persist($comment);
            $em->flush();
    
            return $this->redirectToRoute('news_show', ['id' => $news->getId()]);
        }

        return $this->render('news/show.html.twig', [
            'form' => $form->createView(),
            'news' => $news,
            'commentsList' => $commentsList,
        ]);
    }
}
