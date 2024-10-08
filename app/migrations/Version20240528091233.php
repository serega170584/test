<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


class Version20240528091233 extends AbstractMigration
{
    private const CONSUMERS = [
        [
            'code' => 'pharma_distributor',
            'name' => 'Дистрибьютор Пульс',
            'description' => 'Дистрибьютор Пульс',
        ],
    ];

    public function getDescription(): string
    {
        return 'Добавление потребителя для нового дистрибьютора Пульс';
    }

    public function up(Schema $schema): void
    {
        foreach (self::CONSUMERS as $consumer) {
            $this->addSql(
                "INSERT INTO consumer (code, name, description)
                VALUES ('{$consumer['code']}', '{$consumer['name']}', '{$consumer['description']}');"
            );
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::CONSUMERS as $consumer) {
            $this->addSql(
                "DELETE FROM consumer 
                WHERE code = '{$consumer['code']}';"
            );
        }
    }
}