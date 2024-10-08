<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240124035817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_address_entity_shop_entity_address_entity_id');
        $this->addSql('DROP INDEX idx_address_entity_shop_entity_shop_entity_id');
        $this->addSql('DROP INDEX idx_address_to_address_entity_address_id');
        $this->addSql('DROP INDEX idx_address_to_address_entity_parent_id');

        $this->addSql('CREATE UNIQUE INDEX idx_address_entity_external_id ON address_entity(external_id)');
        $this->addSql('CREATE UNIQUE INDEX idx_address_level_entity_external_id ON address_level_entity(external_id)');
        $this->addSql('CREATE UNIQUE INDEX idx_address_to_address_entity_address_parent ON address_to_address_entity(address_id, parent_id)');
        $this->addSql('CREATE UNIQUE INDEX idx_shop_entity_external_id ON shop_entity(external_id)');
        $this->addSql('CREATE UNIQUE INDEX idx_shop_entity_xml_id ON shop_entity(xml_id)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_address_entity_external_id');
        $this->addSql('DROP INDEX idx_address_level_entity_external_id');
        $this->addSql('DROP INDEX idx_address_to_address_entity_address_parent');
        $this->addSql('DROP INDEX idx_shop_entity_external_id');
        $this->addSql('DROP INDEX idx_shop_entity_xml_id');

        $this->addSql('CREATE INDEX idx_address_entity_shop_entity_address_entity_id ON address_entity_shop_entity (address_entity_id)');
        $this->addSql('CREATE INDEX idx_address_entity_shop_entity_shop_entity_id ON address_entity_shop_entity (shop_entity_id)');
        $this->addSql('CREATE INDEX idx_address_to_address_entity_address_id ON address_to_address_entity (address_id)');
        $this->addSql('CREATE INDEX idx_address_to_address_entity_parent_id ON address_to_address_entity (parent_id)');

    }
}
