<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626081419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE start_date start_date DATETIME DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE date_limit_for_registration date_limit_for_registration DATETIME DEFAULT NULL, CHANGE max_registration_number max_registration_number INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE start_date start_date DATETIME NOT NULL, CHANGE duration duration INT NOT NULL, CHANGE date_limit_for_registration date_limit_for_registration DATETIME NOT NULL, CHANGE max_registration_number max_registration_number INT NOT NULL');
    }
}
