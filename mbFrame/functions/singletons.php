<?php
// ---------------------------------------------------------------------- //
// DATABASE Singleton
function db()  {
	static $instance;
    if (!is_object($instance)) {
        require_once(dirname(__FILE__).'/../classes/_core/database.class.php');
        $instance = Database::getInstance(frame()->aConfig['db']['host'],
                                          frame()->aConfig['db']['user'],
                                          frame()->aConfig['db']['pass'],
                                          frame()->aConfig['db']['db']
                                         );
    } // if
    return $instance;
}
// ---------------------------------------------------------------------- //
function user($id=false, $group=false)  {
    static $selfRef = false;
    static $_group = false;
    
    if ($group !== false)  {
        $_group = $group;
    } else if (frame()->issetSessionVar('usergroup'))  {
        $_group = frame()->getSessionVar('usergroup');
    }

    if ($selfRef === false || $id)  {
        if (!$id) {
            if (frame()->issetSessionVar('userid'))  {
                if ($_group == 'admin')         return $selfRef = new user_admin(frame()->getSessionVar('userid'));
                if ($_group == 'participant')   return $selfRef = new user_participant(frame()->getSessionVar('userid'));
                if ($_group == 'jury')          return $selfRef = new user_jury(frame()->getSessionVar('userid'));
                return $selfRef = new user(frame()->getSessionVar('userid'));
            }
            else {
                // return new dummy object instead of false
                $foo = new user();
                $foo->setDummy(true);
                $selfRef = $foo;
                return $foo;
            }
        } 
        if ($_group == 'admin')             $selfRef = new user_admin($id);
        else if ($_group == 'participant')  $selfRef = new user_participant($id);
        else if ($_group == 'jury')         $selfRef = new user_jury($id);
        else                                $selfRef = new user($id);
    }
    return $selfRef;
}
// ---------------------------------------------------------------------- //

function contest($id=false)  {
    static $selfRef = false;

    if ($selfRef === false || $id)  {
        if (!$id) {
            if (frame()->issetSessionVar('contest_id')) return $selfRef = new contest(frame()->getSessionVar('contest_id'));
            else {
                // return new dummy object instead of false
                $foo = new contest();
                $foo->setDummy(true);
                $selfRef = $foo;
                return $foo;
            }
        }
        $selfRef = new contest($id);
    }
    return $selfRef;
}
// ---------------------------------------------------------------------- //

?>
