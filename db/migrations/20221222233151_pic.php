<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Pic extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        ($this->table("pics"))->addColumn("path", "string")
            ->addColumn("building_id", "integer", ["signed" => false])
            ->addForeignKey("building_id", "buildings", "id", ["update" => "CASCADE", "delete" => "SET NULL"])
            ->addTimestamps()
            ->create();
    }
}
