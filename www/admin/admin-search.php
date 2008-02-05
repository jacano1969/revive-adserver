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
require_once MAX_PATH . '/lib/OA/Admin/UI/Search.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-statistics.inc.php';
require_once MAX_PATH . '/www/admin/lib-gui.inc.php';

phpAds_registerGlobalUnslashed('keyword', 'client', 'campaign', 'banner', 'zone', 'affiliate', 'compact');

OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER);


// Check Searchselection
if (!isset($client)) $client = false;
if (!isset($campaign)) $campaign = false;
if (!isset($banner)) $banner = false;
if (!isset($zone)) $zone = false;
if (!isset($affiliate)) $affiliate = false;


if ($client == false &&    $campaign == false &&
    $banner == false &&    $zone == false &&
    $affiliate == false)
{
    $client = true;
    $campaign = true;
    $banner = true;
    $zone = true;
    $affiliate = true;
}

if (!isset($compact)) {
    $compact = false;
}

if (!isset($keyword)) {
    $keyword = '';
}


// Send header with charset info
header ("Content-Type: text/html".(isset($phpAds_CharSet) && $phpAds_CharSet != "" ? "; charset=".$phpAds_CharSet : ""));

$agencyId = OA_Permission::getAgencyId();

$dalZones = OA_Dal::factoryDAL('zones');
$rsZones = $dalZones->getZoneByKeyword($keyword, $agencyId);
$rsZones->reset(); // Reset RecordSet (execute the query on database)

$dalAffiliates = OA_Dal::factoryDAL('affiliates');
$rsAffiliates = $dalAffiliates->getAffiliateByKeyword($keyword, $agencyId);
$rsAffiliates->reset();

$dalBanners = OA_Dal::factoryDAL('banners');
$rsBanners = $dalBanners->getBannerByKeyword($keyword, $agencyId);
$rsBanners->reset();

$dalClients = OA_Dal::factoryDAL('clients');
$rsClients = $dalClients->getClientByKeyword($keyword, $agencyId);
$rsClients->reset();

$dalCampaigns = OA_Dal::factoryDAL('campaigns');
$rsCampaigns = $dalCampaigns->getCampaignAndClientByKeyword($keyword, $agencyId);
$rsCampaigns->reset();

$matchesFound = false;
if ($rsClients->getRowCount() ||
    $rsCampaigns->getRowCount() ||
    $rsBanners->getRowCount() ||
    $rsAffiliates->getRowCount() ||
    $rsZones->getRowCount())
{
    $matchesFound = true;
}

$aClients = array();
while ($rsClients->fetch()) {
    $aClient = $rsClients->toArray();
    $aClient['clientname'] = phpAds_breakString ($aClient['clientname'], '30');
    $aClient['campaigns'] = array();

    if (!$compact) {
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->clientid = $aClient['clientid'];
        $doCampaigns->find();

        while ($doCampaigns->fetch()) {
            $aCampaign = $doCampaigns->toArray();
            $aCampaign['campaignname'] = phpAds_breakString ($aCampaign['campaignname'], '30');
            $aCampaign['banners'] = array();

            $doBanners = OA_Dal::factoryDO('banners');
            $doBanners->campaignid = $aCampaign['campaignid'];
            $doBanners->find();

            while ($doBanners->fetch()) {
                $aBanner = $doBanners->toArray();

                $aBanner['name'] = $GLOBALS['strUntitled'];
                if (isset($aBanner['alt']) && $aBanner['alt']) $aBanner['name'] = $aBanner['alt'];
                if (isset($aBanner['description']) && $aBanner['description']) $aBanner['name'] = $aBanner['description'];
                $aBanner['name'] = phpAds_breakString ($aBanner['name'], '30');

                $aCampaign['banners'][] = $aBanner;
            }
        }

        $aClient['campaigns'][] = $aCampaign;
    }

    $aClients[] = $aClient;
}


$aCampaigns = array();
while ($rsCampaigns->fetch()) {
    $aCampaign = $rsCampaigns->toArray();
    $aCampaign['campaignname'] = phpAds_breakString ($aCampaign['campaignname'], '30');
    $aCampaign['banners'] = array();

    if (!$compact) {
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = $aCampaign['campaignid'];
        $doBanners->find();

        while ($doBanners->fetch()) {
            $aBanner = $doBanners->toArray();

            $aBanner['name'] = $GLOBALS['strUntitled'];
            if (isset($aBanner['alt']) && $aBanner['alt']) $aBanner['name'] = $aBanner['alt'];
            if (isset($aBanner['description']) && $aBanner['description']) $aBanner['name'] = $aBanner['description'];
            $aBanner['name'] = phpAds_breakString ($aBanner['name'], '30');

            $aCampaign['banners'][] = $aBanner;
        }
    }

    $aCampaigns[] = $aCampaign;
}


$aBanners = array();
while ($rsBanners->fetch()) {
    $aBanner = $rsBanners->toArray();

    $aBanner['name'] = $GLOBALS['strUntitled'];
    if (isset($aBanner['alt']) && $aBanner['alt']) $aBanner['name'] = $aBanner['alt'];
    if (isset($aBanner['description']) && $aBanner['description']) $aBanner['name'] = $aBanner['description'];
    $aBanner['name'] = phpAds_breakString ($aBanner['name'], '30');

    $aBanners[] = $aBanner;
}


$aAffiliates = array();
while ($rsAffiliates->fetch()) {
    $aAffiliate = $rsAffiliates->toArray();
    $aAffiliate['name'] = phpAds_breakString ($aAffiliate['name'], '30');

    if (!$compact) {
        $doZones = OA_Dal::factoryDO('zones');
        $doZones->affiliateid = $aAffiliate['affiliateid'];
        $doZones->find();

        while ($doZones->fetch()) {
            $aZone = $doZones->toArray();
            $aZone['zonename'] = phpAds_breakString ($aZone['zonename'], '30');

            $aAffiliate['zones'][] = $aZone;
        }
    }

    $aAffiliates[] = $aAffiliate;
}


$aZones = array();
while ($rsZones->fetch()) {
    $aZone = $rsZones->toArray();
    $aZone['zonename'] = phpAds_breakString ($aZone['zonename'], '30');

    $aZones[] = $aZone;
}

$oTpl = new OA_Admin_Template('admin-search.html');

$oTpl->assign('matchesFound', $matchesFound);

$oTpl->assign('keyword', $keyword);
$oTpl->assign('compact', $compact);

$oTpl->assign('client', $client);
$oTpl->assign('campaign', $campaign);
$oTpl->assign('banner', $banner);
$oTpl->assign('affiliate', $affiliate);
$oTpl->assign('zone', $zone);

$oTpl->assign('aClients', $aClients);
$oTpl->assign('aCampaigns', $aCampaigns);
$oTpl->assign('aBanners', $aBanners);
$oTpl->assign('aAffiliates', $aAffiliates);
$oTpl->assign('aZones', $aZones);


$oUI = new OA_Admin_UI_Search();

$oUI->showHeader($keyword);
$oTpl->display();
$oUI->showFooter();

?>