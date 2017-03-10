<?php

    /**
     * Base-class
     *
     * This class contains some basic methods.
     * Why? For the future when I decide to expand
     * the service to do other things than just tickets ;)
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

    class ostSOAP
    {

        protected $user;

        /**
         * Validate username and password
         *
         * @param string username
         * @param string password
         */
        protected function validateUser($username, $password)
        {
            global $thisuser; // global var for 1.6
            global $thisstaff; // global var for 1.7
            
            // set error log file
            ini_set("log_errors", 1);
            ini_set("error_log", "soap-log.log");
            
            // Get the user
            //$this->user = new StaffSession($username);
            
            // v1.10
            $this->user = StaffSession::lookup($username);

            // Also make it a global otherwise osTicket native functions will fail
            $thisuser = $thisstaff = $this->user;

            // Return if the user is validated
            if ($this->user->getId() && $this->user->check_passwd($password) && $this->user->isStaff()) {
                error_log('validation ok with userid: '.$this->user->getId().' on '.$username.'|'.$password);
                return $this->user->getId();
            } else {
                return false;
                error_log('user validation failed');
            }
        }
        
        /**
         * Raise an Invalid User SOAP error
         *
         * @param string username
         * @param string password
         * @return soap_fault Fault containing the errormessage :)
         */
        protected function raiseInvalidUserError($password)
        {
            $msg = 'Invalid username [' . $this->user->getUserName() . '] or password [' . $password . ']';

            if (isVersion('1.6'))
                Sys::log(LOG_WARNING, "SOAP error", $msg);

            return new soap_fault('AUTH', '', 'Authentication failed', $msg);
        }

        /**
         * Raise a permission SOAP error
         *
         * @param string to
         * @return soap_fault Fault containing the errormessage :)
         */
        protected function raisePermissionError()
        {
            $msg = 'User [' . $this->user->getUserName() . '] has no permission to perform this action or is not a staff member';

            if (isVersion('1.6'))
                Sys::log(LOG_WARNING, "SOAP error", $msg);

            return new soap_fault('PERM', '', 'Permission error', $msg);
        }

        /**
         * Log a message to osTicket
         *
         * @param string type
         * @param string title
         * @param string message
         */
        protected function log($type = 'info', $title, $message)
        {
            global $ost;

            switch ($type)
            {
                case 'debug':
                    if (isVersion('1.6'))
                        Sys::log(LOG_DEBUG, $title, $message);
                    if (isVersion('1.7'))
                        $ost->logDebug($title, $message);
                    break;

                case 'info':
                    if (isVersion('1.6'))
                        Sys::log(LOG_INFO, $title, $message);
                    if (isVersion('1.7'))
                        $ost->logInfo($title, $message);
                    break;

                case 'warning':
                    if (isVersion('1.6'))
                        Sys::log(LOG_WARNING, $title, $message);
                    if (isVersion('1.7'))
                        $ost->logWarning($title, $message);
                    break;

                case 'error':
                    if (isVersion('1.6'))
                        Sys::log(LOG_ERR, $title, $message);
                    if (isVersion('1.7'))
                        $ost->logError($title, $message);
                    break;
            }
        }

        protected function soapDecode($var)
        {
            return utf8_encode(html_entity_decode($var));
        }
        
        function dumpObjectAsLog( $object=null ){
            ob_start();                    // start buffer capture
            var_dump( $object );           // dump the values
            $contents = ob_get_contents(); // put the buffer into a variable
            ob_end_clean();                // end capture
            error_log( $contents );        // log contents of the result of var_dump( $object )
        }
        
        function printRAsLog( $object=null ){
            ob_start();                    // start buffer capture
            print_r( $object );           // dump the values
            $contents = ob_get_contents(); // put the buffer into a variable
            ob_end_clean();                // end capture
            error_log( $contents );        // log contents of the result of var_dump( $object )
        }

    }

?>