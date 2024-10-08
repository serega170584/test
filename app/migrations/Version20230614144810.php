<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614144810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE consumer (id SERIAL NOT NULL, code VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE TABLE consumer_entity_shop_group_entity (consumer_entity_id INT NOT NULL, shop_group_entity_id INT NOT NULL, PRIMARY KEY(consumer_entity_id, shop_group_entity_id))'
        );
        $this->addSql('CREATE INDEX IDX_479398ABCF4C5649 ON consumer_entity_shop_group_entity (consumer_entity_id)');
        $this->addSql('CREATE INDEX IDX_479398ABA8652588 ON consumer_entity_shop_group_entity (shop_group_entity_id)');
        $this->addSql(
            'CREATE TABLE relationship_of_store_groups_to_shop (uf_xml_id VARCHAR(100) NOT NULL, shop_group_id INT NOT NULL, PRIMARY KEY(shop_group_id, uf_xml_id))'
        );
        $this->addSql('CREATE INDEX IDX_9C96B31625EC23D1 ON relationship_of_store_groups_to_shop (shop_group_id)');
        $this->addSql(
            'CREATE TABLE shop_group (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, code VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, description VARCHAR(255) DEFAULT NULL, level INT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AAD61E9777153098 ON shop_group (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AAD61E97727ACA70 ON shop_group (parent_id)');
        $this->addSql(
            'CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql(
            'CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;'
        );
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql(
            'CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();'
        );
        $this->addSql(
            'ALTER TABLE consumer_entity_shop_group_entity ADD CONSTRAINT FK_479398ABCF4C5649 FOREIGN KEY (consumer_entity_id) REFERENCES consumer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE consumer_entity_shop_group_entity ADD CONSTRAINT FK_479398ABA8652588 FOREIGN KEY (shop_group_entity_id) REFERENCES shop_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE relationship_of_store_groups_to_shop ADD CONSTRAINT FK_9C96B31625EC23D1 FOREIGN KEY (shop_group_id) REFERENCES shop_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE shop_group ADD CONSTRAINT FK_AAD61E97727ACA70 FOREIGN KEY (parent_id) REFERENCES shop_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer_entity_shop_group_entity DROP CONSTRAINT FK_479398ABCF4C5649');
        $this->addSql('ALTER TABLE consumer_entity_shop_group_entity DROP CONSTRAINT FK_479398ABA8652588');
        $this->addSql('ALTER TABLE relationship_of_store_groups_to_shop DROP CONSTRAINT FK_9C96B31625EC23D1');
        $this->addSql('ALTER TABLE shop_group DROP CONSTRAINT FK_AAD61E97727ACA70');
        $this->addSql('DROP TABLE consumer');
        $this->addSql('DROP TABLE consumer_entity_shop_group_entity');
        $this->addSql('DROP TABLE relationship_of_store_groups_to_shop');
        $this->addSql('DROP TABLE shop_group');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
