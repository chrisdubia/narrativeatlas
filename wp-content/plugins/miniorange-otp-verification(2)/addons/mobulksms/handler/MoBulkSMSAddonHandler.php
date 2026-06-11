<?php

namespace OTP\Addons\MoBulkSMS\Handler;

use OTP\Objects\BaseAddOnHandler;

use OTP\Traits\Instance;
use OTP\Addons\MoBulkSMS\Handler\MoBulkSMSHandler;
    /**
     * The class is used to handle all Ultimate Member Password Reset related functionality.
     * <br/><br/>
     * This class hooks into all the available notification hooks and filters of
     * Ultimate Member to provide the possibility of overriding the default password reset
     * behaviour of Ultimate Member and replace it with OTP.
     */
    class MoBulkSMSAddonHandler extends BaseAddonHandler
    {
        use Instance;

        /**
         * Constructor checks if add-on has been enabled by the admin and initializes
         * all the class variables. This function also defines all the hooks to
         * hook into to make the add-on functionality work.
         */
        function __construct()
        {
            parent::__construct();
            if (!$this->moAddOnV()) return;
            MoBulkSMSHandler::instance();

        }

        /** Set a unique for the AddOn */
        function setAddonKey()
        {
            $this->_addOnKey = 'mo_bulk_sms';
        }

        /** Set a AddOn Description */
        function setAddOnDesc()
        {
            $this->_addOnDesc = mo_("Allows admin to send SMSs in bulk."
                ."Click on the settings button to the right to configure settings for the same.");
        }

        /** Set an AddOnName */
        function setAddOnName()
        {
            $this->_addOnName = mo_("miniOrange Bulk SMS Addon");
        }

        /** Set Settings Page URL */
        function setSettingsUrl()
        {
            $this->_settingsUrl = add_query_arg( array('addon'=> 'mo_bulk_sms'), $_SERVER['REQUEST_URI']);
        }

        /** Set an Addon Video link */
        function setAddOnVideo()
        {
        // $this->_addOnVideo = MoOTPDocs::ULTIMATEMEMBER_SMS_NOTIFICATION_LINK['videoLink'];
        }

        /** Set an Addon Video link */
        function setAddOnDocs()
        {
        // $this->_addOnVideo = MoOTPDocs::ULTIMATEMEMBER_SMS_NOTIFICATION_LINK['videoLink'];
        }

    }