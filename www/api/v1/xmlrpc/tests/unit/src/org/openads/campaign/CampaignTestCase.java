/*
+---------------------------------------------------------------------------+
| OpenX v2.5                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openx.org/                         |
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
|  Copyright 2003-2007 Openads Limited                                      |
|                                                                           |
|  Licensed under the Apache License, Version 2.0 (the "License");          |
|  you may not use this file except in compliance with the License.         |
|  You may obtain a copy of the License at                                  |
|                                                                           |
|    http://www.apache.org/licenses/LICENSE-2.0                             |
|                                                                           |
|  Unless required by applicable law or agreed to in writing, software      |
|  distributed under the License is distributed on an "AS IS" BASIS,        |
|  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. |
|  See the License for the specific language governing permissions and      |
|  limitations under the License.                                           |
+---------------------------------------------------------------------------+
$Id$
*/

package org.openads.campaign;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;
import org.openads.advertiser.AdvertiserTestCase;
import org.openads.config.GlobalSettings;
import org.openads.utils.DateUtils;

/**
 * Base class for all campaign web service tests
 * 
 * @author <a href="mailto:apetlyovanyy@lohika.com">Andriy Petlyovanyy</a>
 */
public class CampaignTestCase extends AdvertiserTestCase {
	protected static final String GET_CAMPAIGN_LIST_BY_ADVERTISER_ID_METHOD = "getCampaignListByAdvertiserId";
	protected static final String GET_CAMPAIGN_METHOD = "getCampaign";
	protected static final String ADD_CAMPAIGN_METHOD = "addCampaign";
	protected static final String DELETE_CAMPAIGN_METHOD = "deleteCampaign";
	protected static final String MODIFY_CAMPAIGN_METHOD = "modifyCampaign";
	protected static final String CAMPAIGN_ZONE_STATISTICS_METHOD = "campaignZoneStatistics";
	protected static final String CAMPAIGN_DAILY_STATISTICS_METHOD = "campaignDailyStatistics";
	protected static final String CAMPAIGN_PUBLISHER_STATISTICS_METHOD = "campaignPublisherStatistics";
	protected static final String CAMPAIGN_BANNER_STATISTICS_METHOD = "campaignBannerStatistics";

	protected static final String CAMPAIGN_ID = "campaignId";
	protected static final String ADVERTISER_ID = "advertiserId";
	protected static final String CAMPAIGN_NAME = "campaignName";
	protected static final String END_DATE = "endDate";
	protected static final String START_DATE = "startDate";
	protected static final String IMPRESSIONS = "impressions";
	protected static final String CLICKS = "clicks";
	protected static final String WEIGHT = "weight";
	protected static final String PRIORITY = "priority";

	protected Integer advertiserId;

	protected void setUp() throws Exception {
		super.setUp();

		// create test advertiser
		advertiserId = createAdvertiser();
	}

	protected void tearDown() throws Exception {

		deleteAdvertiser(advertiserId);

		super.tearDown();
	}

	/**
	 * @return campaign id
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public Integer createCampaign() throws XmlRpcException,
			MalformedURLException {

		return createCampaign(getCampaignParams("test"));
	}
	
	/**
	 * @return advertiser id
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public Integer createCampaign(Map<String, Object> params)
			throws XmlRpcException, MalformedURLException {
		((XmlRpcClientConfigImpl) client.getClientConfig())
				.setServerURL(new URL(GlobalSettings.getCampaignServiceUrl()));

		Object[] paramsWithId = new Object[] { sessionId, params };
		final Integer result = (Integer) client.execute(ADD_CAMPAIGN_METHOD, paramsWithId);
		
		return result;
	}

	/**
	 * @param id -
	 *            id of campaign you want to remove
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public boolean deleteCampaign(Integer id) throws XmlRpcException,
			MalformedURLException {
		((XmlRpcClientConfigImpl) client.getClientConfig())
		.setServerURL(new URL(GlobalSettings.getCampaignServiceUrl()));
		
		return (Boolean) client.execute(DELETE_CAMPAIGN_METHOD, new Object[] {
				sessionId, id });
	}

	public Object execute(String method, Object[] params)
			throws XmlRpcException, MalformedURLException {
		// set URL
		((XmlRpcClientConfigImpl) client.getClientConfig())
				.setServerURL(new URL(GlobalSettings.getCampaignServiceUrl()));

		return client.execute(method, params);
	}

	/**
	 * @param prefix
	 * @return
	 */
	public Map<String, Object> getCampaignParams(String prefix) {
		Map<String, Object> params = new HashMap<String, Object>();
		params.put(ADVERTISER_ID, advertiserId);
		params.put(CAMPAIGN_NAME, prefix + CAMPAIGN_NAME);
		params.put(START_DATE, DateUtils.getDate(1998, Calendar.JANUARY, 1));
		params.put(END_DATE, DateUtils.getDate(2007, Calendar.SEPTEMBER, 19));
		params.put(IMPRESSIONS, -1);
		params.put(CLICKS, -1);
		params.put(PRIORITY, 0);
		params.put(WEIGHT, 100);
		return params;
	}
}
