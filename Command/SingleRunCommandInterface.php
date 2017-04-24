<?php

namespace Ibtikar\GlanceDashboardBundle\Command;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
interface SingleRunCommandInterface {

    /**
     * this function is used to stop the command before it start
     */
    public function stopCommand();

    /**
     * this function is used to get the command log file entry prefix
     * @return string the prefix for this command in environment log file
     */
    public function getCommandLogPrefix();

}
