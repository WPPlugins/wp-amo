<?php
namespace ArcStone\AMO;

/**
 * AMO API Wrapper
 *
 * Handles API gets
 *
 * @version 2.2.2
 */

Class API {

	static $api_url = 'https://admin.associationsonline.com/secure/api/index.cfm/'; // hardcoded b/c it won't change (often).
	public $api_key = '';
	public $db_table = 'amo_shortcodes_cache';
	public $response = false;
	public $debug = false;
	public $error = false;

	// vars for pagination
	public $paginated = false;
	public $current_page = 1;
	public $per_page = 0;
	public $total_results = 0;

	public function __construct( $api_key, $debug = false ) {
		global $wpdb;

		if ( !empty( $api_key ) ) {
			$this->api_key = $api_key;
		} else {
			$this->error = 'No API key set.';
		}

		if ( $debug === true ) {
			$this->debug = true;
		}

		$this->db_table = $wpdb->prefix.$this->db_table;
	}

	/**
	 * Check for a valid API key
	 *
	 * @return results JSON or false
	 */
	public function checkKey() {
		return $this->processRequest( 'AMOAssociation' );
	}

	/**
	 * Set current page for pagination
	 *
	 * @since 3.1.0
	 */
	public function setCurrentPage( $page ) {
		$this->current_page = ($page) ? $page : 1;
	}

	/**
	 * Build the API request
	 *
	 * @param string $method API method to use
	 * @param array  $params An array of parameters to include with the request
	 *
	 * @return string URL
	 * @throws \Exception
	 */
	private function _buildRequest( $method, $params ) {
		$api_params = array ( 'apikey' => $this->api_key );

		if ( empty( $method ) ) {
			throw new \Exception( 'Invalid API method.' );
		}

		if ( !empty( $params ) && !is_array( $params ) ) {
			throw new \Exception( 'Invalid API parameters. Array expected.', 1);
		}

		if ( !empty( $params ) ) {

			$api_params = array_merge( $params, $api_params );
		}

		$query_string = http_build_query( $api_params );
		return self::$api_url . $method . '?' . $query_string;
	}

	/**
	 * Curl the API request.
	 *
	 * Request data from API.
	 * Returns data on HTTP status = 200. Otherwise sets an error and returns false
	 *
	 * @param string $request_url API request URI
	 * @return json or false
	 */
	private function _getRequest( $request_url ) {

		$curl_opts = array(
							CURLOPT_URL 			=>	$request_url,
							CURLOPT_SSL_VERIFYPEER	=>	true,
							CURLOPT_RETURNTRANSFER	=>	true
							);

		$ch = curl_init();
		curl_setopt_array( $ch, $curl_opts );
		$returned_data = curl_exec( $ch );
		$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE);

		switch ( $http_status ) {
			case 0:
				$this->error = 'Unable to reach API.';
				break;
			case 200:
				return $returned_data;
				break;
			case 401:
				$this->error = 'Invalid API Key.';
				break;
			case 500:
				$this->error = 'API Server: Internal error.';
				break;
			default:
				$this->error = 'API server returned HTTP code: ' . $http_status;

		}

		return false;

	}

	/**
	 *
	 * Get Fresh Cached data from database.
	 *
	 * Returns non-expired cached for a given request.
	 * If the cache is passed the timeout, returns false.
	 *
	 * @param string $request_url. Request URL.
	 * @param int $cache_timeout Optional. Cache freshness in minutes.
	 * @return string Response JSON.
	 */
	public function _getCache( $request_url, $cache_timeout = 15 ) {
		global $wpdb;

		$request_url_hash = $this->_hashRequestURL( $request_url );
		$results = $wpdb->get_results( $wpdb->prepare(
											"SELECT * FROM {$this->db_table} 
											WHERE `request_url_hash` = %s
											AND TIMESTAMPDIFF(MINUTE, `timestamp`, NOW()) < %d",
											$request_url_hash,
											$cache_timeout
										));

		if ( $results ) {
			return $results[0]->response_json;
		}

		return false;
	}

	/**
	 * Write response to cache.
	 *
	 * @param string $method. API method.
	 * @param string $request_url. API request URL.
	 * @param string $response_json. API response JSON.
	 * @return object or boolean query results
	 */
	public function _writeCache( $method, $request_url, $response_json ) {
		global $wpdb;

		// we're not caching anything >= 64kb, the limit of MySQL's TEXT field.
		if ( strlen( $response_json ) > 65534 ) {
			/**
			 * if a record exists (because previous $response_json was smaller than current $response_json),
			 * delete it, otherwise we'll be stuck with a stale record.
			 */
			$this->clearCacheForRequest( $request_url );
			return false;
		}

		$request_url_hash = $this->_hashRequestURL( $request_url );
		$results = $wpdb->get_results( $wpdb->prepare(
												"SELECT id FROM {$this->db_table}
												WHERE `request_url_hash` = %s",
												$request_url_hash
												));

		// if an entry already exists for this $request_url, update it with the new data.
		if ( $results ) {
			return $wpdb->query( $wpdb->prepare(
								"UPDATE {$this->db_table}
								SET `response_json` = %s, `timestamp` = NOW()
								WHERE id = %d",
								$response_json,
								$results[0]->id
							));
		} else {
			return $wpdb->query( $wpdb->prepare(
									"INSERT INTO {$this->db_table} ( `method`, `request_url_hash`, `response_json`, `timestamp`)
									VALUES(%s, %s, %s, NOW())",
									$method,
									$request_url_hash,
									$response_json
									));
		}
	}

	/**
	 * Clear API results cache
	 *
	 * Deletes all entries from the results cache database table.
	 *
	 * @return query result.
	 */
	public function clearCache() {
		global $wpdb;
		return $wpdb->query( "DELETE FROM {$this->db_table}" );
	}

	/**
	 * Delete from cache DB for a specific request URL
	 *
	 * @param string $request_url the request url
	 * @return boolean delete successful or not
	 * @since 2.2.2
	 */
	private function clearCacheForRequest( $request_url ) {
		global $wpdb;

		return $wpdb->query( $wpdb->prepare(
									"DELETE FROM {$this->db_table} 
									WHERE `request_url_hash` = %s",
									$this->_hashRequestURL( $request_url )
							));
	}

	/**
	 * Hash the request URL.
	 *
	 * @param string The request URL.
	 * @return string MD5 hash of the request URL
	 */
	public function _hashRequestURL( $request_url ) {
		return md5( $request_url );
	}

	/**
	 * Return the error message.
	 *
	 * @return string or false
	 */
	public function getErrorMessage() {
		if ( $this->error ) {
			return $this->error;
		}

		return false;
	}

	/**
	 * Process API request
	 *
	 * Builds the API query. Returns associative array or ''.
	 *
	 * @param string $method The API method
	 * @param array $params Optional. An array of API parameters to be passed to the API.
	 * @param boolean $use_cache Optional. Set to `false` to disable caching for request.
	 * @return array Associative array of results.
	 *
	 * @since 2.0
	 */
	public function processRequest( $method, $params = null, $use_cache = true ) {

		if ( isset($params['debug']) && $params['debug'] === 'true' ) {
			unset( $params['debug'] );
			$this->debug = true;
		}

		/**
		 * If page_per is set, then the shortcode is doing pagination.
		 * With the present API structure, do not actually want to send
		 * this value to the API. Pagination will be done locally.
		 */
		if ( !empty( $params['page_per'] ) ) {
			$this->per_page = $params['page_per'];
			unset( $params['page_per'] ); // remove this parameter from the API request.
			$this->paginated = true;
		}

		$request_url = $this->_buildRequest( $method, $params );
		$debug_used_cache = 'false';

		if ( $use_cache ) {

			$results = $this->_getCache( $request_url );
			$debug_used_cache = 'true';

			if ( !$results ) {
				/**
				 * Note: 	$results might be blank.
				 * 			Presently, the individual shortcode "views" handle error,
				 * 			so the code writes that blank result to the cache.
				 */
				$results = $this->_getRequest( $request_url );

				/**
				 * If the $results === false there has been a communication error with the API.
				 * Let's try to pull cached results from the past 3 month.
				 */
				if ( $results === false ) {
					$debug_used_cache = 'true';
					$results = $this->_getCache( $request_url, (43800 * 3) );
				} else {
					$this->_writeCache( $method, $request_url, $results );
					$debug_used_cache = 'false';
				}
			}
		} else {
			$results = $this->_getRequest( $request_url );
		}

		// json decode
		$results = json_decode( $results, true );
		$this->total_results = count( $results );

		// If pagination set, return current page of chunked array
		if ( !empty( $results) && $this->paginated === true ) {
			$results_chunked = array_chunk( $results, $this->per_page );
			if ( count( $results_chunked ) >= $this->current_page ) {
				$results = $results_chunked[($this->current_page - 1)];
			} else {
				$results = null;
			}

		}


		// rudimentary debug output.
		if ( $this->debug ) {
			echo '<pre>';
			echo "used_cache: $debug_used_cache<br>";
			echo "results count: " . count( $results );
			var_dump($request_url);
			var_dump( $results );
			var_dump( $this->error );
			echo '</pre>';
		}


		return $results;

	}
}
