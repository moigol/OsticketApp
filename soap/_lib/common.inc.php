<?php

    /**
     * Common include-file
     *
     * This class makes sure that everything from
     * osTicket is included/imported when needed.
     *
     * This only works when the SOAP mod is placed
     * under the api-folder (/api/soap)!
     *
     * @author CYBERDE Solutions
     * @copyright 2013
     * @version 1.5-56
     * @date 2013/01/30 20:22:22
     */

    // Include some important files
    if (!file_exists('../../main.inc.php'))
        exit(EX_CONFIG);
    require_once ('../../main.inc.php');

    if (!defined('ROOT_DIR'))
        exit(EX_CONFIG);
    if (!defined('INCLUDE_DIR'))
        exit(EX_CONFIG);

    // Define the soap include dir
    define("INCLUDE_DIR_SOAP", ROOT_DIR . "api/soap/lib/classes/");


    /**
     * AutoLoad function
     * Called automatically by PHP
     *
     * @param classname
     */
    function __autoload($classname)
    {
        $classname = strtolower($classname);

        if (substr($classname, 0, 3) == 'ost')
        {
            $classname = substr($classname, 3);

            if (file_exists(INCLUDE_DIR_SOAP . "$classname.class.php"))
                require_once (INCLUDE_DIR_SOAP . "$classname.class.php");
        }
        elseif (file_exists(INCLUDE_DIR . "class." . $classname . ".php"))
            require_once (INCLUDE_DIR . "class." . $classname . ".php");
    }

    /**
     * Check osTicket version
     *
     * @param string Required version
     * @return boolean isversion
     */
    function isVersion($version = '1.6')
    {
        return strpos(THIS_VERSION, $version) !== false;
    }
    
    function getVersion()
    {
        return THIS_VERSION;
    }

?>