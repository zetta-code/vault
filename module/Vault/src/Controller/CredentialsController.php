<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Controller;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zetta\DoctrineUtil\Paginator\Paginator;
use Zetta\Vault\Entity\Credential;
use Zetta\Vault\Entity\Permission;
use Zetta\Vault\Entity\Tag;
use Zetta\Vault\Form\CredentialForm;
use Zetta\Vault\Form\MemberForm;
use Zetta\ZendBootstrap\Form\SearchForm;

class CredentialsController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $credentialRepository;

    /**
     * CredentialsController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the CredentialsController credentialRepository
     * @return EntityRepository
     */
    public function getCredentialRepository()
    {
        if ($this->credentialRepository === null) {
            $this->credentialRepository = $this->entityManager->getRepository(Credential::class);
        }
        return $this->credentialRepository;
    }

    /**
     * Set the CredentialsController credentialRepository
     * @param EntityRepository $credentialRepository
     * @return CredentialsController
     */
    public function setCredentialRepository($credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
        return $this;
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function indexAction()
    {
        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->leftJoin('credential.account', 'account')
            ->leftJoin('credential.section', 'section')
            ->where('credential.deletedAt IS NULL')
            ->orderBy('account.name', 'ASC')
            ->addOrderBy('section.name', 'ASC')
            ->addOrderBy('credential.name', 'ASC')
        ;

        if (!$this->isAllowed('Zetta\Vault\Controller\Sections', 'index')) {
            $qb->join('account.permissions', 'permission')
                ->andWhere('permission.user = :user AND (permission.credential IS NULL OR permission.credential = credential) AND permission.allow = TRUE')
                ->setParameter('user', $this->identity());
        }

        $q = trim($this->params()->fromQuery('q', ''));
        if (!empty($q)) {
            if (strpos($q, ':') !== false) {
                $cmds = explode(',', $q);
                foreach ($cmds as $i => $cmd) {
                    $eval = explode(':', $cmd);
                    if (count($eval) === 2) {
                        switch (strtolower(trim($eval[0]))) {
                            case 's':
                            case 'section':
                                $qb->andWhere('section.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                break;
                            case 'a':
                            case 'account':
                                $qb->andWhere('account.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                break;
                        }
                    } else {
                        $qb->andWhere('credential.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($cmd) . '%');
                    }
                }
            } else {
                $qb->andWhere('credential.name LIKE :q')->setParameter('q', '%' . $q . '%');
            }
        }

        // Paginator
        $credentials = new Paginator($qb);
        $credentials->setDefaultItemCountPerPage(25);
        $page = (int)$this->params()->fromQuery('page');
        if ($page) {
            $credentials->setCurrentPageNumber($page);
        }

        $searchForm = new SearchForm();
        $searchForm->setAttribute('class', 'form-inline search-top');
        $searchForm->get('q')->setAttribute('placeholder', _('Search Credential'));
        $searchForm->setData($this->params()->fromQuery());
        $searchForm->prepare();

        $viewModel = new ViewModel(compact(
            'credentials',
            'searchForm'
        ));

        return $viewModel;
    }

    public function addAction()
    {
        $form = new CredentialForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'credentials', 'action' => 'add']));
        $form->get('credential')->get('account')->setAttribute('data-url', $this->url()->fromRoute('vault/default', ['controller' => 'accounts', 'action' => 'sections']));

        $credential = new Credential();
        $form->bind($credential);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $credential->setUsername($this->crypt()->encrypt($credential->getUsername()));
                $credential->setValue($this->crypt()->encrypt($credential->getValue()));

                $tagBuilder = $this->entityManager->getRepository(Tag::class)->createQueryBuilder('tag')
                    ->where('tag.name LIKE :name AND tag.deletedAt IS NULL')
                    ->setMaxResults(1);
                $tagNames = $form->get('credential')->get('tags')->getValue();
                foreach ($credential->getTags() as $i => $tag) {
                    if ($tag->getId() === null) {
                        $check = $tagBuilder->setParameter('name', $tagNames[$i])
                            ->getQuery()->getOneOrNullResult();

                        if ($check === null) {
                            $tag->setName($tagNames[$i]);
                        } else {
                            $credential->removeTag($tag);
                            if (!$credential->getTags()->contains($check)) {
                                $credential->addTag($check);
                            }
                        }
                    }
                }

                $this->entityManager->persist($credential);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(_('The credential has been added.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The credential could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'credential',
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

        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential = :credential AND credential.deletedAt IS NULL')
            ->setParameter('credential', $id);
        /** @var Credential $credential */
        $credential = $qb->getQuery()->getOneOrNullResult();
        if ($credential === null) {
            $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
        }

        $viewModel = new ViewModel(compact(
            'credential'
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

        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential = :credential AND credential.deletedAt IS NULL')
            ->setParameter('credential', $id);
        /** @var Credential $credential */
        $credential = $qb->getQuery()->getOneOrNullResult();
        if ($credential === null) {
            $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
        }

        $form = new CredentialForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'credentials', 'action' => 'edit', 'id' => $id]));
        $form->get('credential')->get('account')->setAttribute('data-url', $this->url()->fromRoute('vault/default', ['controller' => 'accounts', 'action' => 'sections']));
        if ($credential->getSection() !== null) {
            $form->get('credential')->get('section')->setAttribute('data-val', $credential->getSection()->getId());
        }
        $form->getInputFilter()->get('credential')->get('value')->setRequired(false);
        $credential->setUsername($this->crypt()->decrypt($credential->getUsername()));
        $form->bind($credential);
        $valueEncripted = $credential->getValue();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $credential->setUsername($this->crypt()->encrypt($credential->getUsername()));

                if (!empty($credential->getValue())) {
                    $credential->setValue($this->crypt()->encrypt($credential->getValue()));
                } else {
                    $credential->setValue($valueEncripted);
                }

                $tagBuilder = $this->entityManager->getRepository(Tag::class)->createQueryBuilder('tag')
                    ->where('tag.name LIKE :name AND tag.deletedAt IS NULL')
                    ->setMaxResults(1);
                $tagNames = $form->get('credential')->get('tags')->getValue();
                foreach ($credential->getTags() as $i => $tag) {
                    if ($tag->getId() === null) {
                        $check = $tagBuilder->setParameter('name', $tagNames[$i])
                            ->getQuery()->getOneOrNullResult();

                        if ($check === null) {
                            $tag->setName($tagNames[$i]);
                        } else {
                            $credential->removeTag($tag);
                            if (!$credential->getTags()->contains($check)) {
                                $credential->addTag($check);
                            }
                        }
                    }
                }

                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The credential has been updated.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The credential could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'credential',
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

        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential = :credential AND credential.deletedAt IS NULL')
            ->setParameter('credential', $id);
        /** @var Credential $credential */
        $credential = $qb->getQuery()->getOneOrNullResult();
        if ($credential !== null) {
            $request = $this->getRequest();
            if ($request->isDelete() || $request->isPost()) {
                $credential->deletedAt();
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The credential has been deleted.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
            }

            $viewModel = new ViewModel(compact(
                'credential'
            ));

            return $viewModel;
        } else {
            $step = (int)$this->params()->fromPost('step', 0);
            if ($step !== 0) {
                $items = json_decode($this->params()->fromPost('items', '[0]'));

                $qb = $this->credentialRepository->createQueryBuilder('credential')
                    ->where('credential.id IN(:ids) AND credential.deletedAt IS NULL')
                    ->setParameter('ids', $items)
                    ->orderBy('credential.name', 'ASC');
                /** @var Credential[] $credentials */
                $credentials = $qb->getQuery()->getResult();

                if (count($credentials) === 0) {
                    $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
                    return $this->redirect()->toRoute('home/default', ['controller' => 'credentials']);
                }

                if ($step === 2) {
                    $confirm = ((int)$this->params()->fromPost('confirm', 0)) === 1;
                    if ($confirm) {
                        foreach ($credentials as $credential) {
                            $credential->deletedAt();
                        }

                        $this->entityManager->flush();
                        $this->flashMessenger()->addSuccessMessage(_('The credential has been deleted.'));
                        return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
                    } else {
                        $this->flashMessenger()->addErrorMessage(_('The credential could not be deleted. Please, try again.'));
                    }
                }

                $viewModel = new ViewModel([
                    'credentials' => $credentials,
                    'items' => json_encode($items),
                ]);
                $viewModel->setTemplate('zetta/vault/credentials/delete-list');

                return $viewModel;
            } else {
                $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'credentials']);
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

        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential = :credential AND credential.deletedAt IS NULL')
            ->setParameter('credential', $id);
        /** @var Credential $credential */
        $credential = $qb->getQuery()->getOneOrNullResult();
        if ($credential === null) {
            $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
        }

        $form = new MemberForm($this->entityManager, 'credential');
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'credentials', 'action' => 'members', 'id' => $id]))
            ->setAttribute('class', 'form-inline');
        $members = $this->entityManager->getRepository(Permission::class)->findUsersByCredential($credential);
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
                    ->leftJoin('user.permissions', 'permission', 'WITH', 'permission.credential = :credential')
                    ->where('user.id = :id AND user.deletedAt IS NULL AND (permission.id IS NULL OR permission.allow = FALSE)')
                    ->setParameter('credential', $credential)
                    ->setParameter('id', $data['member'])
                    ->getQuery()->getOneOrNullResult();
                if ($user !== null) {
                    /** @var Permission $permission */
                    $permission = $this->entityManager->getRepository(Permission::class)->createQueryBuilder('permission')
                        ->where('permission.user = :user AND permission.credential = :credential AND permission.allow = FALSE')
                        ->setParameter('user', $user)
                        ->setParameter('credential', $credential)
                        ->getQuery()->getOneOrNullResult();
                    if ($permission === null) {
                        $permission = new Permission();
                        $permission->setUser($user)
                            ->setAccount($credential->getAccount())
                            ->setCredential($credential);
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
            ->where('permission.credential = :credential AND permission.allow = TRUE')
            ->setParameter('credential', $credential)
            ->getQuery()->getResult();

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'credential',
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
            ->where('permission.id = :id AND NOT permission.credential IS NULL AND permission.allow = TRUE')
            ->setParameter('id', $id);
        /** @var Permission $permission */
        $permission = $qb->getQuery()->getOneOrNullResult();
        if ($permission === null) {
            $this->flashMessenger()->addErrorMessage(_('The credential could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
        } else {
            $permission->setAllow(false);
            $this->entityManager->flush();
            return $this->redirect()->toRoute('vault/default', ['controller' => 'credentials', 'action' => 'members', 'id' => $permission->getCredential()->getId()]);
        }
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function revealAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $qb = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential = :credential AND credential.deletedAt IS NULL')
            ->setParameter('credential', $id);
        /** @var Credential $credential */
        $credential = $qb->getQuery()->getOneOrNullResult();
        $data = [
            'success' => false,
        ];
        if ($credential !== null) {
            $data = [
                'success' => true,
                'id' => $id,
                'username' => $this->crypt()->decrypt($credential->getUsername()),
                'value' => $this->crypt()->decrypt($credential->getValue()),
            ];
        }

        $viewModel = new JsonModel($data);
        return $viewModel;
    }

    public function fixCrypt() {
        /** @var Credential[] $credentials */
        $credentials = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->orderBy('credential.createdAt', 'ASC')
            ->getQuery()->getResult();
        $this->crypt()->setKey(base64_decode(substr('base64:', 7)));
        foreach ($credentials as $credential) {
            $credential->setUsername($this->crypt()->decrypt($credential->getUsername()));
            $credential->setValue($this->crypt()->decrypt($credential->getValue()));
        }
        $this->crypt()->setKey(base64_decode(substr('base64:', 7)));
        foreach ($credentials as $credential) {
            $credential->setUsername($this->crypt()->encrypt($credential->getUsername()));
            $credential->setValue($this->crypt()->encrypt($credential->getValue()));
        }
        $this->entityManager->flush();
        $this->redirect()->toRoute('vault/default', ['controller' => 'credentials']);
    }
}
