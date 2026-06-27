<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627130755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_refresh_token');
        $this->addSql('DROP INDEX uniq_access_token');
        $this->addSql('ALTER TABLE access_tokens ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE access_tokens ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_refresh_token ON access_tokens (refresh_token) WHERE (deleted_at IS NULL)');
        $this->addSql('CREATE UNIQUE INDEX uniq_access_token ON access_tokens (token) WHERE (deleted_at IS NULL)');
        $this->addSql('DROP INDEX uniq_user_email');
        $this->addSql('DROP INDEX uniq_user_username');
        $this->addSql('ALTER TABLE users ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE users ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE users ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON users (email) WHERE (deleted_at IS NULL)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_username ON users (username) WHERE (deleted_at IS NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_access_token');
        $this->addSql('DROP INDEX uniq_refresh_token');
        $this->addSql('ALTER TABLE access_tokens DROP updated_at');
        $this->addSql('ALTER TABLE access_tokens DROP deleted_at');
        $this->addSql('CREATE UNIQUE INDEX uniq_access_token ON access_tokens (token)');
        $this->addSql('CREATE UNIQUE INDEX uniq_refresh_token ON access_tokens (refresh_token)');
        $this->addSql('DROP INDEX uniq_user_email');
        $this->addSql('DROP INDEX uniq_user_username');
        $this->addSql('ALTER TABLE users DROP created_at');
        $this->addSql('ALTER TABLE users DROP updated_at');
        $this->addSql('ALTER TABLE users DROP deleted_at');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_username ON users (username)');
    }
}
