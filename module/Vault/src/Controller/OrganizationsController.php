<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Controller;

use Doctrine\ORM\EntityManager;
use Jenssegers\Date\Date;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zetta\DoctrineUtil\Paginator\Paginator;
use Zetta\Vault\Entity\AccountType;
use Zetta\Vault\Entity\AccountBalance;
use Zetta\Vault\Entity\Section;
use Zetta\Vault\Entity\Repository\BalancoRepository;
use Zetta\Vault\Entity\Repository\TransactionRepository;
use Zetta\Vault\Entity\Organization;
use Zetta\Vault\Form\TransactionForm;
use Zetta\ZendBootstrap\Form\SearchForm;

class OrganizationsController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * DashboardController constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function indexAction()
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Organization::class);
        /** @var BalancoRepository $balancoRepository */
        $balancoRepository = $this->entityManager->getRepository(AccountBalance::class);

        $tipo = $this->type();

        // Session save data
        $container = new Container();
        if ($container->offsetExists('data')) {
            $data = $container->offsetGet('data');
        } else {
            $data = Date::now()->startOfMonth();
        }

        $mes = $this->params()->fromQuery('mes');
        if (!is_null($mes)) {
            $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
            $container->offsetSet('data', $periodo);
        } else {
            $periodo = $data;
        }
        $nextPeriodo = $periodo->copy()->endOfMonth();
        $conta = AccountType::DEFAULT;
        $balanco = $balancoRepository->update($periodo, $conta);
        $receita = [
            'pago' => $transactionRepository->sumValorByTipo($tipo, $periodo, $nextPeriodo, $conta),
            'falta' => $transactionRepository->sumValorByTipo($tipo, $periodo, $nextPeriodo, $conta, false),
        ];

        $qb = $transactionRepository->createQueryBuilder('m')
            ->where('m.conta = :conta AND m.tipo = :tipo AND m.active = :active')
            ->andWhere('(m.data >= :datai AND m.data <= :dataf) OR (m.repete = :repete AND m.parcela = :parcela AND m.data <= :dataf)')
            ->setParameters([
                'conta' => $conta,
                'tipo' => $tipo,
                'active' => true,
                'datai' => $periodo,
                'dataf' => $nextPeriodo,
                'repete' => Organization::REPETE_FIXA,
                'parcela' => Organization::PARCELA_FIXA,
            ])
            ->orderBy('DAY(m.data)', 'ASC')
            ->addOrderBy('m.descricao', 'ASC');

        $q = $this->params()->fromQuery('q');
        if (!empty($q)) {
            $qb->join('m.pessoa', 'p')
                ->join('m.categoria', 'c')
                ->andWhere('m.descricao LIKE :q OR m.detalhe LIKE :q OR p.firstName LIKE :q OR c.nome LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }


        // Paginator
        $transactions = new Paginator($qb);
        $transactions->setDefaultItemCountPerPage(25);
        $page = (int)$this->params()->fromQuery('page');
        if ($page) {
            $transactions->setCurrentPageNumber($page);
        }

        $searchForm = new SearchForm();
        $searchForm->setAttribute('class', 'form-inline search-top');
        $searchForm->get('q')->setAttribute('placeholder', _('Buscar Transaction'));
        $searchForm->add([
            'name' => 'mes',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);
        $searchForm->setData($this->params()->fromQuery());
        $searchForm->prepare();

        $viewModel = new ViewModel([
            'transactions' => $transactions,
            'searchForm' => $searchForm,
            'tipo' => $tipo,
            'periodo' => $periodo,
            'mes' => $periodo->format('m/Y'),
            'receita' => $receita
        ]);

        return $viewModel;
    }

    public function addAction()
    {
        $tipo = $this->type();
        $form = new TransactionForm($this->entityManager);
        $form->setAttribute('action', $this->url()->fromRoute('home/default', ['controller' => 'transaction', 'tipo' => $tipo, 'action' => 'add']));

        $transaction = new Organization();
        $form->bind($transaction);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData(UserForm::VALUES_AS_ARRAY);
                $credential = new Credential();
                $credential->setType(Credential::TYPE_PASSWORD);
                $credential->setValue($data['transaction']['password']);
                $credential->hashValue();
                $credential->setUser($transaction);

                if ($transaction->getGender() === Gender::FEMALE) {
                    $transaction->setAvatar($this->thumbnail()->getGirlThumbnailPath());
                } else {
                    $transaction->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
                }
                $transaction->setSignAllowed(true);
                $transaction->generateToken();

                $this->entityManager->persist($credential);
                $this->entityManager->persist($transaction);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(_('The transaction has been added.'));
                return $this->redirect()->toRoute('home/default', ['controller' => 'users']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The transaction could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel ([
            'tipo' => $tipo,
            'transaction' => $transaction,
            'form' => $form,
        ]);
        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function viewAction()
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Organization::class);
        /** @var BalancoRepository $balancoRepository */
        $balancoRepository = $this->entityManager->getRepository(AccountBalance::class);

        $id = $this->params()->fromRoute('id', 0);
        $tipo = $this->type();
        $mes = $this->params()->fromQuery('mes');

        $qb = $transactionRepository->createQueryBuilder('transaction');
        $qb->where('transaction = :transaction AND transaction.active = :active');
        $qb->setParameter('transaction', $id);
        $qb->setParameter('active', true);
        /** @var Organization $transaction */
        $transaction = $qb->getQuery()->getOneOrNullResult();
        if ($transaction === null) {
            $this->flashMessenger()->addErrorMessage(_('The transaction could not be found.'));
            return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
        }

        if ($transaction->getRepete() === Organization::REPETE_FIXA
            && $transaction->getParcela() === Organization::PARCELA_FIXA
            && $mes != null) {
            $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
            if ($periodo <= $transaction->getDate()) {
                return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'view', 'id' => $id]);
            }
        } else {
            $periodo = $transaction->getDate()->startOfMonth();
        }
        $conta = AccountType::DEFAULT;
        $balanco = $balancoRepository->update($periodo, $conta);

        $viewModel = new ViewModel([
            'tipo' => $tipo,
            'periodo' => $periodo,
            'mes' => $periodo->format('m/Y'),
            'transaction' => $transaction,
        ]);

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function deleteAction()
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Organization::class);
        /** @var BalancoRepository $balancoRepository */
        $balancoRepository = $this->entityManager->getRepository(AccountBalance::class);

        $id = $this->params()->fromRoute('id', 0);
        $tipo = $this->type();
        $mes = $this->params()->fromQuery('mes');
        $conta = AccountType::DEFAULT;

        $qb = $transactionRepository->createQueryBuilder('transaction');
        $qb->where('transaction = :transaction AND transaction.active = :active');
        $qb->setParameter('transaction', $id);
        $qb->setParameter('active', true);
        /** @var Organization $transaction */
        $transaction = $qb->getQuery()->getOneOrNullResult();
        if ($transaction !== null) {
            if ($transaction->getRepete() === Organization::REPETE_FIXA
                && $transaction->getParcela() === Organization::PARCELA_FIXA
                && $mes != null) {
                $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
                if ($periodo <= $transaction->getDate()) {
                    return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'view', 'id' => $id]);
                }
            } else {
                $periodo = $transaction->getDate()->startOfMonth();
            }

            $request = $this->getRequest();
            if ($request->isDelete() || $request->isPost()) {
                if ($transaction->getRepete() === Organization::REPETE_NAO) {
                    $opcoes = 0;
                } else {
                    $opcoes = (int)$this->params()->fromPost('opcoes', 0);
                }

                if ($opcoes === 1) {
                    $transaction = $transactionRepository->build($transaction, $periodo);
                    $transactions = $transactionRepository->findParcelas($transaction);
                } else if ($opcoes === 2) {
                    $transaction = $transactionRepository->build($transaction, $periodo, false);
                    $transactions = $transactionRepository->findParcelas($transaction, true);
                } else {
                    $transactions = [$transaction];
                }

                $oldest = $periodo;
                foreach ($transactions as $m) {
                    $transaction->setActive(false);
                    // Older periodo
                    if ($m->getDate() < $oldest) {
                        $oldest = $m->getDate();
                    }
                }

                $balancoRepository->atualizarBalancos($oldest, $conta);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('The transaction has been deleted.'));
                return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
            }

            $viewModel = new ViewModel([
                'tipo' => $tipo,
                'periodo' => $periodo,
                'mes' => $periodo->format('m/Y'),
                'transaction' => $transaction,
            ]);
        } else {
            $etapa = (int)$this->params()->fromPost('etapa', 0);
            if ($etapa !== 0) {
                $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
                $ids = json_decode($this->params()->fromPost('transactions', '[0]'));

                $qb = $transactionRepository->createQueryBuilder('transaction')
                    ->where('transaction.id IN(:transactions)')
                    ->andWhere('transaction.conta = :conta AND transaction.tipo = :tipo AND transaction.active = :active')
                    ->setParameters([
                        'transactions' => $ids,
                        'conta' => $conta,
                        'tipo' => $tipo,
                        'active' => true,
                    ])
                    ->orderBy('DAY(transaction.data)', 'ASC')
                    ->addOrderBy('transaction.descricao', 'ASC');
                /** @var Organization[] $transactions */
                $transactions = $qb->getQuery()->getResult();

                if ($etapa === 2) {
                    $confirm = ((int)$this->params()->fromPost('confirm', 0)) === 1;
                    if ($confirm) {
                        $oldest = $periodo;
                        foreach ($transactions as $m) {
                            $m->setActive(false);
                            // Older periodo
                            if ($m->getDate() < $oldest) {
                                $oldest = $m->getDate();
                            }
                        }

                        $balancoRepository->atualizarBalancos($oldest, $conta);
                        $this->entityManager->flush();
                        $this->flashMessenger()->addSuccessMessage(_('The transaction has been deleted.'));
                        return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
                    } else {
                        $this->flashMessenger()->addErrorMessage(_('The transaction could not be deleted. Please, try again.'));
                    }
                }

                $viewModel = new ViewModel([
                    'transactions' => $transactions,
                    'tipo' => $tipo,
                    'periodo' => $periodo,
                    'mes' => $periodo->format('m/Y'),
                    'ids' => json_encode($ids),
                ]);
                $viewModel->setTemplate('zetta/vault/transactions/delete-list');

                return $viewModel;
            } else {
                $this->flashMessenger()->addErrorMessage(_('The transaction could not be found.'));
                return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
            }
        }

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function payAction()
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Organization::class);
        /** @var BalancoRepository $balancoRepository */
        $balancoRepository = $this->entityManager->getRepository(AccountBalance::class);

        $id = $this->params()->fromRoute('id', 0);
        $tipo = $this->type();
        $mes = $this->params()->fromQuery('mes');
        $conta = AccountType::DEFAULT;

        $qb = $transactionRepository->createQueryBuilder('transaction');
        $qb->where('transaction = :transaction AND transaction.active = :active');
        $qb->setParameter('transaction', $id);
        $qb->setParameter('active', true);
        /** @var Organization $transaction */
        $transaction = $qb->getQuery()->getOneOrNullResult();
        if ($transaction !== null) {
            if ($mes !== null) {
                $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
            } else {
                $periodo = $transaction->getDate()->copy()->startOfMonth();
            }

            $transactionRepository->pay($transaction, $periodo);
            $balancoRepository->atualizarBalancos($periodo, $conta);
            $this->entityManager->flush();
            if ($transaction->isPago()) {
                $this->flashMessenger()->addSuccessMessage(_('Pagamento realizado com sucesso.'));
            } else {
                $this->flashMessenger()->addInfoMessage(_('Pagamento cancelado com sucesso.'));
            }
            return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
        } else {
            $etapa = (int)$this->params()->fromPost('etapa', 0);
            if ($etapa !== 0) {
                $periodo = Date::createFromFormat('d/m/Y', '01/' . $mes)->startOfDay();
                $ids = json_decode($this->params()->fromPost('transactions', '[0]'));

                $qb = $transactionRepository->createQueryBuilder('transaction')
                    ->where('transaction.id IN(:transactions)')
                    ->andWhere('transaction.conta = :conta AND transaction.tipo = :tipo AND transaction.active = :active')
                    ->setParameters([
                        'transactions' => $ids,
                        'conta' => $conta,
                        'tipo' => $tipo,
                        'active' => true,
                    ])
                    ->orderBy('DAY(transaction.data)', 'ASC')
                    ->addOrderBy('transaction.descricao', 'ASC');
                /** @var Organization[] $transactions */
                $transactions = $qb->getQuery()->getResult();

                if ($etapa === 2) {
                    $pago = ((int)$this->params()->fromPost('pago', 0)) === 1;
                    $oldest = $periodo;
                    foreach ($transactions as $m) {
                        $transactionRepository->pay($m, $periodo, $pago);
                        // Older periodo
                        if ($m->getDate() < $oldest) {
                            $oldest = $m->getDate();
                        }
                    }

                    $balancoRepository->atualizarBalancos($oldest, $conta);
                    $this->entityManager->flush();
                    if ($pago) {
                        $this->flashMessenger()->addSuccessMessage(_('Pagamento realizado com sucesso.'));
                    } else {
                        $this->flashMessenger()->addInfoMessage(_('Pagamento cancelado com sucesso.'));
                    }
                    return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
                }

                $viewModel = new ViewModel([
                    'transactions' => $transactions,
                    'tipo' => $tipo,
                    'periodo' => $periodo,
                    'mes' => $periodo->format('m/Y'),
                    'ids' => json_encode($ids),
                ]);
                $viewModel->setTemplate('zetta/vault/transactions/pay-list');

                return $viewModel;
            } else {
                $this->flashMessenger()->addErrorMessage(_('The transaction could not be found.'));
                return $this->redirect()->toRoute('vault/transactions', ['tipo' => 'receitas', 'action' => 'index']);
            }
        }
    }

    /**
     * @param string $type
     * @return int
     */
    protected function type($type = null)
    {
        if ($type === null) {
            $type = $this->params()->fromRoute('type', 'receita');
        }
        foreach (Section::TYPES as $i => $t) {
            if ($type == mb_strtolower($t) . 's') {
                return $i;
            }
        }

        return Section::TIPO_RECEITA;
    }
}
