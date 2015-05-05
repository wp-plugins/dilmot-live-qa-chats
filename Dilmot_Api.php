<?php
require_once('Dilmot_Plugin.php');

class Dilmot_api {
	private $aPlugin;
	private $api_url;
	private $post_id;

	function __construct() {
  	$this->aPlugin = new Dilmot_Plugin();
  }

  public function authenticate($received_md5_api_key, $received_token) {
  	if (empty($received_md5_api_key)) {
  		$realm = 'Restricted area';
    	header('HTTP/1.1 401 Unauthorized');
    	header('WWW-Authenticate: Digest realm="'.$realm.
      	     '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
    	die('Authentication is needed');
    }

		$api_key = $this->aPlugin->getOption('ApiKey');
		$last_token = $this->aPlugin->getOption('LastAccessToken');
		if ($last_token < $received_token) {
			$md5_api_key = md5($received_token . $api_key);
			if ($md5_api_key != $received_md5_api_key) {
				$msg = "Wrong api key";
			}

		} else {
			$msg = "Token expired";
		}
		if ($msg) {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.1 401 Unauthorized');
			die($msg);
		}

		// everything is ok - update token so next call will success
		$this->aPlugin->updateOption('LastAccessToken',$received_token);
  }

  public function execute($action, $stream_id, $data) {
  	try {
  		$this->init_api_url($action, $stream_id);
  		$this->post_id = $this->find_post($stream_id);

			switch ($action) {
				case 'create':
					$res = $this->update_stream($stream_id, $data, true);
					break;
				case 'update':
					$res = $this->update_stream($stream_id, $data);
					break;
				case 'close_stream':
					$res = $this->close_stream($stream_id, $data);
					break;
				case 'open_stream':
					$res = $this->open_stream($stream_id, $data);
					break;
				case 'delete':
					$res = $this->delete_stream($stream_id);
					break;
				default:
					throw new Exception("Unexcpected action '$action'");
					break;
			}

			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.1 200 OK');
			echo json_encode($res);
			exit;


		} catch (Exception $e) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		  echo 'Message: ' . $e->getMessage();
		}
  }

	// Return the url of Dilmot API for configured account
  private function init_api_url($action, $stream_id) {
		$this->account = $this->aPlugin->getOption('DilmotAccount');
	  if ($this->account == '') {
	  	throw new Exception('Missing account name');
	  }
		
		$accout_url = $this->aPlugin->getAccountUrl($this->account);
		$api_url = "$accout_url/api/streams/$stream_id";

		$this->api_url = $api_url;
  }

	// Search for a post of the given stream. Of not exist, return null
  private function find_post($stream_id) {
		if ( !ctype_digit((string)$stream_id)) {
			throw new Exception('stream-id should be an integer');
		}

		// search for post with same stream id
		$args = array(
			'meta_key' => 'stream_id',
			'meta_value' => $stream_id	
		);

		$the_query = new WP_Query( $args );
		$stream_post_id = null;
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				if ($stream_post_id) {
					throw new Exception("Found more than one post with stream_id $streamId");
				}
				$the_query->the_post();
				$stream_post_id = get_the_ID();
			}
		}

		return $stream_post_id;
  }

  //////////////////////////
  // API functions
  ////////////////////////
  private function update_stream($stream_id, $data, $is_new = false) {
  	if ($is_new && $this->post_id) {
  		throw new Exception("Post of stream [$stream_id] with ID {$this->post_id} already exist");
  	}

		$streamInfo = $data["stream_info"];

		$category = $this->aPlugin->getOption('StreamsCategory');
		$post = array(
			'post_content'   => "[dilmot-stream account=\"{$this->account}\" id=\"$stream_id\"]",
			'post_name'      => $streamInfo['title'],
			'post_title'     => $streamInfo['title'],
			'post_status'    => 'publish',
			'post_category'  => array($category),
		);

		if ($streamInfo['private_stream'] && !empty($streamInfo['private_stream_pass'])) {
			$post['post_password'] = $streamInfo['private_stream_pass'];
		} else {
			$post['post_password'] = null;
		}

		if ($this->post_id) {
			$post['ID'] = $this->post_id;
			$post_id = wp_update_post( $post );	

		} else {
			$post_id = wp_insert_post( $post );	
			if (!$post_id) {
				throw new Exception('Failed to create new post');
			}
		}

		// create custom fields array
		$custom_fields = array('stream_id' => $stream_id);
		$keysMap = array(
			'stream_status' => 'status',
			'stream_description' => 'description',
			'stream_slug' => 'slug',
			'stream_short_url' => 'short_url',
    	'pusher_key' => 'pusher_key',
    	'channel_name' => 'channel_name',
    	'image' => 'image',
    	'image_thumb' => 'image_thumb',
    	'starts_at' => 'starts_at',
    	'private_stream' => 'private_stream',
    	'private_stream_pass' => 'private_stream_pass',
    	'custom_stream_header' => 'custom_stream_header',
		);

		foreach ($keysMap as $custom_field_name => $stream_info_key) {
			if (array_key_exists($stream_info_key,$streamInfo)) {
				$custom_fields[$custom_field_name] = $streamInfo[$stream_info_key];
			}
		}

		// set custom fields
		foreach ($custom_fields as $meta_key => $meta_value) {
			$meta_id = update_post_meta($post_id, $meta_key, $meta_value);
			if ($meta_id === false) {
				// TBD - error on creating this custom field
			}
		}

		// get the stream info box html
		if (array_key_exists("stream_info_box_html", $data)) {
			$meta_id = update_post_meta($post_id, 'stream_info_box_html', $data["stream_info_box_html"]);
		}

		$post_url = get_permalink($post_id);
		return array(
			'post_id' => $post_id,
			'post_url' => $post_url
		);
	}

	private function close_stream($stream_id, $data) {
		$streamHtml = $data["stream_html"];
		update_post_meta($this->post_id, 'stream_status', "closed");
		update_post_meta($this->post_id, 'stream_info_box_html', $data["stream_info_box_html"]);
		update_post_meta($this->post_id, 'stream_html', $streamHtml);

		return $this->post_id;
	}

	private function open_stream($stream_id, $data) {
		$this->update_stream($stream_id, $data);
		$meta_id = update_post_meta($this->post_id, 'stream_html', '');
		return $this->post_id;
	}

	private function delete_stream($stream_id) {
		$post = array(
			'ID'          => $this->post_id,
			'post_status' => 'trash',
		);
		wp_update_post( $post );	

		return $this->post_id;
	}

}

// this function is available only for (PHP 5 >= 5.5.0)
if (!function_exists('json_last_error_msg')) {
	function json_last_error_msg() {
		static $errors = array(
			JSON_ERROR_NONE             => null,
			JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
			JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
			JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
			JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
		);
		if (function_exists('json_last_error')) {
			$error = json_last_error();
		} else {
			$error = -1;
		}
		return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
	}
}
