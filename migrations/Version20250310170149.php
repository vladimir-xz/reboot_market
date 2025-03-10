<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310170149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_description ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_description ADD CONSTRAINT FK_C1CBDE394584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1CBDE394584665A ON product_description (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_description DROP CONSTRAINT FK_C1CBDE394584665A');
        $this->addSql('DROP INDEX UNIQ_C1CBDE394584665A');
        $this->addSql('ALTER TABLE product_description DROP product_id');
    }
}
