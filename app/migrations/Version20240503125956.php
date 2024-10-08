<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503125956 extends AbstractMigration
{
    private const CONSUMERS = [
        [
            'code' => 'web',
            'name' => 'Web клиент аптек',
            'description' => '',
        ],
        [
            'code' => 'ios',
            'name' => 'iOS клиент аптек',
            'description' => '',
        ],
        [
            'code' => 'android',
            'name' => 'Android клиент аптек',
            'description' => '',
        ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach (self::CONSUMERS as $consumer) {
            $this->addSql(
                "
INSERT INTO consumer (code, name, description)
VALUES ('{$consumer['code']}', '{$consumer['name']}', '{$consumer['description']}');
        "
            );
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
