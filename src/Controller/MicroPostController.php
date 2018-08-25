<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 8/18/18
 * Time: 7:07 PM
 */

namespace App\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class MicroPostController
 * @package App\Controller
 * @Route("/micro-post")
 */
class MicroPostController extends Controller
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
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(\Twig_Environment $twig, MicroPostRepository $microPostRepository,
                                FormFactoryInterface $formFactory, EntityManagerInterface $entityManager,
                                RouterInterface $router, FlashBagInterface $flashBag,
                                AuthorizationCheckerInterface $authorizationChecker)
    {

        $this->twig = $twig;
        $this->microPostRepository = $microPostRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->authorizationChecker = $authorizationChecker;
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
     * @Security("is_granted('edit', microPost)",message="Access denied")
     */
    public function edit(MicroPost $microPost, Request $request)
    {
        //on base controller class
        //$this->denyAccessUnlessGranted('edit',$microPost);

//        if($this->authorizationChecker->isGranted('edit',$microPost)){
//            throw new UnauthorizedHttpException();
//        }

        $form = $this->formFactory->create(MicroPostType::class, $microPost);
        $form->handleRequest($request);
        dump($microPost);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            dump($microPost);
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
     * @Security("is_granted('delete', microPost)",message="Access denied")
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
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function add(Request $request, TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()->getUser();
        dump($user);

        $microPost = new MicroPost();
        $microPost->setTime(new \DateTime());
        $microPost->setUser($user);

        $form = $this->formFactory->create(MicroPostType::class, $microPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();
            dump($microPost);

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }

        return $this->render('micro-post/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


}