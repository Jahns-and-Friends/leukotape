<?php

class template {

    var $baseDirTPL;
    var $baseDirPHP;
    var $extension;
    var $phpFile;

    var $templateData;
    var $parsedData;
    var $placeholders;
    var $blocks;
    var $blockMatch;
    var $blockEnable;


    function __construct($baseDirTPL='', $baseDirPHP='') {
        if (!empty($baseDirTPL)) $this->baseDirTPL = $baseDirTPL;
        else if (isset(frame()->aConfig['paths']['template']['tpl'])) $this->baseDirTPL = frame()->aConfig['paths']['template']['tpl'];

        if (!empty($baseDirPHP)) $this->baseDirPHP = $baseDirPHP;
        else if (isset(frame()->aConfig['paths']['template']['php'])) $this->baseDirPHP = frame()->aConfig['paths']['template']['php'];

        $this->extension    = "tpl";
        $this->templateData = '';
        $this->parsedData   = '';
        $this->placeholders = array();
        $this->blocks       = array();
        $this->blockMatch   = array();
        $this->blockEnable  = array();
        $this->phpFile      = '';
    }

    function getTPL($templateName)  {
        global $g_params;
        $file = false;

        // try to find language file first - normal filename, but with _LANGUAGECODE appended
        if (file_exists($this->baseDirTPL.'/'.($templateName)."_".language().".".$this->extension)) 
            $file = '/'.($templateName)."_".language().".".$this->extension;
        // fallback to version without language string within filename, which is legit if it contains only few text.
        else if (file_exists($this->baseDirTPL.'/'.($templateName).".".$this->extension))
            $file = '/'.($templateName).".".$this->extension;

        if ($file && ($this->templateData = file_get_contents($this->baseDirTPL.$file)) !== FALSE)  {
            #echo "[TPL] ".$file."<br />";
            return true;
        }
    }

    function getPHP($templateName)  {
        global $g_params;
        $file = false;
        $templateName = str_replace('.php', '', $templateName);
        // look for file in main dir
        if (file_exists($this->baseDirPHP.$templateName.".php")) $file = $templateName.".php";
        if ($file && $this->phpFile = $this->baseDirPHP.$file) {
           # echo "[PHP] getPHP found ".$file." for $templateName<br />";
            return true;
        }
    }

    function load($templateName)  {
        $this->getTPL($templateName);
        $this->getPlaceholders();
        $this->locateBlocks();
        $this->getPHP($templateName);
    }

    function getPlaceholders()  {
        if (preg_match_all("/(\[%%.*%%\])/isU", $this->templateData, $match))  {
            foreach ($match[1] as $value)  {
                $key = $this->cleanPlaceholder($value);
                if (!isset($this->placeholders[$key]))
                    $this->placeholders[$key] = '';
            }
        } else return NULL;
        return $this->placeholders;
    }

     function locateBlocks()  {
        $this->blocks       = array();
        $this->blockMatch   = array();
        if (preg_match_all("/(\[##.*##\])(.*)(\\1)/isU", $this->templateData, $match))  {
            foreach($match[2] as $bKey => $bData)
            if (preg_match_all("/(\[##.*##\])(.*)(\\1)/isU", $bData, $matchInner))  {
                foreach($matchInner[1] as $key => $values)  {
                    $this->blockMatch[] = $matchInner[0][$key];
                    $this->blocks[]     = str_replace(array("[##", "##]"), '', $values);
                }
            }

            # copy the placeholders to array.
            # if the value for such a placeholder is 1/true at the start of parsing, this
            # block is kept. if no value is set (or false/0), the block is completely removed.
            # get available blocks with getBlocks, after locating them
            foreach($match[1] as $key => $values)  {
                $this->blockMatch[] = $match[0][$key];
                $this->blocks[]     = str_replace(array("[##", "##]"), '', $values);
            }

            // hack for nested blocks. only works for one specific case ("admin/epsonProducts.php")
            $this->blocks=array_reverse($this->blocks);
            $this->blockMatch=array_reverse($this->blockMatch);
        }
    }

    function getBlocks()  {
        return $this->blocks;
    }

    function getBlockData($blockDescr, $stripTags=true)  {
        if (empty($this->blockMatch)) return null;
        foreach($this->blockMatch as $key => $value)  {
            if (preg_match("/^(\[##$blockDescr##\])(.*)\\1/isU", $value, $match))  {
                if ($stripTags) return $match[2];
                else return $value;
            }

        }
        return null;
    }


