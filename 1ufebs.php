<?
  error_reporting( E_ERROR );
  date_default_timezone_set("Asia/Baghdad");
  //Стандартная кодировка документа
  define('Encoding','WINDOWS-1251');
  require_once('lib4php.php');

  //Заголовок XML-документа
  define('XMLHead',"<?xml version='1.0' encoding='".Encoding."'?>");


  $dateo=date('Y-m-d');
  $ini=parse_ini_file("1ufebs.ini");
  echo2log($ini['logfile'], "--- START ".$_SERVER['COMPUTERNAME']."->".$_SERVER['SCRIPT_NAME']);
  $file4already="$ini[dir4out]\\$dateo.lo";
  $text4already=file_get_contents($file4already);

  for($i=1;$i<=9;$i++)
    {
     $dir4mci=$ini["dir4mci$i"];
     if($dir4mci=='') continue;
     echo2log($ini['logfile'], "Scan dir: $dir4mci");
     $filez=dir2arr($dir4mci);
     foreach($filez as $file_id=>$fn)
       {
        echo2log($ini['logfile'], "Load xml: $fn");
        $fn1=strtolower(basename($fn));
        if((strpos($text4already,$fn1)!==false)and(substr($fn1,0,4)!='a107'))//наши отправленные не сохраняем в обработанные чтоб не накладывались
          echo2log($ini['logfile'], "\t*** ALREADY USE $fn1");
        else
          if(($x=simplexml_load_file("$fn"))!==false)
            {
             #var_dump($x->{'sen:SigEnvelope'});
             $o64='';
             foreach($x->xpath('//sen:Object') as $u) $o64=base64_decode($u);
             #var_dump($o64);
             if($o64!='')
               {
                $inout='tt';
                $dt='';
                $o64=str_replace('<ed:',  '<',  $o64);// избавимся от Ед
                $o64=str_replace('</ed:', '</', $o64);//
                $xsmp=simplexml_load_string($o64);
                $xdom=dom_import_simplexml($xsmp);
                $nn=$xdom->nodeName;
                $dt=$xsmp['EDDate'];
                echo2log($ini['logfile'], "\t$dt $xsmp[EDAuthor] - $xsmp[EDReceiver]");
                if($xsmp['EDAuthor']==$ini['EDAuthor']) $inout='out';
                elseif($xsmp['EDReceiver']==$ini['EDAuthor']) $inout='in';

                if($dt!='')
                  {
                   $dirout="$ini[dir4out]\\$dt";
                   $fnout=str_replace(":","","$inout$nn-".basename($fn).".xml");
                   
                   echo2log($ini['logfile'], "\t$dirout - $fnout");
                   
                   if(!file_exists($dirout)) if(!mkdir($dirout,0777,true)) echo2log($ini['logfile'], "\t*** Error! dont mkdir");
                   if(file_exists($dirout))
                     {
                      if(!file_exists("$dirout\\$fnout"))
                        {
                         echo2log($ini['logfile'], "OK.");
                         if($nn=='ED211')
                            if(($xsmp['Acc']=='30101810900000000107')and($xsmp['EDDate']==date('Y-m-d'))and(isset($xsmp['InquirySession'])))
                              {
                               echo2log($ini['logfile'], "\tSENDEMAIL - $xsmp[Acc]  $xsmp[EDDate] (рейс $xsmp[InquirySession]), баланс: ".Commas(xml100($xsmp['OutBal'])));
                               sendEmail($ini['mailto'], utf2win("ED211 получен рейс$xsmp[InquirySession] баланс - ".Commas(xml100($xsmp['OutBal']))),
                                                         "EndTime: $xsmp[EndTime]".
                                                   "<br>\nEnterBal: ".Commas(xml100($xsmp['EnterBal'])).
                                                   "<br>\nDebetSum:  ".Commas(xml100($xsmp['DebetSum'])).
                                                   "<br>\nCreditSum: ".Commas(xml100($xsmp['CreditSum']))
                                        );
                              }
                         file_put_contents("$dirout\\$fnout",xmlcrnl($o64));
                         file_put_contents($file4already, curtime($fn1), FILE_APPEND | LOCK_EX);
                         echo2log($ini['logfile'], "-\n-\n-\n");
                        }
                      else
                        echo2log($ini['logfile'], "\t***Warn! Already exists outfile $fnout (dont recreate)...");
                     }
                  }
                else
                  {
                   echo2log($ini['logfile'], "\t***Error! after decode64");
                  }
               }
            }
          else
            {
             echo2log($ini['logfile'], "\t*** Warning! $fn - dont xml (ignore)");
            }
       }
    }
  echo2log($ini['logfile'], "ENND\n");
  

  //
  //
  //
  function xmlcrnl($t)
    {
     $r=$t;
     $w=array('Packet',
              'ed', 'dsig',
              'Error', 'PartInfo', 'TransInfo',
              'BNKSEEK', 'PLAN', 'ListAccOK', 'OKLS',
              'Customer', 'TUInfo', 'Name', 'ListAcc', 'Proprietory',
              'Report', 'Paye', 'Depart', 'Bank', 'Purpose', 'InitialED', 
              'OriginalEPD', 'AccDoc', 'ReportContent',
              'SettlementTime', 'Session', 'Processing',
              'ParticipantInfo', 'BICDirectoryEntry', 'Accounts', 'CreditInfo'
             );

     foreach($w as $a)
       {
        $r=preg_replace("/(\<$a)/i",   "\n$1",    $r);
        $r=preg_replace("/(\<\/$a)/i", "\n$1", $r);
       }
     return $r;
    }
?>
