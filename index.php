<?php
    if (isset($_GET['id1']) and isset($_GET['id2']) and isset($_GET['id3'])){
        $id1 = $_GET['id1'];
        $id2 = $_GET['id2'];
        $id3 = $_GET['id3'];
        $ch = curl_init("https://www.ladbsservices2.lacity.org/OnlineServices/PermitReport/PcisPermitDetail?id1=". $id1 . "&id2=" .$id2 . "&id3=".$id3);
        
        // get html content using curl
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);

        $dom = new DOMDocument();
        libxml_use_internal_errors(TRUE); //disable libxml errors
        if(!empty($content)){ //if any html is actually returned            

            $dom->loadHTML($content);
            libxml_clear_errors(); //remove errors for yucky html
            
            $xpath = new DOMXPath($dom);
            
            $error = $xpath->query('//*[@id="MainWrapper"]/div/div/p');
            if ($error->length > 0  && $error[0]->nodeValue == 'Permit Details'){
                echo "No Permits Match the data provided: " . $id1 . '-' . $id2 . '-' . $id3;
                exit;
            }
            //get all the h2's with an id
            $titlecontent = $xpath->query('//*[@id="MainWrapper"]/div/h5');
            
            if($titlecontent->length > 0){
                foreach($titlecontent as $row){
                    $title = $row->nodeValue;
                    echo $title;
                }
            }

            $dt_content = $xpath->query('//*[@id="MainWrapper"]/div/dl/dt');
            if ($dt_content->length > 0 ){
                foreach($dt_content as $row){
                    $data = array(
                        "dt" => $row->nodeValue
                    );
                    $dldata[] = $data;
                }
            }

            $dd_content = $xpath->query('//*[@id="MainWrapper"]/div/dl/dd');
            if ($dd_content->length > 0){
                $index = 0 ; 
                foreach ($dd_content as $row){
                    $dldata[$index]['dd'] = $row->nodeValue;
                    $index++;
                }    
            }
            $tabledata = array();
            $table_content = $xpath->query('//*[@id="MainWrapper"]/div/table');
            if($table_content->length > 0){
                $index = 0;
                foreach ($table_content as $row){
                    $tabledata[$index] = array();
                    foreach ($row->getElementsByTagName('tr') as $tr){
                        $unit = array();
                        foreach ($tr->getElementsByTagName('td') as $td){
                            array_push($unit, $td->nodeValue);
                        }
                        $tabledata[$index][] = $unit;
                    }
                    $index++;
                }                                   
            }
            $tabeltitle = $xpath->query('//*[@id="MainWrapper"]/div/h3');            
            if ($tabeltitle->length > 0){
                $index = 0;
                foreach ($tabeltitle as $row){
                    if ($index == 0) { $index++; continue;}
                    $tabledata[$index-1]['title'] = $row->nodeValue;
                    $index++;
                }
            }
        }else{
            echo "Error : Cant get data"; exit;
        }
    }else{
        echo "Error : fetch ids data in url";       
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Permit and inspection Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/CSS/bootstrap.css" />
    <!-- <link rel="stylesheet" type="text/css" href="/CSS/font-awesome.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="/CSS/jquery.ui.all.css" /> -->
    <link rel="stylesheet" type="text/css" href="/CSS/navstyle.css" />
    <link rel="stylesheet" type="text/css" href="/CSS/site.css" />
    <link rel="stylesheet" type="text/css" href="/CSS/style.css" />
</head>
<body>
    <div class="navbar navbar-inverse navbar-fixed-top no-print" id="topnav">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12 col-sm-5 col-md-4 xs-padding">
                    <a class="navbar-brand xs-padding" href="http://www.ladbs.org">
                        <img class="logo-image" src="css/images.png" height="40" style="margin-top:-10px; height:40px;" alt="City Logo">
                    </a>
                    <a class="hidden-xs xs-line-height" href="http://ladbs.org/default"><span class="menu-icon">◀</span> Back to LADBS</a>
                </div>
            </div>
        </div>
    </div>
    <div id="ladbs_main" class="site-banner no-print" role="banner"></div>
    <section role="main">
        <section id="MainWrapper" role="region" class="main_content">
        <style>
            .dl-horizontal dt {
                width: 200px;
            }
            .dl-horizontal dd {
                margin-left: 220px;
            }
        </style>
        <div class="container">
                <h5 class="no-print title title-full-width">
                    <?= $title?>
                </h5>
                <!-- <h3 class="yes-print">
                    Certificate Information:
                    5342 W LEXINGTON AVE 1-10    90029
                </h3> -->
                <dl class="dl-horizontal xs-datalist">
                    <?php
                    foreach ($dldata as $uint){ ?>
                        <dt><?= $uint['dt'] ?></dt>        
                        <dd><?= $uint['dd'] ?></dd>        
                    <?php }?>                    
                </dl>
                <?php
                    foreach($tabledata as $row){ ?>                                 
                    <h3 class="xs-fontSize">
                        <?=$row['title'] ?>
                    </h3>                    
                    <table class="table table-details">
                        <tbody>                        
                            <?php foreach($row as $unit){?>
                                <tr>
                                    <?php 
                                    if (is_array($unit) || is_object($unit)){
                                        if (trim($row['title'])=='Contact Information'){
                                            // foreach ($unit as $td)
                                            //     echo "<td>" . $td . "</td>";                                        
                                            echo "<td>" . $unit[2] . "</td>";
                                        }else{
                                            foreach ($unit as $td)
                                               echo "<td>" . $td ."</td>";
                                        }
                                    }
                                    ?>
                                </tr> 
                                <?php } ?>                           
                        </tbody>
                    </table>

                <?php }?>                
        </div>
    </section>

</body>
</html>
