<?
  error_reporting( E_ERROR );
  date_default_timezone_set("Asia/Baghdad");
  //Стандартная кодировка документа
  define('Encoding','WINDOWS-1251');

  //Заголовок XML-документа
  define('XMLHead',"<?xml version='1.0' encoding='".Encoding."'?>");



  $dateo=date('Y-m-d');

  $ini=parse_ini_file("1ufebs.ini");

  for($i=1;$i<=9;$i++)
    {
     $dir4mci=$ini["dir4mci$i"];
     echo "Scan dir: $dir4mci\n";
     $filez=dir2arr($dir4mci);
     foreach($filez as $file_id=>$fn)
       {
        if(($x=simplexml_load_file("$fn"))!==false)
          {
           echo "Load xml: $fn - ok\n";
           #var_dump($x->{'sen:SigEnvelope'});
           $o64='';
           foreach($x->xpath('//sen:Object') as $u) $o64=base64_decode($u);
           #var_dump($o64);
           if($o64!='')
             {
              $inout='tt';
              $dt='';
              $xsmp=simplexml_load_string($o64);
              $xdom=dom_import_simplexml($xsmp);
              $nn=$xdom->nodeName;
              $dt=$xsmp['EDDate'];
              if($xsmp['EDAuthor']==$ini['EDAuthor']) $inout='out';
              elseif($xsmp['EDReceiver']==$ini['EDAuthor']) $inout='in';

              if($dt!='')
                {
                 mkdir("$ini[dir4out]\\$dt");
                 file_put_contents("$ini[dir4out]\\$dt\\$inout$nn-".basename($fn).".xml",xmlcrnl($o64));
                }
             }
          }
        else
          {
           echo "$fn - dont xml...<br>\n";
          }
       }
    }
  
  function dir2arr($dir)
    {
     $r=array(); 

     $cdir=scandir($dir); 
     foreach($cdir as $key=>$value) 
       { 
        if(!in_array($value,array(".",".."))) 
          { 
           if(is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
             { 
             } 
           else 
             { 
              $r[]=strtoupper($dir.DIRECTORY_SEPARATOR.$value);
             }
          } 
       } 
     return $r;
    } 

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
              'Session', 'Processing',
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
