<?php 
// contains little procedural functions to output various HTML strings

// Adapted code from the MIT licensed QuickDD class
// created also by me
function NamasteQuickDDDate($name, $date=NULL, $format=NULL, $markup=NULL, $start_year=1900, $end_year=2100)
{
   // normalize params
   if(empty($date) or !preg_match("/\d\d\d\d\-\d\d-\d\d/",$date)) $date=date("Y-m-d");
    if(empty($format)) $format="YYYY-MM-DD";
    if(empty($markup)) $markup=array();

    $parts=explode("-",$date);
    $html="";

    // read the format
    $format_parts=explode("-",$format);

    $errors=array();
    
    // let's output
    foreach($format_parts as $cnt=>$f)
    {
        if(preg_match("/[^YMD]/",$f)) 
        { 
            $errors[]="Unrecognized format part: '$f'. Skipped.";
            continue;
        }

        // year
        if(strstr($f,"Y"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."year\"".$extra_html.">\n";

            for($i=$start_year;$i<=$end_year;$i++)
            {
                $selected="";
                if(!empty($parts[0]) and $parts[0]==$i) $selected=" selected";
                
                $val=$i;
                // in case only two digits are passed we have to strip $val for displaying
                // it's either 4 or 2, everything else is ignored
                if(strlen($f)<=2) $val=substr($val,2);        
                
                $html.="<option value='$i'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // month
        if(strstr($f,"M"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."month\"".$extra_html.">\n";

            for($i=1;$i<=12;$i++)
            {
                $selected="";
                if(!empty($parts[1]) and intval($parts[1])==$i) $selected=" selected";
                
                $val=sprintf("%02d",$i);
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // day - we simply display 1-31 here, no extra intelligence depending on month
        if(strstr($f,"D"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."day\"".$extra_html.">\n";

            for($i=1;$i<=31;$i++)
            {
                $selected="";
                if(!empty($parts[2]) and intval($parts[2])==$i) $selected=" selected";
                
                if(strlen($f)>1) $val=sprintf("%02d",$i);
                else $val=$i;
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }
    }

    // that's it, return dropdowns:
    return $html;
}

// safe redirect
function namaste_redirect($url) {
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}

// new line for CSV
function namaste_define_newline() {
	// credit to http://yoast.com/wordpress/users-to-csv/
	$unewline = "\r\n";
	if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
	   $unewline = "\r\n";
	} else if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
	   $unewline = "\r";
	} else {
	   $unewline = "\n";
	}
	return $unewline;
}


function namaste_get_mime_type() {
	// credit to http://yoast.com/wordpress/users-to-csv/
	$USER_BROWSER_AGENT="";

			if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OPERA';
			} else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='IE';
			} else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OMNIWEB';
			} else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='MOZILLA';
			} else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
		    	$USER_BROWSER_AGENT='KONQUEROR';
			} else {
		    	$USER_BROWSER_AGENT='OTHER';
			}

	$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
				? 'application/octetstream'
				: 'application/octet-stream';
	return $mime_type;
}

// displays session flash, errors etc, and clears them if required
function namaste_display_alerts() {
	global $error, $success;
	
	if(!empty($_SESSION['namaste_flash']))
	{
		echo "<div class='namaste-alert'><p>".$_SESSION['namaste_flash']."</p></div>";
		unset($_SESSION['namaste_flash']);
	}
	
	if(!empty($error)){
		echo '<div class="namaste-error"><p>'.$error.'</p></div>';
	}
	
	if(!empty($success)){
		echo '<div class="namaste-success"><p>'.$success.'</p></div>';
	}
}

function namaste_datetotime($date) {
	list($year, $month, $day) = explode("-",$date);
	return mktime(1, 0, 0, $month, $day, $year);
}

// function to conditionally add DB fields
function namaste_add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
}