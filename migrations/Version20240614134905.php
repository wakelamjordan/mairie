<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614134905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE confirmation_email ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE confirmation_email ADD CONSTRAINT FK_78FA6FD8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_78FA6FD8A76ED395 ON confirmation_email (user_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495AD6CDAA');
        $this->addSql('DROP INDEX UNIQ_8D93D6495AD6CDAA ON user');
        $this->addSql('ALTER TABLE user DROP confirmation_email_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE confirmation_email DROP FOREIGN KEY FK_78FA6FD8A76ED395');
        $this->addSql('DROP INDEX UNIQ_78FA6FD8A76ED395 ON confirmation_email');
        $this->addSql('ALTER TABLE confirmation_email DROP user_id');
        $this->addSql('ALTER TABLE user ADD confirmation_email_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495AD6CDAA FOREIGN KEY (confirmation_email_id) REFERENCES confirmation_email (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495AD6CDAA ON user (confirmation_email_id)');
    }
}
