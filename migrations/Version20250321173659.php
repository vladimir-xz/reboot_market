<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321173659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE freight_rate (id SERIAL NOT NULL, country_id INT NOT NULL, shipping_method_id INT NOT NULL, weight INT NOT NULL, postcode VARCHAR(15) NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6BD540B6F92F3E70 ON freight_rate (country_id)');
        $this->addSql('CREATE INDEX IDX_6BD540B65F7D6850 ON freight_rate (shipping_method_id)');
        $this->addSql('CREATE TABLE shipping_method (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE shipping_method_country (shipping_method_id INT NOT NULL, country_id INT NOT NULL, PRIMARY KEY(shipping_method_id, country_id))');
        $this->addSql('CREATE INDEX IDX_22FD0FC05F7D6850 ON shipping_method_country (shipping_method_id)');
        $this->addSql('CREATE INDEX IDX_22FD0FC0F92F3E70 ON shipping_method_country (country_id)');
        $this->addSql('ALTER TABLE freight_rate ADD CONSTRAINT FK_6BD540B6F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE freight_rate ADD CONSTRAINT FK_6BD540B65F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES shipping_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipping_method_country ADD CONSTRAINT FK_22FD0FC05F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES shipping_method (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipping_method_country ADD CONSTRAINT FK_22FD0FC0F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE freight_rate DROP CONSTRAINT FK_6BD540B6F92F3E70');
        $this->addSql('ALTER TABLE freight_rate DROP CONSTRAINT FK_6BD540B65F7D6850');
        $this->addSql('ALTER TABLE shipping_method_country DROP CONSTRAINT FK_22FD0FC05F7D6850');
        $this->addSql('ALTER TABLE shipping_method_country DROP CONSTRAINT FK_22FD0FC0F92F3E70');
        $this->addSql('DROP TABLE freight_rate');
        $this->addSql('DROP TABLE shipping_method');
        $this->addSql('DROP TABLE shipping_method_country');
    }
}
