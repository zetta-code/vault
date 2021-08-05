<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zetta\Vault\Entity\Account;
use Zetta\Vault\Entity\Credential;
use Zetta\Vault\Entity\Section;

class IndexController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $accountRepository;

    /**
     * @var EntityRepository
     */
    protected $sectionRepository;

    /**
     * @var EntityRepository
     */
    protected $credentialRepository;

    /**
     * IndexController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the IndexController accountRepository
     * @return EntityRepository
     */
    public function getAccountRepository()
    {
        if ($this->accountRepository === null) {
            $this->accountRepository = $this->entityManager->getRepository(Account::class);
        }
        return $this->accountRepository;
    }

    /**
     * Set the IndexController accountRepository
     * @param EntityRepository $accountRepository
     * @return IndexController
     */
    public function setAccountRepository($accountRepository)
    {
        $this->accountRepository = $accountRepository;
        return $this;
    }

    /**
     * Get the IndexController sectionRepository
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
     * Set the IndexController sectionRepository
     * @param EntityRepository $sectionRepository
     * @return IndexController
     */
    public function setSectionRepository($sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
        return $this;
    }

    /**
     * Get the IndexController credentialRepository
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
     * Set the IndexController credentialRepository
     * @param EntityRepository $credentialRepository
     * @return IndexController
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
        $accounts = $this->getAccountRepository()->createQueryBuilder('account')
            ->join('account.credentials', 'credential')
            ->leftJoin('account.sections', 'section')
            ->where('account.deletedAt IS NULL')
            ->orderBy('account.name', 'ASC');

        $sections = $this->getSectionRepository()->createQueryBuilder('section')
            ->join('section.accounts', 'account')
            ->join('section.credentials', 'credential')
            ->where('account = :account AND section.deletedAt IS NULL')
            ->orderBy('section.name', 'ASC');

        $credentials = $this->getCredentialRepository()->createQueryBuilder('credential')
            ->where('credential.account = :account AND credential.section = :section AND credential.deletedAt IS NULL')
            ->addOrderBy('credential.name', 'ASC');

        if (!$this->isAllowed('Zetta\Vault\Controller\Sections', 'index')) {
            $accounts->join('account.permissions', 'permission')
                ->andWhere('permission.user = :user AND permission.allow = TRUE')
                ->setParameter('user', $this->identity());

            $sections->join('account.permissions', 'permission')
                ->andWhere('permission.user = :user AND (permission.credential IS NULL OR permission.credential = credential) AND permission.allow = TRUE')
                ->setParameter('user', $this->identity());

            $credentials->join('credential.account', 'account')
                ->join('account.permissions', 'permission')
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
                            case 'a':
                            case 'account':
                                $accounts->andWhere('account.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                break;
                            case 's':
                            case 'section':
                                $sections->andWhere('section.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                $accounts->andWhere('section.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                break;
                            case 't':
                            case 'tag':
                                $accounts->leftJoin('account.tags', 'accountTag')
                                    ->leftJoin('credential.tags', 'credentialTag')
                                    ->andWhere('accountTag.name LIKE :cmd_' . $i . ' OR credentialTag.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                $sections->leftJoin('account.tags', 'accountTag')
                                    ->leftJoin('credential.tags', 'credentialTag')
                                    ->andWhere('accountTag.name LIKE :cmd_' . $i . ' OR credentialTag.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                $credentials
                                    ->leftJoin('credential.account', 'account')
                                    ->leftJoin('account.tags', 'accountTag')
                                    ->leftJoin('credential.tags', 'credentialTag')
                                    ->andWhere('(NOT account.id IS NULL AND accountTag.name LIKE :cmd_' . $i . ') OR credentialTag.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($eval[1]) . '%');
                                break;
                        }
                    } else {
                        $credentials->andWhere('credential.name LIKE :cmd_' . $i)->setParameter('cmd_' . $i, '%' . trim($cmd) . '%');
                    }
                }
            } else {
                $accounts->andWhere('credential.name LIKE :q')->setParameter('q', '%' . $q . '%');
                $sections->andWhere('credential.name LIKE :q')->setParameter('q', '%' . $q . '%');
                $credentials->andWhere('credential.name LIKE :q')->setParameter('q', '%' . $q . '%');
            }
        }

        /** @var Account[] $accounts */
        $accounts = $accounts->getQuery()->getResult();
        $credentialsByAccount = [];
        foreach ($accounts as $account) {
            $sections->setParameter('account', $account);
            $account->setSections($sections->getQuery()->getResult());
            $credentialsByAccount[$account->getId()] = [];

//            $c = $this->getCredentialRepository()->createQueryBuilder('credential')
//                ->where('credential.account = :account AND credential.section IS NULL AND credential.deletedAt IS NULL')
//                ->addOrderBy('credential.name', 'ASC');
//            $c->setParameter('account', $account);
//            $account->setCredentials($c->getQuery()->getResult());
            foreach ($account->getSections() as $j => $section) {
                $credentials->setParameter('account', $account)
                    ->setParameter('section', $section);
                $credentialsByAccount[$account->getId()][$section->getId()] = $credentials->getQuery()->getResult();
            }
        }

        $viewModel = new ViewModel(compact(
            'accounts',
            'credentialsByAccount'
        ));

        return $viewModel;
    }
}
