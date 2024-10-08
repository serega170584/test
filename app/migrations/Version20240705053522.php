<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705053522 extends AbstractMigration
{
    private const CONSUMERS = [
        [
            'code' => 'android_distr',
            'name' => 'Android с дистрибьюторами',
            'description' => 'Android с дистрибьюторами',
        ],
        [
            'code' => 'ios_distr',
            'name' => 'iOS с дистрибьюторами',
            'description' => 'iOS с дистрибьюторами',
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
