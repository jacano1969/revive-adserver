<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/plugins/deliveryLimitations/DeliveryLimitationsCommaSeparatedData.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

/**
 * A Client delivery limitation plugin, for filtering delivery of ads on the
 * basis of the viewer's operating system.
 *
 * Works with:
 * A comma separated string of operating system codes. See the Os.res.inc.php
 * resource file for details of the valid operating system codes.
 *
 * Valid comparison operators:
 * =~, !~
 *
 * @package    OpenadsPlugin
 * @subpackage DeliveryLimitations
 * @author     Andrew Hill <andrew@m3.net>
 * @author     Chris Nutting <chris@m3.net>
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */
class Plugins_DeliveryLimitations_Client_Os extends Plugins_DeliveryLimitations_CommaSeparatedData
{
    function init($data)
    {
        parent::init($data);
        $this->setAValues(array_keys($this->res));
    }

    /**
     * Return name of plugin
     *
     * @return string
     */
    function getName()
    {
        return MAX_Plugin_Translation::translate('Operating system', $this->module, $this->package);
    }

    /**
     * Return if this plugin is available in the current context
     *
     * @return boolean
     */
    function isAllowed()
    {
        return !empty($GLOBALS['_MAX']['CLIENT']);
    }

    /**
     * Outputs the HTML to display the data for this limitation
     *
     * @return void
     */
    function displayArrayData()
    {
        $tabindex =& $GLOBALS['tabindex'];

		echo "<table cellpadding='3' cellspacing='3'>";
		foreach ($this->res as $key => $value) {
			if ($i % 4 == 0) echo "<tr>";
			echo "<td><input type='checkbox' name='acl[{$this->executionorder}][data][]' value='$key'".(in_array($key, $this->data) ? ' checked="checked"' : '')." tabindex='".($tabindex++)."'>".ucfirst($value)."</td>";
			if (($i + 1) % 4 == 0) echo "</tr>";
			$i++;
		}
		if (($i + 1) % 4 != 0) echo "</tr>";
		echo "</table>";
    }


    /**
     * A private method to return this delivery limitation plugin as a SQL limiation.
     *
     * @access private
     * @param string $comparison As for Plugins_DeliveryLimitations::_getSqlLimitation(),
     *                           but only '=' and '!=' permitted.
     * @param string $data A comma separated list of operating system codes.
     * @return mixed As for Plugins_DeliveryLimitations::_getSqlLimitation().
     *
     * @TODO Needs to be changed to deal with databases other than MySQL.
     */
    function _getSqlLimitation($comparison, $data)
    {
        return $this->_getSqlLimitationForArray($comparison, $data, 'os');
    }

}

?>
