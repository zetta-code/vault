<?php
/**
 * @link      http://github.com/zetta-code/zend-skeleton-application for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Application\Controller;

use Application\Entity\Credential;
use Application\Entity\Role;
use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zetta\DoctrineUtil\Paginator\Paginator;
use Zetta\ZendAuthentication\Entity\Enum\Gender;
use Zetta\ZendAuthentication\Form\UserForm;
use Zetta\ZendBootstrap\Form\SearchForm;

class UsersController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $userRepository;

    /**
     * UsersController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the UsersController userRepository
     * @return EntityRepository
     */
    public function getUserRepository()
    {
        if ($this->userRepository === null) {
            $this->userRepository = $this->entityManager->getRepository(User::class);
        }
        return $this->userRepository;
    }

    /**
     * Set the UsersController userRepository
     * @param EntityRepository $userRepository
     * @return UsersController
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    public function indexAction()
    {
        $qb = $this->getUserRepository()->createQueryBuilder('user')
            ->where('user.deletedAt IS NULL');
        $q = $this->params()->fromQuery('q');
        $container = new Container('users');
        if ($q === null) {
            $q = $container->q;
        }
        if ($q !== null) {
            $container->q = $q;
            $qb->andWhere('user.email LIKE :q OR user.name LIKE :q')
                ->setParameter('q', "%$q%");
        }
        $qb->orderBy('user.name', 'ASC');

        // Paginator
        $users = new Paginator($qb);
        $users->setDefaultItemCountPerPage(25);
        $page = intval($this->params()->fromQuery('page'));
        if ($page) {
            $users->setCurrentPageNumber($page);
        }

        $searchForm = new SearchForm();
        $searchForm->setAttribute('class', 'form-inline search-top');
        $searchForm->get('q')->setAttribute('placeholder', _('Search User'));
        $searchForm->setData($this->params()->fromQuery());
        $searchForm->prepare();

        $viewModel = new ViewModel([
            'users' => $users,
            'searchForm' => $searchForm,
        ]);

        return $viewModel;
    }

    public function addAction()
    {
        $form = new UserForm($this->entityManager, 'user', [
            'identityClass' => User::class,
            'identityProperty' => 'username',
            'emailProperty' => 'email',
            'roleClass' => Role::class,
        ]);
        $form->setValidationGroup($form->getInputFilter()->getNewValidationGroup());
        $form->setAttribute('action', $this->url()->fromRoute('home/default', ['controller' => 'users', 'action' => 'add']));

        $user = new User();
        $user->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
        $form->bind($user);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData(UserForm::VALUES_AS_ARRAY);
                $credential = new Credential();
                $credential->setType(Credential::TYPE_PASSWORD);
                $credential->setValue($data['user']['password']);
                $credential->hashValue();
                $credential->setUser($user);

                if ($user->getGender() === Gender::FEMALE) {
                    $user->setAvatar($this->thumbnail()->getGirlThumbnailPath());
                } else {
                    $user->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
                }
                $user->setSignAllowed(true);
                $user->generateToken();

                $this->entityManager->persist($credential);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(_('The user has been added.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The user could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel ([
            'user' => $user,
            'form' => $form,
        ]);
        return $viewModel;
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getUserRepository()->createQueryBuilder('user')
            ->where('user = :user AND  user.deletedAt IS NULL')
            ->setParameter('user', $id);
        /** @var User $user */
        $user = $qb->getQuery()->getOneOrNullResult();
        if ($user === null) {
            $this->flashMessenger()->addErrorMessage(_('The user could not be found.'));
            return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
        }

        $viewModel = new ViewModel([
            'user' => $user,
        ]);

        return $viewModel;
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getUserRepository()->createQueryBuilder('user');
        $qb->where('user = :user AND  user.deletedAt IS NULL')
            ->setParameter('user', $id);
        /** @var User $user */
        $user = $qb->getQuery()->getOneOrNullResult();
        if ($user === null) {
            $this->flashMessenger()->addErrorMessage(_('The user could not be found.'));
            return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
        }

        $form = new UserForm($this->entityManager, 'user', [
            'identityClass' => User::class,
            'identityProperty' => 'username',
            'emailProperty' => 'email',
            'roleClass' => Role::class,
        ]);
        $form->setValidationGroup($form->getInputFilter()->getNewValidationGroup());
        $form->setAttribute('action', $this->url()->fromRoute('home/default', ['controller' => 'users', 'action' => 'edit', 'id' => $id]));
        $form->getInputFilter()->get('user')->get('password')->setRequired(false);
        $form->getInputFilter()->get('user')->get('username')->setRequired($user->getUsername() !== null);
        $form->bind($user);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData(UserForm::VALUES_AS_ARRAY);
                $credential = $this->entityManager->getRepository(Credential::class)->findOneBy([
                    'user' => $user,
                    'type' => Credential::TYPE_PASSWORD,
                ]);
                if ($credential === null) {
                    $credential = new Credential();
                    $credential->setUser($user);
                    $credential->setType(Credential::TYPE_PASSWORD);
                    if (empty($data['user']['password'])) {
                        $credential->setValue('#');
                    } else {
                        $credential->setValue($data['user']['password']);
                    }
                    $credential->hashValue();
                    $this->entityManager->persist($credential);
                } else {
                    if (!empty($data['user']['password'])) {
                        $credential->setValue($data['user']['password']);
                        $credential->hashValue();
                    }
                }

                $this->thumbnail()->process($user->getAvatar(null), $user->getAvatar(null));
                if ($user->getAvatar(null) === $this->thumbnail()->getDefaultThumbnailPath() && $user->getGender() === Gender::FEMALE) {
                    $user->setAvatar($this->thumbnail()->getGirlThumbnailPath());
                } elseif ($user->getAvatar(null) === $this->thumbnail()->getGirlThumbnailPath() && $user->getGender() === Gender::MALE) {
                    $user->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
                }

                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The user has been updated.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The user could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel ([
            'user' => $user,
            'form' => $form,
        ]);
        return $viewModel;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getUserRepository()->createQueryBuilder('user');
        $qb->where('user = :user AND user.deletedAt IS NULL');
        $qb->setParameter('user', $id);
        /** @var User $user */
        $user = $qb->getQuery()->getOneOrNullResult();
        if ($user === null) {
            $this->flashMessenger()->addErrorMessage(_('The user could not be found.'));
            return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
        }

        $request = $this->getRequest();
        if ($request->isDelete() || $request->isPost()) {
            $user->deletedAt();
            $user->setSignAllowed(false);
            $user->generateToken();
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(_('The user has been deleted.'));
            return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
        } else {
            $viewModel = new ViewModel([
                'user' => $user,
            ]);

            return $viewModel;
        }
    }
}