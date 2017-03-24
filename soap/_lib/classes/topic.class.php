<?php

    /**
     * ostTopic Class
     *
     * This is THE class that does all the work when a SOAP
     * call for topics has been made
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

     class ostTopic extends ostSOAP {

        /**
         * Raise a Staff SOAP error
         *
         * @param string to
         * @return soap_fault Fault containing the errormessage :)
         */
        private function raiseTopicNotExistError() {
            return new soap_fault('TOPIC','','Error','Topic does not exist');
        }


        /**
         * Get topic - internal use
         *
         * @param int topicID
         * @return class Topic
         */
        private function get($topicId) {
            $topic = new Topic($topicId);

            return ($topic->getId() ? $topic : null);
        }


        /**
         * Get all info from a topic
         *
         * @param string username
         * @param string password
         * @param int topicId
         * @return TopicInfo topic information
         */
        public function getInfo($username, $password, $topicId) {
            // lets validate the user
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $topic = $this->get($topicId);

            // Do we have a valid topic?
            if ($topic == null) {
                return $this->raiseTopicNotExistError();
            }

            $info = array(
                'id'            => $topic->getId(),
                'name'          => $topic->getName(),
                'department'    => $topic->getDeptId(),
                'priority'      => $topic->getPriorityId(),
                'isEnabled'     => $topic->isEnabled(),
                'isActive'      => $topic->isActive()
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
         * @return TopicInfoArray staff members
         */
        public function listAll($username, $password) {
            $result = array();

            // lets validate the user
            if(!$this->validateUser($username, $password)) {
                return $this->raiseInvalidUserError($password);
            }

            // Query tickets
            $query = db_query(
                'SELECT topic_id FROM '.TOPIC_TABLE.' ORDER BY topic'
            );

            // Loop through the results
            if (db_num_rows($query))
                while ($row = db_fetch_array($query)) {
                    $topic = $this->get($row['topic_id']);

                    $result[] = array(
                        'id'            => $topic->getId(),
                        'name'          => $topic->getName(),
                        'department'    => $topic->getDeptId(),
                        'priority'      => $topic->getPriorityId(),
                        'isEnabled'     => $topic->isEnabled(),
                        'isActive'      => $topic->isActive()
                    );
                }
            else
                return new soap_fault('TOPIC','','Error','No topics were found');

            // Return tickets (if there are any :P)
            return new soapval(
                '',
                false,
                $result
            );
        }
    }

?>