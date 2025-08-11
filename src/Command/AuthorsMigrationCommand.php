<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Command\MigrateAuthors\MigrateAuthorsCommand;
use App\Service\Messenger\MessageBusWrapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:migrate-authors', description: 'Migrate authors command')]
final class AuthorsMigrationCommand extends Command
{

    /**
     * @param MessageBusWrapper $bus
     */
    public function __construct(private readonly MessageBusWrapper $bus)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Migrating authors...');

        $result = $this->bus->dispatchWithResult(new MigrateAuthorsCommand());

        $io->success($result['created'] . ' authors created');
        $io->success($result['linked'] . ' authors linked');

        return Command::SUCCESS;
    }
}
