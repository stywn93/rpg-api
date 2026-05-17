<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestrictUserRolesToAdminAndUser extends Migration
{
    public function up()
    {
        $this->db->query("UPDATE `users` SET `role` = 'user' WHERE `role` IS NULL OR `role` NOT IN ('admin', 'user')");
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('parent', 'petugas', 'admin', 'pimpinan') NOT NULL DEFAULT 'parent'");
        $this->db->query("UPDATE `users` SET `role` = 'parent' WHERE `role` = 'user'");
    }
}
