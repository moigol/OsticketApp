<?php

    /**
     * ostDepartment Class
     *
     * This is THE class that does all the work when a SOAP
     * call for departments has been made
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

     class ostDepartment extends ostSOAP {

        /**
         * Raise a Department SOAP error
         *
         * @param string to
         * @return soap_fault Fault containing the errormessage :)
         */
        private function raiseDepartmentNotExistError() {
            return new soap_fault('DEPARTMENT','','Error','Department does not exist');
        }


        /**
         * Get department - internal use
         *
         * @param int departmentId
         * @return class Topic
         */
        private function get($departmentId) {
            $dept = Dept::lookup($departmentId);

            return ($dept->getId() ? $dept : null);
        }


        /**
         * Get all info from a department
         *
         * @param string username
         * @param string password
         * @param int departmentId
         * @return DepartmentInfo department information
         */
        public function getInfo($username, $password, $departmentId) {
            // lets validate the user
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $dept = $this->get($departmentId);

            // Do we have a valid staff member?
            if ($dept == null) {
                return $this->raiseDepartmentNotExistError();
            }

            $info = array(
                'id'            => $dept->getId(),
                'name'          => $dept->getName(),
                'email'         => $dept->getEmailAddress(),
                'isPublic'      => $dept->isPublic()
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
         * @return DepartmentInfoArray staff members
         */
        public function listAll($username, $password) {
            $result = array();

            // lets validate the user
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }

            // Query tickets
            if (isVersion('1.10')) {
                $sql = 'SELECT id FROM '.DEPT_TABLE.' ORDER BY name';
            } else {
                $sql = 'SELECT dept_id FROM '.DEPT_TABLE.' ORDER BY dept_name';
            }
            
            $query = db_query($sql);

            // Loop through the results
            if (db_num_rows($query)) {
                while ($row = db_fetch_array($query)) {
                    $dept = $this->get($row['id']);

                    $result[] = array(
                        'id'            => $dept->getId(),
                        'name'          => $dept->getName(),
                        'email'         => $dept->getEmailAddress(),
                        'isPublic'      => $dept->isPublic()
                    );
                }
            } else {
                return new soap_fault('DEPARTMENT','','Error','No departments were found');
            }

            // Return tickets (if there are any :P)
            return new soapval(
                '',
                false,
                $result
            );
        }
    }

?>