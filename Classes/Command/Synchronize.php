<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Command;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Utilities\SynchronizationUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class Synchronize extends Command
{
    /**
     * Synchronize constructor.
     * @param string $name
     * @param SynchronizationUtility $synchronizationUtility
     * @param PersistenceManager $persistenceManager
     * @param ConnectionPool $connectionPool
     * @param DataMapper $dataMapper
     */
    public function __construct(
        string $name,
        protected readonly SynchronizationUtility $synchronizationUtility,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly ConnectionPool $connectionPool,
        protected readonly DataMapper $dataMapper
    ) {
        parent::__construct($name);
    }

    /**
     * Konfiguriert das Kommando.
     */
    protected function configure()
    {
        $this->setDescription('Synchronizes latest posts from accounts.')
            ->addArgument(
                'amount',
                InputArgument::OPTIONAL,
                'Amount of Social Networks to synchronize, zero for no limit.'
            );
    }

    /**
     * Führt das Kommando aus und synchronisiert Accounts.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf('<info>Start synchronization for accounts...</info>'));

        /** @var Account $account */
        foreach ($this -> getAccounts() as $account) {
            // Execution Time hochsetzen, denn das einlesen kann dauern!
            set_time_limit(240);

            $output->writeln(sprintf('-----------------------------------------------------------'));
            $output->writeln(sprintf("Start synchronization for account '%s' of network '%s'", $account -> getUid(), $account -> getNetwork()));

            // Account synchronisieren
            try {
                $this -> synchronizationUtility -> account($account);
                $output->writeln(sprintf('<info>Synchronization completed!</info>'));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('<error>Synchronization failed: %s</error>', $e -> getMessage()));
            }

            // Persists now directly, so if timeout is coming, you don't lose all progress.
            $this -> persistenceManager -> persistAll();
        }

        $output->writeln(sprintf('-----------------------------------------------------------'));
        $output->writeln(sprintf('Accounts have been synchronized.'));
        return 0;
    }

    protected function getAccounts(): array
    {
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('tx_fpsocial_domain_model_account');
        $result = $queryBuilder
            -> select('*')
            -> from('tx_fpsocial_domain_model_account')
            -> where(
                $queryBuilder ->  expr() -> eq('synchronize', 1)
            )
            -> executeQuery();
        $allAccounts = $result -> fetchAllAssociative();

        $relevantAccounts = [];
        /** @var array $account */
        foreach ($allAccounts as $account) {
            $nextUpdate = \DateTime::createFromFormat('Y-m-d H:i:s', $account['last_synchronization']);

            if (
                $nextUpdate === false ||
                !($account['synchronization_interval'] ?? null) ||
                $nextUpdate -> add(new \DateInterval($account['synchronization_interval'])) < new \DateTime()
            ) {
                $relevantAccounts[] = $this -> dataMapper -> map(Account::class, [$account])[0];
            }
        }
        return $relevantAccounts;
    }
}
