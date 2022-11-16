<?php

class User extends objStorage  {

    protected static $_bSynchTable  = true;

    protected static $_s_db_sTablename = 'users';
    protected static $_s_db_aTabledef = array(
            'id'            => array('type' => 'int',     'extra'  => 'auto_increment', 'primary' => true),
            'anrede'        => array('type' => 'varchar', 'length' => '255'),
            'vorname'       => array('type' => 'varchar', 'length' => '255'),
            'nachname'      => array('type' => 'varchar', 'length' => '255'),
            'strasse'       => array('type' => 'varchar',  'length' => '255'),
            'hausnummer'    => array('type' => 'varchar',  'length' => '255'),
            'plz'           => array('type' => 'varchar', 'length' => '255'),
            'ort'           => array('type' => 'varchar', 'length' => '255'),
            'land'          => array('type' => 'varchar', 'length' => '255'),
            'telefon'       => array('type' => 'varchar', 'length' => '255'),
            'email'         => array('type' => 'varchar', 'length' => '255'),
            'kassenbon'     => array('type' => 'varchar', 'length' => '255'),
            'datenschutz'   => array('type' => 'int'),
            'informationen' => array('type' => 'int'),
            'workshopOrt'   => array('type' => 'varchar', 'length' => '255'),
            'frage_1'       => array('type' => 'varchar', 'length' => '255'),
            'frage_2'       => array('type' => 'varchar', 'length' => '255'),
            'frage_3'       => array('type' => 'varchar', 'length' => '255'),
            'frage_4'       => array('type' => 'varchar', 'length' => '255'),
            'frage_5'       => array('type' => 'varchar', 'length' => '255'),
            'date_created'  => array('type' => 'varchar', 'length' => '255'),
            'deleted'       => array('type' => 'int')
    );


    public static function s_loadByHash($sHash)  {
        $retVal = db()->quick("SELECT * FROM users WHERE xhash = '".mres($sHash)."'");
        if (!$retVal) return false;
        $u = new User($retVal);

        return $u;
    }

}

?>
