<?php

class Order extends objStorage  {

    protected static $_bSynchTable  = true;

    protected static $_s_db_sTablename = 'order';
    protected static $_s_db_aTabledef = array(

            'id'                   => array('type' => 'int', 'extra' => 'auto_increment', 'primary' => true),

            'uid'                  => array('type' => 'int'),
            'orderno'              => array('type' => 'varchar', 'length' => '255'),
            'present-1'            => array('type' => 'varchar', 'length' => '255'),
            'present-2'            => array('type' => 'varchar', 'length' => '255'),
            'present-3'            => array('type' => 'varchar', 'length' => '255'),
            'present-4'            => array('type' => 'varchar', 'length' => '255'),
            'deliverydate'         => array('type' => 'varchar', 'length' => '255'),

            'date_created'         => array('type' => 'bigint', 'length'  => '14', 'default' => '0'),
            'date_changed'         => array('type' => 'bigint', 'length'  => '14', 'default' => '0'),
            'date_exported'        => array('type' => 'bigint', 'length'  => '14', 'default' => '0'),

            'deleted'              => array('type' => 'tinyint', 'length' => '1', 'default'  => '0'),
    );

}

?>
