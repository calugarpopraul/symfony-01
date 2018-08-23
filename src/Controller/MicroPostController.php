<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 8/18/18
 * Time: 7:07 PM
 */

namespace App\Controller;

use App\Entity\MicroPost;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;


class MicroPostController extends AbstractController
{
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(\Twig_Environment $twig, MicroPostRepository $microPostRepository,
                                FormFactoryInterface $formFactory, EntityManagerInterface $entityManager,
                                RouterInterface $router, FlashBagInterface $flashBag)
    {

        $this->twig = $twig;
        $this->microPostRepository = $microPostRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    /**
     * @Route("/", name="micro_post_index")
     */
    public function index()
    {
        return $this->render('micro-post/index.html.twig', [
            'posts' => $this->microPostRepository->findBy([], ['time' => 'DESC'])
        ]);
    }

    /**
     * @param MicroPost $microPost
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/edit/{id}", name="micro_post_edit")
     */
    public function edit(MicroPost $microPost, Request $request)
    {
        $form = $this->formFactory->create(MicroPostType::class, $microPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }

        return $this->render('micro-post/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/view/{id}", name="micro_post_post")
     */
    public function post(MicroPost $post)
    {
        //$post = $this->microPostRepository->find($id);

        return $this->render('micro-post/post.html.twig',
            [
                'post' => $post
            ]);
    }

    /**
     * @Route("/delete/{id}", name="micro_post_delete")
     */
    public function delete(MicroPost $microPost)
    {
        $this->entityManager->remove($microPost);
        $this->entityManager->flush();

        $this->flashBag->add('notice', 'Micro post was deleted');

        return new RedirectResponse(
            $this->router->generate('micro_post_index')
        );
    }

    /**
     * @Route("/add", name="micro_post_add")
     */
    public function add(Request $request)
    {

        $microPost = new MicroPost();
        $microPost->setTime(new \DateTime());

        $form = $this->formFactory->create(MicroPostType::class, $microPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }

        return $this->render('micro-post/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


}