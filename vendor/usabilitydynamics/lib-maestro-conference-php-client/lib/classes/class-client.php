<?php
/**
 * Client
 * API Documentation link: http://myaccount.maestroconference.com/sites/myaccount.maestroconference.com/files/API_for_Conference_Management.pdf
 *
 * @since 0.1.0
 */
namespace UsabilityDynamics\MC {

  if( !class_exists( 'UsabilityDynamics\MC\Client' ) ) {

    class Client  {
      
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
      private $apiUrl = " http://myaccount.maestroconference.com/_access/";
      
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
      public function __construct( $customer, $key ) {
      
      }
      
      /**
       * This function creates a new person in the conference with the given data, only role is
       * required other fields are optional. It returns a person object
       * 
       * @param string $conferenceId UID of existing conference
       * @param string $role Role to assign person, must be one of valid ROLE types
       * @param string $name (Optional) Name to assign this person
       * @param string $email (Optional) Email of the person
       * @param string $notes (Optional) Notes for this person
       * @param string $custom1 (Optional) Custom1 field data.
       * @param string $custom2 (Optional) Custom2 field data
       *
       * @return boolean/string PIN code returned by Maestro Conference API Call or false on error.
       */
      public function AddPerson( $conferenceId, $role, $name = '', $email = '', $notes = '', $custom1 = '' , $custom2 = '' ) {
        $response = $this->request( array_filter( array(
          'conference' => $conferenceId,
          'role' => $role,
          'email' => $email,
          'notes' => $notes,
          'custom1' => $custom1,
          'custom2' => $custom2,
        ) ) );
        return $response['code'] == 0 && isset( $response[ 'value' ][ 'PIN' ] ) ? $response[ 'value' ][ 'PIN' ] : false;
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
       *
       * @param array $args
       * @return array
       */
      private function request( $args ) {
        $respone = array(
          'code' => "0",
          'message' => "",
          'value' => "" 
        );
      
        try {
        
        } catch ( Exception $e ) {
          $e->getMessage();
          
          $response = array(
            'code' => "1",
            'message' => $e->getMessage(),
            'value' => "",
          );
        }
        
        return $respone;
      }
      
    }

  }

}
