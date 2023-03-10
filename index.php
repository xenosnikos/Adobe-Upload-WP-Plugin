<?php
/**
* Plugin Name: adobe upload
* Description: This plugin uploads the file to adobe cloud and deletes the file from server after specified time.
* Version: 0.1
* Author: Nick Xenos

**/

add_action( 'video_new_cron', 'cw_function' );
function cw_function() {
error_log('crondelete run');
// Delete file code

$uploads = wp_upload_dir();
    $filedirectory= $uploads['basedir']."/wpforms/2476-95b95801c048c4dc0ae00909afdc113e";
    

    
   

	$fileList = glob($filedirectory."/*");
    


	foreach($fileList as $file){
                                           
		error_log('cronfilelist '.$file);
		if(is_file($file)){
			$creationTimestamp = filectime($file); //1663093380
			 $nowdate=strtotime('-30 minutes'); // 30 Minute  ago
		     if($creationTimestamp < $nowdate)
			 {		
				error_log('deletefile '.$file);
				unlink($file); //delete
			
				
			 }
	    }
	 }

}

add_action( 'wpforms_process_complete_2476', 'sendingDataToJava', 10, 4);

   function sendingDataToJava( $fields, $entry, $form_data, $entry_id) {

             $uploads = wp_upload_dir();
   
 
 error_log('fileds'.print_r($fields,true));

	if(isset($fields[8])){
		error_log(' fields'.print_r($fields[8]['value_raw'][0]['name'],true));
		error_log(' fields name'.$fields[8]['value_raw'][0]['name']);	
               $file=$fields[8]['value_raw'][0]['file'];
               
               error_log('file'.$file);
                error_log('file'.$uploads['basedir']."/wpforms/2476-95b95801c048c4dc0ae00909afdc113e/".$file);
                


			  $path= file_get_contents($uploads['basedir']."/wpforms/2476-95b95801c048c4dc0ae00909afdc113e/".$file);
              
                              error_log('file'.filesize($path));

              
              
                    $response= curlcall($file,$path);
	 	                $response=json_decode($response,1);
	 			    	 error_log('curldata'.print_r($response,true));
	 			    	 if($response['properties']['status.code']==201){
                                  $response1=curlupdate($file);
								  $response1=json_decode($response1,1);
				                	 error_log('curlupdate'.print_r($response1,true));

						$eid=$entry_id;
						$urlvalue="https://qual-dam-ams-author.moethennessy.com/".$response['properties']['path'];
						$sql="update ". getenv('MYSQL_DB_NAME').".wp_wpforms_entries set fields = JSON_SET(fields, '$.\"27\".value', '$urlvalue')  where entry_id = '$eid'";
                        error_log('sql'.$sql);
						global $wpdb;
						$results = $wpdb->get_results($sql);
						error_log('result'.print_r($results,true));
             	}
			}

	// error_log(($arrFiles) ."  results");


}

function curlcall($filename,$path)
{
	
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://qual-dam-ams-author.moethennessy.com/api/assets/moet-hennessy/maison/hns/myway/2023/europe/$filename",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $path,

  CURLOPT_HTTPHEADER => array(
    'Content-Type: video/mp4',
    'Authorization: Basic bWgtbXl3YXktdGVjaG5pY2FsLXVzZXI6bENSSVkyITA1bTEq',

  ),
));

$response = curl_exec($curl);

curl_close($curl);
return $response;

}


function curlupdate($file){
	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://qual-dam-ams-author.moethennessy.com/api/assets/moet-hennessy/maison/hns/myway/2023/europe/$file",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS =>'{
    "class":"assets",
    "properties":{
        "jcr.title":"My asset"
    }
 }',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic bWgtbXl3YXktdGVjaG5pY2FsLXVzZXI6bENSSVkyITA1bTEq',
  ),
));

$response = curl_exec($curl);

curl_close($curl);
return $response;
}