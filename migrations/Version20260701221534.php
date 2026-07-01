<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701221534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Site settings KV store + pg_trgm fuzzy search index on products.name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_settings (setting_key VARCHAR(100) NOT NULL, setting_value TEXT DEFAULT NULL, PRIMARY KEY (setting_key))');

        // Trigram similarity for typo-tolerant product search ("did you mean").
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        $this->addSql('CREATE INDEX idx_products_name_trgm ON products USING gin (name gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_products_name_trgm');
        $this->addSql('DROP TABLE site_settings');
        // Extension left installed; it is harmless and may be used elsewhere.
    }
}
