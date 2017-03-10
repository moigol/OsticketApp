<?php

    /**
     * ostTicket Class
     *
     * This is THE class that does all the work when a SOAP
     * call for tickets has been made.
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */
    
    
    date_default_timezone_set('UTC');

    class ostTicket extends ostSOAP
    {

        /**
         * Raise a Ticket SOAP error
         *
         * @param string to
         * @return soap_fault Fault containing the errormessage :)
         */
        private function raiseTicketNotExistError()
        {
            return new soap_fault('TICKET', '', 'Error', 'Ticket does not exist');
        }


        /**
         * Get ticket - internal use
         *
         * @param int ticketId
         * @return class Ticket
         */
        private function get($ticketId, $extid = false)
        {
            if ($extid)
                $ticket = Ticket::lookup(Ticket::getIdByExtId((int)$ticketId));
            else
                $ticket = Ticket::lookup($ticketId);

            return ($ticket->getId() ? $ticket : null);
        }
        
        
        /**
         * Sort multi-dimensional array
         */
        public function aasort (&$array, $key) {
            $sorter=array();
            $ret=array();
            reset($array);
            foreach ($array as $ii => $va) {
                $sorter[$ii]=$va[$key];
            }
            asort($sorter);
            foreach ($sorter as $ii => $va) {
                $ret[$ii]=$array[$ii];
            }
            $array=$ret;
        }


        /**
         * Open ticket
         *
         * @param string username
         * @param string password
         * @param ticketData Ticket data
         * @param string origin (default Web)
         * @param bool autorespond (default True)
         * @param bool alertstaff (default True)
         * @return int ticketId
         */
        public function open($username, $password, $origin = 'Web', $autorespond, $alertstaff, $ticketData) //BSK Add $origin, $autorespond, $alertstaff. $origin is used to determine which fields will be validated.
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // does the user has permission to do this?
            if (!$this->user->canAccess($this->user))
            {
                return $this->raisePermissionError();
            }

            // Valid API key so let's continue
            $errors = array();

            // make sure the data has the UTF8 charset otherwise DB inserts would fail bigtime
            foreach ($ticketData as $key => $value)
            {
                if ($value != null) // Don't add values that are null anyway...
                    $ticketData[$key] = $this->soapDecode($value);
            }

            // create the ticket
            // BSK Add create_by_staff option
            if (strcasecmp($origin, 'staff') == 0) {
                if (isVersion('1.6')) {
                    $ticketData['issue'] = $ticketData['message']; // Put the message in the issue field for Staff submitted tickets
                    unset($ticketData['message']);
                    $ticket = Ticket::create_by_staff($ticketData, $errors);
                }
                if (isVersion('1.7') || isVersion('1.8') || isVersion('1.9') || isVersion('1.10'))
                    $ticket = Ticket::open($ticketData, $errors);
            } else {
                $ticket = Ticket::create($ticketData, $errors, $origin, $autorespond, $alertstaff); //BSK Add $origin, $autorespond, $alertstaff
            }

            if ($ticket)
            {
                $this->log('info', 'SOAP: Ticket opened', 'A ticket has been opened through SOAP from IP ' . $_SERVER['REMOTE_ADDR']);

                if (isVersion('1.8') || isVersion('1.9') || isVersion('1.10'))
                    return new soapval('', false, $ticket->getNumber());
                else
                    return new soapval('', false, $ticket->getExtId());
            } else { // whoopsie
                $this->log('warning', 'SOAP: openTicket failed', 'Opening a ticket from ' . $_SERVER['REMOTE_ADDR'] . " failed");

                return new soap_fault('SERVER', '', 'openTicket failed', implode(', ', $errors));
            }
        }

        /**
         * Get all info from a ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return TicketInfo info
         */
        public function getInfo($username, $password, $ticketId)
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            $info = array(
                'id' => $ticket->getId(),
                'number' => $ticket->getNumber(),
                'email' => $ticket->getEmail(),
                'fullname' => $ticket->getName(),
                'created' => date(DATE_RFC3339, strtotime($ticket->getCreateDate())),
                'updated' => date(DATE_RFC3339, strtotime($ticket->getUpdateDate())),
                'duedate' => date(DATE_RFC3339, strtotime($ticket->getDueDate())),
                'priority' => $ticket->getPriorityId(),
                'phone' => $ticket->getPhoneNumber(),
                'status' => $ticket->getStatus(),
                'staff' => $ticket->getStaffId(),
                'assigned' => $ticket->getAssigned(),
                'department' => $ticket->getDeptId(),
                'topic' => $ticket->getTopicId(),
                'subject' => $ticket->getSubject(),
                'overdue' => $ticket->isOverdue(),
                'closed' => $ticket->isClosed()
            );

            return new soapval('', false, $info);
        }


        /**
         * Get ticket status
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return string Status
         */
        public function getStatus($username, $password, $ticketId)
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            return new soapval('', false, $ticket->getStatus());
        }


        /**
         * Get all messages from a ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param fromDate (RFC3339 format)
         * @return TicketMessagesArray messages
         */
        public function getMessages($username, $password, $ticketId, $fromDate = null)
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            $messages = array();

            // Get all messages and responses
            if (isVersion('1.6'))
            {
                //get messages
                $sql = 'SELECT msg.*, count(attach_id) as attachments  FROM ' . TICKET_MESSAGE_TABLE . ' msg ' .
                       ' LEFT JOIN ' . TICKET_ATTACHMENT_TABLE . ' attach ON  msg.ticket_id=attach.ticket_id AND msg.msg_id=attach.ref_id AND ref_type=\'M\' ' .
                       ' WHERE  msg.ticket_id=' . db_input($ticket->getId()) .
                       ($fromDate != null ? ' AND msg.created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '') .
                       ' GROUP BY msg.msg_id ORDER BY created';

                $msgres = db_query($sql);

                while ($msg_row = db_fetch_array($msgres))
                {
                    $message = array(
                        'question' => array(
                            'id' => $msg_row['msg_id'],
                            'created' => date(DATE_RFC3339, strtotime($msg_row['created'])),
                            'name' => $ticket->getName(),
                            'message' => $msg_row['message']
                        ),
                        'answers' => array()
                    );

                    //get answers for messages
                    $sql = 'SELECT resp.*,count(attach_id) as attachments FROM ' . TICKET_RESPONSE_TABLE . ' resp ' .
                           ' LEFT JOIN ' . TICKET_ATTACHMENT_TABLE . ' attach ON  resp.ticket_id=attach.ticket_id AND resp.response_id=attach.ref_id AND ref_type=\'R\' ' .
                           ' WHERE msg_id=' . db_input($msg_row['msg_id']) .
                           ' AND resp.ticket_id=' . db_input($ticket->getId()) .
                           ($fromDate != null ? ' AND resp.created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '') .
                           ' GROUP BY resp.response_id ORDER BY created';

                    $resp = db_query($sql);

                    while ($resp_row = db_fetch_array($resp))
                    {
                        $respID = $resp_row['response_id'];

                        $message['answers'][] = array(
                            'id' => $resp_row['response_id'],
                            'created' => date(DATE_RFC3339, strtotime($resp_row['created'])),
                            'name' => $resp_row['staff_name'],
                            'message' => $resp_row['response']
                        );
                    } //endwhile...response loop.

                    $msgid = $msg_row['msg_id'];

                    $messages[] = $message;
                }
            }

            if (isVersion('1.7'))
            {
                if($thread = $ticket->getThread(false)) {
                    $message = null;

                    foreach($thread as $entry) {
                        switch ($entry['thread_type'])
                        {
                            case 'M':
                                if ($message != null)
                                    $messages[] = $message;

                                if ($fromDate == null) $fromDate = 0;

                                if (strtotime($entry['created']) > strtotime($fromDate))
                                    $message = array(
                                        'question' => array(
                                            'id'      => $entry['id'],
                                            'created' => date(DATE_RFC3339, strtotime($entry['created'])),
                                            'name'    => Format::htmlchars($ticket->getName()),
                                            'message' => Format::htmlchars($entry['body'])
                                        ),
                                        'answers'  => array()
                                    );
                                break;

                            case 'R':
                                if ($message != null)
                                    $message['answers'][] = array(
                                        'id'      => $entry['id'],
                                        'created' => date(DATE_RFC3339, strtotime($entry['created'])),
                                        'name'    => Format::htmlchars($entry['poster']),
                                        'message' => Format::htmlchars($entry['body'])
                                    );
                                break;
                            
                            default:
                                break;
                        }
                    }

                    if ($message != null)
                        $messages[] = $message;
                }
            }

            if (isVersion('1.8') || isVersion('1.9'))
            {
                $message = null;

                if($thread = $ticket->getClientThread()) {
                    foreach($thread as $entry) {
                        if ($fromDate == null) $fromDate = 0;

                        if (strtotime($entry['created']) > strtotime($fromDate))
                            $message = array(
                                'message' => array(
                                    'id'      => $entry['id'],
                                    'created' => date(DATE_RFC3339, strtotime($entry['created'])),
                                    'name'    => Format::htmlchars($entry['poster']),
                                    'body'    => Format::htmlchars($entry['body']),
                                    'type'    => ($entry['thread_type'] == 'M' ? 'question' : 'answer')
                                )
                            );
                        
                        if (($tentry = $ticket->getThreadEntry($entry['id']))
                        && ($urls = $tentry->getAttachmentUrls())
                        && ($links=$tentry->getAttachmentsLinks())) {
                            foreach($urls as $url) {
                                $message['message']['attachments'][] = array('download_url' => $url['download_url'], 'filename' => $url['filename']);
                            }
                            
                            //mail("james@atomicx.com", "Thread entry attachments", print_r($urls, true).'\n'.print_r($links, true));
                        }
                        
                        if ($message != null)
                            $messages[] = $message;
                        
                        //mail("james@atomicx.com", "Thread entry data", print_r($message, true));
                        
                        $message = null;
                    }
                }
                
                if($thread = $ticket->getNotes()) {
                    foreach($thread as $entry) {
                        if ($fromDate == null) $fromDate = 0;

                        if (strtotime($entry['created']) > strtotime($fromDate))
                            $message = array(
                                'message' => array(
                                    'id'      => $entry['id'],
                                    'created' => date(DATE_RFC3339, strtotime($entry['created'])),
                                    'name'    => Format::htmlchars($entry['poster']),
                                    'body'    => Format::htmlchars($entry['body']),
                                    'type'    => 'note'
                                )
                            );
                        
                        if (($tentry = $ticket->getThreadEntry($entry['id']))
                        && ($urls = $tentry->getAttachmentUrls())
                        && ($links=$tentry->getAttachmentsLinks())) {
                            foreach($urls as $url) {
                                $message['message']['attachments'][] = array('download_url' => $url['download_url'], 'filename' => $url['filename']);
                            }
                            
                            //mail("james@atomicx.com", "Thread entry attachments", print_r($urls, true).'\n'.print_r($links, true));
                        }
                        
                        if ($message != null)
                            $messages[] = $message;
                        
                        $message = null;
                        
                        //mail("james@atomicx.com", "Thread entry data", print_r($entry, true));
                    }
                }
            }
            
            if (isVersion('1.10'))
            {
                $message = null;

                if($thread = $ticket->getClientThread()) {
                    foreach($thread as $entry) {
                        
                        //$this->printRAsLog( $entry );
                        if ($fromDate == null) $fromDate = 0;

                        if (strtotime($entry->created) > strtotime($fromDate))
                            $message = array(
                                'message' => array(
                                    'id'      => $entry->id,
                                    'created' => date(DATE_RFC3339, strtotime($entry->created)),
                                    'name'    => Format::htmlchars($entry->poster),
                                    'body'    => Format::htmlchars($entry->body),
                                    'type'    => ($entry->type == 'M' ? 'question' : 'answer')
                                )
                            );
                        
                        if (($tentry = $ticket->getThreadEntry($entry->id))
                        && ($urls = $tentry->getAttachmentUrls())
                        && ($links=$tentry->getAttachmentsLinks())) {
                            foreach($urls as $url) {
                                $message['message']['attachments'][] = array('download_url' => $url['download_url'], 'filename' => $url['filename']);
                            }
                            
                            //mail("james@atomicx.com", "Thread entry attachments", print_r($urls, true).'\n'.print_r($links, true));
                        }
                        
                        if ($message != null)
                            $messages[] = $message;
                        
                        //mail("james@atomicx.com", "Thread entry data", print_r($message, true));
                        
                        $message = null;
                    }
                }
                
                if($thread = $ticket->getNotes()) {
                    foreach($thread as $entry) {
                        if ($fromDate == null) $fromDate = 0;

                        if (strtotime($entry->created) > strtotime($fromDate))
                            $message = array(
                                'message' => array(
                                    'id'      => $entry->id,
                                    'created' => date(DATE_RFC3339, strtotime($entry->created)),
                                    'name'    => Format::htmlchars($entry->poster),
                                    'body'    => Format::htmlchars($entry->body),
                                    'type'    => 'note'
                                )
                            );
                        
                        if (($tentry = $ticket->getThreadEntry($entry->id))
                        && ($urls = $tentry->getAttachmentUrls())
                        && ($links=$tentry->getAttachmentsLinks())) {
                            foreach($urls as $url) {
                                $message['message']['attachments'][] = array('download_url' => $url['download_url'], 'filename' => $url['filename']);
                            }
                            
                            //mail("james@atomicx.com", "Thread entry attachments", print_r($urls, true).'\n'.print_r($links, true));
                        }
                        
                        if ($message != null)
                            $messages[] = $message;
                        
                        $message = null;
                        
                        //mail("james@atomicx.com", "Thread entry data", print_r($entry, true));
                    }
                }
            }
            
            usort($messages, function($a, $b) {
                return strtotime($a['message']['created']) - strtotime($b['message']['created']);
            });
            
            mail("james@atomicx.com", "Thread entry messages", print_r($messages, true));


            return new soapval('', false, $messages);
        }

        /**
         * Get all messages from a ticket starting from given date
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param date fromDate (RFC3339 format)
         * @return TicketMessagesArray messages
         */
        public function getMessagesFromDate($username, $password, $ticketId, $date)
        {
            return $this->getMessages($username, $password, $ticketId, $date);
        }


        /**
         * Get all notes from a ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return TicketMessagesArray messages
         */
        public function getNotes($username, $password, $ticketId)
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            $notes = array();

            // Get notes
            if (isVersion('1.6'))
            {
                $sql = 'SELECT * FROM ' . TICKET_NOTE_TABLE . ' WHERE ticket_id=' . db_input($ticket->getId()) .
                       ' ORDER BY created DESC';
                $query = db_query($sql);

                while ($row = db_fetch_array($query))
                {
                    $notes[] = array(
                        'created' => date(DATE_RFC3339, strtotime($row['created'])),
                        'staff' => $row['staff_id'],
                        'title' => $row['title'],
                        'note' => $row['note']
                    );
                }
            }

            if (isVersion('1.7') || isVersion('1.8') || isVersion('1.9') || isVersion('1.10'))
            {
                if($thread = $ticket->getThread(true)) {
                    foreach($thread as $entry) {
                        if ($entry['thread_type'] == 'N')
                            $notes[] = array(
                                'created' => date(DATE_RFC3339, strtotime($entry['created'])),
                                'staff' => Format::htmlchars($entry['staff_id']),
                                'title' => Format::htmlchars($entry['title']),
                                'note' => Format::htmlchars($entry['body'])
                            );
                    }
                }
            }

            return new soapval('', false, $notes);
        }


        /**
         * Assign ticket to staff
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param int staffId
         * @param string message
         * @return bool result
         */
        public function assign($username, $password, $ticketId, $staffId, $message)
        {
            $result = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Assign staff
            if (isVersion('1.6'))
            {
                if ($result = $ticket->assignStaff($staffId, $message))
                {
                    if(!isVersion('1.10'))  { $ticket->reload(); }
                }
            }

            if (isVersion('1.7') || isVersion('1.8') || isVersion('1.9'))
            {
                if ($result = $ticket->setStaffId($staffId))
                {
                    $this->postNote($username, $password, $ticketId, "Ticket assigned to " . $ticket->getStaff()->getName(), $message);
                    if(!isVersion('1.10'))  { $ticket->reload(); }
                }
            }
            
            if ( isVersion('1.10'))
            {
                if ($result = $ticket->setStaffId($staffId))
                {
                    $this->postNote($username, $password, $ticketId, "Ticket assigned to " . $ticket->getStaff()->getName(), $message);
                }
            }

            // Return the result
            return new soapval('', false, $result);
        }


        /**
         * Close ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return boolean Result
         */
        public function close($username, $password, $ticketId, $note = null)
        {
            global $thisuser;

            $result = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }
            
            // does the user has permission to do this?
            if (!$this->user->canAccess($ticket))
            {
                return $this->raisePermissionError();
            }

            $version = explode('.', getVersion());

            // Close ticket
            if ((isVersion('1.9') && intval($version[2]) >= 4) || isVersion('1.10'))  {
                $result = $ticket->setStatus('3');
            }else{
                $result = $ticket->close();
            }

            if ($result)
            {
                if ($note == null)
                    $note = 'Ticket closed without response by ' . $thisuser->getName();
                else
                    $note .= ' - ' . $thisuser->getName();
                
                $ticket->logActivity('Ticket Closed', $note);
            }

            // Return the result
            return new soapval('', false, $result);
        }


        /**
         * Reopen ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return boolean Result
         */
        public function reopen($username, $password, $ticketId, $note = null)
        {
            global $thisuser;

            $result = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);
            
            // does the user has permission to do this?
            if (!$this->user->canAccess($ticket))
            {
                return $this->raisePermissionError();
            }
            
            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            if ($ticket->isClosed())
            {
                $version = explode('.', getVersion());
                
                // Reopen ticket
                if ((isVersion('1.9') && intval($version[2]) >= 4) || isVersion('1.10'))  {
                    $result = $ticket->setStatus('1');
                }else{
                    $result = $ticket->close();
                }

                if ($result)
                {
                    if ($note == null)
                        $note = 'Ticket reopened (without comments) by ' . $thisuser->getName();
                    else
                        $note .= ' - ' . $thisuser->getName();
                    
                    $ticket->logActivity('Ticket Reopened', $note);
                }
            }

            // Return the result
            return new soapval('', false, $result);
        }


        /**
         * Delete ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @return boolean Result
         */
        public function delete($username, $password, $ticketId)
        {
            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);
            
            // does the user has permission to do this?
            if (!$this->user->canAccess($ticket))
            {
                return $this->raisePermissionError();
            }

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Delete the ticket and return the result
            return new soapval('', false, $ticket->delete());
        }


        /**
         * Release ticket
         *
         * @param string username
         * @param string password
         * @param int ticketId
         */
        public function release($username, $password, $ticketId)
        {
            global $thisuser;

            $result = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Release ticket from assignee
            if ($staff = $ticket->getStaff())
            {
                if (isVersion('1.6'))
                    $result = $ticket->release();

                if (isVersion('1.7') || isVersion('1.8') || isVersion('1.9') || isVersion('1.10'))
                    $result = $ticket->setStaffId(0);

                if ($result)
                {
                    $note = 'Ticket released (unassigned) from ' . $staff->getName() . ' by ' . $thisuser->getName();
                    $ticket->logActivity('Ticket unassigned', $msg);

                    if(!isVersion('1.10'))  { $ticket->reload(); }
                }
            }

            // Return the result
            return new soapval('', false, $result);
        }


        /**
         * Post note
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param string title
         * @param string message
         * @return int noteId
         */
        public function postNote($username, $password, $ticketId, $title, $message)
        {
            $noteId = 0;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Post note
            if (isVersion('1.6'))
                if ($noteId = $ticket->postNote($title, $message))
                {
                    if(!isVersion('1.10'))  { $ticket->reload(); }
                }

            if (isVersion('1.7') || isVersion('1.8') || isVersion('1.9'))
            {
                $errors = array();

                if ($noteId = $ticket->postNote(array('title' => $title, 'note' => $message), $errors, $this->user))
                {
                    if(!isVersion('1.10'))  { $ticket->reload(); }
                }
            }
            
            if (isVersion('1.10'))
            {
                $errors = array();
                $noteId = $ticket->postNote(array('title' => $title, 'note' => $message), $errors, $this->user);
            }

            // Return the note ID
            return new soapval('', false, $noteId);
        }


        /**
         * Post reply
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param int messageId
         * @param string message
         * @return int respId
         */
        public function postReply($username, $password, $ticketId, $messageId, $message, $alert = true, $status = null)
        {
            $respId = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Post reply
            $respId = 0;

            if (isVersion('1.6'))
            {
                $respId = $ticket->postResponse($messageId, $message);
            }

            $errors = array();

            $version = explode('.', getVersion());

            // Close ticket
            if ((isVersion('1.9') && intval($version[2]) >= 4))  {
                switch ($status) {
                    case 'close':
                        $status = 3;
                        break;
                    default:
                        $status = 1;
                        break;
                }
                $noteId = $ticket->postReply(array('response' => $message, 'msgId' => $messageId, 'reply_ticket_status' => $status), $errors, $alert);
                //mail('james@atomicx.com', 'ticket reply', 'Status: '.$status.'\nErrors: '.print_r($errors, true).'\nAlert: '.print_r($alert, true));
            } elseif (isVersion('1.7') || isVersion('1.8') || isVersion('1.9')) {
                $noteId = $ticket->postReply(array('response' => $message, 'msgId' => $messageId, 'reply_ticket_status' => $status), $errors, $alert);
            } elseif (isVersion('1.10')) {
                switch ($status) {
                    case 'closed':
                        $status = 3;
                        break;
                    default:
                        $status = 1;
                        break;
                }
                $noteId = $ticket->postReply(array('response' => $message, 'msgId' => $messageId, 'reply_status_id' => $status), $errors, $alert);
            }

            // Refresh and answer if needed
            if ($noteId)
            {
                if(!isVersion('1.10'))  { $ticket->reload(); }

                if ($ticket->isopen())
                    $ticket->markAnswered();
                
                return new soapval('', false, $noteId->getId());
            }

            // Return the response ID
            return new soapval('', false, $respId);
        }


        /**
         * Post reply
         *
         * @param string username
         * @param string password
         * @param int ticketId
         * @param int messageId
         * @param string message
         * @return int msgId
         */
        public function postMessage($username, $password, $ticketId, $message)
        {
            $msgId = false;

            // lets validate the user
            if (!$this->validateUser($username, $password))
            {
                return $this->raiseInvalidUserError($password);
            }

            // Get the ticket
            $ticket = $this->get($ticketId);

            // Do we have a valid ticket?
            if ($ticket == null)
            {
                return $this->raiseTicketNotExistError();
            }

            // Post message
            if ($msgId = $ticket->postMessage($message, 'Web'))
            {
                if(!isVersion('1.10'))  { $ticket->reload(); }
            }

            // Return the message ID
            return new soapval('', false, $msgId);
        }


        /**
         * List by status
         *
         * @param string username
         * @param string password
         * @param string status (open/closed/all)
         * @param date fromDate (RFC3339 format)
         * @return TicketInfoArray tickets
         */
        public function listByStatus($username, $password, $status, $fromDate = null)
        {
            $result = array();

            // lets validate the user
            if (!($staffId = $this->validateUser($username, $password)))
            {
                return $this->raiseInvalidUserError($password);
            }

            $version = explode('.', getVersion());
            
            //$this->dumpObjectAsLog( $staff );
            
            if ((isVersion('1.9') && intval($version[2]) >= 4) || isVersion('1.10')) {
                // Query tickets
                if (strtoupper($status) == 'ALL')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                elseif (strtoupper($status) == 'OPEN')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND status_id=\'1\'' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                elseif (strtoupper($status) == 'ASSIGNED')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND status_id=\'1\' AND staff_id=\''.$staffId.'\'' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                else
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND (status_id=\'2\' OR status_id=\'3\')' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC LIMIT 100';
            }else{
                // Query tickets
                if (strtoupper($status) == 'ALL')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                elseif (strtoupper($status) == 'OPEN')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND status=\'' . $status . '\'' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                elseif (strtoupper($status) == 'ASSIGNED')
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND status=\'open\' AND staff_id=\''.$staffId.'\'' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC';
                else
                    $sql = 'SELECT DISTINCT ticket_id FROM ' . TICKET_TABLE . ' WHERE 1 AND status=\'' . $status . '\'' .
                           ($fromDate != null ? ' AND created >= \'' . date(DATE_RFC3339, strtotime($fromDate)) . '\'' : '').
                           ' ORDER BY updated DESC LIMIT 100';
            }
            
            //mail("james@atomicx.com", "Soap Results SQL", $sql);
            
            $query = db_query($sql);
            // error_log('sql:'. $sql);
            // Loop through the results
            while ($row = db_fetch_array($query))
            {
                $ticket = $this->get($row['ticket_id'], false);
                
                $result[] = array(
                    'id' => $row['ticket_id'],
                    'number' => $ticket->getNumber(),
                    'email' => $ticket->getEmail(),
                    'fullname' => $ticket->getName(),
                    'created' => date(DATE_RFC3339, strtotime($ticket->getCreateDate())),
                    'updated' => date(DATE_RFC3339, strtotime($ticket->getUpdateDate())),
                    'duedate' => date(DATE_RFC3339, strtotime($ticket->getDueDate())),
                    'priority' => $ticket->getPriorityId(),
                    'phone' => $ticket->getPhoneNumber(),
                    'status' => $ticket->getStatus(),
                    'staff' => $ticket->getStaffId(),
                    'assigned' => $ticket->getAssigned(),
                    'department' => $ticket->getDeptId(),
                    'topic' => $ticket->getTopicId(),
                    'subject' => $ticket->getSubject(),
                    'overdue' => $ticket->isOverdue(),
                    'closed' => $ticket->isClosed()
                );
            }
            
            //mail("james@atomicx.com", "Soap Results", print_r($result, true));

            // Return tickets (if there are any :P)
            return new soapval('', false, $result);
        }


        /**
         * List from date
         *
         * @param string username
         * @param string password
         * @param string status (open/closed/all)
         * @param string fromDate (RFC3339 format)
         * @return TicketInfoArray tickets
         */
        public function listFromDate($username, $password, $status, $fromDate)
        {
            return $this->listByStatus($username, $password, $status, $fromDate);
        }

    }

?>