    function setData($placeholder, $data)  {
        if (strlen($data)==0 || !isset($this->placeholders[$placeholder])) return false;

        if (is_array($placeholder) && is_array($data) && sizeof($placeholder) == sizeof($data))  {
            foreach($placeholder as $key => $value)
                $this->placeholders[$value] = $data[$key];
        } else $this->placeholders[$placeholder] = $data;
        return true;
    }

	/*
	 * Bug behoben, wo die Platzhalter aus $replaceData nicht �bernommen wurden,
	 * wenn keine PHP Datei geladen wurde.
	 */
    function parse($cleanPlaceholders=true)  {
        global $replaceData, $config, $blockEnable, $g_userid, $g_file, $g_rewrite, $g_page_title;
        if (empty($this->templateData)) return false;

        $this->parsedData = $this->templateData;

        if (empty($this->placeholders))
            $this->placeholders = array();

        // if there is a php file, load it and merge its data with ours
        if (!empty($this->phpFile))  {
            require_once($this->phpFile);
            if (!isset($replaceData)) $replaceData = array();
            $this->placeholders = array_merge($this->placeholders, $replaceData);
        } else {
            if (!is_array($replaceData)) $replaceData = array();
            $this->placeholders = array_merge($this->placeholders, $replaceData);
        }

        if (!empty($this->blocks))  {
            foreach($this->blocks as $key => $value)  {
                if (isset($blockEnable[$value]) && $blockEnable[$value])  {
                    $this->parsedData = str_replace( '[##'.$value.'##]', '', $this->parsedData );
                } else {
                    $this->parsedData = str_replace( $this->blockMatch[$key], '', $this->parsedData );
                }
            }
        }

        foreach($this->placeholders as $key => $value)  {
            $key2  = $this->cleanPlaceholder($key);
            $data = $this->placeholders[$key];
            if (strlen($data) || $cleanPlaceholders)  {
                $this->parsedData = str_replace("[%%".$key."%%]", $data, $this->parsedData);
            }
        }
        return true;
    }

    function output()  {
        if (empty($this->parsedData)) return false;
        echo $this->parsedData;
        return true;
    }

    function getOutput()  {
        if (empty($this->parsedData)) return false;
        return $this->parsedData;
    }

    function quickParse($templateName, $replacements = array(), $blockEnable=array(), $utf8_encode = true, $utf8_preencode_template = false)  {
        global $replaceData, $g_file, $g_rewrite, $g_page_title;

        if (!$this->getTPL($templateName))  {
            echo "cannot find: ".$this->baseDirTPL.$templateName.".".$this->extension."<br>";
            return NULL;
        }

        if ($utf8_preencode_template)  {
            $utf8_encode = false;
            $this->templateData = utf8_encode($this->templateData);
        }

        $this->getPlaceholders();
        if (!empty($replacements))
            $this->placeholders = array_merge($this->placeholders, $replacements);

        if (!$this->getPHP($templateName))  {
          // do NOT return, as a PHP file is optional!
        }
        // do some blockin'
        $this->locateBlocks();
        $this->blockEnable = $blockEnable;

        if (!empty($this->blocks))  {
            foreach($this->blocks as $key => $value)  {
                if (isset($blockEnable[$value]) && $blockEnable[$value])  {
                    $this->templateData = str_replace( '[##'.$value.'##]', '', $this->templateData );
                } else {
                    $this->templateData = str_replace( $this->blockMatch[$key], '', $this->templateData );
                }
            }
        }
        $this->parse();

        return (($utf8_encode) ? utf8_encode($this->getOutput()) : $this->getOutput());
    }

    /// ++++++++++ INTERNAL FUNCTIONS +++++++++++++++++++++++++++++++++++++ ///

    private function cleanPlaceholder($placeHolder)  {
        return str_replace(array("[","]","%"), '', $placeHolder);
    }

    public static function s_quickParse($templateName, $replacements = array(), $blockEnable=array(), $utf8_encode = true, $utf8_preencode_template = false, $baseDirTPL=false, $baseDirPHP=false)  {
        global $g_userid, $g_file, $g_rewrite, $g_page_title;
        $self = new template();

        if ($baseDirTPL) $self->baseDirTPL = $baseDirTPL;
        else if (isset(frame()->aConfig['paths']['template']['tpl']))  $self->baseDirTPL = frame()->aConfig['paths']['template']['tpl'];
        if ($baseDirPHP) $self->baseDirPHP = $baseDirPHP;
        else if (isset(frame()->aConfig['paths']['template']['php']))  $self->baseDirPHP = frame()->aConfig['paths']['template']['php'];

        $self->quickParse($templateName, $replacements, $blockEnable, $utf8_encode, $utf8_preencode_template);
        $text = $self->getOutput();
        return $text;
    }


