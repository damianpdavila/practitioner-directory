<?php
/**
 * @package        Joomla
 * @subpackage     OSMembership
 * @author         Damian Davila
 * @copyright      Copyright (C) 2022 Moventis, LLC
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');

/* @var OSMembershipViewMembersHtml $this */

$showAvatar              = $this->params->get('show_avatar', 1);
$showPlan                = $this->params->get('show_plan', 1);
$showSubscriptionDate    = $this->params->get('show_subscription_date', 1);
$showSubscriptionEndDate = $this->params->get('show_subscription_end_date', 0);
$showLinkToProfile       = $this->params->get('show_link_to_detail', 0);
$showMembershipId        = $this->params->get('show_membership_id', 0);

$bootstrapHelper = $this->bootstrapHelper;
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');
$centerClass     = $bootstrapHelper->getClassMapping('center');

$fields = $this->fields;

// Remove first_name and last_name as it is displayed in single name field
/*
for ($i = 0, $n = count($fields); $i < $n; $i++)
{
	if (in_array($fields[$i]->name, ['first_name', 'last_name']))
	{
		unset($fields[$i]);
	}
}
*/
$cols    = count($fields);
$rootUri = JUri::root(true);

use Joomla\CMS\Factory;
$document = Factory::getDocument();
$document->addScript(JURI::base().'templates/aatcvm/js/markerclusterer.js', array('version'=>'auto'));
$document->addScript(JURI::base().'templates/aatcvm/js/pracdirmap.js', array('version'=>'auto'));
$document->addScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyAOf2GKffM0r_T8-Ecbg4L-1no02RXux5k&libraries=geometry,places');

?>
<script type="text/javascript">
    window.addEventListener("load", function() {

		// Trap the click on the member list search reset button.
		// Note that the button markup has inline click handler, so must add click handler on parent in order to intercept the button click successfully.
		var mbr_search_filter_container = document.querySelector('#osm-members-list div.filters');
		var mbr_search_reset_button = document.querySelector('#osm-members-list div.filters button.btn:nth-child(2)');
		var mbr_search_input = document.getElementById('filter_search');

		mbr_search_filter_container.addEventListener("click", function(evt) {

			if (mbr_search_reset_button.contains(evt.target)) {

				if (mbr_search_input.value.length > 0) { 
					// if text in field, then do normal process and re-do search
					;
				} else { 
					evt.preventDefault();
					evt.stopImmediatePropagation();
					// otherwise, just reset the map because there's no prior database search to reset (probably)
					document.querySelector('#osm-map input.btn:nth-child(2)').click();
				}
			}

		}, true);

	});
</script>

<?php

$data = array(
	"data" => array(),
	"member_count" => ""
);

