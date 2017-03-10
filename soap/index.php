<?php

    /**
     * SOAP MOD for osTicket
     *
     * Since osTicket doesn't have an API which you can use
     * to talk to from you own apps I decided to create
     * a webservice (SOAP-service) that allows you to do so :)
     *
     * And here it is baby!
     *
     * Note:
     *  This service is in no way associated with or
     *  endorsed by osTicket nor it's creators (yet?).
     *
     * Terms:
     *  - You may not redistribute this product without permission
     *  - You are not allowed to claim this product as yours
     *  - You are responsible for any problems that might occur by using this product
     *
     *  By using it you agree to these terms.
     *
     * Website and documentation:
     *  http://www.cyberde.nl/software-en-US/osticket-soap-mod/
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

    require_once("lib/common.inc.php");
    require_once(INCLUDE_DIR_SOAP.'nusoap.class.php');

    define('SOAP_NAMESPACE',    ($_SERVER['HTTPS'] ? "https://" : "http://").$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/');
    define('SOAP_VERSION',      '1.5-56');

    // Create the server
    $server = new soap_server();

    // Set some flags
    $server->debug_flag = false;
    $server->configureWSDL('osTicket Webservice v' . SOAP_VERSION,SOAP_NAMESPACE);
    $server->wsdl->schemaTargetNamespace = SOAP_NAMESPACE;
    $server->soap_defencoding = 'UTF-8';


    /**
     * Add complex data types to the wsdl
     */

    // TicketData
    $server->wsdl->addComplexType(
        'TicketData',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'name'  => array(
                'name' => 'name',
                'type' => 'xsd:string'
            ),
            'email'  => array(
                'name' => 'email',
                'type' => 'xsd:string'
            ),
            'subject'  => array(
                'name' => 'subject',
                'type' => 'xsd:string'
            ),
            'message'  => array(
                'name' => 'message',
                'type' => 'xsd:string'
            ),
            'source'  => array(
                'name' => 'source',
                'type' => 'xsd:string'
            ),
            'deptId' => array( //BSK addded deptId
                'name' => 'deptId',
                'type' => 'xsd:int'
            ),
            'staffId' => array( //BSK addded staffId for create_by_staff
                'name' => 'staffId',
                'type' => 'xsd:int'
            ),
            'topicId'  => array(
                'name' => 'topicId',
                'type' => 'xsd:int'
            ),
            'pri'  => array(
                'name' => 'pri',
                'type' => 'xsd:int'
            ),
            'phone'  => array(
                'name' => 'phone',
                'type' => 'xsd:string'
            ),
            'ticket_state' => array(
                'name' => 'ticket_state',
                'type' => 'xsd:string'
            ),
            'response' => array(
                'name' => 'response',
                'type' => 'xsd:string'
            ),
            'assignId' => array(
                'name' => 'assignId',
                'type' => 'xsd:string'
            ),
            'note' => array(
                'name' => 'note',
                'type' => 'xsd:string'
            ),
            'duedate' => array( //BSK added duedate
                'name' => 'duedate',
                'type' => 'xsd:dateTime'
            ),
            'time' => array( //BSK added time
                'name' => 'time',
                'type' => 'xsd:dateTime'
            )
        )
    );


    // TicketInfo
    $server->wsdl->addComplexType(
        'TicketInfo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'id'  => array(
                'name' => 'id',
                'type' => 'xsd:int'
            ),
            'number'  => array(
                'name' => 'number',
                'type' => 'xsd:int'
            ),
            'email'  => array(
                'name' => 'email',
                'type' => 'xsd:string'
            ),
            'fullname'  => array(
                'name' => 'fullname',
                'type' => 'xsd:string'
            ),
            'created'  => array(
                'name' => 'created',
                'type' => 'xsd:dateTime'
            ),
            'updated'  => array(
                'name' => 'updated',
                'type' => 'xsd:dateTime'
            ),
            'duedate'  => array(
                'name' => 'duedate',
                'type' => 'xsd:dateTime'
            ),
            'priority'  => array(
                'name' => 'priority',
                'type' => 'xsd:int'
            ),
            'phone'  => array(
                'name' => 'phone',
                'type' => 'xsd:string'
            ),
            'status'  => array(
                'name' => 'status',
                'type' => 'xsd:string'
            ),
            'staff'  => array(
                'name' => 'staff',
                'type' => 'xsd:int'
            ),
            'assigned' => array(
                'name' => 'assigned',
                'type' => 'xsd:string'
            ),
            'department'  => array(
                'name' => 'department',
                'type' => 'xsd:int'
            ),
            'topic'  => array(
                'name' => 'topic',
                'type' => 'xsd:int'
            ),
            'subject'  => array(
                'name' => 'subject',
                'type' => 'xsd:string'
            ),
            'overdue'  => array(
                'name' => 'overdue',
                'type' => 'xsd:boolean'
            ),
            'closed'  => array(
                'name' => 'closed',
                'type' => 'xsd:boolean'
            )
        )
    );

    // TicketInfoArray
    $server->wsdl->addComplexType(
        'TicketInfoArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:TicketInfo[]'
            )
        ),
        'tns:TicketInfo'
    );

    // TicketMessage
    $server->wsdl->addComplexType(
        'TicketMessage',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'id'        => array(
                'name'  => 'id',
                'type'  => 'xsd:int'
            ),
            'created'   => array(
                'name'  => 'created',
                'type'  => 'xsd:dateTime'
            ),
            'name'   => array(
                'name'  => 'name',
                'type'  => 'xsd:string'
            ),
            'message'   => array(
                'name'  => 'message',
                'type'  => 'xsd:string'
            ),
            'body'   => array(
                'name'  => 'body',
                'type'  => 'xsd:string'
            ),
            'type'   => array(
                'name'  => 'type',
                'type'  => 'xsd:string'
            )
        )
    );

    // TicketMessagArray
    $server->wsdl->addComplexType(
        'TicketMessageArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:TicketMessage[]'
            )
        ),
        'tns:TicketMessage'
    );

    // TicketMessages
    $server->wsdl->addComplexType(
        'TicketMessages',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'question'  => array(
                'name'  => 'question',
                'type'  => 'tns:TicketMessage'
            ),
            'answers'   => array(
                'name'  => 'answers',
                'type'  => 'tns:TicketMessageArray'
            ),
            'message'   => array(
                'name'  => 'message',
                'type'  => 'tns:TicketMessage'
            )
        )
    );

    // TicketMessagesArray
    $server->wsdl->addComplexType(
        'TicketMessagesArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:TicketMessages[]'
            )
        ),
        'tns:TicketMessages'
    );

    // TicketNote
    $server->wsdl->addComplexType(
        'TicketNote',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'created'   => array(
                'name'  => 'created',
                'type'  => 'xsd:dateTime'
            ),
            'staff'   => array(
                'name'  => 'staff',
                'type'  => 'xsd:int'
            ),
            'title'   => array(
                'name'  => 'title',
                'type'  => 'xsd:string'
            ),
            'note'   => array(
                'name'  => 'note',
                'type'  => 'xsd:string'
            )
        )
    );

    // TicketNoteArray
    $server->wsdl->addComplexType(
        'TicketNoteArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:TicketNote[]'
            )
        ),
        'tns:TicketNote'
    );

    // StaffInfo
    $server->wsdl->addComplexType(
        'StaffInfo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'id'  => array(
                'name' => 'id',
                'type' => 'xsd:int'
            ),
            'email'  => array(
                'name' => 'email',
                'type' => 'xsd:string'
            ),
            'fullname'  => array(
                'name' => 'fullname',
                'type' => 'xsd:string'
            ),
            'firstname'  => array(
                'name' => 'firstname',
                'type' => 'xsd:string'
            ),
            'lastname'  => array(
                'name' => 'lastname',
                'type' => 'xsd:string'
            ),
            'username'  => array(
                'name' => 'username',
                'type' => 'xsd:string'
            ),
            'signature'  => array(
                'name' => 'signature',
                'type' => 'xsd:string'
            ),
            'isManager'  => array(
                'name' => 'isManager',
                'type' => 'xsd:boolean'
            ),
            'isAdmin'  => array(
                'name' => 'isAdmin',
                'type' => 'xsd:boolean'
            ),
            'isAvailable'  => array(
                'name' => 'isAvailable',
                'type' => 'xsd:boolean'
            ),
            'isActive'  => array(
                'name' => 'isActive',
                'type' => 'xsd:boolean'
            ),
            'tzOffset' => array(
                'name' => 'tzOffset',
                'type' => 'xsd:int'
            ),
            'canCreate' => array(
                'name' => 'canCreate',
                'type' => 'xsd:boolean'
            ),
            'canEdit' => array(
                'name' => 'canEdit',
                'type' => 'xsd:boolean'
            ),
            'canClose' => array(
                'name' => 'canClose',
                'type' => 'xsd:boolean'
            ),
            'canDelete' => array(
                'name' => 'canDelete',
                'type' => 'xsd:boolean'
            ),
            'canReply' => array(
                'name' => 'canReply',
                'type' => 'xsd:boolean'
            ),
            'canAssign' => array(
                'name' => 'canAssign',
                'type' => 'xsd:boolean'
            ),
            'canTransfer' => array(
                'name' => 'canTransfer',
                'type' => 'xsd:boolean'
            )
        )
    );

    // StaffInfoArray
    $server->wsdl->addComplexType(
        'StaffInfoArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:StaffInfo[]'
            )
        ),
        'tns:StaffInfo'
    );

    // TopicInfo
    $server->wsdl->addComplexType(
        'TopicInfo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'id'  => array(
                'name' => 'id',
                'type' => 'xsd:int'
            ),
            'name'  => array(
                'name' => 'name',
                'type' => 'xsd:string'
            ),
            'department'  => array(
                'name' => 'department',
                'type' => 'xsd:int'
            ),
            'priority'  => array(
                'name' => 'priority',
                'type' => 'xsd:int'
            ),
            'isEnabled'  => array(
                'name' => 'isEnabled',
                'type' => 'xsd:boolean'
            ),
            'isActive'  => array(
                'name' => 'isActive',
                'type' => 'xsd:boolean'
            )
        )
    );

    // TopicInfoArray
    $server->wsdl->addComplexType(
        'TopicInfoArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:TopicInfo[]'
            )
        ),
        'tns:TopicInfo'
    );

    // DepartmentInfo
    $server->wsdl->addComplexType(
        'DepartmentInfo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'id'  => array(
                'name' => 'id',
                'type' => 'xsd:int'
            ),
            'name'  => array(
                'name' => 'name',
                'type' => 'xsd:string'
            ),
            'email'  => array(
                'name' => 'email',
                'type' => 'xsd:string'
            ),
            'isPublic'  => array(
                'name' => 'isPublic',
                'type' => 'xsd:boolean'
            )
        )
    );

    // DepartmentInfoArray
    $server->wsdl->addComplexType(
        'DepartmentInfoArray',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'               => 'SOAP-ENC:arrayType',
                'wsdl:arrayType'    => 'tns:DepartmentInfo[]'
            )
        ),
        'tns:DepartmentInfo'
    );


    /**
     * Register soap methods
     */

    // ostDepartment.getInfo
    $server->register('ostDepartment.getInfo',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'departmentId'  => 'xsd:int'
        ),
        array(
            'result'    => 'tns:DepartmentInfo'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets information from a department'
    );

    // ostDepartment.listAll
    $server->register('ostDepartment.listAll',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string'
        ),
        array(
            'result'    => 'tns:DepartmentInfoArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'List departments'
    );

    // ostTicket.assign
    $server->register('ostTicket.assign',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int',
            'staffId'  => 'xsd:int',
            'message'  => 'xsd:string',
        ),
        array(
            'result'    => 'xsd:boolean'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Assign a ticket'
    );

    // ostTicket.close
    $server->register('ostTicket.close',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int',
            'note'  => 'xsd:string'
        ),
        array(
            'result'    => 'xsd:boolean'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Close a ticket'
    );

    // ostTicket.delete
    $server->register('ostTicket.delete',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'result'    => 'xsd:boolean'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Delete a ticket'
    );

    // ostTicket.getInfo
    $server->register('ostTicket.getInfo',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'info'    => 'tns:TicketInfo'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets info from a Ticket'
    );

    // ostTicket.getMessages
    $server->register('ostTicket.getMessages',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'messages'    => 'tns:TicketMessagesArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets messages from a Ticket'
    );

    // ostTicket.getMessagesFromDate
    $server->register('ostTicket.getMessagesFromDate',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int',
            'date'      => 'xsd:dateTime'
        ),
        array(
            'messages'    => 'tns:TicketMessagesArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets messages from a Ticket starting from given date (RFC3339 format)'
    );


    // ostTicket.getMessages
    $server->register('ostTicket.getNotes',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'notes'     => 'tns:TicketNoteArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets notes from a Ticket'
    );

    // ostTicket.getStatus
    $server->register('ostTicket.getStatus',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'status'    => 'xsd:string'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets the status of a Ticket'
    );


    // ostTicket.listByStatus
    $server->register('ostTicket.listByStatus',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'status'        => 'xsd:string'
        ),
        array(
            'tickets'    => 'tns:TicketInfoArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets tickets by given status (open/closed/all)'
    );

    // ostTicket.listFromDate
    $server->register('ostTicket.listFromDate',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'status'        => 'xsd:string',
            'date'          => 'xsd:dateTime'
        ),
        array(
            'tickets'    => 'tns:TicketInfoArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets tickets starting from a given date (RFC3339 format) with given status (open/closed/all)'
    );

    // ostTicket.open
    $server->register('ostTicket.open',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'origin'        => 'xsd:string',
            'autorespond'   => 'xsd:boolean',
            'alertstaff'    => 'xad:boolean',
            'ticketData'    => 'tns:TicketData',
        ),
        array(
            'ticketId'    => 'xsd:int'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Adds a ticket to the system'
    );

    // ostTicket.postMessage
    $server->register('ostTicket.postMessage',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'ticketId'      => 'xsd:int',
            'message'       => 'xsd:string'
        ),
        array(
            'msgId'        => 'xsd:int'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Post a message to a ticket'
    );

    // ostTicket.postNote
    $server->register('ostTicket.postNote',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int',
            'title'     => 'xsd:string',
            'message'   => 'xsd:string'
        ),
        array(
            'noteId'    => 'xsd:int'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Post a note to a ticket'
    );

    // ostTicket.postReply
    $server->register('ostTicket.postReply',
        array(
            'username'      => 'xsd:string',
            'password'      => 'xsd:string',
            'ticketId'      => 'xsd:int',
            'messageId'     => 'xsd:int',
            'message'       => 'xsd:string',
            'alert'       => 'xsd:boolean',
            'status'       => 'xsd:string'
        ),
        array(
            'respId'        => 'xsd:int'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Post a reply to message from a ticket'
    );

    // ostTicket.release
    $server->register('ostTicket.release',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int'
        ),
        array(
            'result'    => 'xsd:boolean'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Release a ticket from assignee'
    );

    // ostTicket.reopen
    $server->register('ostTicket.reopen',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'ticketId'  => 'xsd:int',
            'note'  => 'xsd:string'
        ),
        array(
            'result'    => 'xsd:boolean'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Reopen a ticket'
    );

    // ostStaff.getId
    $server->register('ostStaff.getId',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string'
        ),
        array(
            'result'    => 'xsd:int'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets ID of staff member by login credentials'
    );
    
    // ostStaff.getInfoForLogin
    $server->register('ostStaff.getInfoForLogin',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string'
        ),
        array(
            'result'    => 'tns:StaffInfo'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets information from a staffmember login'
    );
    
    // ostStaff.getInfo
    $server->register('ostStaff.getInfo',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'staffId'   => 'xsd:int'
        ),
        array(
            'result'    => 'tns:StaffInfo'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets information from a staffmember'
    );

    // ostStaff.listAll
    $server->register('ostStaff.listAll',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string'
        ),
        array(
            'result'    => 'tns:StaffInfoArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'List staffmembers'
    );

    // ostTopic.getInfo
    $server->register('ostTopic.getInfo',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string',
            'topicId'   => 'xsd:int'
        ),
        array(
            'result'    => 'tns:TopicInfo'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'Gets information from a topic'
    );

    // ostTopic.listAll
    $server->register('ostTopic.listAll',
        array(
            'username'  => 'xsd:string',
            'password'  => 'xsd:string'
        ),
        array(
            'result'    => 'tns:TopicInfoArray'
        ),
        SOAP_NAMESPACE,
        false,false,false,
        'List topics'
    );



    /**
     * Some logic
     */

    // pass incoming (posted) data
	if ( isset( $HTTP_RAW_POST_DATA ) ) {
		$input = $HTTP_RAW_POST_DATA;
	} else {
		$input = implode( "\r\n", file( 'php://input' ) );
	}

	// execute whatever is requested from the webservice.
	$server->service( $input );

?>