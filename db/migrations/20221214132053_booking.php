<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Booking extends AbstractMigration
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
        ($this->table("bookings"))->addColumn("building_id", "integer", ["signed" => false])
            ->addColumn("customer_id", "integer", ["signed" => false])
            ->addColumn("date", "date")
            ->addColumn("price", "integer")
            ->addColumn("is_paid", "boolean")
            ->addColumn("inv", "string")
            ->addColumn("inv_link", "string")
            ->addColumn("detail", "json")
            ->addTimestamps()
            ->addIndex(["inv"], ["unique" => true])
            ->addForeignKey("building_id", "buildings", "id", ["delete" => "CASCADE", "update" => "CASCADE"])
            ->addForeignKey("customer_id", "customers", "id", ["delete" => "CASCADE", "update" => "CASCADE"])
            ->create();
    }
}