/*
These 3 sets of data are manipulated to create the member data output.

Custom fields: $fields
[
	{"id":"15","plan_id":"0","name":"latitude","title":"Address Latitude","description":"","field_type":null,"required":"0","values":"","default_values":"","rows":"0","cols":"0","size":"0","css_class":"","extra":"","ordering":"15","published":"1","datatype_validation":"0","field_mapping":null,"is_core":"0","show_on_subscription_payment":"0","taxable":"1","newsletter_field_mapping":null,"populate_from_previous_subscription":"1","prompt_text":"","filterable":"0","pattern":null,"min":"0","max":"0","step":"0","show_on_subscription_form":"0","show_on_subscriptions":"0","hide_on_membership_renewal":"0","hide_on_email":"0","hide_on_export":"0","show_on_members_list":"1","show_on_group_member_form":"0","is_searchable":"0","show_on_profile":"0","show_on_user_profile":"1","fee_field":"0","fee_values":"","fee_formula":"","profile_field_mapping":"","depend_on_field_id":"0","depend_on_options":"[]","joomla_group_ids":"","max_length":"0","place_holder":"","multiple":"0","validation_rules":"","server_validation_rules":"","validation_error_message":"","modify_subscription_duration":"","can_edit_on_profile":"1","fieldtype":"Text","populate_from_group_admin":"0","access":"1","filter":"","container_class":"","container_size":"","input_size":"","assignment":"0","allowed_file_types":"","lannguage":null,"fieldSuffix":""},
	{"id":"16","plan_id":"0","name":"longitude","title":"Address Longitude","description":"","field_type":null,"required":"0","values":"","default_values":"","rows":"0","cols":"0","size":"0","css_class":"","extra":"","ordering":"16","published":"1","datatype_validation":"0","field_mapping":null,"is_core":"0","show_on_subscription_payment":"0","taxable":"1","newsletter_field_mapping":null,"populate_from_previous_subscription":"1","prompt_text":"","filterable":"0","pattern":null,"min":"0","max":"0","step":"0","show_on_subscription_form":"0","show_on_subscriptions":"0","hide_on_membership_renewal":"0","hide_on_email":"0","hide_on_export":"0","show_on_members_list":"1","show_on_group_member_form":"0","is_searchable":"0","show_on_profile":"0","show_on_user_profile":"1","fee_field":"0","fee_values":"","fee_formula":"","profile_field_mapping":"","depend_on_field_id":"0","depend_on_options":"[]","joomla_group_ids":"","max_length":"0","place_holder":"","multiple":"0","validation_rules":"","server_validation_rules":"","validation_error_message":"","modify_subscription_duration":"","can_edit_on_profile":"1","fieldtype":"Text","populate_from_group_admin":"0","access":"1","filter":"","container_class":"","container_size":"","input_size":"","assignment":"0","allowed_file_types":"","lannguage":null,"fieldSuffix":""}
]
Custom field values: $this->fieldsData
{"1605":{"15":"26.0092472","16":"-80.3933008"}}
...

Member details: $this->items, $row->field in the loop
[
	{"id":"1605","plan_id":"1","user_id":"2722","coupon_id":"0","first_name":"Damian","last_name":"Davila","organization":"Moventis","address":"18331 Pines Blvd #121","address2":"","city":"Pembroke Pines","state":"Florida","zip":"33029","country":"United States","phone":"+1,954-319-3567","fax":"","email":"damian@moventisusa.com","comment":"","created_date":"2020-07-10 12:54:59","payment_date":"0000-00-00 00:00:00","from_date":"2017-01-12 00:00:00","to_date":"0000-00-00 00:00:00","published":"1","amount":"0.00","tax_amount":"0.00","discount_amount":"0.00","gross_amount":"0.00","subscription_code":null,"payment_method":"os_paypal","transaction_id":"QNJNSG8L5DI7AXDL","act":"subscribe","from_subscription_id":"0","renew_option_id":"0","upgrade_option_id":"0","first_reminder_sent":"0","second_reminder_sent":"0","process_payment_for_subscription":"0","vies_registered":"0","offline_recurring_email_sent":"0","show_on_members_list":"1","refunded":"0","parent_id":"0","auto_subscribe_processed":"0","is_free_trial":"0","subscribe_newsletter":"1","agree_privacy_policy":"1","mollie_customer_id":null,"mollie_recurring_start_date":null,"tax_rate":"0.000000","trial_payment_amount":"0.000000","payment_amount":"0.000000","payment_currency":null,"receiver_email":null,"avatar":"moventis-logo-square.png","payment_made":"0","params":null,"recurring_profile_id":"","subscription_id":"","recurring_subscription_cancelled":"0","renewal_count":"0","from_plan_id":"0","membership_id":"2722","invoice_year":"0","is_profile":"1","invoice_number":"0","profile_id":"1605","language":"en-GB","username":"Damian","user_password":null,"payment_processing_fee":"0.00","group_admin_id":"0","subscription_end_sent":"0","third_reminder_sent":"0","first_reminder_sent_at":null,"second_reminder_sent_at":null,"third_reminder_sent_at":null,"subscription_end_sent_at":null,"plan_main_record":"1","plan_subscription_status":"1","plan_subscription_from_date":"2017-01-12 00:00:00","plan_subscription_to_date":"0000-00-00 00:00:00","setup_fee":"0.00","gateway_customer_id":null,"ip_address":null,"first_sms_reminder_sent":"0","second_sms_reminder_sent":"0","third_sms_reminder_sent":"0","formatted_invoice_number":null,"formatted_membership_id":null,"plan_title":"AATCVM Board Members"}
	...
]
*/

$custom_field_names = array_column($fields, 'name');
$latitude_key = array_search('latitude', $custom_field_names);
$latitude_key = $fields[$latitude_key]->id;
$longitude_key = array_search('longitude', $custom_field_names);
$longitude_key = $fields[$longitude_key]->id;

$fieldsData = $this->fieldsData;
$previous_user_id = '';

for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++)
{
	$row = $this->items[$i];

	if ($row->user_id == $previous_user_id)
	{
		// Default behavior is to show all subscriptions, but here want only unique subscribers.
		// Therefore, check for duplicates using Joomla user id which is stored with each subscription.
		continue;
	}
	$previous_user_id = $row->user_id;

	$link = JRoute::_('index.php?option=com_osmembership&view=member&id=' . $row->id . '&Itemid=' . $this->Itemid);
	$member_data = [];
	$member_data['id'] = $row->id;
	$member_data['address'] = $row->address . '<br/>'. $row->address2 . '<br/>' . $row->city .', '. $row->state .' '. $row->zip .' '. $row->country;
	$member_data['longitude'] = $fieldsData[$row->id][$longitude_key];
	$member_data['latitude'] = $fieldsData[$row->id][$latitude_key];;
	$member_data['name'] = $row->first_name . ' ' . $row->last_name;
	$member_data['email'] = $row->email;
	$member_data['organization'] = $row->organization;
	$member_data['profile_marker_picture'] = '';
	$member_data['member_link'] = $link;
	$member_data['avatar'] = '';
	
	if ($showAvatar)
	{
		if ($row->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $row->avatar))
		{
			$member_data['avatar'] = $row->avatar;
		}
	}
	$data['data'][] = $member_data;

}
$data['member_count'] = count($data['data']);
$data = json_encode($data, JSON_HEX_TAG);

