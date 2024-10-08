<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231213140742 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_group ADD COLUMN updated_at timestamp DEFAULT NOW()');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_group DROP COLUMN updated_at');
    }
}
