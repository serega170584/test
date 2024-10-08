<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623144822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_group DROP CONSTRAINT FK_AAD61E97727ACA70');
        $this->addSql(
            'ALTER TABLE shop_group ADD CONSTRAINT FK_AAD61E97727ACA70 FOREIGN KEY (parent_id) REFERENCES shop_group (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_group DROP CONSTRAINT fk_aad61e97727aca70');
        $this->addSql(
            'ALTER TABLE shop_group ADD CONSTRAINT fk_aad61e97727aca70 FOREIGN KEY (parent_id) REFERENCES shop_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
