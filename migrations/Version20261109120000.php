<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add projects for sprint 2.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE projects (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, title VARCHAR(180) NOT NULL, description LONGTEXT NOT NULL, budget NUMERIC(12, 2) NOT NULL, deadline DATE NOT NULL, category VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_PROJECTS_CLIENT (client_id), INDEX IDX_PROJECTS_STATUS (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_PROJECTS_CLIENT FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projects');
    }
}
