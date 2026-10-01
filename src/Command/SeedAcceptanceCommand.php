<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\AcceptanceFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:acceptance:seed', description: 'Reset disposable demo schemas and seed acceptance fixtures.')]
final class SeedAcceptanceCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = AcceptanceFixtures::reset($this->registry);
        $output->writeln(json_encode($ids, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
