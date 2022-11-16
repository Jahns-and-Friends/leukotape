<?php
    $digCache = '';

    // Variablenstruktur ausgeben
    function dig($content,$output=1,$depth=0,$nospace=0)
     {
       global $digCache;
       // Konsole
       if (!$_SERVER['SERVER_NAME']) { print_r($content); return; }
       // Einrücken
       $sp='&nbsp;';
       $depspace="";
       $html = '';
       $space = '';
       $notfirst=false;
       if ($depth)
        {
          $space=str_repeat($sp,$depth);
          if (!$nospace) $depspace=$space;
        } else $digCache=array(md5(mt_rand()),md5(mt_rand()));
       // Grafik
       $light1="<font color=\"#AAAAAA\">"; $light2="</font>";
       $red1="<font color=\"red\">"; $red2="</font>"; $rlight1="<font color=\"#550000\">"; $rlight2="</font>";
       $blue1="<font color=\"blue\">"; $blue2="</font>"; $blight1="<font color=\"#000077\">"; $blight2="</font>";
       // Typen unterscheiden
       $type=gettype($content);       
       switch($type)
        {
          case "boolean": $html.=$depspace.($content?"true":"false")." ".$light1."(boolean)".$light2."\n"; break;
          case "integer": $html.=$depspace.$content." ".$light1."(integer)".$light2."\n"; break;
          case "double": $html.=$depspace.$content." ".$light1."(double)".$light2."\n"; break;
          case "resource": $html.=$depspace.$content." ".$light1."(resource)".$light2."\n"; break;
          case "string": $html.=$depspace.chr(34).htmlentities($content).chr(34)." ".$light1."(string(".strlen($content)."))".$light2."\n"; break;
          case "array": case "object":
            if ($type=="array") { $typet="Array"; $sign="()"; $col1=$red1; $col2=$red2; $li1=$rlight1; $li2=$rlight2; } else { $typet="Object ".get_class($content); $sign="{}"; $col1=$blue1; $col2=$blue2; $li1=$blight1; $li2=$blight2; }
            $html.="<a name=\"dig\" style=\"text-decoration:none; font-family:Courier New\">".$depspace.$col1.$typet.$col2."</a>";
            if (($type=="object") && (array_search(($md5=md5(serialize($content))),$digCache)!==false)) $html.=" <a href=\"#dig$md5\" style=\"text-decoration:none;\">".$light1."(reference)".$light2."</a>\n"; else
             {
               if (isset($md5)) $digCache[]=$md5;
               $html.="\n".$space.$sp.$col1.$sign[0].$col2."\n";
               if ($type=="object")  {
                   if (method_exists($content, 'dump')) $content = $content->dump();
                   else $content=get_object_vars($content);
               }
               $count=count($content);
               $max=0;
               foreach($content as $key => $value) $max=max($max,strlen($key));
               foreach($content as $key => $value)
                {
                  if (((gettype($value)=="array") || (gettype($value)=="object")) && ($notfirst)) $html.="\n";
                  $html.=$space.$sp.$sp.$sp."[".$li1.$key.$li2."] ".((!is_array($value) && (!is_object($value)))?str_repeat($sp,$max-strlen($key)):"")."=> ".dig($value,$output,$depth+3,1);
                  if (((gettype($value)=="array") || (gettype($value)=="object")) && ($count>1)) $html.="\n";
                  $notfirst=1; $count--;
                }
               $html.=$space.$sp.$col1.$sign[1].$col2."\n";
             }
            break;
          case "NULL": $html.=$depspace."NULL\n"; break;
          default: $html.=$depspace.chr(34).$content.chr(34)." ".$light1."(unknown type)".$light2."\n";
        }
       // Abschliessen
       if (!$depth)
        {
          $html="<table><tr><td><table bgcolor=\"white\"><tr><td><font face=\"Courier New\" size=\"2\">".str_replace("\n","<br>\n",str_replace("\n\n\n","\n\n",$html))."</font></td></tr></table></td></tr></table>";
          if ($output) echo $html;
        }
       return($html);
    }
?>