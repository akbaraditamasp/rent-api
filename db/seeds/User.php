<?php


use Phinx\Seed\AbstractSeed;

class User extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $table = $this->table('users');
        $table->insert([
            "username" => "admin",
            "password" => password_hash("123456", PASSWORD_BCRYPT)
        ])
            ->saveData();
    }
}
