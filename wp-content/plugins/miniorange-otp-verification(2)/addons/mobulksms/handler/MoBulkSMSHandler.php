<?php

namespace OTP\Addons\MoBulkSMS\Handler;
use OTP\Traits\Instance;
use OTP\Helper\MoConstants;
use OTP\Helper\MoUtility;
use OTP\Helper\MoPHPSessions;
use OTP\Helper\MoMessages;

class MoBulkSMSHandler
{
    use Instance;


    private $user_login;
    private $user_email;
    private $user_phone;
    private $user_ip;
    private $isUserBlocked;
    private $user_id;
    private $otp_type;
    private $show_error;

    function __construct()
    {
        $this->count = 0;
        $this->limit = 5;
        $this->message = mo_("Dear user, you have been notified by the administrator of site: ").get_site_url();
        $this->phoneNumberArray=[];
        $this->sendSMSInBulk();
        $this->getFilePathOrPhoneNumbers();
        add_action('admin_enqueue_scripts',array($this, 'initializeScript'));
    }

    function initializeScript(){
        wp_register_script( 'mobulksms', MOV_BULK_SMS_URL . 'includes/js/moBulkSMSScript.js',array('jquery') );
        wp_localize_script( 'mobulksms', 'mobulksms', array(
            'siteURL'       => wp_ajax_url(),
        ));
        wp_enqueue_script( 'mobulksms' );
    }

    function getFilePathOrPhoneNumbers(){
        if(isset($_POST['action']) && $_POST['action']=='mo_bulk_sms_upload' && !isset($_POST['file'])){
            if ( 0 < $_FILES['file']['error'] ) {
                wp_send_json(
                [
                    "result"=>"error",
                    "message"=>mo_("There is some issue with the uploaded file. Please check and upload it again.")
                ]); 
            }
            else{
                    $log_filename =MOV_DIR. "\log";
                    if (!file_exists($log_filename)) 
                    {
                        mkdir($log_filename, 0777, true);
                    }
                    $fileName = $log_filename."/temp.csv";
                        move_uploaded_file($_FILES['file']['tmp_name'], $fileName);
                        $this->saveCSVToArray($fileName);
                }
                wp_send_json(
                [
                    "result"=>"success",
                    "message"=>"File has been uploaded",
                    "type"=>"file",
                ] 
                );
            
        }
        if(isset($_POST['action']) && $_POST['action']=='mo_bulk_sms_upload' && isset($_POST['file']) && $_POST['file'] == "undefined"){
            if(empty($_POST['mo_phone_number_csv'])){
                wp_send_json(
                [
                    "result"=>"error",
                    "message"=>"Please provide the phone numbers or upload the file."
                ]); 
            }
            $message = empty($_POST['mo_bulk_sms_body_csv']) ? $this->message : $_POST['mo_bulk_sms_body_csv'];
            $this->savePhoneToArray($_POST['mo_phone_number_csv'],$message);
             wp_send_json(
                [
                    "result"=>"success",
                    "message"=>"File has been uploaded",
                    "type"=>"phone",
                ] 
                );
        }


    }


    function savePhoneToArray($phoneNumbers,$message){
        $this->phoneNumberArray = explode(",", $phoneNumbers);
        $message = str_replace(",", "^^", $message);
        $stringToPut = "";
        $numberOfRows = 0;
        for( $i = 0; $i < sizeof($this->phoneNumberArray); $i++){
            if(!empty($this->phoneNumberArray[$i]))
                $stringToPut .= $this->phoneNumberArray[$i] .  "," . $message . "\n";
            if($numberOfRows++ > 100 ){
             wp_send_json(
                [
                    "result"=>"error",
                    "message"=>mo_("Please provide maximum 100 records in one CSV file.")
                ]); 
            }
        }
        $this->writeCSV($stringToPut);
    }

    function saveCSVToArray($fileName){
        $file = fopen($fileName,"r");
        $stringToPut = '';
        $numberOfRows = 0;
        while(! feof($file))
        {
            $stringToGet = fgetcsv($file);
            $phone = $stringToGet[0];
            $message = str_replace(",", "^^", $stringToGet[1]);
            $stringToPut .= $phone . "," . $message . "\n";
            if($numberOfRows++ > 100 ){
             wp_send_json(
                [
                    "result"=>"error",
                    "message"=>mo_("Please provide maximum 100 records in one CSV file.")
                ]); 
         }
        }
        fclose($file);
        unlink($fileName);
        $this->writeCSV($stringToPut);
    }

    function sendSMSInBulk(){
            if(isset($_POST['action']) && $_POST['action']=='send_bulk_sms'){
            $this->count = $_POST['count'];
            $bulkSMSData = $this->getFileContents($this->count);
            $type = $_POST['type'];
            if(null == sizeof($bulkSMSData)){
                $fileName = MOV_DIR . "\log" . "\moBulkSMSTempFile.csv";
                unlink($fileName);
                wp_send_json(
                [
                    "result"=>"success",
                    "message"=>"Congratulations!! SMSs have been sent, please check the logs if required. Please click here to download the logs file: <a href='".MOV_URL."/log/moBulkSMSLogFile.log' target='_blank'>Download Logs</a>"
                ] 
                );
            }
            $i = 0;
            for($i = 0 ; ( $i < sizeof($bulkSMSData) ); $i++)
            {
                $phoneNumber = $bulkSMSData[$i][0];
                $validPhoneNumber = MoUtility::processPhoneNumber($phoneNumber);
                if(!MoUtility::validatePhoneNumber($validPhoneNumber)){
                    $response = "Message Could not be sent to Phone number ".$validPhoneNumber;  
                }
                else{
                    $smsBody = empty($bulkSMSData[$i][1]) ? $this->message : $bulkSMSData[$i][1];
                    $smsBody = str_replace("^^", ",", $smsBody);

                    $response = MoUtility::send_phone_notif($validPhoneNumber, urlencode($smsBody));
                    $response = !$response ? "Message Could not be sent to Phone number ".$validPhoneNumber. "with template: ".$smsBody : "Message Sent to Successfully to ".$validPhoneNumber . " Your message was: ".$smsBody;
                }
                $this->writeLogs($response);
            }
                wp_send_json(
                [
                    "result"=>"inprogress",
                    "message"=>"Messages are being sent, please check the logs if required.",
                    "count"=> $this->count + $this->limit,
                    "type"=>$type,
                ] 
            );
        }
    }

    function getFileContents($count){
        $fileName = MOV_DIR . "\log" . "\moBulkSMSTempFile.csv";
        $file = fopen($fileName,"r");
        $stringToGet = [];
        $rowNumber = 0;
        while(($stringToPut = fgetcsv($file)) !== false)
        {
            $rowNumber++;
            if ($rowNumber <= $count) continue;
            if ($rowNumber <= $count  + $this->limit)
                array_push($stringToGet, $stringToPut);
        }
        fclose($file);
        return $stringToGet;
    }

    function writeLogs($log_msg)
    {
        $log_filename =MOV_DIR. "\log";
        if (!file_exists($log_filename)) 
        {
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename.'/moBulkSMSLogFile.log';
        file_put_contents($log_file_data,date('d-M-Y').":".$log_msg . "\n", FILE_APPEND);
    }

    function writeCSV($stringToPut)
    {
        $log_filename = MOV_DIR. "\log";
        if (!file_exists($log_filename)) 
        {
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename.'/moBulkSMSTempFile.csv';
        file_put_contents($log_file_data, $stringToPut, FILE_APPEND);
    } 

}