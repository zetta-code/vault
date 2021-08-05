<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zetta\DoctrineUtil\Paginator\Paginator;
use Zetta\Vault\Entity\Section;
use Zetta\Vault\Form\SectionForm;
use Zetta\ZendBootstrap\Form\SearchForm;

class SectionsController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $sectionRepository;

    /**
     * SectionsController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the SectionsController sectionRepository
     * @return EntityRepository
     */
    public function getSectionRepository()
    {
        if ($this->sectionRepository === null) {
            $this->sectionRepository = $this->entityManager->getRepository(Section::class);
        }
        return $this->sectionRepository;
    }

    /**
     * Set the SectionsController sectionRepository
     * @param EntityRepository $sectionRepository
     * @return SectionsController
     */
    public function setSectionRepository($sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
        return $this;
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function indexAction()
    {
        $qb = $this->getSectionRepository()->createQueryBuilder('section')
            ->where('section.deletedAt IS NULL')
            ->orderBy('section.name', 'ASC');

        $q = $this->params()->fromQuery('q');
        if (!empty($q)) {
            $qb->andWhere('section.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        // Paginator
        $sections = new Paginator($qb);
        $sections->setDefaultItemCountPerPage(25);
        $page = (int)$this->params()->fromQuery('page');
        if ($page) {
            $sections->setCurrentPageNumber($page);
        }

        $searchForm = new SearchForm();
        $searchForm->setAttribute('class', 'form-inline search-top');
        $searchForm->get('q')->setAttribute('placeholder', _('Search Section'));
        $searchForm->setData($this->params()->fromQuery());
        $searchForm->prepare();

        $viewModel = new ViewModel(compact(
            'sections',
            'searchForm'
        ));

        return $viewModel;
    }

    public function addAction()
    {
        $form = new SectionForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'sections', 'action' => 'add']));

        $section = new Section();
        $form->bind($section);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $this->entityManager->persist($section);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(_('The section has been added.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The section could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'section',
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

        $qb = $this->getSectionRepository()->createQueryBuilder('section')
            ->where('section = :section AND section.deletedAt IS NULL')
            ->setParameter('section', $id);
        /** @var Section $section */
        $section = $qb->getQuery()->getOneOrNullResult();
        if ($section === null) {
            $this->flashMessenger()->addErrorMessage(_('The section could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
        }

        $viewModel = new ViewModel(compact(
            'section'
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

        $qb = $this->getSectionRepository()->createQueryBuilder('section')
            ->where('section = :section AND section.deletedAt IS NULL')
            ->setParameter('section', $id);
        /** @var Section $section */
        $section = $qb->getQuery()->getOneOrNullResult();
        if ($section === null) {
            $this->flashMessenger()->addErrorMessage(_('The section could not be found.'));
            return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
        }

        $form = new SectionForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('vault/default', ['controller' => 'sections', 'action' => 'edit', 'id' => $id]));
        $form->bind($section);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The section has been updated.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The section could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel(compact(
            'section',
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

        $qb = $this->getSectionRepository()->createQueryBuilder('section')
            ->where('section = :section AND section.deletedAt IS NULL')
            ->setParameter('section', $id);
        /** @var Section $section */
        $section = $qb->getQuery()->getOneOrNullResult();
        if ($section !== null) {
            $request = $this->getRequest();
            if ($request->isDelete() || $request->isPost()) {
                $section->deletedAt();
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The section has been deleted.'));
                return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
            }

            $viewModel = new ViewModel(compact(
                'section'
            ));

            return $viewModel;
        } else {
            $step = (int)$this->params()->fromPost('step', 0);
            if ($step !== 0) {
                $items = json_decode($this->params()->fromPost('items', '[0]'));

                $qb = $this->sectionRepository->createQueryBuilder('section')
                    ->where('section.id IN(:ids) AND section.deletedAt IS NULL')
                    ->setParameter('ids', $items)
                    ->orderBy('section.name', 'ASC');
                /** @var Section[] $sections */
                $sections = $qb->getQuery()->getResult();

                if (count($sections) === 0) {
                    $this->flashMessenger()->addErrorMessage(_('The section could not be found.'));
                    return $this->redirect()->toRoute('home/default', ['controller' => 'sections']);
                }

                if ($step === 2) {
                    $confirm = ((int)$this->params()->fromPost('confirm', 0)) === 1;
                    if ($confirm) {
                        foreach ($sections as $section) {
                            $section->deletedAt();
                        }

                        $this->entityManager->flush();
                        $this->flashMessenger()->addSuccessMessage(_('The section has been deleted.'));
                        return $this->redirect()->toRoute('vault/default', ['controller' => 'sections']);
                    } else {
                        $this->flashMessenger()->addErrorMessage(_('The section could not be deleted. Please, try again.'));
                    }
                }

                $viewModel = new ViewModel([
                    'sections' => $sections,
                    'items' => json_encode($items),
                ]);
                $viewModel->setTemplate('zetta/vault/sections/delete-list');

                return $viewModel;
            } else {
                $this->flashMessenger()->addErrorMessage(_('The section could not be found.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'sections']);
            }
        }
    }
}
