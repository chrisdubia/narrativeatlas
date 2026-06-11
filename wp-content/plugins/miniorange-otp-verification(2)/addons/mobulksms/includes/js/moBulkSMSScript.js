$mo = jQuery;
$mo(document).ready(function () {

  $mo("#mo_bulk_file").change(function() {
    fileValidation(this);
  });
  $mo("#mo_bulk_file").click(function(){
    $mo("#mo_phone_number_csv").val("");
    $mo("#mo_bulk_sms_body_csv").val("");
  })

  $mo("#mo_phone_number_csv").on('input',function(){
    $mo("#mo_bulk_file").val("");
  })
    
    $mo("#mo-bulk-sms-submit").on("click", function(e){
        e.preventDefault();
        $mo("#mo-loader-div").css("display","block");
        var file_data = $mo("#mo_bulk_file").prop("files")[0];
        var phone_data = $mo("#mo_phone_number_csv").val();
        var sms_data = $mo("#mo_bulk_sms_body_csv").val();   
        var form_data = new FormData();
        form_data.append("file", file_data);
        form_data.append("action","mo_bulk_sms_upload");
        form_data.append("mo_phone_number_csv",phone_data);
        form_data.append("mo_bulk_sms_body_csv",sms_data);
        $mo.ajax({
            type: "POST",
            url: mobulksms.siteURL,
            data: form_data,
            dataType: "json",
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $mo("#mo-loading-span").empty();
                $mo("#mo-loading-span").text("SMSs are being sent, please do not refresh or go back.");
                $mo("#mo_bulk_sms_message_error_div").css("color","black");
                $mo("#mo-loader-div").show();
            },
            success: function(response){ //console.log(response);
                $mo("#mo_bulk_sms_message_span").html("");
                if(response.result == "success"){
                    type = response.type;
                   callSyncAjax(0,type);
                }
                if(response.result == "error"){
                   $mo("#mo_bulk_sms_message_span ,#mo_bulk_sms_message_error_div").show();
                      $mo("#mo_bulk_sms_message_error_div").css("color","red");
                      $mo("#mo_bulk_sms_message_span").text(response.message);
                      $mo("#mo-bulk-sms-submit").removeAttr("disabled");
                      $mo("#mo-loader-div").hide();
                      $mo("#mo_bulk_sms_message_span").focus();
                }
            }
        });
    });



});

function fileValidation(mothis){
    var file = mothis.files[0];
    var fileType = file.type;
    console.log(fileType);
    var match = ["application/vnd.ms-excel"];
    if( fileType != match[0] )
    {
        alert("Sorry, only CSV files are allowed to upload.");
        $mo("#mo_bulk_file").val("");
        return false;
    }
}

function callSyncAjax(count,type){
    $mo.ajax({
            type: "POST",
            url: mobulksms.siteURL,
            data: {"action":"send_bulk_sms","type":true,"count":count,"type":type},
            dataType: "json",
            crossDomain:!0,
            success: function(response){
                $mo("#mo_bulk_sms_message_span").html("");
                    if(response.result=="success"){
                        $mo("#mo_bulk_sms_message_span ,#mo_bulk_sms_message_error_div").show();
                        $mo("#mo_bulk_sms_message_error_div").css("color","green");
                        $mo("#mo_bulk_sms_message_span").html(response.message);
                        $mo("#mo-bulk-sms-submit").removeAttr("disabled");
                        $mo("#mo-loading-span").html(response.message);
                        $mo("#mo-loading-span").css("color","green");
                        $mo("#mo_bulk_sms_message_span").focus();
                        setTimeout(function(){
                          $mo("#mo-loader-div").hide();
                        },200);

                    }
                    else if(response.result=="inprogress"){
                        callSyncAjax(response.count,response.type);
                    }
                    else{
                      $mo("#mo_bulk_sms_message_span ,#mo_bulk_sms_message_error_div").show();
                      $mo("#mo_bulk_sms_message_error_div").css("color","red");
                      $mo("#mo_bulk_sms_message_span").text(response.message);
                      $mo("#mo-bulk-sms-submit").removeAttr("disabled");
                      $mo("#mo_bulk_sms_message_span").focus();
                }
            }
        });
}

    