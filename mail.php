<?php
	require_once("config.php");	
	require_once("./classes/Dia.php");
	require_once("./classes/Page.php");
	require_once("./classes/Log.php");
	require_once('./classes/smarty/Smarty.class.php');
	require_once("./classes/Bookmark.php");
 	require_once("./classes/class.phpmailer.php");

	// Registrar caminho completo para os arquivos de imagem e CSS
    $dia_path = "http://" . $config['SERVERNAME'] . $config['PATH_DATA'];

    $q  = stripslashes($_POST["q"]);
	// Formar consulta a partir dos favoritos marcados
    if( $_POST["option"] == 'from_to' ){
        $from = ( isset($_POST["from"]) ? abs( $_POST["from"] ) : 1 );
        $count = ( isset($_POST["count"]) ? abs( $_POST["count"] ) : sizeOf($bookmark->list) );
    }else if( $_POST["option"] == 'selected' ){
        session_start();
        if( isset($_SESSION["bookmark"]) ){
            $bookmark = unserialize($_SESSION["bookmark"]);
            $q = "\"".$bookmark->list[0]."\"";
            $len = sizeOf($bookmark->list);
            for($i = 1; $i < $len; $i=$i+1){
                $q .= " OR "."\"".$bookmark->list[$i]."\"";
            }
            $q = "id:(". $q .")";

            $from = 1;
            $count = sizeOf($bookmark->list);
        }else{
            die("Error");
        }
    }
    
    // Dados do endereço eletrônico
    $recipientMail = $_POST['recipientMail'];
    $senderMail = $_POST['senderMail'];
    $subject = $_POST['subject'];
    $lang = $_POST['lang'];
    
    if($_POST['option'] != 'query_only' ){
        $col = $_REQUEST["col"];
        $site = $_REQUEST["site"];

        if ( !isset($col) ){
            $col = $defaultCollection; 				// valor default criada no script de configuracao do sistema
            $VARS["col"] = $col;					// registra no array vars para uso na XSL
        }

        if ( !isset($site) ){
            $site= $defaultSite;					// valor default criada no script de configuracao do sistema
            $VARS["site"] = $site;					// registra no array vars para uso na XSL
        }

        $index= $_REQUEST["index"];
        $sort = $_REQUEST["sort"]; 		      		//sort parameter
        $output = ( isset($_REQUEST["output"]) && $_REQUEST["output"] != '' ? $_REQUEST["output"] : "json" );

        $VARS["count"] = $count;

        //DIA server connection object
        $dia = new Dia($site, $col, $count, $output, $lang);
        $page= new Page();

        $initial_restricion = html_entity_decode($colectionData->restriction);
        // filtro de pesquisa = restricao inicial  E filtro where  E filtro externo E filtro(s) selecionados
        $filterSearch = array_merge((array)$initial_restricion,(array)$whereFilter, (array)$filter,(array)$filter_chain);

        // set additiona parameters
        $dia->setParam('fb',$fb);
        $dia->setParam('fl',$fl);
        $dia->setParam('sort',$sort);

        $diaResponse = $dia->search($q, $index, $filterSearch, $from);
        $result = json_decode($diaResponse );

        include("./templates/email-head.php");
        include("./templates/email-top.php");
        $page_body = $page->email();
        include("./templates/email-bottom.php");

        $html_code = $page_head . $page_top . $page_body . $page_bottom;
    }else{
        include("./templates/email-head.php");
        include("./templates/email-top.php");
        include("./templates/email-bottom.php");
        $html_code = $page_head . $page_top . $page_bottom;
    }

	// Envio do e-mail
	$mail = new PHPMailer();
	$mail->SetLanguage("br");
	$mail->IsHTML(true);
	$mail->CharSet = "utf-8";
	
	$mail->IsSMTP();
	$mail->Host = "esmeralda.bireme.br";
	$mail->SMTPAuth = true;
	$mail->Username = "bvs.contato";
	$mail->Password = "c0nt@t0"; // SMTP password
	
	$mail->From = $senderMail;
	$mail->FromName = $senderMail;
	$mail->AddAddress( $recipientMail, "$recipientMail");
	$mail->Subject = $subject;
	$mail->Body = $html_code;
	
	if(!$mail->Send()){
	  print("Error: ");
	  die($mail->ErrorInfo);
	}else{
	  print "e-mail enviado!";
	}
?>
