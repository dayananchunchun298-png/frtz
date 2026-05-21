<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email verification fields to app_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD is_verified TINYINT(1) NOT NULL DEFAULT 0, ADD verification_token VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user DROP verification_token, DROP is_verified');
    }
}

