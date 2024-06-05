<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240426134314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, raisonSociale VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C7440455D8D12E2A (raisonSociale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE courrier (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT DEFAULT NULL, type INT NOT NULL, bordereau INT NOT NULL, nom VARCHAR(255) NOT NULL, civilite VARCHAR(50) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, codePostal VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, signature LONGBLOB DEFAULT NULL, procuration VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BEF47CAAF7B4C561 (bordereau), INDEX IDX_BEF47CAA10335F61 (expediteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE destinataire (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, civilite VARCHAR(4) DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, codePostal VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, INDEX IDX_FEA9FF9210335F61 (expediteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expediteur (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, codePostal VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, updatedAt DATETIME NOT NULL, createdAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_ABA4CF8EE7927C74 (email), INDEX IDX_ABA4CF8E19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facteur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_2E32D460E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, etat VARCHAR(50) NOT NULL, statutCode INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statutcourrier (id INT AUTO_INCREMENT NOT NULL, statut_id INT DEFAULT NULL, courrier_id INT DEFAULT NULL, facteur_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_A60D879BF6203804 (statut_id), INDEX IDX_A60D879B8BF41DC7 (courrier_id), INDEX IDX_A60D879B3155FA5 (facteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, fonction VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE courrier ADD CONSTRAINT FK_BEF47CAA10335F61 FOREIGN KEY (expediteur_id) REFERENCES expediteur (id)');
        $this->addSql('ALTER TABLE destinataire ADD CONSTRAINT FK_FEA9FF9210335F61 FOREIGN KEY (expediteur_id) REFERENCES expediteur (id)');
        $this->addSql('ALTER TABLE expediteur ADD CONSTRAINT FK_ABA4CF8E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE statutcourrier ADD CONSTRAINT FK_A60D879BF6203804 FOREIGN KEY (statut_id) REFERENCES statut (id)');
        $this->addSql('ALTER TABLE statutcourrier ADD CONSTRAINT FK_A60D879B8BF41DC7 FOREIGN KEY (courrier_id) REFERENCES courrier (id)');
        $this->addSql('ALTER TABLE statutcourrier ADD CONSTRAINT FK_A60D879B3155FA5 FOREIGN KEY (facteur_id) REFERENCES facteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE courrier DROP FOREIGN KEY FK_BEF47CAA10335F61');
        $this->addSql('ALTER TABLE destinataire DROP FOREIGN KEY FK_FEA9FF9210335F61');
        $this->addSql('ALTER TABLE expediteur DROP FOREIGN KEY FK_ABA4CF8E19EB6921');
        $this->addSql('ALTER TABLE statutcourrier DROP FOREIGN KEY FK_A60D879BF6203804');
        $this->addSql('ALTER TABLE statutcourrier DROP FOREIGN KEY FK_A60D879B8BF41DC7');
        $this->addSql('ALTER TABLE statutcourrier DROP FOREIGN KEY FK_A60D879B3155FA5');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE courrier');
        $this->addSql('DROP TABLE destinataire');
        $this->addSql('DROP TABLE expediteur');
        $this->addSql('DROP TABLE facteur');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE statutcourrier');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
