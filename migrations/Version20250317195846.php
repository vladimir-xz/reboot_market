<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317195846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD description_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADD9F966B FOREIGN KEY (description_id) REFERENCES product_description (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADD9F966B ON product (description_id)');
        $this->addSql('ALTER TABLE product_description DROP CONSTRAINT fk_c1cbde394584665a');
        $this->addSql('DROP INDEX uniq_c1cbde394584665a');
        $this->addSql('ALTER TABLE product_description DROP product_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_description ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_description ADD CONSTRAINT fk_c1cbde394584665a FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_c1cbde394584665a ON product_description (product_id)');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADD9F966B');
        $this->addSql('DROP INDEX UNIQ_D34A04ADD9F966B');
        $this->addSql('ALTER TABLE product DROP description_id');
    }
}
