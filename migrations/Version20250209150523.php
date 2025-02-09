<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209150523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE specification (id SERIAL NOT NULL, property VARCHAR(50) NOT NULL, value VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE specification_product (specification_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(specification_id, product_id))');
        $this->addSql('CREATE INDEX IDX_90F08570908E2FFE ON specification_product (specification_id)');
        $this->addSql('CREATE INDEX IDX_90F085704584665A ON specification_product (product_id)');
        $this->addSql('ALTER TABLE specification_product ADD CONSTRAINT FK_90F08570908E2FFE FOREIGN KEY (specification_id) REFERENCES specification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE specification_product ADD CONSTRAINT FK_90F085704584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE specification_product DROP CONSTRAINT FK_90F08570908E2FFE');
        $this->addSql('ALTER TABLE specification_product DROP CONSTRAINT FK_90F085704584665A');
        $this->addSql('DROP TABLE specification');
        $this->addSql('DROP TABLE specification_product');
    }
}
