<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231102112913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO consumer(code, name, description) VALUES('pharma_clients', 'клиенты использующие виртуальные группы', 'Сайт в дальнейшем планируем и МП')");

        $this->addSql('CREATE TABLE address_entity (id SERIAL, parent_id INT DEFAULT NULL, level_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, guid VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, parent_full_name VARCHAR(255) NOT NULL, first_level_parent VARCHAR(255) NOT NULL, display BOOLEAN NOT NULL, external_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_address_entity_parent_id ON address_entity (parent_id)');
        $this->addSql('CREATE INDEX idx_address_entity_level_id ON address_entity (level_id)');
        $this->addSql('CREATE UNIQUE INDEX idx_address_entity_guid ON address_entity (guid)');
        $this->addSql('CREATE TABLE address_entity_shop_entity (address_entity_id INT NOT NULL, shop_entity_id INT NOT NULL, PRIMARY KEY(address_entity_id, shop_entity_id))');
        $this->addSql('CREATE INDEX idx_address_entity_shop_entity_address_entity_id ON address_entity_shop_entity (address_entity_id)');
        $this->addSql('CREATE INDEX idx_address_entity_shop_entity_shop_entity_id ON address_entity_shop_entity (shop_entity_id)');
        $this->addSql('CREATE TABLE address_level_entity (id SERIAL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, level INT NOT NULL, external_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE address_to_address_entity (id SERIAL, address_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, depth INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_address_to_address_entity_address_id ON address_to_address_entity (address_id)');
        $this->addSql('CREATE INDEX idx_address_to_address_entity_parent_id ON address_to_address_entity (parent_id)');
        $this->addSql('CREATE TABLE shop_entity (id SERIAL, xml_id VARCHAR(255) NOT NULL, external_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE address_entity ADD CONSTRAINT fk_address_entity_parent_id FOREIGN KEY (parent_id) REFERENCES address_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address_entity ADD CONSTRAINT fk_address_entity_level_id FOREIGN KEY (level_id) REFERENCES address_level_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address_entity_shop_entity ADD CONSTRAINT fk_address_entity_shop_entity_address_entity_id FOREIGN KEY (address_entity_id) REFERENCES address_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address_entity_shop_entity ADD CONSTRAINT fk_address_entity_shop_entity_shop_entity_id FOREIGN KEY (shop_entity_id) REFERENCES shop_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address_to_address_entity ADD CONSTRAINT fk_address_to_address_entity_address_id FOREIGN KEY (address_id) REFERENCES address_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address_to_address_entity ADD CONSTRAINT fk_address_to_address_entity_parent_id FOREIGN KEY (parent_id) REFERENCES address_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM consumer WHERE code = 'pharma_clients'");

        $this->addSql('ALTER TABLE address_entity DROP CONSTRAINT fk_address_entity_parent_id');
        $this->addSql('ALTER TABLE address_entity DROP CONSTRAINT fk_address_entity_level_id');
        $this->addSql('ALTER TABLE address_entity_shop_entity DROP CONSTRAINT fk_address_entity_shop_entity_address_entity_id');
        $this->addSql('ALTER TABLE address_entity_shop_entity DROP CONSTRAINT fk_address_entity_shop_entity_shop_entity_id');
        $this->addSql('ALTER TABLE address_to_address_entity DROP CONSTRAINT fk_address_to_address_entity_address_id');
        $this->addSql('ALTER TABLE address_to_address_entity DROP CONSTRAINT fk_address_to_address_entity_parent_id');
        $this->addSql('DROP TABLE address_entity');
        $this->addSql('DROP TABLE address_entity_shop_entity');
        $this->addSql('DROP TABLE address_level_entity');
        $this->addSql('DROP TABLE address_to_address_entity');
        $this->addSql('DROP TABLE shop_entity');
    }
}
