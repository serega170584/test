<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503125516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql('DROP INDEX uniq_705b3727a689f3fa');
        $this->addSql('ALTER TABLE consumer DROP client_code');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer ADD client_code VARCHAR(50) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_705b3727a689f3fa ON consumer (client_code)');
    }
}
