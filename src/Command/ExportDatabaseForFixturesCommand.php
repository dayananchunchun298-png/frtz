<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:export-db-for-fixtures',
    description: 'Export database rows as JSON (for fixture generation)',
)]
class ExportDatabaseForFixturesCommand extends Command
{
    private const TABLES = [
        'users' => 'app_user',
        'products' => 'product',
        'services' => 'service',
        'appointments' => 'appointment',
        'orders' => '`order`',
        'order_items' => 'order_item',
        'payments' => 'payment',
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [];
        foreach (self::TABLES as $key => $table) {
            $data[$key] = $this->connection->fetchAllAssociative("SELECT * FROM {$table}");
        }

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }
}
