<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Controller;

use Application\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zetta\DoctrineUtil\Paginator\Paginator;
use Zetta\Vault\Entity\Account;
use Zetta\Vault\Entity\Permission;
use Zetta\Vault\Entity\Repository\AccountRepository;
use Zetta\Vault\Entity\Section;
use Zetta\Vault\Entity\Tag;
use Zetta\Vault\Form\AccountForm;
use Zetta\Vault\Form\MemberForm;
use Zetta\ZendBootstrap\Form\SearchForm;

class AccountsController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * AccountsController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the AccountsController accountRepository
     * @return AccountRepository
     */
    public function getAccountRepository()
    {
        if ($this->accountRepository === null) {
            $this->accountRepository = $this->entityManager->getRepository(Account::class);
        }
        return $this->accountRepository;
    }

    /**
     * Set the AccountsController accountRepository
     * @param AccountRepository $accountRepository
     * @return AccountsController
     */
    public function setAccountRepository($accountRepository)
    {
        $this->accountRepository = $accountRepository;
        return $this;
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function indexAction()
    {
        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account.deletedAt IS NULL')
            ->orderBy('account.name', 'ASC');

        $q = $this->params()->fromQuery('q');
        if (!empty($q)) {
            $qb->andWhere('account.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        // Paginator
        $accounts = new Paginator($qb);
        $accounts->setDefaultItemCountPerPage(25);
        $page = (int)$this->params()->fromQuery('page');
        if ($page) {
            $accounts->setCurrentPageNumber($page);
        }

        $searchForm = new SearchForm();
        $searchForm->setAttribute('class', 'form-inline search-top');
        $searchForm->get('q')->setAttribute('placeholder', _('Search Account'));
        $searchForm->setData($this->params()->fromQuery());
        $searchForm->prepare();

        $viewModel = new ViewModel(compact(
            'accounts',
            'searchForm'
        ));

        return $viewModel;
    }

    public function addAction()
    {
        $form = new AccountForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'accounts', 'action' => 'add']));

        $account = new Account();
        $form->bind($account);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $tagBuilder = $this->entityManager->getRepository(Tag::class)->createQueryBuilder('tag')
                    ->where('tag.name LIKE :name AND tag.deletedAt IS NULL')
                    ->setMaxResults(1);
                $tagNames = $form->get('account')->get('tags')->getValue();
                foreach ($account->getTags() as $i => $tag) {
                    if ($tag->getId() === null) {
                        $check = $tagBuilder->setParameter('name', $tagNames[$i])
                            ->getQuery()->getOneOrNullResult();

                        if ($check === null) {
                            $tag->setName($tagNames[$i]);
                        } else {
                            $account->removeTag($tag);
                            if (!$account->getTags()->contains($check)) {
                                $account->addTag($check);
                            }
                        }
                    }
                }
                $this->entityManager->persist($account);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(_('The account has been added.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The account could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'account',
            'form'
        ));
        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account = :account AND account.deletedAt IS NULL')
            ->setParameter('account', $id);
        /** @var Account $account */
        $account = $qb->getQuery()->getOneOrNullResult();
        if ($account === null) {
            $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
        }

        $viewModel = new ViewModel(compact(
            'account'
        ));

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account = :account AND account.deletedAt IS NULL')
            ->setParameter('account', $id);
        /** @var Account $account */
        $account = $qb->getQuery()->getOneOrNullResult();
        if ($account === null) {
            $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
        }

        $form = new AccountForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'accounts', 'action' => 'edit', 'id' => $id]));
        $form->bind($account);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $tagBuilder = $this->entityManager->getRepository(Tag::class)->createQueryBuilder('tag')
                    ->where('tag.name LIKE :name AND tag.deletedAt IS NULL')
                    ->setMaxResults(1);
                $tagNames = $form->get('account')->get('tags')->getValue();
                foreach ($account->getTags() as $i => $tag) {
                    if ($tag->getId() === null) {
                        $check = $tagBuilder->setParameter('name', $tagNames[$i])
                            ->getQuery()->getOneOrNullResult();

                        if ($check === null) {
                            $tag->setName($tagNames[$i]);
                        } else {
                            $account->removeTag($tag);
                            if (!$account->getTags()->contains($check)) {
                                $account->addTag($check);
                            }
                        }
                    }
                }
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The account has been updated.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The account could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'account',
            'form'
        ));
        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account = :account AND account.deletedAt IS NULL')
            ->setParameter('account', $id);
        /** @var Account $account */
        $account = $qb->getQuery()->getOneOrNullResult();
        if ($account !== null) {
            $request = $this->getRequest();
            if ($request->isDelete() || $request->isPost()) {
                $account->deletedAt();
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The account has been deleted.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
            }

            $viewModel = new ViewModel(compact(
                'account'
            ));

            return $viewModel;
        } else {
            $step = (int)$this->params()->fromPost('step', 0);
            if ($step !== 0) {
                $items = json_decode($this->params()->fromPost('items', '[0]'));

                $qb = $this->accountRepository->createQueryBuilder('account')
                    ->where('account.id IN(:ids) AND account.deletedAt IS NULL')
                    ->setParameter('ids', $items)
                    ->orderBy('account.name', 'ASC');
                /** @var Account[] $accounts */
                $accounts = $qb->getQuery()->getResult();

                if (count($accounts) === 0) {
                    $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
                    return $this->redirect()->toRoute('home/default', ['controller' => 'accounts']);
                }

                if ($step === 2) {
                    $confirm = ((int)$this->params()->fromPost('confirm', 0)) === 1;
                    if ($confirm) {
                        foreach ($accounts as $account) {
                            $account->deletedAt();
                        }

                        $this->entityManager->flush();
                        $this->flashMessenger()->addSuccessMessage(_('The account has been deleted.'));
                        return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
                    } else {
                        $this->flashMessenger()->addErrorMessage(_('The account could not be deleted. Please, try again.'));
                    }
                }

                $viewModel = new ViewModel([
                    'accounts' => $accounts,
                    'items' => json_encode($items),
                ]);
                $viewModel->setTemplate('zetta/vault/accounts/delete-list');

                return $viewModel;
            } else {
                $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'accounts']);
            }
        }
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function membersAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account = :account AND account.deletedAt IS NULL')
            ->setParameter('account', $id);
        /** @var Account $account */
        $account = $qb->getQuery()->getOneOrNullResult();
        if ($account === null) {
            $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
        }

        $form = new MemberForm($this->entityManager, 'account');
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'accounts', 'action' => 'members', 'id' => $id]))
            ->setAttribute('class', 'form-inline');
        $members = $this->entityManager->getRepository(Permission::class)->findUsersByAccount($account);
        $haystack = [];
        foreach ($members as $member) {
            $haystack[$member->getId()] = $member->getName();
        }
        $form->get('member')
            ->setAttribute('class', 'selectpicker mr-sm-2')
            ->setLabelAttributes(['class' => 'sr-only'])
            ->setOption('div', null)
            ->setValueOptions($haystack);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData($form::VALUES_AS_ARRAY);
                /** @var User $user */
                $user = $this->entityManager->getRepository(User::class)->createQueryBuilder('user')
                    ->leftJoin('user.permissions', 'permission', 'WITH', 'permission.account = :account AND permission.credential IS NULL')
                    ->where('user.id = :id AND user.deletedAt IS NULL AND (permission.id IS NULL OR permission.allow = FALSE)')
                    ->setParameter('account', $account)
                    ->setParameter('id', $data['member'])
                    ->getQuery()->getOneOrNullResult();
                if ($user !== null) {
                    /** @var Permission $permission */
                    $permission = $this->entityManager->getRepository(Permission::class)->createQueryBuilder('permission')
                        ->where('permission.user = :user AND permission.account = :account AND permission.credential IS NULL AND permission.allow = FALSE')
                        ->setParameter('user', $user)
                        ->setParameter('account', $account)
                        ->getQuery()->getOneOrNullResult();
                    if ($permission === null) {
                        $permission = new Permission();
                        $permission->setUser($user)
                            ->setAccount($account);
                        $this->entityManager->persist($permission);
                    }
                    $permission->setAllow(true);

                    $this->entityManager->flush();
                    $this->flashMessenger()->addSuccessMessage(_('The member has been added.'));
                } else {
                    $this->flashMessenger()->addInfoMessage(_('The member has already been added.'));
                }
                return $this->redirect()->refresh();
            } else {
                $this->flashMessenger()->addErrorMessage(_('The member could not be added. Please, try again.'));
            }
        }

        $members = $this->entityManager->getRepository(Permission::class)->createQueryBuilder('permission')
            ->where('permission.account = :account AND permission.credential IS NULL AND permission.allow = TRUE')
            ->setParameter('account', $account)
            ->getQuery()->getResult();

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'account',
            'members',
            'form'
        ));

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function revokeAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->entityManager->getRepository(Permission::class)->createQueryBuilder('permission')
            ->where('permission.id = :id AND permission.credential IS NULL AND permission.allow = TRUE')
            ->setParameter('id', $id);
        /** @var Permission $permission */
        $permission = $qb->getQuery()->getOneOrNullResult();
        if ($permission === null) {
            $this->flashMessenger()->addErrorMessage(_('The account could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts']);
        } else {
            $permission->setAllow(false);
            $this->entityManager->flush();
            return $this->redirect()->toRoute('vault/default', ['controller' => 'accounts', 'action' => 'members', 'id' => $permission->getAccount()->getId()]);
        }
    }

    /**
     * @return JsonModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sectionsAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getAccountRepository()->createQueryBuilder('account')
            ->where('account = :account AND account.deletedAt IS NULL')
            ->setParameter('account', $id);
        /** @var Account $account */
        $account = $qb->getQuery()->getOneOrNullResult();
        $data = [
            'success' => false,
        ];
        if ($account !== null) {
            $qb = $this->entityManager->getRepository(Section::class)->createQueryBuilder('section')
                ->join('section.accounts', 'account')
                ->where('account = :account AND section.deletedAt IS NULL')
                ->setParameter('account', $account)
                ->orderBy('section.name', 'ASC');
            /** @var Section[] $sections */
            $sections = $qb->getQuery()->getResult();

            $data['success'] = true;
            $data['id'] = $id;
            $data['sections'] = [];
            foreach ($sections as $section) {
                $data['sections'][] = [
                    'id' => $section->getId(),
                    'name' => $section->getName()
                ];
            }
        }

        $viewModel = new JsonModel($data);
        return $viewModel;
    }
}
