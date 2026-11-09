<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user profiles and reusable skills for sprint 1.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(180) NOT NULL, company VARCHAR(120) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_USERS_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skills (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, normalized_name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_SKILLS_NORMALIZED_NAME (normalized_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_skills (user_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_USER_SKILLS_USER (user_id), INDEX IDX_USER_SKILLS_SKILL (skill_id), PRIMARY KEY(user_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_skills ADD CONSTRAINT FK_USER_SKILLS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_skills ADD CONSTRAINT FK_USER_SKILLS_SKILL FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_skills');
        $this->addSql('DROP TABLE skills');
        $this->addSql('DROP TABLE users');
    }
}
