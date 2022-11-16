<?php
        // Braucht in Perl:
        //   - Spreadsheet::WriteExcel
        //   - Parse::RecDescent
        //   - Text::Balanced

        class BatExcelUnicode
         {
           var $xlsfile;
           var $perlfile;
           var $createfile=1;
           var $fp;
           var $sheetunit=0;
           var $formatunit=0;

           function BatExcelUnicode($xlsfile=0)
            {
              if (!$xlsfile)
               {
                 $xlsfile="/tmp/excel_".date("YmdHis")."-".rand(1000,9999).".xls";
                 $this->createfile=0;
               }
              $this->xlsfile=$xlsfile;
              $this->perlfile="/tmp/batexcel_perl_".date("YmdHis")."-".rand(1000,9999).".pl";

              $this->excelunit++;
              $this->fp=fopen($this->perlfile,'wb+');
              fwrite ($this->fp,"use strict;\n");
              fwrite ($this->fp,"use Spreadsheet::WriteExcel;\n");
              fwrite ($this->fp,"my \$excel=new Spreadsheet::WriteExcel (\"".$this->xlsfile."\");\n");
            }

           function closeExcel()
            {
              fwrite ($this->fp,"\$excel->close();\n");
              fclose ($this->fp);
              $errorfile='/tmp/batexcelerror';
              exec("perl $this->perlfile 2> $errorfile");
              if (@filesize($errorfile)) echo "BatExcel error: ".ereg_replace("\r?\n","\n<br>",file_get_contents($errorfile));
              @unlink($errorfile);
              unlink($this->perlfile);
              if (!$this->createfile)
               {
                 $bin=file_get_contents($this->xlsfile);
                 unlink($this->xlsfile);
                 return($bin);
               }
            }
           function setTempdir($dir) { fwrite ($this->fp,"\$excel->set_tempdir(\"".$dir."\");\n"); }
           function &addWorkSheet($value="")
            {
              $this->sheetunit++;
              $sheet = new BatExcelSheet($this->sheetunit,$this->fp,$value);
              $sheet->name=$value;
              return ($sheet);
            }
           function &addFormat($array="")
            {
              $this->formatunit++;
              $format = new BatExcelFormat($this->formatunit,$this->fp, $this);
              if ($array) $format->setProperties($array);
              return ($format);
            }
           function setCustomColor($index,$red,$green=-1,$blue=-1)
            {
              if ($green==-1 || $blue==-1) $color=$index.",\"$red\"";
              else $color=$index.",".$red.",".$green.",".$blue;
              fwrite ($this->fp,"\$excel->set_custom_color(".$color.");\n");
              return ($index);
            }
           function setPaletteX15() { fwrite ($this->fp,"\$excel->set_palette_xl5();\n"); }
           function setCodepage($codepage) { fwrite ($this->fp,"\$excel->set_codepage($codepage);\n"); }
         }


        class BatExcelSheet
         {

           var $formulaunit=0;

           function BatExcelSheet($unit,$fp,$value="")
            {
              $this->fp=$fp;
              $this->unit=$unit;
              $this->currentRow=0;
              if ($value) $value="\"$value\"";
              fwrite ($this->fp,"my \$sheet".$this->unit." = \$excel->addworksheet(".$value.");\n");
            }

           function clearString($string)
            {
              $string=str_replace('\\','\\\\',$string); //'
              $content=str_replace("'","\'",$string);
              return ($content);
            }

           function write($row,$col="",$content="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$content;
                 $content=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit; else $formatvar = '';
              fwrite ($this->fp,"\$sheet".$this->unit."->write($rowcol,'".str_replace("'","\\'",$content)."'$formatvar);\n");
            }

           function write_unicode($row,$col="",$content="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$content;
                 $content=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit; else $formatvar = '';
              fwrite ($this->fp,"\$sheet".$this->unit."->write_unicode($rowcol,'".str_replace("'","\\'",$content)."'$formatvar);\n");
            }


           function writeNumber($row,$col="",$number="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$number;
                 $number=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              fwrite ($this->fp,"\$sheet".$this->unit."->write_number($rowcol,$number$formatvar);\n");
            }
           function writeString($row,$col="",$string="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$string;
                 $string=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;

              $string="'".$this->clearString($string)."'";
              fwrite ($this->fp,"\$sheet".$this->unit."->write_string($rowcol,$string$formatvar);\n");
            }
           function keepLeadingZeros($zero=1) { fwrite ($this->fp,"\$sheet".$this->unit."->keep_leading_zeros($zero);\n"); }
           function writeBlank($row,$col="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              fwrite ($this->fp,"\$sheet".$this->unit."->write_blank($rowcol,\"\"$formatvar);\n");
            }

           // Proprietäre Funktion mit internem Zeiger (Wolfgang, 13.10.2003)
           function writeNextRow($array,$format="")
            {
              $this->writeRow($this->currentRow,0, $array,$format);
              $this->currentRow++;
            }

            function writeNextRowUnicode($array,$format="")  {
              if (!is_array($array)) return false;
              if (!empty($array))  {
                  $col = 0;
                  foreach($array as $key => $value)  $this->write_unicode($this->currentRow,$col++,$value,$format);
              }
              $this->currentRow++;
            }

           function writeRow($row,$col="",$array="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$array;
                 $array=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;

              // Array zerlegen (Überprüfung ob array ein oder zwei dimensional ist)
              $count1=count($array);
              $i=1;
              foreach ($array as $key1 => $value1)
               {
                 if (is_array($value1))
                  {
                    $count2=count($value1);
                    $content.="[";
                    $j=1;
                    foreach ($value1 as $key2 => $value2)
                     {
                       $content.="'".$this->clearString($value2)."'";
                       if ($j<$count2) $content.=",";
                       $j++;
                     }
                    $content.="]";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
                 else
                  {
                    $content.="'".$this->clearString($value1)."'";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
               }
              $arraycontent="my @array=(".$content.");";
              fwrite ($this->fp,"$arraycontent\n");
              fwrite ($this->fp,"\$sheet".$this->unit."->write_row($rowcol,\@array$formatvar);\n");
            }

           function writeRowUnicode($row,$col="",$array="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$array;
                 $array=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;

              // Array zerlegen (Überprüfung ob array ein oder zwei dimensional ist)
              $count1=count($array);
              $i=1;
              foreach ($array as $key1 => $value1)
               {
                 if (is_array($value1))
                  {
                    $count2=count($value1);
                    $content.="[";
                    $j=1;
                    foreach ($value1 as $key2 => $value2)
                     {
                       $content.="'".$this->clearString($value2)."'";
                       if ($j<$count2) $content.=",";
                       $j++;
                     }
                    $content.="]";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
                 else
                  {
                    $content.="'".$this->clearString($value1)."'";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
               }
              $arraycontent="my @array=(".$content.");";
              fwrite ($this->fp,"$arraycontent\n");
              fwrite ($this->fp,"\$sheet".$this->unit."->write_unicode($rowcol,\@array$formatvar);\n");
            }

           function writeCol($row,$col="",$array="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$array;
                 $array=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;

              #Array zerlegen (Überprüfung ob array ein oder zwei dimensional ist)
              $count1=count($array);
              $i=1;
              foreach ($array as $key1 => $value1)
               {
                 if (is_array($value1))
                  {
                    $count2=count($value1);
                    $content.="[";
                    $j=1;
                    foreach ($value1 as $key2 => $value2)
                     {
                       $content.="'".$this->clearString($value2)."'";
                       if ($j<$count2) $content.=",";
                       $j++;
                     }
                    $content.="]";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
                 else
                  {
                    $content.="'".$this->clearString($value1)."'";
                    if ($i<$count1) $content.=",";
                    $i++;
                  }
               }
              $arraycontent="my @array=(".$content.");";
              fwrite ($this->fp,"$arraycontent\n");
              fwrite ($this->fp,"\$sheet".$this->unit."->write_col($rowcol,\@array$formatvar);\n");
            }
           function writeUrl($row,$col="",$url="",$string="",$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 if (!$string && !$format) $format=$url;
                 else { $format=$string; $string=$url; }
                 $url=$col;
               }
              else
               {
                 $rowcol=$row.",".$col;
                 if (is_object($string)) { $format=$string; $string=""; }
               }
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              if ($string) $urlstring="\"$url\",\"$string\"";
              else $urlstring="\"$url\"";
              fwrite ($this->fp,"\$sheet".$this->unit."->write_url($rowcol,$urlstring$formatvar);\n");
            }

           function writeUrlRange($row1,$col1,$row2="",$col2="",$url="",$string="",$format="")
            {
              if (is_string($row1))
               {
                 $rowcol="\"$row1\"";
                 if ($row2 && !$col2)
                  {
                    if (is_object($row2)) $format=$row2;
                    else $string=$row2;
                  }
                 if ($row2 && $col2) { $format=$col2; $string=$row2; }
                 $url=$col1;
               }
              else
               {
                 $rowcol=$row1.",".$col1.",".$row2.",".$col2;
                 if (is_object($string)) { $format=$string; $string=""; }
               }
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              if ($string) $urlstring="\"$url\",\"$string\"";
              else $urlstring="\"$url\"";
              fwrite ($this->fp,"\$sheet".$this->unit."->write_url_range($rowcol,$urlstring$formatvar);\n");
            }
           function writeFormula($row,$col,$formula,$format="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $format=$formula;
                 $formula=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              fwrite ($this->fp,"\$sheet".$this->unit."->write_formula($rowcol,\"$formula\"$formatvar);\n");
            }
           function storeFormula($formula)
            {
              $this->formulaunit++;
              fwrite ($this->fp,"my \$formula$this->formulaunit = \$sheet".$this->unit."->store_formula(\"$formula\");\n");
              return ($this->formulaunit);
            }
           function repeatFormula($row,$col,$formula="",$format="",$pattern="",$replace="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $replace=$pattern;
                 $pattern=$format;
                 $format=$formula;
                 $formula=$col;
               }
              else $rowcol=$row.",".$col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              else $formatvar=",undef";
              fwrite ($this->fp,"\$sheet".$this->unit."->repeat_formula($rowcol,\$formula$formula$formatvar,\"$pattern\",\"$replace\");\n");
            }
           function writeComment($row,$col,$string="")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $string=$col;
               }
              else $rowcol=$row.",".$col;
              fwrite ($this->fp,"\$sheet".$this->unit."->write_comment($rowcol,\"$string\");\n");
            }
           function insertBitmap($row,$col,$filename="optional",$x="optional",$y="optional",$scale_x="optional",$scale_y="optional")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $scale_y=$scale_x;
                 $scale_x=$y;
                 $y=$x;
                 $x=$filename;
                 $filename=$col;
               }
              else $rowcol=$row.",".$col;
              if ($x!="default")
               {
                 if ($scale_x!="optional") $coords=",".$x.",".$y.",".$scale_x.",".$scale_y;
                 else $coords=",".$x.",".$y;
               }
              fwrite ($this->fp,"\$sheet".$this->unit."->insert_bitmap($rowcol,\"$filename\"$coords);\n");
            }
           function getName() { return($this->name); }
           function activate() { fwrite ($this->fp,"\$sheet".$this->unit."->activate();\n"); }
           function select() { fwrite ($this->fp,"\$sheet".$this->unit."->select();\n"); }
           function setFirstSheet() { fwrite ($this->fp,"\$sheet".$this->unit."->set_first_sheet();\n"); }
           function protect($password="")
            {
              if ($password) $password="\"$password\"";
              fwrite ($this->fp,"\$sheet".$this->unit."->protect($password);\n");
            }
           function setSelection($first_row,$first_col="",$last_row="optional",$last_col="optional")
            {
              if (is_string($first_row)) $rowcol="\"$first_row\"";
              else if ($last_row!="optional") $rowcol="$first_row,$first_col,$last_row,$last_col";
              fwrite ($this->fp,"\$sheet".$this->unit."->set_selection($rowcol);\n");
            }
           function setRow($row,$height="undef",$format="undef",$hidden="optional",$level="optional")
            {
              if (is_string($row)) $row="\"$row\"";
              if ($hidden!="optional") $hiddenlevel=",$hidden";
              if ($level!="optional") $hiddenlevel.=",$level";
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              else if ($hiddenlevel) $formatvar=",".$format;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_row($row,$height$formatvar$hiddenlevel);\n");
            }
           function setColumn($first_col,$last_col,$width="undef",$format="undef",$hidden="optional",$level="optional")
            {
              if (is_string($first_col))
               {
                 $col="\"$first_col\"";
                 $optional=$hidden;
                 if ($format=="undef") $hidden="optional";
                 else $hidden=$format;
                 $format=$width;
                 $width=$last_col;
               }
              else $col=$first_col.", ".$last_col;
              if ($hidden!="optional") $hiddenlevel=",$hidden";
              if ($level!="optional") $hiddenlevel.=",$level";
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              else if ($hiddenlevel) $formatvar=",".$format;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_column($col,$width$formatvar$hiddenlevel);\n");
            }
           function outlineSettings($visible=1,$symbols_below="optional",$symbols_right="optional",$auto_style="optional")
            {
              $settings=$visible;
              if ($symbols_below!="optional") $settings.=",".$symbols_below;
              if ($symbols_right!="optional") $settings.=",".$symbols_right;
              if ($auto_style!="optional") $settings.=",".$auto_style;
              fwrite ($this->fp,"\$sheet".$this->unit."->outline_settings($settings);\n");
            }
           function freezePanes($row,$col="optional",$top_row="optional",$left_col="optional")
            {
              if (is_string($row))
               {
                 $rowcol="\"$row\"";
                 $left_col=$top_row;
                 $top_row=$col;
               }
              else
               {
                 $rowcol=$row.",".$col;
                 if ($top_row!="optional") $topleft=",".$top_row.",".$left_col;
               }
              fwrite ($this->fp,"\$sheet".$this->unit."->freeze_panes($rowcol$topleft);\n");
            }
           function thawPanes($y,$x,$top_row="optional",$left_col="optional")
            {
              if ($top_row!="optional") $topleft=",".$top_row.",".$left_col;
              fwrite ($this->fp,"\$sheet".$this->unit."->thaw_panes($x,$y$topleft);\n");
            }
           function mergeRange($first_row,$first_col="",$last_row="",$last_col="",$content="",$format="")
            {
              if (is_string($first_row))
               {
                 $rowcol="\"$first_row\"";
                 $format=$last_row;
                 $content=$first_col;
               }
              else $rowcol=$first_row.",".$first_col.",".$last_row.",".$last_col;
              if (is_object($format)) $formatvar=",\$format".$format->unit;
              fwrite ($this->fp,"\$sheet".$this->unit."->merge_range($rowcol,\"$content\"$formatvar);\n");
            }
           function setZoom($scale) { fwrite ($this->fp,"\$sheet".$this->unit."->set_zoom($scale);\n"); }
           function setLandscape() { fwrite ($this->fp,"\$sheet".$this->unit."->set_landscape();\n"); }
           function setPortrait() { fwrite ($this->fp,"\$sheet".$this->unit."->set_portrait();\n"); }
           function setPaper($index) { fwrite ($this->fp,"\$sheet".$this->unit."->set_paper($index);\n"); }
           function centerHorizontally() { fwrite ($this->fp,"\$sheet".$this->unit."->center_horizontally();\n"); }
           function centerVertically() { fwrite ($this->fp,"\$sheet".$this->unit."->center_vertically();\n"); }
           function setMargins($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins($inches);\n"); }
           function setMarginsLR($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_LR($inches);\n"); }
           function setMarginsTB($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_TB($inches);\n"); }
           function setMarginsLeft($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_left($inches);\n"); }
           function setMarginsRight($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_right($inches);\n"); }
           function setMarginsTop($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_top($inches);\n"); }
           function setMarginsBottom($inches) { fwrite ($this->fp,"\$sheet".$this->unit."->set_margins_bottom($inches);\n"); }
           function setHeader($string,$margin="")
            {
              if ($margin) $margin=",".$margin;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_header(\"$string\"$margin);\n");
            }
           function setFooter($string,$margin="")
            {
              if ($margin) $margin=",".$margin;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_footer(\"$string\"$margin);\n");
            }
           function repeatRows($first_row,$last_row="")
            {
              if ($last_row) $last_row=",".$last_row;
              fwrite ($this->fp,"\$sheet".$this->unit."->repeat_rows($first_row$last_row);\n");
            }
           function repeatColumns($first_col,$last_col="")
            {
              if (is_string($first_col)) $firstlast="\"$first_col\"";
              else
               {
                 if ($last_col) $firstlast=$first_col.",".$last_col;
                 else $firstlast=$first_col;
               }
              fwrite ($this->fp,"\$sheet".$this->unit."->repeat_columns($firstlast);\n");
            }
           function hideGridlines($option=1) { fwrite ($this->fp,"\$sheet".$this->unit."->hide_gridlines($option);\n"); }
           function printRowColHeaders() { fwrite ($this->fp,"\$sheet".$this->unit."->print_row_col_headers();\n"); }
           function printArea($first_row,$first_col="",$last_row="",$last_col="")
            {
              if (is_string($first_row)) $firstlast="\"$first_row\"";
              else if ($last_row) $firstlast=$first_row.",".$first_col.",".$last_row.",".$last_col;
              fwrite ($this->fp,"\$sheet".$this->unit."->print_area($firstlast);\n");
            }
           function fitToPages($width,$height=0)
            {
              if ($height) $widthheight=$width.",".$height;
              else $widthheight=$width;
              fwrite ($this->fp,"\$sheet".$this->unit."->fit_to_pages($widthheight);\n");
            }
           function setPrintScale($scale) { fwrite ($this->fp,"\$sheet".$this->unit."->set_print_scale($scale);\n"); }
           function setHPagebreaks($breaks)
            {
              if (is_array($breaks))
               {
                 $count=count($breaks);
                 $i=0;
                 foreach ($breaks as $key => $break)
                  {
                    $content.=$break;
                    if ($i<$count) $content.=",";
                    $i++;
                  }
               }
              else $content=$breaks;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_h_pagebreaks($content);\n");
            }
           function setVPagebreaks($breaks)
            {
              if (is_array($breaks))
               {
                 $count=count($breaks);
                 $i=0;
                 foreach ($breaks as $key => $break)
                  {
                    $content.=$break;
                    if ($i<$count) $content.=",";
                    $i++;
                  }
               }
              else $content=$breaks;
              fwrite ($this->fp,"\$sheet".$this->unit."->set_v_pagebreaks($content);\n");
            }
         }


        class BatExcelFormat
         {

           function BatExcelFormat($unit,$fp,$xls)
            {
              $this->fp=$fp;
              $this->unit=$unit;
              $this->xls=$xls;
              fwrite ($this->fp,"my \$format".$this->unit."=\$excel->addformat();\n");
            }

          function setProperties($array)
           {
             $count=count($array);
             $i=1;
             foreach ($array as $key => $value)
              {
                if (!is_int($value)) $value="\"$value\"";
                if ($i<$count) $value.=", ";
                $property=$key."=>".$value;
                $properties.=$property;
                $i++;
              }
             fwrite ($this->fp,"\$format".$this->unit."->set_properties(".$properties.");\n");

           }
          function setFont($font="Arial") { fwrite ($this->fp,"\$format".$this->unit."->set_font(\"".$font."\");\n"); }
          function setSize($size=10) { fwrite ($this->fp,"\$format".$this->unit."->set_size($size);\n"); }
          function setColor($color=8)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_color(".$color.");\n");
           }
          function setBold($bold=1) { fwrite ($this->fp,"\$format".$this->unit."->set_bold($bold);\n"); }
          function setItalic($italic=1) { fwrite ($this->fp,"\$format".$this->unit."->set_italic($italic);\n"); }
          function setUnderline($underline=1) { fwrite ($this->fp,"\$format".$this->unit."->set_underline($underline);\n"); }
          function setStrikeout($strikeout=1) { fwrite ($this->fp,"\$format".$this->unit."->set_strikeout($strikeout);\n"); }
          function setScript($script=1) { fwrite ($this->fp,"\$format".$this->unit."->set_script($script);\n"); }
          function setOutline($outline=1) { fwrite ($this->fp,"\$format".$this->unit."->set_outline($outline);\n"); }
          function setShadow($shadow=1) { fwrite ($this->fp,"\$format".$this->unit."->set_shaddow($shadow);\n"); }
          function setNumFormat($numformat) { fwrite ($this->fp,"\$format".$this->unit."->set_num_format(\"".$numformat."\");\n"); }
          function setLocked($locked=1) { fwrite ($this->fp,"\$format".$this->unit."->set_locked($locked);\n"); }
          function setHidden($hidden=1) { fwrite ($this->fp,"\$format".$this->unit."->set_hidden($hidden);\n"); }
          function setAlign($align) { fwrite ($this->fp,"\$format".$this->unit."->set_align(\"".$align."\");\n"); }
          function setMerge() { fwrite ($this->fp,"\$format".$this->unit."->set_merge();\n"); }
          function setTextWrap($wrap=1) { fwrite ($this->fp,"\$format".$this->unit."->set_text_wrap($wrap);\n"); }
          function setRotation($rotation=1) { fwrite ($this->fp,"\$format".$this->unit."->set_rotation($rotation);\n"); }
          function setTextJustlast($justlast=1) { fwrite ($this->fp,"\$format".$this->unit."->set_text_justlast($justlast);\n"); }
          function setPattern($pattern=1) { fwrite ($this->fp,"\$format".$this->unit."->set_pattern($pattern);\n"); }
          function setBGColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_bg_color(".$color.");\n");
           }
          function setFGColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_fg_color(".$color.");\n");
           }
          function setBorder($border=1) { fwrite ($this->fp,"\$format".$this->unit."->set_border($border);\n"); }
          function setBottom($border=1) { fwrite ($this->fp,"\$format".$this->unit."->set_bottom($border);\n"); }
          function setTop($border=1) { fwrite ($this->fp,"\$format".$this->unit."->set_top($border);\n"); }
          function setLeft($border=1) { fwrite ($this->fp,"\$format".$this->unit."->set_left($border);\n"); }
          function setRight($border=1) { fwrite ($this->fp,"\$format".$this->unit."->set_right($border);\n"); }
          function setBorderColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_border_color(".$color.");\n");
           }
          function setBottomColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_bottom_color(".$color.");\n");
           }
          function setTopColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_top_color(".$color.");\n");
           }
          function setLeftColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_left_color(".$color.");\n");
           }
          function setRightColor($color)
           {
             if (!is_int($color)) $color="\"$color\"";
             fwrite ($this->fp,"\$format".$this->unit."->set_right_color(".$color.");\n");
           }
          function &copy(&$format) { fwrite ($this->fp,"\$format".$this->unit."->copy(\$format$format->unit);\n"); }
          function &cloneFormat()
           {
             $newformat=$this->xls->addFormat();
             $newformat->copy($this);
             return($newformat);
           }
         }

?>