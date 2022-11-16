<?php

        //                                                                                               //
        //  ---+ Example +---                                                                            //
        //                                                                                               //
        //  $http=new HTTP();                                                                         //
        //                                                                                               //
        //  $parameter="hl=de&ie=ISO-8859-1";                                                     //
        //  $directives="User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; QXW0339d)";      //
        //                                                                                               //
        //  $result=$http->get("www.google.de","/search",$parameter,$directives);                        //
        //  echo $result[content];                                                                       //
        //                                                                                               //
        //  $result=$http->post("www.brot.de","/index.php",$parameter,$directives);  //
        //  echo $result[content];                                                                       //
        //                                                                                               //


        class HTTP
         {
           var $timeout=20;
           var $httpport=80;

           // ---+ GET +---

           function get($host,$file='/',$parameter=false,$directives=false)
            {
              // URL in Host und File zerlegen
              if (strpos($host,"http://")===0) $host=substr($host,7);
              if (($i=strpos($host,"/"))!==false) { $file=substr($host,$i); $host=substr($host,0,$i); }
              // GET-Parameter an File anhängen
              if (is_array($parameter))
                foreach($parameter as $name => $value)
                 {
                   if (!$sign) $sign="?"; else $sign="&";
                   $file.="$sign$name=".urlencode($value);
                 } else if ($parameter) $file.="?$parameter";
              // Seite holen
              $fp=@fsockopen($host,$this->httpport,&$errno,&$errstr,$this->timeout);
              if (!$fp) return($errstr);
              socket_set_timeout($fp,$this->timeout);
              fputs($fp,"GET $file HTTP/1.0\r\n");
              fputs($fp,"Host: $host\r\nConnection: close\r\n");
              if ($directives) fputs($fp,$directives."\r\n");
              fputs($fp,"\r\n");
              while (!feof($fp)) { $line=fgets($fp,32767); if (!$line) break; $content.=$line; }
              fclose($fp);
              // Ergebnis zerlegen
              ereg("\r?\n\r?\n",$content,$matches);
              $pos=strpos($content,$matches[0]);
              $header=substr($content,0,$pos);
              $content=substr($content,$pos+strlen($matches[0]));
              return(array(header=>$header,content=>$content));
            }

           function get_ssl($host,$file='/',$parameter=false,$directives=false)
            {
              // URL in Host und File zerlegen
              if (strpos($host,"https://")===0) $host=substr($host,7);
              if (($i=strpos($host,"/"))!==false) { $file=substr($host,$i); $host=substr($host,0,$i); }
              // GET-Parameter an File anhängen
              if (is_array($parameter))
                foreach($parameter as $name => $value)
                 {
                   if (!$sign) $sign="?"; else $sign="&";
                   $file.="$sign$name=".urlencode($value);
                 } else if ($parameter) $file.="?$parameter";
              // Seite holen
              $fp=@fsockopen($host,443,&$errno,&$errstr,$this->timeout);
              if (!$fp) return($errstr);
              socket_set_timeout($fp,$this->timeout);
              fputs($fp,"GET $file HTTPS/1.0\r\n");
              fputs($fp,"Host: $host\r\nConnection: close\r\n");
              if ($directives) fputs($fp,$directives."\r\n");
              fputs($fp,"\r\n");
              while (!feof($fp)) { $line=fgets($fp,32767); if (!$line) break; $content.=$line; }
              fclose($fp);
              // Ergebnis zerlegen
              ereg("\r?\n\r?\n",$content,$matches);
              $pos=strpos($content,$matches[0]);
              $header=substr($content,0,$pos);
              $content=substr($content,$pos+strlen($matches[0]));
              return(array(header=>$header,content=>$content));
            }


           // ---+ POST +---

           function post($host,$file='/',$parameter=false,$directives=false,$port=false)
            {
              if ($port === false) $port = $this->httpport;

              // URL in Host und File zerlegen
              if (strpos($host,"http://")===0) $host=substr($host,7);
              if (($i=strpos($host,"/"))!==false) { $file=substr($host,$i); $host=substr($host,0,$i); }
              // POST-Parameter in String umwandeln
              if (is_array($parameter))
                foreach($parameter as $name => $value)
                  if (is_array($value)) foreach($value as $foo => $bar) $post.=($post?'&':'').$name.'['.$foo.']='.$bar; else
                  $post.=($post?'&':'').$name."=".urlencode($value); else $post=$parameter;
              // Seite holen
              $fp=@fsockopen($host,$port,&$errno,&$errstr,$this->timeout);
              if (!$fp) return($errstr);
			           socket_set_timeout($fp,$this->timeout);
              fputs($fp,"POST $file HTTP/1.0\r\n");
              fputs($fp,"Host: $host\r\n");
              fputs($fp,"Connection: close\r\n");
              fputs($fp,"Content-Type: application/x-www-form-urlencoded\r\n");
              fputs($fp,"Content-Length: ".strlen($post)."\r\n");
              if ($directives) fputs($fp,$directives."\r\n");
              fputs($fp,"\r\n");
              fputs($fp,$post);
              $line='';
              $content='';
              while (!feof($fp)) { $line=fgets($fp,32767); if (!$line) break; $content.=$line; }
              fclose($fp);
              // Ergebnis zerlegen
              ereg("\r?\n\r?\n",$content,$matches);
              $pos=strpos($content,$matches[0]);
              $header=substr($content,0,$pos);
              $content=substr($content,$pos+strlen($matches[0]));
              return(array('header'=>$header,'content'=>$content));
            }
         }
?>