<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
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

// Require the initialisation file
require_once '../../init.php';

// Required files
require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/max/Admin/Languages.php';
require_once MAX_PATH . '/lib/max/Admin/Redirect.php';
require_once MAX_PATH . '/lib/OA/Central/AdNetworks.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-statistics.inc.php';
require_once MAX_PATH . '/www/admin/lib-zones.inc.php';
require_once MAX_PATH . '/lib/OA/Dll/Publisher.php';
require_once MAX_PATH . '/lib/OA/Admin/Menu.php';

// Register input variables
phpAds_registerGlobalUnslashed ('move', 'name', 'website', 'contact', 'email', 'language', 'advsignup',
                               'errormessage', 'submit', 'publiczones_old', 'formId', 'category', 'country', 'language');

// Security check
OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER);
OA_Permission::enforceAccessToObject('affiliates', $affiliateid, true);

// Initialise Ad  Networks
$oAdNetworks = new OA_Central_AdNetworks();

/*-------------------------------------------------------*/
/* Process submitted form                                */
/*-------------------------------------------------------*/

if (isset($formId)) {
    // Setup a new publisher object and set the fields passed in from the form:
    $oPublisher = new OA_Dll_PublisherInfo();
    $oPublisher->agencyId       = $agencyid;
    $oPublisher->contactName    = $contact;
    $oPublisher->emailAddress   = $email;
    $oPublisher->publisherId    = $affiliateid;
    $oPublisher->publisherName  = $name;
    $oPublisher->oacCategoryId  = $category;
    $oPublisher->oacCountryCode = $country;
    $oPublisher->oacLanguageId  = $language;
    $oPublisher->website        = $website;

    // Do I need to handle this?
//    $oPublisher->adNetworks =   ($adnetworks == 't') ? true : false;
    $oPublisher->advSignup  =   ($advsignup == 't') ? true : false;

    $oPublisherDll = new OA_Dll_Publisher();
    if ($oPublisherDll->modify($oPublisher) && !$oPublisherDll->_noticeMessage) {
        $redirect_url = "affiliate-zones.php?affiliateid={$oPublisher->publisherId}";
        MAX_Admin_Redirect::redirect($redirect_url);
        exit;
    }
}

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

if ($affiliateid != "") {
    OA_Admin_Menu::setPublisherPageContext($affiliateid, 'affiliate-edit.php');
    phpAds_PageShortcut($strAffiliateHistory, 'stats.php?entity=affiliate&breakdown=history&affiliateid='.$affiliateid, 'images/icon-statistics.gif');
    phpAds_PageHeader("4.2.2");
    echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br /><br /><br />";
    phpAds_ShowSections(array("4.2.2", "4.2.3","4.2.4","4.2.5","4.2.6","4.2.7"));

    // Do not get this information if the page
    // is the result of an error message
    if (!isset($affiliate)) {
        $doAffiliates = OA_Dal::factoryDO('affiliates');
        if ($doAffiliates->get($affiliateid)) {
            $affiliate = $doAffiliates->toArray();
        }
    }
} else {
    phpAds_PageHeader("4.2.1");
    echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br /><br /><br />";
    phpAds_ShowSections(array("4.2.1"));
}

/*-------------------------------------------------------*/
/* Form requirements                                     */
/*-------------------------------------------------------*/

// Get unique affiliate
// XXX: Although the JS suggests otherwise, this unique_name constraint isn't enforced.
$doAffiliates = OA_Dal::factoryDO('affiliates');
// TODOPERM - do we really want unique names here?
$aUniqueNames = $doAffiliates->getUniqueValuesFromColumn('name', $affiliate['name']);

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

require_once MAX_PATH . '/lib/OA/Admin/Template.php';

$oTpl = new OA_Admin_Template('affiliate-edit.html');

$oTpl->assign('error',  $oPublisherDll->_errorMessage);
$oTpl->assign('notice', $oPublisherDll->_noticeMessage);
$oTpl->assign('affiliateid', $affiliateid);
$oTpl->assign('move', $move);

$oTpl->assign('fieldsTop', array(
    array(
        'title'     => $strBasicInformation,
        'fields'    => array(
            array(
                'name'      => 'website',
                'label'     => $strWebsite,
                'value'     => $affiliate['website']
            ),
            array(
                'type'      => 'custom',
                'template'  => 'adnetworks',
                'label'     => 'Ad Networks',
                'vars'      => array(
                                    'checked'           => !empty($affiliate['an_website_id']),
                                    'checked_advsignup' => !empty($affiliate['as_website_id']),
                                    'disabled'          => !$GLOBALS['_MAX']['CONF']['sync']['checkForUpdates']
                                   ),
            ),
            array(
                'name'      => 'name',
                'label'     => $strName,
                'value'     => $affiliate['name']
            ),
            array(
                'name'      => 'category',
                'label'     => $strCategory,
                'type'      => 'select',
                'options'   => $oAdNetworks->getCategoriesSelect(),
                'value'     => $affiliate['oac_category_id'],
                'style'     => 'width: 15em',
                //'disabled'  => !empty($affiliate['an_website_id'])
            ),
            array(
                'type'      => 'custom',
                'template'  => 'country-language',
                'vars'      => array(
                                'aCountries'  => $oAdNetworks->getCountriesSelect(),
                                'aLanguages' => $oAdNetworks->getLanguagesSelect(),
                                'country'  => $affiliate['oac_country_code'],
                                'language' => $affiliate['oac_language_id']
                               ),
               // 'disabled'  => !empty($affiliate['an_website_id'])
            ),
            array(
                'name'      => 'contact',
                'label'     => $strContact,
                'value'     => $affiliate['contact']
            ),
            array(
                'name'      => 'email',
                'label'     => $strEMail,
                'value'     => $affiliate['email']
            )
        )
    )
));
$oTpl->assign('showAdDirect', (defined('OA_AD_DIRECT_ENABLED') && OA_AD_DIRECT_ENABLED === true) ? true : false);

//var_dump($oTpl);
//die();
$oTpl->display();

?>

<script language='JavaScript'>
<!--
    max_formSetRequirements('website', '<?php echo addslashes($strWebsite); ?>', true, 'url');
    max_formSetRequirements('category', '<?php echo addslashes($strCategory); ?>', true, 'present');
    max_formSetRequirements('country', '<?php echo addslashes($strCountry); ?>', true, 'present');
    max_formSetRequirements('language', '<?php echo addslashes($strLanguage); ?>', true, 'present');
    max_formSetConditionalValidate('category', '$("#advsignup").get(0).checked==true');
    max_formSetConditionalValidate('country', '$("#advsignup").get(0).checked==true');
    max_formSetConditionalValidate('language', '$("#advsignup").get(0).checked==true');
    max_formSetRequirements('name', '<?php echo addslashes($strName); ?>', true, 'unique');
    max_formSetUnique('name', '|<?php echo addslashes(implode('|', $aUniqueNames)); ?>|');
//-->
</script>

<?php

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

phpAds_PageFooter();

?>
