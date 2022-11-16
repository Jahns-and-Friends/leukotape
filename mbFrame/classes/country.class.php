<?php

class country extends objStorage  {

    protected static $_bSynchTable  = false;

    protected static $_s_db_sTablename = 'countries';
    protected static $_s_db_aTabledef = array(
        'id'        => array('type' => 'int', 'extra' => 'auto_increment', 'primary' => true),
        'name_en'   => array('type' => 'varchar', 'length' => '64'),
        'name_de'   => array('type' => 'varchar', 'length' => '64'),
        'iso_short' => array('type' => 'varchar', 'length' => '2'),
        'iso_long'  => array('type' => 'varchar', 'length' => '3'),
        'code'      => array('type' => 'varchar', 'length' => '4'),
    );
    
    const ISO_SHORT = 0;
    const ISO_LONG  = 1;
    
    public static function s_getByISO($iso, $iso_type = 0)  {
        $iso = trim($iso);
        if (!strlen($iso)) return false;
        $retVal = db()->quick("SELECT * FROM countries WHERE ".(($iso_type==0)?"iso_short" : "iso_long")." = '".mres($iso)."' LIMIT 1");
        if($retVal) $retVal['code'] = preg_replace("/[^0-9]/", "", $retVal['code']);
        return (($retVal) ? new country($retVal) : false);
    }
    
    public static function s_getList($language='de')  {
        if ($language != 'en' && $language != 'de') return;
        $retVal = db()->send("SELECT * FROM countries ORDER BY name_{$language} ASC");
        $out = array();
        if ($retVal)  {
            foreach($retVal as $k => $v)  {
                if (strlen($v['iso_short']))
                    $out[$v['iso_short']] = $v['name_'.$language];
            }
        }
        return $out;
    }

}

?>
