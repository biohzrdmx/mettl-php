<?php

	/**
	 * Mettl API for PHP
	 * A simple PHP interface for the Mettl ReST API
	 * Conforming to Mettl API v1.9
	 * @author   biohzrdmx  <github.com/biohzrdmx>
	 * @version  1.0
	 * @license  MIT
	 * @uses     cURL
	 */

	Class Mettl {
		/**
		 * The access credentials for the Mettl API
		 * @var array
		 */
		protected $credentials;

		/**
		 * Default constructor
		 * @param  array  $credentials Array with access credentials
		 */
		function __construct($credentials) {
			$this->credentials = $credentials;
		}

		/**
		 * Return a new instance of the Mettl class
		 * @param  array  $credentials Array with access credentials
		 * @return object              The new Mettl object
		 */
		static function newInstance($credentials) {
			return new self($credentials);
		}

		/**
		 * Execute an HTTP request using cURL
		 * @uses   cURL
		 * @param  string $method HTTP method (get, post)
		 * @param  string $url    URL to request
		 * @param  array  $params Array of parameters, associative (optional)
		 * @return mixed          The response body or False on error
		 */
		protected function request($method, $url, $params = array()) {
			global $site;
			# Create query string
			$query = http_build_query($params);
			$method = strtolower($method);
			if ( $query && ($method == 'get' || $method == 'delete') ) {
				$url = "{$url}?{$query}";
			}
			print_a($url);
			# Open connection
			$ch = curl_init();
			# Set the url, number of POST vars, POST data, etc
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if ($method == 'post') {
				curl_setopt($ch, CURLOPT_POST, count($params));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			} else if ($method == 'put') {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			} else if ($method == 'delete') {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			}
			# Execute request
			$result = curl_exec($ch);
			# Close connection
			curl_close($ch);
			# Return API response (in UTF8 encoding)
			return utf8_encode($result);
		}

		/**
		 * Create signature based on parameters
		 * @param  string $verb   HTTP verb ('get', 'post', etc)
		 * @param  string $url    Service URL
		 * @param  array  $params Array with parameters
		 * @return array          The signed array
		 */
		protected function signParameters($verb, $url, $params) {
			$ret = $params;
			$verb = strtoupper($verb);
			# Get current timestamp
			$timestamp = time();
			# Add extra fields
			$params['ak'] = rawurlencode($this->credentials['public_key']);
			$params['ts'] = rawurlencode($timestamp);
			# Sort parameters
			uksort($params, 'strcasecmp');
			# Build request string
			$items = implode("\n", $params);
			$str = "{$verb}{$url}\n{$items}";
			# Generate signature
			$signature = base64_encode( hash_hmac( 'sha1', $str, $this->credentials['private_key'], true) );
			# Add signature
			$params['asgn'] = $signature;
			# And return signed array
			return $params;
		}

		/**
		 * Get all Assessments
		 * @param  integer $limit      Number of assessments to fetch
		 * @param  integer $offset     Return number of assessments starting from offset
		 * @param  string  $sort       Sorting scheme chosen while returning assessments ('createdAt', 'testTaken', 'name')
		 * @param  string  $sort_order Ascending or Descending order of sorting ('asc', 'desc')
		 * @return object              Object containing list of assessments in client's account or error object
		 */
		function getAssessments($limit = 20, $offset = 0, $sort = 'createdAt', $sort_order = 'desc') {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = 'http://api.mettl.com/v1/assessments';
			# Populate parameters array
			$params = array(
				'limit' => $limit,
				'offset' => $offset,
				'sort' => $sort,
				'sort_order' => $sort_order
			);
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Get details of a particular Assessment
		 * @param  string $assessment_id The assessment Id
		 * @return object                Object containing summary of test
		 */
		function getAssessment($assessment_id) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/assessments/%s', $assessment_id);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Get all schedule details for an Assessment
		 * @param  string $assessment_id The assessment Id
		 * @return Object                Object containing details of all schedules
		 */
		function getAssessmentSchedules($assessment_id) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/assessments/%s/schedules', $assessment_id);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Create Schedule for a particular Assessment
		 * @param  string $assessment_id The assessment Id
		 * @param  array  $sc            Schedule Details of a single Schedule that needs to be created for the corresponding Assessment
		 * @return object                Object containing details of a particular schedule
		 */
		function addAssessmentSchedule($assessment_id, $sc) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'post';
			$url = sprintf('http://api.mettl.com/v1/assessments/%s/schedules', $assessment_id);
			# Populate parameters array
			$params = array(
				'sc' => json_encode($sc)
			);
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Get all schedules in an Account
		 * @param  integer $limit      Number of schedules to fetch
		 * @param  integer $offset     Return number of schedules starting from offset
		 * @param  string  $sort       Sorting scheme chosen while returning schedules ('createdAt', 'testTaken', 'name')
		 * @param  string  $sort_order Ascending or Descending order of sorting ('asc', 'desc')
		 * @return object              Object containing details of all schedules
		 */
		function getSchedules($limit = 20, $offset = 0, $sort = 'createdAt', $sort_order = 'desc') {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = 'http://api.mettl.com/v1/schedules';
			# Populate parameters array
			$params = array(
				'limit' => $limit,
				'offset' => $offset,
				'sort' => $sort,
				'sort_order' => $sort_order
			);
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Get details of a particular schedule in an account
		 * @param  string $access_key The schedule access key
		 * @return object             Object containing details of a particular schedule
		 */
		function getSchedule($access_key) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/schedules/%s', $access_key);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Fetch status of test for all candidates
		 * @param  string  $access_key The schedule access key
		 * @param  integer $limit      Number of candidates to fetch
		 * @param  integer $offset     Return # of candidates starting from offset number
		 * @param  string  $sort       Sorting scheme chosen while returning candidates ('testStartTime', 'name')
		 * @param  string  $sort_order Ascending or Descending order of sorting ('asc', 'desc')
		 * @return object              Object containing details of the candidates registered for particular schedule
		 */
		function getScheduleCandidates($access_key, $limit = 20, $offset = 0, $sort = 'testStartTime', $sort_order = 'desc') {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/schedules/%s/candidates', $access_key);
			# Populate parameters array
			$params = array(
				'limit' => $limit,
				'offset' => $offset,
				'sort' => $sort,
				'sort_order' => $sort_order
			);
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Register candidate(s) for a test
		 * @param  string $access_key The schedule access key
		 * @param  array  $rd         Registration details of multiple candidates (max. 20 candidates) combined
		 * @return object             Object containing details of the registration status corresponding to each distinct user (whose credentials were supplied)
		 */
		function addScheduleCandidate($access_key, $rd) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'post';
			$url = sprintf('http://api.mettl.com/v1/schedules/%s/candidates', $access_key);
			# Populate parameters array
			$params = array(
				'rd' => json_encode($rd),
			);
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Fetch status of a test for a candidate
		 * @param  string $access_key         The schedule access key
		 * @param  string $candidate_email_id The candidate email
		 * @return object                     Object containing details of the candidate with the specified email registered for that particular schedule
		 */
		function getScheduleCandidate($access_key, $candidate_email_id) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/schedules/%s/candidates/%s', $access_key, $candidate_email_id);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Delete Report
		 * @param  string $access_key         The schedule access key
		 * @param  string $candidate_email_id The candidate email
		 * @return object                     Object containing the resut of the operation
		 */
		function deleteScheduleCandidate($access_key, $candidate_email_id) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'delete';
			$url = sprintf('http://api.mettl.com/v1/schedules/%s/candidates/%s', $access_key, $candidate_email_id);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

		/**
		 * Get all details for a particular candidate
		 * @param  string $candidate_email_id The candidate email
		 * @return object                     Object containing all details for a particular candidate
		 */
		function getCandidate($candidate_email_id) {
			$ret = false;
			# Set HTTP verb and URL
			$verb = 'get';
			$url = sprintf('http://api.mettl.com/v1/candidates/%s', $candidate_email_id);
			# Populate parameters array
			$params = array();
			# Sign parameters array
			$params = $this->signParameters($verb, $url, $params);
			# Execute request
			$response = $this->request($verb, $url, $params);
			# Decode the JSON object
			if ($response) {
				$ret = json_decode($response);
			}
			return $ret;
		}

	}

?>