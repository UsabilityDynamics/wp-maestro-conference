<?php

/**
 * Client
 * API Documentation link: http://myaccount.maestroconference.com/sites/myaccount.maestroconference.com/files/API_for_Conference_Management.pdf
 *
 * @since 0.1.0
 */

namespace UsabilityDynamics\MC {

  if (!class_exists('UsabilityDynamics\MC\Client')) {

	class Client {

	  /**
	   * An optional type parameter of XML or JSON to define the format for the returned data.
	   *
	   * @var string
	   */
	  private $responseFormat = "json";

	  /**
	   * MaestroConference API URL
	   *
	   * @var string
	   */
	  private $apiUrl = "http://myaccount.maestroconference.com/_access/";

	  /**
	   * UID of the customer
	   *
	   * @var string
	   */
	  private $customer = false;

	  /**
	   * API key defined for this customer
	   *
	   * @var string
	   */
	  private $key = false;

	  /**
	   *
	   * @param string $customer UID of the customer
	   * @param string $key API key defined for this customer
	   */
	  public function __construct($customer = null, $key = null) {
		$this->customer = $customer;
		$this->key = $key;
	  }

	  /**
	   * This call is used to retrieve data about the customer. 
	   *
	   * @return boolean/object customer.
	   */
	  public function getCustomer() {
		$response = $this->request(array(), 'getCustomer', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve a list of conferences that are currently active 
	   *
	   * @return boolean/object list of conference.
	   */
	  public function getActiveConference() {
		$response = $this->request(array(), 'getActiveConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve a list of conferences that are upcoming. 
	   *
	   * @return boolean/object list of conference.
	   */
	  public function getUpcomingConference() {
		$response = $this->request(array(), 'getUpcomingConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve a list of conferences that are currently possible to start.
	   *
	   * @return boolean/object list of conference.
	   */
	  public function getPossibleConference() {
		$response = $this->request(array(), 'getPossibleConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve a list of conferences that have expired.
	   *
	   * @return boolean/object list of conference.
	   */
	  public function getExpiredConference() {
		$response = $this->request(array(), 'getExpiredConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve a list of conferences that were canceled.
	   *
	   * @return boolean/object list of conference.
	   */
	  public function getCanceledConference() {
		$response = $this->request(array(), 'getCanceledConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is used to retrieve data about the conference and related UIDs only.
	   * For full conference details including child objects use getConferenceData.
	   *
	   * @param string $conferenceId UID of existing conference
	   *
	   * @return boolean/object of conference.
	   */
	  public function getConference($conferenceId) {
		$response = $this->request(array_filter(array(
			'conferenceUID' => $conferenceId
				)), 'getConference', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to retrieve all the data associated with the conference and all the child objects such as person, settings and customer
	   *
	   * @param string $conferenceId UID of existing conference
	   *
	   * @return boolean/object of conference.
	   */
	  public function getConferenceData($conferenceId) {
		$response = $this->request(array_filter(array(
			'conferenceUID' => $conferenceId
				)), 'getConferenceData', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to retrieve the data associated with the conference reservation.
	   *
	   * @param string $conferenceId UID of existing conference
	   *
	   * @return boolean/object.
	   */
	  public function getConferenceReservation($conferenceId) {
		$response = $this->request(array_filter(array(
			'reservationUID' => $conferenceId
				)), 'getConferenceReservation', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to retrieve information associated with a specific call.
	   * Will not retrieve data for active call.  Use the conductor API for live calls.
	   *
	   * @param string $conferenceId UID of existing conference
	   * @param string $callUID Unique Identifier of the call
	   *
	   * @return boolean/object.
	   */
	  public function getCall($conferenceId, $callUID) {
		$response = $this->request(array_filter(array(
			'conferenceUID' => $conferenceId,
			'callUID' => $callUID
				)), 'getCall', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to retrieve information associated with a specific call.
	   * Will not retrieve data for active call.
	   * Use the conductor API for live calls.
	   * This will return a list of callers and details about the call.
	   *
	   * @param string $conferenceId UID of existing conference
	   * @param string $callUID Unique Identifier of the call
	   *
	   * @return boolean/object.
	   */
	  public function getCallData($conferenceId, $callUID) {
		$response = $this->request(array_filter(array(
			'conferenceUID' => $conferenceId,
			'callUID' => $callUID
				)), 'getCallData', 'get');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to update customer editable fields.
	   *
	   * @param string $field Field to update (contactEmail, greenroom, recording, affiliateCode,
	   * reminder, backgroundMusic, preCall, postCall, banner, callerInterface, regComment).
	   * @param string $value Value to be assigned to field
	   *
	   * @return boolean/an updated customer object.
	   */
	  public function updateCustomer($field, $value) {
		$response = $this->request(array_filter(array(
			'value' => $value,
			'field' => $field
				)), 'updateCustomer', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call creates a reservationless conference with the supplied parameters.
	   *
	   * @param numeric $estimatedCallers Estimated number of callers for this conference
	   * @param string $name (Optional) Name of conference
	   * @param string $contactEmail (Optional)If supplied it overrides the default from the customer record.  If missing then the customer contactEmail list is used
	   * @param boolean $greenroom (Optional)If supplied it overrides the customer default
	   * @param boolean $recording (Optional)If supplied it overrides the customer default
	   *
	   * @return boolean/conference object.
	   */
	  public function createConferenceReservationless($estimatedCallers, $name = '', $contactEmail = '', $greenroom = '', $recording = '') {
		$response = $this->request(array_filter(array(
			'name' => $name,
			'contactEmail' => $contactEmail,
			'greenroom' => $greenroom,
			'recording' => $recording,
			'estimatedCallers' => $estimatedCallers
				)), 'createConferenceReservationless', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call creates a reservation based conference with the supplied parameters. 
	   * It returns a conference object. 
	   * The parameters allow for variable number of reservations to be added during creation by
	   * specifying separate estimatedCallers, startDate and duration for each reservation. 
	   * The conference will be created if at least one of the supplied reservation items are valid. 
	   *
	   * @param numeric $reservationCount Number of reservation for this conference.  Must be at least 1
	   * @param numeric $estimatedCallers Estimated number of callers for this reservation
	   * @param datetime $startDate Starting date and time for this reservation(yyyy.MM.dd HH:mm:ss)
	   * @param numeric $duration Number of minutes this reservation is for, it will be rounded up to next 15 minute increment
	   * @param string $name (Optional) Name of conference
	   * @param string $contactEmail (Optional)If supplied it overrides the default from the customer record.  If missing then the customer contactEmail list is used
	   * @param boolean $greenroom (Optional)If supplied it overrides the customer default
	   * @param boolean $recording (Optional)If supplied it overrides the customer default
	   * @param boolean $backgroundMusic (Optional)If supplied it overrides the customer default
	   *
	   * @return boolean.
	   * Error codes:
	   * 1- No valid reservation was found.  None of the specified reservation items we able to be used to create the conference
	   */
	  public function createConferenceReserved($reservationCount, $estimatedCallers, $startDate, $duration, $name = '', $contactEmail = '', $greenroom = '', $recording = '', $backgroundMusic = '') {
		$response = $this->request(array_filter(array(
			'name' => $name,
			'contactEmail' => $contactEmail,
			'greenroom' => $greenroom,
			'recording' => $recording,
			'backgroundMusic' => $backgroundMusic,
			'reservationCount' => $reservationCount,
			'estimatedCallers1' => $estimatedCallers,
			'startDate1' => $startDate,
			'duration1' => $duration
				)), 'createConferenceReserved', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call creates a reservation for an existing conference with the supplied parameters.
	   *
	   * @param string $conference UID of existing conference
	   * @param numeric $estimatedCallers Estimated number of callers for this reservation
	   * @param datetime $startDate Starting date and time for this reservation (yyyy.MM.dd HH:mm:ss)
	   * @param numeric $duration (Optional)Number of minutes this reservation is for, it will be rounded up to next 15 minute increment
	   *
	   * @return boolean/conference schedule object.
	   * Error codes:
	   * 1 - Reservation start time over lapse with another.
	   * 2 - Reservation duration over lapse with another.
	   * 3 - Estimated caller size in invalid
	   * 4 - Cannot add conference reservation to reservationless conference
	   */
	  public function addConferenceReservation($conference, $estimatedCallers, $startDate, $duration = '') {
		$response = $this->request(array_filter(array(
			'conference' => $conference,
			'estimatedCallers' => $estimatedCallers,
			'startDate' => $startDate,
			'duration' => $duration
				)), 'addConferenceReservation', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This function creates a new person in the conference with the given data, only role is
	   * required other fields are optional.
	   *
	   * @param string $conferenceId UID of existing conference
	   * @param string $role Role to assign person, must be one of valid ROLE types
	   * @param string $name (Optional) Name to assign this person
	   * @param string $email (Optional) Email of the person
	   * @param string $notes (Optional) Notes for this person
	   * @param string $custom1 (Optional) Custom1 field data.
	   * @param string $custom2 (Optional) Custom2 field data
	   *
	   * @return boolean/person object.
	   */
	  public function addPerson($conferenceId, $role, $name = '', $email = '', $notes = '', $custom1 = '', $custom2 = '') {
		$response = $this->request(array_filter(array(
			'conference' => $conferenceId,
			'role' => $role,
			'name' => $name,
			'email' => $email,
			'notes' => $notes,
			'custom1' => $custom1,
			'custom2' => $custom2,
				)), 'addPerson', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This function updates a person with the given data based on the UID
	   *
	   * @param string $person UID of the person to apply updates against
	   * @param string $field Name of the field to update (name, email, notes, custom1, custom2, role)
	   * @param string $value New value to assign to field
	   *
	   * @return boolean/person object.
	   */
	  public function updatePerson($person, $field, $value) {
		$response = $this->request(array_filter(array(
			'person' => $person,
			'field' => $field,
			'value' => $value
				)), 'updatePerson', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This function removes a person in the conference with the given UID
	   *
	   * @param string $conference UID of existing conference
	   * @param string $person UID of the person to remove
	   *
	   * @return boolean
	   */
	  public function removePerson($conference, $person) {
		$response = $this->request(array_filter(array(
			'conference' => $conference,
			'person' => $person
				)), 'removePerson', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This function edits a reservation with the passed in parameters
	   *
	   * @param string $reservationUID UID of existing conference reservation
	   * @param datetime $startDate (Optional) New start time for this reservation.  Invalid for reservationless conference
	   * @param integer $estimatedCallers (Optional) Estimated number of callers.
	   * @param integer $duration (Optional) Reservation length.  Invalid for reservationless conference
	   *
	   * @return boolean/ an updated reservation object
	   * Error codes:
	   * 1 - Reservation start time over lapse with another.
	   * 2 - Reservation duration over lapse with another.
	   * 3 - Estimated caller size is invalid
	   * 4 - Cannot edit start date or duration of reservationless conference
	   */
	  public function updateConferenceReservation($reservationUID, $startDate, $estimatedCallers, $duration) {
		$response = $this->request(array_filter(array(
			'reservationUID' => $reservationUID,
			'startDate' => $startDate,
			'estimatedCallers' => $estimatedCallers,
			'duration' => $duration
				)), 'updateConferenceReservation', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to remove a conference reservation.  Note you cannot remove a past reservation or the last reservation on a conference
	   *
	   * @param string $reservationUID UID of existing conference reservation
	   *
	   * @return boolean/string
	   * Error codes:
	   * 1 - Conference is reservationless
	   * 2 - Last reservation of conference, cannot remove.
	   * 3 - Reservation is already past.
	   * 4 - Reservation is currently active.
	   */
	  public function removeConferenceReservation($reservationUID) {
		$response = $this->request(array_filter(array(
			'reservationUID' => $reservationUID
				)), 'removeConferenceReservation', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to cancel a conference.  It will return a conference object for the canceled conference
	   *
	   * @param string $conference Unique Identifier of existing conference to cancel
	   *
	   * @return boolean/string
	   * Error codes:
	   * 1 - Conference does not exist or is invalid
	   */
	  public function cancelConference($conference) {
		$response = $this->request(array_filter(array(
			'conference' => $conference
				)), 'cancelConference', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to update conference editable fields
	   *
	   * @param string $field Field to update (contactEmail,registerText,notes,recording,gree nroom,reminder,backgroundMusic, callerInterface, preCall, postCall, banner,regComment)
	   * @param string $value Value to be assigned to field
	   *
	   * @return boolean/ an updated conference object
	   * Error codes:
	   * 1 - Conference does not exist or is invalid
	   * 2 - Field is not valid
	   */
	  public function updateConference($conference, $field, $value) {
		$response = $this->request(array_filter(array(
			'value' => $value,
			'field' => $field,
			'conference' => $conference
				)), 'updateConference', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * This call is designed to update custom values associated with Customer, Conference, Call, Caller or person.
	   * To remove a custom value just assign it an empty value. You cannot update call or callers that are currently active.
	   *
	   * @param string $objectType Type of the object (conference, person, call, caller, customer)
	   * @param string $objectUID UID of the object to associate the value
	   * @param string $customKey Key to associate the value with
	   * @param string $customValue Value to associate to the object's given key
	   *
	   * MC_CONFIRMATION_PAGE - URL of the confirmation page to redirect the user to when they use the self registration URL,
	   * if a confirmation query parameter is provided to the self registration URL it will over ride this value.
	   * If this value is missing or set to blank it will use the default confirmation page.
	   *
	   * RESELLER_INFO_TEXT - The text to display in the reseller information  button on the conductor screen. 
	   * Both  RESELLER_INFO_TEXT and  RESELLER_INFO_URL need to be set for the  button to show up.
	   *
	   * RESELLER_INFO_URL - The URL to display on the browser in a new  window when the button is clicked.  
	   * Both  RESELLER_INFO_TEXT and  RESELLER_INFO_URL need to be set for the  button to show up. 
	   *
	   * RESELLER_INFO_TEXT  - The text to display in the reseller information  button on the conductor screen. 
	   * Both  RESELLER_INFO_TEXT and  RESELLER_INFO_URL need to be set for the  button to show up.  
	   *
	   * RESELLER_INFO_URL  - The URL to display on the browser in a new  window when the button is clicked.  
	   * Both  RESELLER_INFO_TEXT and  RESELLER_INFO_URL need to be set for the  button to show up.
	   *
	   * @return boolean
	   */
	  public function updateCustomValue($objectType, $objectUID, $customKey, $customValue) {
		$response = $this->request(array_filter(array(
			'objectType' => $objectType,
			'objectUID' => $objectUID,
			'customKey' => $customKey,
			'customValue' => $customValue
				)), 'updateCustomValue', 'post');
		return $response['code'] == 0 ? $response : false;
	  }

	  /**
	   * Request to API
	   *
	   * Error codes:
	   * '-1' General Error not categorized else where, read message for more details.
	   * '-2' Parameter error, one of the supplied parameters is invalid.
	   *      The message will provide more detail as to the actual failure
	   * '-3' Servlet error, usually due to comminucations error.
	   *      Read message for more details, call can probably be retried.
	   * '-4' Unhandled Exception. Sometihng the processing of this call failed in an unplaned process.
	   *      If this persist please contact customer support.
	   * '-5' Invalid customer key
	   *
	   * @param array $args
	   * @return array
	   */
	  private function request($args, $action, $request_type) {
		$response = array(
			'message' => "",
			'value' => array(),
			'code' => "0"
		);

		try {
		  $args['customer'] = $this->customer;
		  $args['key'] = $this->key;
		  $args["type"] = $this->responseFormat;
		  $ch = curl_init();
		  $Url = $this->apiUrl . $action;

		  if ($request_type == 'get') {
			$Url = $Url . '?' . http_build_query($args) . "\n";
		  } elseif ($request_type == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
		  }

		  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($ch, CURLOPT_URL, $Url);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_VERBOSE, 1);
		  $result = curl_exec($ch);

		  curl_close($ch);
		  $response = json_decode($result, true);
		} catch (Exception $e) {
		  $e->getMessage();

		  $response = array(
			  'code' => "1",
			  'message' => $e->getMessage(),
			  'value' => "",
		  );
		}

		return array('response' => $response);
	  }

	}

  }
}