?>

<script type="text/javascript">

jQuery(document).ready(function() {

	google.maps.event.addDomListener(window, 'load', showPractitionerMap(<?php echo $data;?>));

});

</script>
<style>
#markerlist {
	height: 500px;
	overflow: auto;
	box-shadow: 0px 0px 8px #00f3;
    background-color: #fcfcfc;
}
#markerlist div {
	margin: 0px 0px 0px 2em;
    text-indent: -1em;
}
a.title {
    line-height: 1.72em;
}
.btn {
  box-shadow: 0px 0px 5px #00f3;
}
button.btn:focus {
    outline: none;
}
.wrapper {
  display: grid;
  gap: 20px;
  grid-template-areas:
    "map"
    "list";
}
.filters input {
  height: auto;
  width: 100%;
  box-sizing: border-box;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
}
.osm-map {
	grid-area: map;
}
.osm-list {
	grid-area: list;
}
#osm-members-list .filters {
	display: grid;
	grid-template-columns: auto 83px;
	grid-template-areas: "search-input search-buttons";	
	gap: 3px;
}
#osm-members-list .filters div.filter-search {
	float: none;
  	grid-area: search-input;
}
#osm-members-list .filters div:not(.filter-search) {
	float: none;
	margin-left: 0;
	grid-area: search-buttons;
}
#osm-members-list form {
	margin-bottom: 0px;
}
#osm-map .filters {
	display: grid;
	gap: 5px;
	grid-template-areas: 
	"map-country"
	"map-radius"
	"map-buttons";	
	margin-bottom: 10px;
}
#osm-map .filters .btn {
  vertical-align: top;
  width: 92px;
}
#osm-map .filters input {
  margin-bottom: 0px;
}
#osm-map .filters #pac-input {
	grid-area: map-country;
}
#osm-map .filters #pac-radius {
	grid-area: map-radius;
}
#osm-map .filters .map-buttons {
  grid-area: map-buttons;
}
.info h3 {
	margin-bottom: 3px;
}

@media (min-width: 768px) {
  .wrapper {
    grid-template-columns: auto 30%;
    grid-template-areas:
      "map list";
  }
  #osm-map .filters {
	display: grid;
	grid-template-columns: 2fr 1fr 190px;
	grid-template-areas: "map-country map-radius map-buttons";	
  }
}
</style>	

<h1 class="osm-page-title"><?php echo $this->params->get('page_heading') ?: JText::_('OSM_MEMBERS_LIST') ; ?></h1>
<section id="osm-members-map-intro" class="osm-container">
	<div class="intro">
		<p>Our directory of registered TCVM professionals is intended for use by potential clients to locate veterinarians who can provide TCVM services for their animals or for TCVM practitioners to find and connect with others in their field.</p>
		<ul>
			<li>Enter a city, or state, or state + zip code (U.S. only) in the space below and click "Locate" button to find local TCVM practitioners in the area.</li>
			<li>You may also drag the map to other areas, and use the plus (+) and minus (-) buttons to zoom in or out.</li>
			<li>Numbered circles represent clusters of multiple practitioners. Click on clusters to expand them and see individual listings.</li>
		</ul>
		<p><strong>Registered AATCVM/WATCVM members:</strong> To update your profile information, simply log in first then <a href="index.php?option=com_osmembership&view=profile" title="Access the profile update page">click here to view the profile update page</a>.</p>
	</div>
</section>
<section id="osm-members-map" class="wrapper">
	<div id="osm-members-list" class="osm-list">
		<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_osmembership&view=members&Itemid='.$this->Itemid); ?>">
			<div class="filters">
				<?php echo $this->loadTemplate('search'); ?>
			</div>
			<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
		</form>
		<div id="markerlist" class=""></div>
	</div>
	<div id="osm-map" class="osm-map">
		<div class="filters">
			<input id="pac-input" class="controls hasTooltip" type="text" placeholder="City, State, Country" data-original-title="Enter the city, state, and country">
			<input id="pac-radius" class="controls hasTooltip" id='radius' name='radius' type="number" placeholder="Search radius" data-original-title="Specify the search radius from center of that location">
			<div class="map-buttons">
				<input class="btn" value="Locate" onclick="codeAddress()" type="button">
				<input class="btn" value="Reset Map" onclick="reset_state()" type="button">
			</div>
		</div>
		<div id="map" style="height:500px;"></div>
	</div>
</section>