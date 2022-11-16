<?php
        //
        //  ---+ mail Klasse +--- 
        //
        //
        //  Konstruktor: mailer mailer(string from, to, subject, text, int send)
        //
        //  01.03.2011 fixed $config global to frame()->aConfig in overrides
        //  13.07.2011 fixed NOTICE errors due vars not inited
        //



        class mailer
         {
           protected $htmlrelated=array();
           protected $attachments=array();
           protected $xmailer="PHP MailClass v1.5";
           protected $multipartwarning="This is a multi-part message in MIME format.";
           protected $charset="iso-8859-1";
           protected $types=array
            (
              "\.gif\$"   => "image/gif",
              "\.jpe?g\$" => "image/jpeg",
              "\.css\$"   => "text/css",
              "\.doc\$"   => "application/msword",
              "\.pdf\$"   => "application/pdf"
            );
           protected $break="\n";

           protected $sender = '';
           protected $headers = '';
           protected $textcontent = '';
           protected $htmlcontent = '';
           protected $content  = '';
              
           // Konstruktor
           function __construct($from="",$to="",$subject="",$text="",$send=0)
            {
              if ($from!="") $this->from=$from;
              if ($to!="") $this->to=$to;
              $this->sender = '';
              if ($subject!="") $this->subject=$subject;
              if ($text) $this->setText($text);
              if ($send) $this->send();
            }

           // Text-Content setzen
           function setText($content="")
            {
              if ($content=="")
               {
                 $content=$this->htmlcontent;
                 $content=preg_replace("/<style>[^<]*</style>/","",$content); // Stylezz raus
                 $content=preg_replace("/<img[^>]*>/","",$content); // Bilder raus
                 $content=preg_replace("/<a[ ]*href=\"([^#\"]*)\"[^>]*>/","\\1 ",$content); // Links umwandeln
                 $content=preg_replace("/&.?.?.?.?.?;/","",$content); // &nbsp; und ähnliches raus
                 $content=preg_replace("/<br[^>]*>/","\r\n",$content); // <br> in Zeilenumbrüche umwandeln
                 $content=preg_replace("/<[^>]*>/","",$content); // restliche Tags raus
                 $content=preg_replace("/(\r?\n) */","\\1",$content); // Einrückungen raus
               }
              $this->textcontent=trim($content);
            }
        
           // HTML-Content setzen
           function setHtml($content="",$parse=0,$path="")
            {
              $this->cidcount=0;
              $this->htmlrelated=array();
              if ($parse) // Grafiken HTML-related ersetzen
                {
                 $fields=array("src","background");
                   for ($m=0;$m<count($fields);$m++)
                    {
                    preg_match_all("/<[^>]*".$fields[$m]."=\"([^\"]*)\"[^>]*/i",$content,$images);
                    for ($i=0;$i<count($images[1]);$i++)
                     {
                       $cid = NULL;
                       $name=$images[1][$i]; if (preg_match('/^http:\/\//',$name)) continue;
                       $basename=basename($name);
                       for ($f=0;$f<count($names);$f++) if ($names[$f][0]==$name) { $cid=$names[$f][1]; break; }
                       if (!$cid)
                        {
                          $fp=fopen($path.$images[1][$i],'r'); $image=fread($fp,1000000); fclose($fp);
                          if (!$fp) die("mailerror: ".$path.'/'.$images[1][$i]);
                          $cid=$this->addHTMLRelated($image,$this->guessContentType($basename),$basename);
                          $names[]=array($name,$cid);
                        }
                       $hit=$images[0][$i];
                       $newhit=str_replace($images[1][$i],"cid:$cid",$hit);
                       $content=str_replace($hit,$newhit,$content);
                     }
                    }
               }
              $this->htmlcontent=trim($content);
            }

            public function setCharset($newCharset) { $this->charset = $newCharset; }
            
           // HTML related Attachment hinzufügen
           function addHTMLRelated($content,$type,$name)
            {
              global $SERVER_NAME; if ($SERVER_NAME=="") $servername="mailer.com"; else $servername=$SERVER_NAME;
              $sum=md5($name); $cid="part".($this->cidcount+1).".".substr($sum,0,8).".".substr($sum,8,8)."@".$servername; $this->cidcount++;
              $this->htmlrelated[]=array('content'=>$content,'contenttype'=>$type,'cid'=>$cid,'name'=>$name);
              return($cid);
            }
           function guessContentType($name)
            {
              foreach($this->types as $regexp => $type) if (preg_match($regexp,$name)) return($type);
              return("unknown");
            }
        
           // Attachment hinzufügen
           function addAttachment($content,$name,$contenttype="")
            {
              if (!$contenttype) $contenttype=$this->guessContentType($name);
              $this->attachments[]=array('content'=>$content,'contenttype'=>$contenttype,'name'=>$name);
            }
        
        
           // ---+ Interne Methoden die nur von send() verwendet werden sollten +---
           
           // Text in Quoted Printable umwandeln
           function encodeQuotedPrintable($content)
            {
              // Gleichheitszeichen encoden
              $content=str_replace("=","=3D",trim($content));
              // 8-Bit Zeichen encoden
              $c=strlen($content);
              for($i=0;$i<$c;$i++) if (ord($content[$i])>126)
               {
                 $content=substr($content,0,$i)."=".strtoupper(dechex(ord($content[$i]))).substr($content,$i+1);
                 $c=strlen($content); $i+=2;
               }
              // Zeilenlänge auf 72 einschränken
              $lines=preg_split("/\r?\n/",$content); $content = '';
              foreach($lines as $line)
               {
                 while (strlen($line)>72)
                  {
                    $i=71; while (($line[$i-1]=="=") || ($line[$i-2]=="=")) $i--;
                    $content.=substr($line,0,$i)."=".$this->break;
                    $line=substr($line,$i);
                  }
                 $content.=$line.$this->break;
               }
              $content=trim($content);
              // Leerzeichen und Tab am Ende der Zeile encoden
              $content=str_replace(chr(0x20).$this->break,"=20".$this->break,$content);
              $content=str_replace(chr(0x09).$this->break,"=09".$this->break,$content);
              return($content);
            }
            
           // Textblock einfügen
           function addContentText()
            {
              $this->content.="Content-Type: text/plain; charset=".$this->charset.$this->break;
              $this->content.="Content-Transfer-Encoding: quoted-printable".$this->break.$this->break;
              $this->content.=$this->encodeQuotedPrintable($this->textcontent).$this->break.$this->break;
            }
            
           // HTML-Block einfügen
           function addContentHTML()
            {
              $this->content.="Content-Type: text/html;".$this->break;
              $this->content.=" charset=".$this->charset.$this->break;
              $this->content.="Content-Transfer-Encoding: quoted-printable".$this->break.$this->break;
              $this->content.=$this->encodeQuotedPrintable($this->htmlcontent).$this->break.$this->break;
            }
                     
           // Boundary erstellen
           function getBoundary() { return(strtoupper("----=_NextPart_".date("YmdHis")."_".rand(1000000,9000000))); }
            
           // Multipart-Block beginnen
           function addContentMultipart($type,$internaltype="")
            {
              $boundary=$this->getBoundary();
              $this->content.="Content-Type: multipart/$type;".$this->break;
              if ($internaltype) $this->content.=" type=\"$internaltype\";".$this->break;
              $this->content.=" boundary=\"$boundary\"".$this->break.$this->break;
              if (!$this->mpwarningsent) $this->content.=$this->multipartwarning.$this->break;
              $this->content.=$this->break."--$boundary".$this->break;
              $this->mpwarningsent=1;
              return($boundary);
            }
            
           // HTML related-Bereiche einfügen
           function addContentHTMLRelated($boundary)
            {
              $c=count($this->htmlrelated);
              for ($i=0;$i<$c;$i++)
               {
                 $part=$this->htmlrelated[$i];
                 if ($part['name']) $name=";".$this->break." name=\"".$part['name']."\""; else $name="";
                 $this->content.="Content-Type: ".$part['contenttype'].$name.$this->break;
                 $this->content.="Content-Transfer-Encoding: base64".$this->break;
                 $this->content.="Content-ID: <".$part[cid].">".$this->break.$this->break;
                 $this->content.=chunk_split(base64_encode($part['content']),72,$this->break).$this->break;
                 $this->content.="--".$boundary;
                 if ($i==($c-1)) $this->content.="--";
                 $this->content.=$this->break;
               }
            }
        
           // Attachments einfügen
           function addContentAttachments($boundary)
            {
              $c=count($this->attachments);
              for ($i=0;$i<$c;$i++)
               {
                 $this->content.="Content-Type: ".$this->attachments[$i]['contenttype'].";".$this->break;
                 $this->content.=" name=\"".$this->attachments[$i]['name']."\"".$this->break;
                 $this->content.="Content-Transfer-Encoding: base64".$this->break;
                 $this->content.="Content-Disposition: attachment;".$this->break;
                 $this->content.=" filename=\"".$this->attachments[$i]['name']."\"".$this->break.$this->break;
                 $this->content.=chunk_split(base64_encode($this->attachments[$i]['content']),72,$this->break).$this->break;
                 $this->content.="--".$boundary;
                 if ($i==($c-1)) $this->content.="--";
                 $this->content.=$this->break;
               }
            }
            
           // Subject encoden
           function encodeSubject($subject)
            {
              $encode = 0;
              $new = '';
              $l=strlen($subject);
              for($i=0;$i<$l;$i++) if (ord($subject[$i])>126) { $encode=1; break; }
              if (!$encode) return($subject);
              for($i=0;$i<$l;$i++) { $c=ord($subject[$i]); if ($c<127) $new.=chr($c); else $new.="=".strtoupper(dechex($c)); }
              return('=?'.$this->charset.'?Q?'.$new.'?=');
            }


           // Mail abschicken
           function send($preview=0)
            {
              // Werte säubern
              $this->from=trim($this->from); $this->to=trim($this->to); $this->subject=trim($this->subject);
              if ($this->sender=='') $this->sender=$this->from;
              if (preg_match("/<([^>]+)>/",$this->sender,$matches)) $this->sender=$matches[1];

              $this->textcontent = (($this->textcontent) ? $this->textcontent : '');
              $this->htmlcontent = (($this->htmlcontent) ? $this->htmlcontent : '');
              
              // Backup erstellen
              $this->backup=array($this->to,$this->subject,$this->sender,$this->headers,$this->textcontent,$this->htmlcontent);
            
              // Header beginnen
              $this->mpwarningsent=0;
              unset($this->content);
              $this->content.="MIME-Version: 1.0".$this->break;
              if ($this->from) $this->content.="From: ".$this->from.$this->break;
              if ($this->xmailer) $this->content.="X-Mailer: ".$this->xmailer.$this->break;

              // Frame-Override / Frame-BCC
              
              if (isset(frame()->aConfig['mailer']['bcc']) && frame()->aConfig['mailer']['bcc'])
               {
                 if (!is_array(frame()->aConfig['mailer']['bcc'])) $bcc=array(frame()->aConfig['mailer']['bcc']); else $bcc=frame()->aConfig['mailer']['bcc'];
                 foreach($bcc as $email) $this->headers=trim($this->headers).$this->break."Bcc: ".$email.$this->break;
               }
              if (isset(frame()->aConfig['mailer']['override']) && strlen(frame()->aConfig['mailer']['override']))
               {
                 $overridetext="[Mailer override]".$this->break."Original destination: ".$this->to.$this->break.(trim($this->headers)?"Original headers:".$this->break.trim($this->headers).$this->break:"").$this->break.$this->break;
                 if ($this->textcontent) $this->textcontent=$overridetext.$this->textcontent;
                 if ($this->htmlcontent) $this->htmlcontent=str_replace($this->break,"<br>".$this->break,$overridetext).$this->htmlcontent;
                 $this->to=frame()->aConfig['mailer']['override'];
                 $this->sender=$this->to;
                 $this->subject="[Mailer override] ".$this->subject;
                 unset($this->headers);
               }
               
              // Zusätzliche Header anfügen
              if (isset($this->headers) && strlen($this->headers)) $this->content.=trim($this->headers).$this->break;
              
              // Bei Attachments zusätzlichen Multipartheader generieren
              if (count($this->attachments)) $boundary3=$this->addContentMultipart("mixed");

              // Reine Text-Mail
              if (!isset($this->htmlcontent) || !$this->htmlcontent) $this->addContentText();
              
              // Reine HTML-Mail
              if ((!$this->textcontent) && (!count($this->htmlrelated))) $this->addContentHTML();
              
              // Reine HTML-Mail mit Related-Teilen
              if ((!$this->textcontent) && (count($this->htmlrelated)))
               {
                 $boundary=$this->addContentMultipart("related");
                 $this->addContentHTML();
                 $this->content.="--".$boundary.$this->break;
                 $this->addContentHTMLRelated($boundary);
               }

              // Text/HTML-Mail
              if (($this->textcontent) && ($this->htmlcontent) && (!count($this->htmlrelated)))
               {
                 $boundary=$this->addContentMultipart("alternative");
                 $this->addContentText();
                 $this->content.="--".$boundary.$this->break;
                 $this->addContentHTML();
                 $this->content.="--".$boundary."--".$this->break;
               }
               
              // Text/HTML-Mail mit Related-Teilen
              if (($this->textcontent) && ($this->htmlcontent) && (count($this->htmlrelated)))
               {
                 $boundary1=$this->addContentMultipart("related","multipart/alternative");
                 $boundary2=$this->addContentMultipart("alternative");
                 $this->addContentText();
                 $this->content.="--".$boundary2.$this->break;
                 $this->addContentHTML();
                 $this->content.="--".$boundary2."--".$this->break.$this->break;
                 $this->content.="--".$boundary1.$this->break;
                 $this->addContentHTMLRelated($boundary1);
               }
               
              // Attachments anhängen
              if (count($this->attachments))
               {
                 $this->content.=$this->break."--".$boundary3.$this->break;
                 $this->addContentAttachments($boundary3);
               }

              // Mail abschicken
              if ((!$preview) && ($this->to))
               {
                 $i=strpos($this->content,$this->break.$this->break);
                 $mailheaders=substr($this->content,0,$i);
                 $mailcontent=substr($this->content,$i+2*strlen($this->break));
                 $merg=mail($this->to,$this->encodeSubject($this->subject),trim($mailcontent).$this->break,$mailheaders,"-f".$this->sender);
               }

              // Backup zurück
              list($this->to,$this->subject,$this->sender,$this->headers,$this->textcontent,$this->htmlcontent)=$this->backup;
              
              if ($preview) return($this->content);
              return($merg);
            }
         }
?>