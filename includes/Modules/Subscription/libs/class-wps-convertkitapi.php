<?php
if ( ! class_exists( 'WPS_ConvertKitApi' ) ) {

	class WPS_ConvertKitApi {
		/**
		 * @var string URL to use for all requests.
		 */
		protected $apiUrl = 'https://api.convertkit.com/';
		/**
		 * @var string The API key for your account.
		 */
		protected $apiKey;
		/**
		 * @var int The API version you wish to use
		 */
		protected $version;
		/**
		 * @var resource The cURL resource
		 */
		protected $ch;
		/**
		 * @param string $apiKey The API key for your account.
		 * @param int $version The API version you wish to use.
		 */
		public function __construct($apiKey, $version = 'v3') {
			$this->apiKey = $apiKey;
			$this->version = $version;
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'ConvertKit-PHP');
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);
		}
		/**
		 * @param array $arguments
		 * @return string
		 */
		protected function prepareQueryString(array $arguments = array()) {
			$arguments['api_key'] = $this->apiKey;
			//$arguments['v'] = $this->version;
			return http_build_query($arguments);
		}
		/**
		 * @param string $apiEndpoint
		 * @param array $arguments
		 * @param string $queryType
		 * @return string
		 */
		protected function queryApi($apiEndpoint, array $arguments = array(), $queryType = 'GET') {
			$fullUrl = $this->apiUrl . $this->version . '/' . $apiEndpoint;
			if ($queryType == 'POST') {
				curl_setopt($this->ch, CURLOPT_POST, true);
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->prepareQueryString($arguments));
			} else {
				$fullUrl .= '?' . $this->prepareQueryString($arguments);
			}
			curl_setopt($this->ch, CURLOPT_URL, $fullUrl);
			$responseBody = curl_exec($this->ch);
			return $responseBody;
		}
		/**
		 * @return string JSON string.
		 */
		public function getCourses() {
			return $this->queryApi('courses');
		}
		/**
		 * @return string JSON string
		 */
		public function getForms() {
			return $this->queryApi('forms');
		}
		/**
		 * @param integer $formId
		 * @param string $email
		 * @param string $firstName
		 * @param string $courseOptedIn
		 * @return string JSON string.
		 */
		public function subscribeToAForm($formId, $email, $firstName = null, $courseOptedIn = 'true') {
			// We want to use a string, not a boolean in the query.
			if(is_bool($courseOptedIn)) {
				$courseOptedIn = ($courseOptedIn == false) ? 'false' : 'true';
			}
			$form_details = $this->getFormDetails($formId);

			$apiEndpoint = 'forms/' . $formId . '/subscribe';

			return $this->queryApi(
				$apiEndpoint,
				array(
					'email' => $email,
					'first_name' => $firstName,
					//'course_opted' => $courseOptedIn
				),
				'POST'
			);
		}
		/**
		 * @param integer $formId
		 * @return string JSON string.
		 */
		public function getFormDetails($formId) {
			$apiEndpoint = 'forms/' . $formId;
			return $this->queryApi($apiEndpoint);
		}
		/**
		 * Closes the cURL connection.
		 */
		public function __destruct() {
			if (is_resource($this->ch)) {
				curl_close($this->ch);
			}
		}
	}

}
