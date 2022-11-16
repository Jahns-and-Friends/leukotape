<?php

// require framework
require_once(dirname(__FILE__).'/../mbFrame.php');

//header('Content-type: text/html; charset=text/plain"');
#header('Content-Type: text/html; charset=iso-8859-1');

cleanPost($_GET);

contest(2);

if (isset($_GET['action']))  {
    
    switch($_GET['action'])  {
        
        
        
        case 'getDocumentCountForFolder':  
            $retVal = array('status' => 0, 'payload' => ''); 
            cleanPost($_POST);
            
            $sub_count = 0;
            
            // checks
            if (!user()->loggedIn())
                die(json_encode($retVal));            
            if (!isset($_POST['folder']) || empty($_POST['folder']))   
                die(json_encode($retVal));
            
            if (!is_array($_POST['folder'])) $_POST['folder'] = array($_POST['folder']);
            
            $out = array();
            foreach($_POST['folder'] as $k => $id)  {
                $docs = folder::s_getFolderFilesForUser(user(), $id, contest()->get('id'));
                
                // filter by target_group and target_id
                if ($docs && isset($_POST['target_group']) && isset($_POST['target_id']))  {                    
                    if ($_POST['target_id'] && strlen($_POST['target_group']))  {
                        $doctmp = $docs;
                        $docs = array();
                        foreach($doctmp as $dk => $dv)  {
                            if ($dv->get('target_group') != $_POST['target_group'])
                                continue;
                            if ($dv->get('target_id') != $_POST['target_id'])
                                continue;
                            $docs[$dv->get('id')] = $dv;
                        }                        
                    }                    
                }
                
                // @TODO: Permission check
                
                
                
                
                
                $count_subfolder = folder::s_getByParent($id);
                $out[$id] = array(
                  'files'     => (($docs) ? sizeof($docs) : 0),
                  'folders'   => (($count_subfolder) ? sizeof($count_subfolder) : 0),
                );
                
               ## $out[$id] = (($docs) ? arraysizeof($docs) : 0);
            }
            
            $retVal = array('status' => 0, 'payload' => 'nothing found'); 
            if (!$out) die(json_encode($retVal));
            
            if (sizeof($out) == 1)  {
                $tmp = array_values($out);
                $retVal = array('status' => 1, 'payload' => $tmp[0], 'subfolders' => $sub_count); 
                die(json_encode($retVal));
            } else {
                $retVal = array('status' => 1, 'payload' => $out, 'subfolders' => $sub_count); 
                die(json_encode($retVal));
            }            
        break;
        
        case 'getFolder' :
            $retVal = array('status' => 0, 'payload' => 'permission denied');
            
            if (!isset($_GET['parent']))  die(json_encode($retVal));
            
            $folder = folder::s_getByParent($_GET['parent']);
          #  if(!$_GET['parent']) print_r($folder);
            
            if (!$folder)  {
               die(json_encode(array('status' => 0, 'payload' => 'no folders found')));
            }
            
            
            $data = array(
                'parent' => $_GET['parent'],
                'items'  => array(),
                'html'   => '',
             );
            
            // template
            $block = template::s_getBlockFromTemplate('vorlagen', 'folder_item', 
                                realpath(frame()->aConfig['root']['local'] . '/content/templates/') . '/',
                                realpath(frame()->aConfig['root']['local'] . '/content/code/') . '/');

            
            // if requested folder has parent, show back icon
            if ($_GET['parent'] != 0)  {
                $parent = new folder($_GET['parent']);                
                $tmpObj = new folder();
                $tmpObj->setDummy(true);
                $tmpObj->set('label',   'folder-back');
                $tmpObj->set('name',    'Zur&uuml;ck');
                $tmpObj->set('parent_id', $parent->get('parent_id'));
                $tmpObj->set('contest_id', contest()->get('id'));
                $tmpObj->setTemp('type_back', true);                
                $tmpObj->setTemp('id', $_GET['parent']);
                array_unshift($folder, $tmpObj);
            }
            
            foreach($folder as $fID => $fObj)  {                
                $r = $b = array();
                $r['label'] = $fObj->get('label');    
                $r['id']    = $fObj->get('id');        
                $r['text']  = $fObj->get('name');
                
                $r['parent_id']  = $fObj->get('parent_id');
                
                $r['addClass'] = '';
                
                if (!$fObj->getTemp('writable') == true)  {
                    $r['addClass']  .= ' locked';
                    $r['dropzone_addClass']  = ' locked';
                }
                
                if ($fObj->getTemp('type_back') == true)  {
                    $r['addClass']  .= ' back';
                }

                
                if ($fObj->get('parent_id'))
                    $r['addClass']  .= ' hasParent';
                   
                
                if ($r['id'] == null) $r['id'] = 0;
                
                
                $data['items'][] = $fObj->getValues();
                $data['html']  .= template::s_parseBlock($block, $r, $b, true, false);
            }
            
            die(json_encode(array('status' => 1, 'payload' => $data)));
            
        break;
        
        
        
        case 'admin_getText' :
            $retVal = ''; 
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup('admin'))  {
               die('');
            }         
            
            $contest_id = contest()->get('id');
            if (!$contest_id) die($retVal);
                
            $label   = $_GET['label'];
            if (!$label) die($retVal);
            
            $text_id = contest_text::s_getIdByLabel($contest_id, $label);
            
            if ($text_id)  {
                // text already exists. update.
                $cDate = new contest_text($text_id);
                $text = $cDate->get('content');
                die($text);
            }
               
            die($retVal);            
        break;
    
        
        case 'admin_saveText' :
            $retVal = array('status' => 0, 'payload' => ''); 
            
            if (!isset($_POST['label']) || !isset($_POST['text']))
                die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup('admin'))  {
               die(json_encode($retVal));
            }
            
            $contest_id = contest()->get('id');
            if (!$contest_id) die(json_encode($retVal));
                
            $label   = $_POST['label'];
            if (!$label) die(json_encode($retVal));
            
            $text   = $_POST['text'];
            if (!$text) die(json_encode($retVal));
            
            $text_id = contest_text::s_getIdByLabel($contest_id, $label);
            
            if ($text_id)  {
                // text already exists. update.
                $cDate = new contest_text($text_id);
                $cDate->set('content', $text);
                $cDate->set('date_changed', mtfts());
                $cDate->save();
            } else {
                // new text.
                $cDate = new contest_text();
                $cDate->set('contest_id', $contest_id);
                $cDate->set('label', $label);
                $cDate->set('content', $text);
                $cDate->set('date_created', mtfts());
                $cDate->save();
            }
            
            // return length of text
            $retVal['status'] = 1;
            $retVal['payload'] = strlen(utf8_decode($text));
            echo json_encode($retVal);
            exit;
        break;
        
        case 'admin_saveDates':
            $retVal = array('status' => 0, 'payload' => ''); 
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup('admin'))  {
               die(json_encode($retVal));
            }
            
            $contest_id = contest()->get('id');
            if (!$contest_id) die(json_encode($retVal));
            
            if (empty($_POST))  die(json_encode($retVal));
                       
            foreach($_POST as $k => $v)  {
                if (substr($k, 0, 5) == 'date_')  {
                    if (!frame()->checkDate($v)) continue;
                    
                    $label = substr($k, 5);
                    
                    // check if date exists
                    $date_id = contest_dates::s_getIdByLabel($contest_id, $label);

                    if ($date_id)  {
                        // date already exists. update.
                        $cDate = new contest_dates($date_id);
                        $cDate->set('date', frame()->cleanDate($v));
                        $cDate->save();
                    } else {
                        // new date.
                        $cDate = new contest_dates();
                        $cDate->set('contest_id', $contest_id);
                        $cDate->set('label', $label);
                        $cDate->set('date', frame()->cleanDate($v));
                        $cDate->save();
                    }
                    $retVal['status'] = true;
                }
            }
            
            echo json_encode($retVal);
            exit;
        break;
        
        case 'admin_updatePermission':
            $retVal = array('status' => 0, 'payload' => ''); 
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup('admin'))  {
               die(json_encode($retVal));
            }
            
            $contest_id = contest()->get('id');
            if (!$contest_id) die(json_encode($retVal));
            
            if (empty($_POST))  die(json_encode($retVal));
            
            $pid   = $_GET['pid'];
            if (!$pid) die(json_encode($retVal));

            $user = new user_participant($_GET['pid']);
            if (!$user) die(json_encode($retVal));
            
            foreach($_POST as $perm => $val)  {
                if (!$val) $user->removePermission($perm);
                else $user->addPermission($perm);
            }
            $retVal['status'] = true;
            die(json_encode($retVal));
        break;
        
        case 'admin_getComment':
            // param check
            if (!isset($_GET['uid'])) exit;
            if (!isset($_GET['jid'])) exit;
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin')))
               exit;
              
            // try to load comment
            $comment = jury_rating::s_getMyCommentFor(new user_jury($_GET['jid']), $_GET['uid']);
            if ($comment) echo $comment->get('comment');
            else echo '';            
        break;
        
        case 'admin_loadFolders':
            // param check
            if (!isset($_GET['group'])) exit;
            if (!isset($_GET['id'])) exit;
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin')))
               exit;
              
            // try to load comment
            $comment = jury_rating::s_getMyCommentFor(new user_jury($_GET['jid']), $_GET['uid']);
            if ($comment) echo $comment->get('comment');
            else echo '';            
        break;
        
        case 'admin_getFolderFiles':
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin')))
               exit;
            
            $retVal = array('status' => 0, 'payload' => '<div class="infobox">Keine Dateien</div>', 'count' => 0);
            
            cleanPost($_POST);
            
            if (!isset($_POST['folder_id']))    die(json_encode($retVal));
            if (!isset($_POST['target_group'])) die(json_encode($retVal));
            if (!isset($_POST['target_id']))    die(json_encode($retVal));

            
            $dbVal = db()->send("SELECT * FROM document
                                  WHERE folder_id = '".mres($_POST['folder_id'])."'
                                  AND   target_group = '".mres($_POST['target_group'])."' 
                                  AND   target_id = '".mres($_POST['target_id'])."'
                                  AND   !deleted");
            if (!$dbVal)  {
                $retVal = array('status' => 0, 'payload' => '<div class="infobox">Keine Dateien '.mysql_error().'</div>', 'count' => 0);
                die(json_encode($retVal));
            }

            $block = template::s_getBlockFromTemplate('vorlagen', 'file_item_ext', 
                                                        realpath(frame()->aConfig['root']['local'] . '/content/templates/') . '/',
                                                        realpath(frame()->aConfig['root']['local'] . '/content/code/') . '/');
            if (!$block)  {
                $retVal = array('status' => 0, 'payload' => $block);
                die(json_encode($retVal));
            }
            $out = '';
            $uneven = false;
            foreach($dbVal as $k => $values)  {
                $r = $b = array();
                $r['uneven'] = ($uneven ? 'uneven' : '');
                $r['file_name']      = ($values['filename_orig']);
                $r['file_name_short']      = shortenString($values['filename_orig'],75);
                $r['file_extension'] = $values['extension'];
                $r['file_size'] = humanFileSize($values['size'],0);
                $r['file_id'] = $values['id'];
                $r['file_hash'] = md5($values['filename']);
                
                if (!$out) $b['file_item_head'] = true;
                
                $b['can_delete'] = true;
                $b['show_owner'] = true;
                $r['file_owner'] = $values['owner_group'];
                
                $u = null;
                switch($values['owner_group'])  {
                    case 'admin':       $u = new user_admin($values['owner_id']); break;
                    case 'jury':        $u = new user_jury($values['owner_id']); break;
                    case 'participant': $u = new user_participant($values['owner_id']); break;
                    default : continue;
                }
                
                $r['file_owner_details'] = sesc(
                        $u->get('firstname').' '.$u->get('lastname') .
                        (($u->get('company')) ? '<br />Firma: '.$u->get('company') : '')
                );
                
                $out .= template::s_parseBlock($block, $r, $b, true, true);
            }
            $out .= '</table>';
            
            $retVal = array('status' => 0, 'payload' => $out, 'count' => sizeof($dbVal));
            die(json_encode($retVal));
        break;
        
        case 'admin_saveDocument':
            $retVal = array('status' => 0, 'payload' => 'permission denied'); 
            
            // param check
            if (!isset($_POST['pid']) || !$_POST['pid'])               die(json_encode($retVal));
            if (!isset($_POST['admin_folderID']) || !$_POST['admin_folderID'])   die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin')))
               die(json_encode($retVal));

            if (!isset($_FILES['admin_myDocument']))  die(json_encode($retVal));
            
            $status = document::s_uploadFile2(contest()->get('id'),
                        $_POST['admin_folderID'],
                        'admin', user()->get('id'),
                        'participant', $_POST['pid'],
                        $_FILES['admin_myDocument'], 
                        frame()->aConfig['paths']['files'] .'/contest/documents/',
                        false);

            $retVal = array('status' => 0, 'payload' => 'failed to save'); 
            if (!$status) die(json_encode($retVal));
            // success
            $retVal['status'] = 1;
            $retVal['payload'] = $status;            
            die(json_encode($retVal));            
        break;
        
        case 'admin_deleteDocument':
            cleanPost($_POST);
            
            $retVal = array('status' => 0, 'payload' => 'permission denied'); 
            
            // param check
            if (!isset($_POST['file_id']))  
                die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin')))
               die(json_encode($retVal));

            // get document
            $doc = new document($_POST['file_id']);
            if ($doc)  {
                document::s_delete($_POST['file_id']);
                $retVal = array('status' => 1, 'payload' => 'file deleted'); 
                die(json_encode($retVal));                
            } else {
                $retVal = array('status' => 0, 'payload' => 'no document found'); 
                die(json_encode($retVal));
            }           
        break;
        
        
        
       
        
        /** **************************************************************** **/
        /** JURY **/
        /** **************************************************************** **/
        case 'jury_getComment':
            // param check
            if (!isset($_GET['uid'])) exit;
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin', 'jury')))
               exit;
              
            // try to load comment
            $comment = jury_rating::s_getMyCommentFor(user(), $_GET['uid']);
            if ($comment) echo $comment->get('comment');
            else echo '';            
        break;
        
        case 'jury_saveDocument':
            $retVal = array('status' => 0, 'payload' => 'permission denied'); 
            
            // param check
            if (!isset($_GET['pid']))               die(json_encode($retVal));
            if (!isset($_POST['jury_folderID']))    die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin', 'jury')))
               die(json_encode($retVal));

            if (!isset($_FILES['jury_myDocument']))  die(json_encode($retVal));
            
            $status = document::s_uploadFile2(contest()->get('id'),
                        $_POST['jury_folderID'],
                        'jury', user()->get('id'),
                        'participant', $_GET['pid'],
                        $_FILES['jury_myDocument'], 
                        frame()->aConfig['paths']['files'] .'/contest/documents/',
                        false);

            $retVal = array('status' => 0, 'payload' => 'failed to save'); 
            if (!$status) die(json_encode($retVal));
            // success
            $retVal['status'] = 1;
            $retVal['payload'] = $status;            
            die(json_encode($retVal));            
        break;
        
        case 'jury_deleteDocument':
            cleanPost($_POST);
            
            $retVal = array('status' => 0, 'payload' => 'permission denied'); 
            
            // param check
            if (!isset($_POST['file_id']))  
                die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin', 'jury')))
               die(json_encode($retVal));

            // get document
            $doc = new document($_POST['file_id']);
            // check ownership
            if ($doc->get('owner_group') == 'jury' && $doc->get('owner_id') == user()->get('id'))  {
                document::s_delete($_POST['file_id']);
                $retVal = array('status' => 1, 'payload' => 'file deleted'); 
                die(json_encode($retVal));                
            } else {
                $retVal = array('status' => 0, 'payload' => 'no permission to delete file'); 
                die(json_encode($retVal));
            }           
        break;
        
        /*
        case 'jury_getMyDocuments':
            $retVal = array('status' => 0, 'payload' => ''); 
            
            // param check
            if (!isset($_GET['pid'])) die(json_encode($retVal));
            
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('admin', 'jury')))
               die(json_encode($retVal));
            
            $documents = document::s_getDocuments(contest()->get('id'), user()->get('id'), document::FOR_JURYMEMBER, $_GET['pid']);
            if (!$documents) die(json_encode($retVal));
             
            // list file blocks
            $out = '';
            $block = template::s_getBlockFromTemplate('vorlagen', 'download', frame()->aConfig['root']['local'].'/content/templates/');
            foreach($documents as $id => $obj)  {
                $r = $b = array();
                $r['mime']           = mb_strtoupper($obj->get('extension'));
                $r['class_mime']     = mb_strtolower($obj->get('extension'));
                $r['extension']      = mb_strtoupper($obj->get('extension'));
                $r['filename']       = sesc($obj->get('filename_orig'));
                $r['filename_short'] = sesc(shortenString($obj->get('filename_orig'), 65));        
                $r['link_file']      = frame()->aConfig['root']['web'] . '/getDocument?id='.md5($obj->get('filename_orig'));
                $r['filesize']       = str_pad(humanFileSize($obj->get('size')), 9, ' ', STR_PAD_LEFT);
                $r['docid']          = $obj->get('id');   
                $r['pid']            = $_GET['pid'];

                $out .= template::s_parseBlock($block, $r, $b, true, false);            
            }
            
            // success
            die($out);

        break;
        */
        
        case 'jury_getFolderFiles':
            // access check
            if (!user()->loggedIn() || !user()->inGroup(array('jury', 'admin')))
               exit;
            
            $retVal = array('status' => 0, 'payload' => '<div class="infobox">Keine Dateien</div>', 'count' => 0);
            
            cleanPost($_POST);
            
            if (!isset($_POST['folder_id']))    die(json_encode($retVal));
            if (!isset($_POST['target_group'])) die(json_encode($retVal));
            if (!isset($_POST['target_id']))    die(json_encode($retVal));

            $files = folder::s_getFolderFilesForUser(user(), $_POST['folder_id'], contest()->get('id'));
            if (!$files)  {
                $retVal = array('status' => 0, 'payload' => '<div class="infobox">Keine Dateien '.mysql_error().'</div>', 'count' => 0);
                die(json_encode($retVal));
            }
            

            // filter by target_group and target_id
                            
            if ($_POST['target_group'] && strlen($_POST['target_id']))  {
                $doctmp = $files;
                $files = array();
                foreach($doctmp as $dk => $dv)  {
                    if ($dv->get('target_group') != $_POST['target_group'])
                        continue;
                    if ($dv->get('target_id') != $_POST['target_id'])
                        continue;
                    $files[$dv->get('id')] = $dv;
                }                        
            }   
            
            if (!$files)  {
                $retVal = array('status' => 0, 'payload' => '<div class="infobox">Keine Dateien '.mysql_error().'</div>', 'count' => 0);
                die(json_encode($retVal));
            }
            

            $block = template::s_getBlockFromTemplate('bewertungen/bewerten', 'file_item', 
                                                        realpath(frame()->aConfig['root']['local'] . '/content/templates/backend/jury/') . '/',
                                                        realpath(frame()->aConfig['root']['local'] . '/content/code/backend/jury/') . '/');
            if (!$block)  {
                $retVal = array('status' => 0, 'payload' => $block);
                die(json_encode($retVal));
            }
            $out = '';
            $uneven = false;
            foreach($files as $k => $values)  {
                $r = $b = array();
                $r['uneven']            = ($uneven ? 'uneven' : '');
                
                $r['file_name']         = ($values->get('filename_orig'));
                $r['file_name_short']   = shortenString($values->get('filename_orig'),95);
                
                $r['file_extension']    = $values->get('extension');
                $r['file_size']         = humanFileSize($values->get('size'),0);
                $r['file_id']           = $values->get('id');
                $r['file_hash']         = md5($values->get('filename'));
                $b['can_delete']        = $values->getTemp('deletable');
                if (!strlen($out)) $b['file_item_head'] = true;
                $out .= template::s_parseBlock($block, $r, $b, true, true);
            }
            $out .= '</table>';
            
            $retVal = array('status' => 0, 'payload' => $out, 'count' => sizeof($files));
            die(json_encode($retVal));
        break;
    }
    
}

?>