    public static function s_getBlockFromTemplate($templateName, $blockName, $baseDirTPL=false, $baseDirPHP=false)  {
        $templateName = trim($templateName);
        $blockName    = trim($blockName);

        if (!strlen($templateName))  return false;

        $blockMatch = $blocks = array();
        $self = new template();

        // new: 26.08.2012
        if ($baseDirTPL) $self->baseDirTPL = $baseDirTPL;
        else if (isset(frame()->aConfig['paths']['template']['tpl']))  $self->baseDirTPL = frame()->aConfig['paths']['template']['tpl'];
        if ($baseDirPHP) $self->baseDirPHP = $baseDirPHP;
        else if (isset(frame()->aConfig['paths']['template']['php']))  $self->baseDirPHP = frame()->aConfig['paths']['template']['php'];
        //

        if (!is_array($blockName))  {
            if (!strlen($blockName)) return false;
            $blockName = array($blockName);
        } else if (empty($blockName)) return false;

        $self->getTPL($templateName);
        $template_data = $self->templateData;
        // trim file data
        $template_data = trim($template_data);
        // match blocks
        if (preg_match_all("/(\[##.*##\])(.*)(\\1)/isU", $template_data, $match))  {
            foreach($match[2] as $bKey => $bData)
            if (preg_match_all("/(\[##.*##\])(.*)(\\1)/isU", $bData, $matchInner))  {
                foreach($matchInner[1] as $key => $values)  {
                    $blockMatch[] = $matchInner[0][$key];
                    $blocks[]     = str_replace(array("[##", "##]"), '', $values);
                }
            }

            # copy the placeholders to array.
            # if the value for such a placeholder is 1/true at the start of parsing, this
            # block is kept. if no value is set (or false/0), the block is completely removed.
            # get available blocks with getBlocks, after locating them
            foreach($match[1] as $key => $values)  {
                $blockMatch[] = $match[0][$key];
                $blocks[]     = str_replace(array("[##", "##]"), '', $values);
            }

            // hack for nested blocks.
            $blocks=array_reverse($blocks);
            $blockMatch=array_reverse($blockMatch);

            // locate block that was requested
            if (empty($blocks) || empty($blockMatch)) return false;

            foreach($blockName as $bKey => $bValue)  {
                foreach($blocks as $key => $value)  {
                    if ($value == $bValue)  {
                        $ret = str_replace('[##'.$value.'##]', '', $blockMatch[$key]);
                        $ret = trim($ret);
                        return $ret;
                    }
                }
            }
            return false;
        }
    }

    public static function s_parseBlock($blockData, $replacements=false, $blockEnable=false, $cleanPlaceholders = true, $utf8_encode = true)  {
        global $g_title;
        if (!strlen($blockData)) return false;

        $foo = new template;
        $foo->templateData = $blockData;
        $foo->placeholders = $replacements;
        $foo->getPlaceholders();
        $foo->locateBlocks();
        $foo->blockEnable = $blockEnable;

        if (!empty($foo->blocks))  {
            foreach($foo->blocks as $key => $value)  {
                if (isset($blockEnable[$value]) && $blockEnable[$value] == true)  {
                    $foo->templateData = str_replace( '[##'.$value.'##]', '', $foo->templateData );
                } else {
                    $foo->templateData = str_replace( $foo->blockMatch[$key], '', $foo->templateData );
                }
            }
        }
        $foo->parse($cleanPlaceholders);
        return (($utf8_encode) ? utf8_encode($foo->getOutput()) : $foo->getOutput());
    }

    public static function s_removePlaceholders($str)  {
        $str = preg_replace("/(\[%%[^%]+%%\])/isU", '', $str);
        $str = preg_replace("/(\[##[^#]+##\])(.*)(\\1)/isU", '', $str);
        return $str;
    }

    public static function s_templateExists($template)  {

    }

    /**
     * MIND the context of the required file!
     * Include any globals you need here.
     */
    public static function s_loadPHP($filename)  {
        global $replaceData, $blockEnable, $g_file, $g_rewrite, $g_channel, $g_params;
        $tmp = new template();
        $tmp->getPHP($filename);

        if ($tmp->phpFile) {
            require_once($tmp->phpFile);
            #echo "[PHP] LOADED: ".$tmp->phpFile."<br />";
            return true;
        }
        return false;

    }
}
?>
