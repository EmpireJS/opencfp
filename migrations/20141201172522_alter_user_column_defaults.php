<?php

use Phinx\Migration\AbstractMigration;

class AlterUserColumnDefaults extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('users')
            ->changeColumn('permissions', 'text', ['null' => true])
            ->changeColumn('activated', 'boolean', ['default' => 0, 'null' => true])
            ->changeColumn('activation_code', 'string', ['null' => true])
            ->changeColumn('activated_at', 'string', ['null' => true])
            ->changeColumn('last_login', 'string', ['null' => true])
            ->changeColumn('persist_code', 'string', ['null' => true])
            ->changeColumn('reset_password_code', 'string', ['null' => true])
            ->changeColumn('first_name', 'string', ['null' => true])
            ->changeColumn('last_name', 'string', ['null' => true])
            ->changeColumn('created_at', 'datetime', ['default' => '0000-00-00 00:00:00'])
            ->changeColumn('updated_at', 'datetime', ['default' => '0000-00-00 00:00:00'])
            ->changeColumn('url', 'string', ['null' => true])
            ->changeColumn('transportation', 'boolean', ['null' => true])
            ->changeColumn('hotel', 'boolean', ['null' => true])
            ->changeColumn('info', 'text', ['null' => true])
            ->changeColumn('bio', 'text', ['null' => true])
            ->changeColumn('photo_path', 'string', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('users')
            ->changeColumn('permissions', 'text')
            ->changeColumn('activated', 'boolean')
            ->changeColumn('activation_code', 'string')
            ->changeColumn('activated_at', 'string')
            ->changeColumn('last_login', 'string')
            ->changeColumn('persist_code', 'string')
            ->changeColumn('reset_password_code', 'string')
            ->changeColumn('first_name', 'string')
            ->changeColumn('last_name', 'string')
            ->changeColumn('created_at', 'datetime')
            ->changeColumn('updated_at', 'datetime')
            ->changeColumn('url', 'string')
            ->changeColumn('transportation')
            ->changeColumn('hotel', 'boolean')
            ->changeColumn('info', 'text')
            ->changeColumn('bio', 'text')
            ->changeColumn('photo_path', 'string')
            ->save();
    }
}