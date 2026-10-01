<?php

declare(strict_types=1);

namespace App\Torture\Command;

use App\Torture\Infrastructure\TortureFixtures;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:torture:seed', description: 'Reset isolated torture databases; small=100, medium=10k, large=100k tasks.')]
final class SeedTortureCommand extends Command
{
    public function __construct(private readonly TortureFixtures $fixtures) { parent::__construct(); }
    protected function configure(): void
    {
        $this->addArgument('size', InputArgument::OPTIONAL, 'small, medium, large', 'small');
        $this->addOption('rows', null, InputOption::VALUE_REQUIRED, 'Override task count (10..1,000,000)');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = $this->fixtures->reset($input->getArgument('size'), $input->getOption('rows') === null ? null : (int) $input->getOption('rows'));
        $output->writeln(json_encode($ids, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}
