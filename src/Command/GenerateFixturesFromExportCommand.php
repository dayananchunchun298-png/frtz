<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate-fixtures-from-export',
    description: 'Generate DataFixtures PHP files from var/db_export.json',
)]
class GenerateFixturesFromExportCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'JSON export path', 'var/db_export.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = dirname(__DIR__, 2);
        $path = $projectDir.'/'.$input->getOption('input');
        if (!is_readable($path)) {
            $output->writeln("<error>Cannot read {$path}</error>");

            return Command::FAILURE;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $output->writeln("<error>Cannot read {$path}</error>");

            return Command::FAILURE;
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }

        /** @var array<string, list<array<string, mixed>>> $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $fs = new Filesystem();
        $dir = $projectDir.'/src/DataFixtures';

        $files = [
            'UserFixtures.php' => $this->renderUserFixtures($data['users'] ?? []),
            'ProductFixtures.php' => $this->renderProductFixtures($data['products'] ?? []),
            'ServiceFixtures.php' => $this->renderServiceFixtures($data['services'] ?? []),
            'AppointmentFixtures.php' => $this->renderAppointmentFixtures($data['appointments'] ?? []),
            'OrderFixtures.php' => $this->renderOrderFixtures($data['orders'] ?? []),
            'OrderItemFixtures.php' => $this->renderOrderItemFixtures($data['order_items'] ?? []),
            'PaymentFixtures.php' => $this->renderPaymentFixtures($data['payments'] ?? []),
            'AppFixtures.php' => $this->renderAppFixtures(),
        ];

        foreach ($files as $name => $contents) {
            $fs->dumpFile($dir.'/'.$name, $contents);
            $output->writeln("Wrote {$name}");
        }

        return Command::SUCCESS;
    }

    private function renderAppFixtures(): string
    {
        return <<<'PHP'
<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Entry point: loads all exported local database fixtures in dependency order.
 * Run: php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Entity fixtures in group "local-export" perform all persistence.
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\UserFixtures::class,
            \App\DataFixtures\ProductFixtures::class,
            \App\DataFixtures\ServiceFixtures::class,
            \App\DataFixtures\AppointmentFixtures::class,
            \App\DataFixtures\OrderFixtures::class,
            \App\DataFixtures\OrderItemFixtures::class,
            \App\DataFixtures\PaymentFixtures::class,
        ];
    }
}

PHP;
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderUserFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $roles = $this->parseRoles($row['roles']);
            $items[] = sprintf(
                "            ['ref' => %s, 'email' => %s, 'roles' => %s, 'password' => %s, 'isVerified' => %s, 'verificationToken' => %s, 'googleId' => %s],\n",
                $this->ref((int) $row['id'], 'user'),
                $this->str($row['email']),
                $this->exportArray($roles),
                $this->str($row['password']),
                (int) $row['is_verified'] ? 'true' : 'false',
                $this->nullableStr($row['verification_token']),
                $this->nullableStr($row['google_id']),
            );
        }

        return $this->wrapFixture(
            className: 'UserFixtures',
            entityFqn: 'App\\Entity\\User',
            entityShort: 'User',
            uses: [],
            dependencies: [],
            loadBody: $this->loadLoop('User', 'user', $items, <<<'LOAD'
                $entity->setEmail($row['email']);
                $entity->setRoles($row['roles']);
                $entity->setPassword($row['password']);
                $entity->setIsVerified($row['isVerified']);
                $entity->setVerificationToken($row['verificationToken']);
                $entity->setGoogleId($row['googleId']);
            LOAD),
            rowsConst: 'ROWS',
            rows: implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderProductFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = sprintf(
                "            ['ref' => %s, 'name' => %s, 'description' => %s, 'price' => %s, 'category' => %s, 'petType' => %s, 'stock' => %d, 'image' => %s, 'isActive' => %s, 'createdAt' => %s, 'updatedAt' => %s],\n",
                $this->ref((int) $row['id'], 'product'),
                $this->str($row['name']),
                $this->nullableStr($row['description']),
                $this->str($row['price']),
                $this->str($row['category']),
                $this->str($row['pet_type']),
                (int) $row['stock'],
                $this->nullableStr($row['image']),
                (int) $row['is_active'] ? 'true' : 'false',
                $this->date($row['created_at']),
                $this->nullableDate($row['updated_at']),
            );
        }

        return $this->wrapFixture(
            'ProductFixtures',
            'App\\Entity\\Product',
            'Product',
            [],
            [],
            $this->loadLoop('Product', 'product', $items, <<<'LOAD'
                $entity->setName($row['name']);
                $entity->setDescription($row['description']);
                $entity->setPrice($row['price']);
                $entity->setCategory($row['category']);
                $entity->setPetType($row['petType']);
                $entity->setStock($row['stock']);
                $entity->setImage($row['image']);
                $entity->setIsActive($row['isActive']);
                $entity->setCreatedAt(new \DateTime($row['createdAt']));
                if ($row['updatedAt'] !== null) {
                    $entity->setUpdatedAt(new \DateTime($row['updatedAt']));
                }
            LOAD),
            'ROWS',
            implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderServiceFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = sprintf(
                "            ['ref' => %s, 'name' => %s, 'description' => %s, 'price' => %s, 'petType' => %s, 'isActive' => %s, 'createdAt' => %s, 'updatedAt' => %s],\n",
                $this->ref((int) $row['id'], 'service'),
                $this->str($row['name']),
                $this->nullableStr($row['description']),
                $this->str($row['price']),
                $this->str($row['pet_type']),
                (int) $row['is_active'] ? 'true' : 'false',
                $this->date($row['created_at']),
                $this->nullableDate($row['updated_at']),
            );
        }

        return $this->wrapFixture(
            'ServiceFixtures',
            'App\\Entity\\Service',
            'Service',
            [],
            [],
            $this->loadLoop('Service', 'service', $items, <<<'LOAD'
                $entity->setName($row['name']);
                $entity->setDescription($row['description']);
                $entity->setPrice($row['price']);
                $entity->setPetType($row['petType']);
                $entity->setIsActive($row['isActive']);
                $entity->setCreatedAt(new \DateTime($row['createdAt']));
                if ($row['updatedAt'] !== null) {
                    $entity->setUpdatedAt(new \DateTime($row['updatedAt']));
                }
            LOAD),
            'ROWS',
            implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderAppointmentFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $petType = (string) ($row['pet_type'] ?? '');
            if ($petType === '') {
                $petType = 'dog';
            }
            $items[] = sprintf(
                "            ['ref' => %s, 'name' => %s, 'appointmentDate' => %s, 'petType' => %s, 'userRef' => %s],\n",
                $this->ref((int) $row['id'], 'appointment'),
                $this->str($row['name']),
                $this->date($row['appointment_date']),
                $this->str($petType),
                $row['user_id'] !== null ? $this->ref((int) $row['user_id'], 'user') : 'null',
            );
        }

        $body = <<<'LOAD'
                $entity->setName($row['name']);
                $entity->setAppointmentDate(new \DateTime($row['appointmentDate']));
                $entity->setPetType($row['petType']);
                if ($row['userRef'] !== null) {
                    $entity->setUser($this->getReference($row['userRef'], User::class));
                }
            LOAD;

        return $this->wrapFixture(
            'AppointmentFixtures',
            'App\\Entity\\Appointment',
            'Appointment',
            ['App\\Entity\\User'],
            ['App\DataFixtures\UserFixtures'],
            $this->loadLoop('Appointment', 'appointment', $items, $body),
            'ROWS',
            implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderOrderFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = sprintf(
                "            ['ref' => %s, 'createdAt' => %s, 'subtotal' => %s, 'tax' => %s, 'total' => %s, 'status' => %s, 'userRef' => %s],\n",
                $this->ref((int) $row['id'], 'order'),
                $this->date($row['created_at']),
                $this->str($row['subtotal']),
                $this->str($row['tax']),
                $this->str($row['total']),
                $this->str($row['status']),
                $row['user_id'] !== null ? $this->ref((int) $row['user_id'], 'user') : 'null',
            );
        }

        $body = <<<'LOAD'
                $entity->setCreatedAt(new \DateTime($row['createdAt']));
                $entity->setSubtotal($row['subtotal']);
                $entity->setTax($row['tax']);
                $entity->setTotal($row['total']);
                $entity->setStatus($row['status']);
                if ($row['userRef'] !== null) {
                    $entity->setUser($this->getReference($row['userRef'], User::class));
                }
            LOAD;

        return $this->wrapFixture(
            'OrderFixtures',
            'App\\Entity\\Order',
            'Order',
            ['App\\Entity\\User'],
            ['App\DataFixtures\UserFixtures'],
            $this->loadLoop('Order', 'order', $items, $body),
            'ROWS',
            implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderOrderItemFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = sprintf(
                "            ['ref' => %s, 'orderRef' => %s, 'productRef' => %s, 'quantity' => %d, 'unitPrice' => %s, 'subtotal' => %s],\n",
                $this->ref((int) $row['id'], 'order_item'),
                $this->ref((int) $row['order_id'], 'order'),
                $this->ref((int) $row['product_id'], 'product'),
                (int) $row['quantity'],
                $this->str($row['unit_price']),
                $this->str($row['subtotal']),
            );
        }

        $body = <<<'LOAD'
                $order = $this->getReference($row['orderRef'], Order::class);
                $entity->setOrder($order);
                $entity->setProduct($this->getReference($row['productRef'], Product::class));
                $entity->setQuantity($row['quantity']);
                $entity->setUnitPrice($row['unitPrice']);
                $entity->setSubtotal($row['subtotal']);
                $order->addItem($entity);
            LOAD;

        return $this->wrapFixture(
            'OrderItemFixtures',
            'App\\Entity\\OrderItem',
            'OrderItem',
            ['App\\Entity\\Order', 'App\\Entity\\Product'],
            ['App\DataFixtures\OrderFixtures', 'App\DataFixtures\ProductFixtures'],
            $this->loadLoop('OrderItem', 'order_item', $items, $body, false),
            'ROWS',
            implode('', $items),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function renderPaymentFixtures(array $rows): string
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = sprintf(
                "            ['ref' => %s, 'orderRef' => %s, 'amount' => %s, 'method' => %s, 'status' => %s, 'transactionReference' => %s, 'createdAt' => %s],\n",
                $this->ref((int) $row['id'], 'payment'),
                $this->ref((int) $row['order_id'], 'order'),
                $this->str($row['amount']),
                $this->str($row['method']),
                $this->str($row['status']),
                $this->nullableStr($row['transaction_reference']),
                $this->date($row['created_at']),
            );
        }

        $body = <<<'LOAD'
                $order = $this->getReference($row['orderRef'], Order::class);
                $entity->setOrder($order);
                $entity->setAmount($row['amount']);
                $entity->setMethod($row['method']);
                $entity->setStatus($row['status']);
                $entity->setTransactionReference($row['transactionReference']);
                $order->addPayment($entity);
            LOAD;

        return $this->wrapFixture(
            'PaymentFixtures',
            'App\\Entity\\Payment',
            'Payment',
            ['App\\Entity\\Order'],
            ['App\DataFixtures\OrderFixtures'],
            $this->loadLoop('Payment', 'payment', $items, $body, false),
            'ROWS',
            implode('', $items),
        );
    }

    /**
     * @param list<string> $uses
     * @param list<class-string> $dependencies
     */
    private function wrapFixture(
        string $className,
        string $entityFqn,
        string $entityShort,
        array $uses,
        array $dependencies,
        string $loadBody,
        string $rowsConst,
        string $rows,
    ): string {
        $useLines = array_merge(
            [
                'Doctrine\\Bundle\\FixturesBundle\\Fixture',
                'Doctrine\\Bundle\\FixturesBundle\\FixtureGroupInterface',
                'Doctrine\\Persistence\\ObjectManager',
                $entityFqn,
            ],
            $dependencies !== [] ? ['Doctrine\\Common\\DataFixtures\\DependentFixtureInterface'] : [],
            $uses,
        );
        $useLines = array_unique($useLines);
        sort($useLines);
        $useBlock = '';
        foreach ($useLines as $use) {
            $useBlock .= "use {$use};\n";
        }

        $implements = ['FixtureGroupInterface'];
        $depsBlock = '';
        if ($dependencies !== []) {
            $implements[] = 'DependentFixtureInterface';
            $depLines = implode(",\n            ", array_map(
                static fn (string $class) => '\\'.$class.'::class',
                $dependencies,
            ));
            $depsBlock = <<<PHP

    public function getDependencies(): array
    {
        return [
            {$depLines},
        ];
    }
PHP;
        }

        $implementsList = implode(', ', $implements);

        return <<<PHP
<?php

namespace App\DataFixtures;

{$useBlock}
/**
 * Exported from local database on 2026-05-22.
 */
class {$className} extends Fixture implements {$implementsList}
{
    private const GROUP = 'local-export';

    private const {$rowsConst} = [
{$rows}    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager \$manager): void
    {
{$loadBody}        \$manager->flush();
    }
{$depsBlock}
}

PHP;
    }

    private function loadLoop(
        string $entityShort,
        string $refPrefix,
        array $items,
        string $setterBlock,
        bool $addReference = true,
    ): string {
        $refLine = $addReference
            ? "            \$this->addReference(\$row['ref'], \$entity, {$entityShort}::class);\n"
            : '';

        $indent = str_repeat(' ', 12);
        $setter = '';
        foreach (explode("\n", trim($setterBlock)) as $line) {
            $setter .= $indent.$line."\n";
        }

        return <<<PHP
        foreach (self::ROWS as \$row) {
            \$entity = new {$entityShort}();
{$setter}{$refLine}            \$manager->persist(\$entity);
        }

PHP;
    }

    private function ref(int $id, string $prefix): string
    {
        return var_export(sprintf('%s_%d', $prefix, $id), true);
    }

    private function str(string $value): string
    {
        return var_export($value, true);
    }

    private function nullableStr(mixed $value): string
    {
        return $value === null || $value === '' ? 'null' : $this->str((string) $value);
    }

    private function date(string $value): string
    {
        return $this->str($value);
    }

    private function nullableDate(mixed $value): string
    {
        return $value === null || $value === '' ? 'null' : $this->str((string) $value);
    }

    /** @return list<string> */
    private function parseRoles(mixed $roles): array
    {
        if (is_array($roles)) {
            return $roles;
        }

        $decoded = json_decode((string) $roles, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @param list<string> $values */
    private function exportArray(array $values): string
    {
        if ($values === []) {
            return '[]';
        }

        return '['.implode(', ', array_map(fn (string $v) => var_export($v, true), $values)).']';
    }
}
