<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align the sprint 1 and sprint 2 indexes with Doctrine metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_PROJECTS_STATUS ON projects');
        $this->addSql('ALTER TABLE projects RENAME INDEX idx_projects_client TO IDX_5C93B3A419EB6921');
        $this->addSql('ALTER TABLE skills RENAME INDEX uniq_skills_normalized_name TO UNIQ_D5311670D69C0128');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_users_email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE user_skills RENAME INDEX idx_user_skills_user TO IDX_B0630D4DA76ED395');
        $this->addSql('ALTER TABLE user_skills RENAME INDEX idx_user_skills_skill TO IDX_B0630D4D5585C142');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_PROJECTS_STATUS ON projects (status)');
        $this->addSql('ALTER TABLE projects RENAME INDEX IDX_5C93B3A419EB6921 TO IDX_PROJECTS_CLIENT');
        $this->addSql('ALTER TABLE skills RENAME INDEX UNIQ_D5311670D69C0128 TO UNIQ_SKILLS_NORMALIZED_NAME');
        $this->addSql('ALTER TABLE users RENAME INDEX UNIQ_1483A5E9E7927C74 TO UNIQ_USERS_EMAIL');
        $this->addSql('ALTER TABLE user_skills RENAME INDEX IDX_B0630D4DA76ED395 TO IDX_USER_SKILLS_USER');
        $this->addSql('ALTER TABLE user_skills RENAME INDEX IDX_B0630D4D5585C142 TO IDX_USER_SKILLS_SKILL');
    }
}
