<?php

    /**
     * ostStaff Class
     *
     * This is THE class that does all the work when a SOAP
     * call for staff has been made
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

     class ostStaff extends ostSOAP {
        /**
         * Raise a Staff SOAP error
         *
         * @param string to
         * @return soap_fault Fault containing the errormessage :)
         */
        private function raiseStaffNotExistError() {
            return new soap_fault('STAFF','','Error','Staff does not exist');
        }


        /**
         * Get staff - internal use
         *
         * @param int staffID
         * @return class Staff
         */
        private function get($staffID) {
            $staff = Staff::lookup($staffID);

            return ($staff->getId() ? $staff : null);
        }


        /**
         * Get staff ID of the current
         *
         * @param
         * @return class Staff
         */
        public function getId($username, $password) {
            if(!($staffId = $this->validateUser($username, $password))) {
                return $this->raiseInvalidUserError($password);
            }
            
            return $staffId;
        }


        /**
         * Get staff info for account
         *
         * @param string username
         * @param string password
         * @return StaffInfo staff information
         */
        public function getInfoForLogin($username, $password) {
            // lets validate the user
            if(!($staffId = $this->validateUser($username, $password))) {
                return $this->raiseInvalidUserError($password);
            }
            
            return $this->getInfo($username, $password, $staffId);
        }


        /**
         * Get all info from a staff member
         *
         * @param string username
         * @param string password
         * @param int staffId
         * @return StaffInfo staff information
         */
        public function getInfo($username, $password, $staffId) {
            // lets validate the user
            
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }
            
            // Get the ticket
            $staff = $this->get($staffId);

            // Do we have a valid staff member?
            if ($staff == null) {
                return $this->raiseStaffNotExistError();
            }

            $info = array(
                'id'            => $staff->getId(),
                'email'         => $staff->getEmail(),
                'fullname'      => $staff->getName(),
                'firstname'     => $staff->getFirstName(),
                'lastname'      => $staff->getLastName(),
                'username'      => $staff->getUserName(),
                'signature'     => $staff->getSignature(),
                'isManager'     => $staff->isManager(),
                'isAdmin'       => $staff->isadmin(),
                'isAvailable'   => $staff->isAvailable(),
                'isActive'      => $staff->isActive(),
                'tzOffset'      => $staff->getTimezone(),
                'canCreate'     => $staff->canAccess($staff),
                'canEdit'       => $staff->canAccess($staff),
                'canClose'      => $staff->canAccess($staff),
                'canDelete'     => $staff->canAccess($staff),
                'canReply'      => $staff->canAccess($staff),
                'canAssign'     => $staff->canAccess($staff),
                'canTransfer'   => $staff->canAccess($staff)
            );

            return new soapval(
                '',
                false,
                $info
            );
        }


        /**
         * List all
         *
         * @param string username
         * @param string password
         * @return StaffInfoArray staff members
         */
        public function listAll($username, $password) {
            $result = array();

            // lets validate the user
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }

            // Query tickets
            $query = db_query(
                'SELECT DISTINCT staff_id FROM '.STAFF_TABLE.' WHERE 1'
            );

            // Loop through the results
            if (db_num_rows($query))
                while ($row = db_fetch_array($query)) {
                    $staff = $this->get($row['staff_id']);

                    $result[] = array(
                        'id'            => $staff->getId(),
                        'email'         => $staff->getEmail(),
                        'fullname'      => $staff->getName(),
                        'firstname'     => $staff->getFirstName(),
                        'lastname'      => $staff->getLastName(),
                        'username'      => $staff->getUserName(),
                        'signature'     => $staff->getSignature(),
                        'isManager'     => $staff->isManager(),
                        'isAdmin'       => $staff->isadmin(),
                        'isAvailable'   => $staff->isAvailable(),
                        'isActive'      => $staff->isActive(),
                        'tzOffset'      => $staff->getTimezone()
                    );
                }
            else
                return new soap_fault('STAFF','','Error','No staff were found');

            // Return tickets (if there are any :P)
            return new soapval(
                '',
                false,
                $result
            );
        }
    }

?>