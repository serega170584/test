<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503094744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_group ADD COLUMN is_distr BOOLEAN DEFAULT FALSE;');
        $this->addSql('ALTER TABLE shop_entity ADD COLUMN is_distr BOOLEAN DEFAULT FALSE;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_group DROP COLUMN is_distr;');
        $this->addSql('ALTER TABLE shop_entity DROP COLUMN is_distr;');
    }
}
