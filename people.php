<?php


define( 'NATIONBUILDER_SLUG', 'atac' ); 
define( 'NATIONBUILDER_API_TOKEN', 'api token' ); 

class PEOPLE
{
	var $error;
	var $slug;
	var $api_token;
	var $status;
	var $person_id;
	
	function __construct( )
	{
		$this->slug			= ( defined( 'NATIONBUILDER_SLUG' ) ) ? NATIONBUILDER_SLUG : false;
		$this->api_token	= ( defined( 'NATIONBUILDER_API_TOKEN' ) ) ? NATIONBUILDER_API_TOKEN : false;;	
	}
	
	private function is_valid_nationbuilder( )
	{
		if ( !$this->slug || !$this->api_token )
		{
			$this->error = 'The NationBuilder Slug or API Token is not configured.';
			return false;
		}
		return true;
	}
	
	private function curl( $url, $data = array( ), $crud = 'POST' )
	{
		$header[] = 'Content-Type: application/json';
		$ch = curl_init( );
		if ( !empty( $data ) )
		{
			$data_json = json_encode( $data );
			$header[] = 'Content-Length: ' . strlen( $data_json );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_json );
		}
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $crud );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Atac - NationBuilder' );
		$response  = curl_exec( $ch );
		$this->status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $response === false )
		{
			$this->error = 'Curl error: ' . curl_error( $ch );
			return false;
		}
		return json_decode( $response );
	}
	
	public function create( $person = array( ) )
	{
		if ( !$this->is_valid_nationbuilder( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/push/?access_token=' . $this->api_token;
		if ( empty( $person ) )
			return false;
		$create = $this->curl( $endpoint, $person, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->person_id = $create->person->id;
			return true;
		}
		return false;
	}
	
	public function update( $id, $person = array( ) )
	{
		if ( !$this->is_valid_nationbuilder( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/' . $id . '/?access_token=' . $this->api_token;
		if ( empty( $person ) )
			return false;
		$update = $this->curl( $endpoint, $person, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->person_id = $update->person->id;
			return true;
		}
		return false;
	}
	
	public function delete( $id )
	{
		if ( !$this->is_valid_nationbuilder( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/' . $id . '/?access_token=' . $this->api_token;
		$data		= array( );
		$delete = $this->curl( $endpoint, $data, 'DELETE' );
		if ( 204 == $this->status )
			return true;
		return false;
	}
	
}
$mode_type		= 'create_person';
$mode_header	= 'Create';
if ( $_POST ):
	$mode	= $_POST['mode'];
	$people = new PEOPLE( );
	switch ( $mode ):
		case 'create_person':
			$first_name	= $_POST['first_name'];
			$last_name	= $_POST['last_name'];
			if ( $first_name && $last_name ):
				$person = array(
					'person' => array(
						'first_name'	=> trim( $first_name ),
						'last_name'		=> trim( $last_name ),
						'full_name'		=> trim( $first_name . ' ' . $last_name ),
					)
				);
				$new_person = $people->create( $person );
				if ( $new_person ):
					$id				= $people->person_id;
					$mode_type		= 'update_person';
					$mode_header	= 'Update';
				endif;
			else:
			
			endif;
			break;
		case 'update_person':
			$mode_type		= 'update_person';
			$mode_header	= 'Update';
			$id				= $_POST['id'];
			$middle_name	= $_POST['middle_name'];
			if ( $id && $middle_name ):
				$updated_person = array(
					'person' => array(
						'middle_name'	=> trim( $middle_name ),
					)
				);
				$update_person = $people->update( $id, $updated_person );
				if ( $update_person ):
					$mode_type		= 'delete_person';
					$mode_header	= 'Delete';
				endif;
			else:
			
			endif;
			break;
		case 'delete_person':
			$mode_type		= 'delete_person';
			$mode_header	= 'Delete';
			$id				= $_POST['id'];
			if ( $id  ):
				$delete_person = $people->delete( $id );
				if ( $delete_person ):
					$complete 		= true;
					$mode_type		= NULL;
					$mode_header	= NULL;
				endif;
			else:
			
			endif;
			break;
	
	endswitch;
endif;
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>NationBuilder | People</title>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundation/6.2.3/foundation.min.css" />
<style type="text/css">
* { margin: 0; }
html, body { height: 100%; }
.page-wrap { min-height: 100%; /* equal to footer height */ margin-bottom: -50px; }
.page-wrap:after { content: ""; display: block; }
footer, .page-wrap:after { height: 50px; }
footer { background: #f07622; padding-top:10px; color:#fff; }
	footer a { color:#fff; }
.top-bar { background: #0a3763; color:#fff; }
</style>
</head>
<body>
<div class="page-wrap">
    <div class="top-bar">
        <div class="top-bar-left">
            <div>NationBuilder | People</div>
        </div>
    </div>
    <br />
    <?php if ( $complete ): ?>
    <div class="row">
        <div class="medium-12 columns">
            <div class="callout success">
                <h5>! Completed</h5>
                <p>You have created, updated and delete a person in your NationBuilder Site.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="medium-12 columns">
            <h1><?php echo $mode_header; ?> a Person</h1>
            <p class="subheader">This is a sample form to demonstrate how to create a new person in your NationBuilder site.  Once created, you can then update that person's information and then delete that person.</p>
        </div>
    </div>
    
    <br />
    <form action="" method="post">
    <input type="hidden" name="mode" value="<?php echo $mode_type; ?>" />
    
    	<?php
        switch ( $mode_type ):
        	case 'create_person':
		?>
        <div class="row">
            <div class="medium-6 columns">
                <label>
                    <input type="text" name="first_name" value="<?php echo $first_name; ?>" placeholder="First Name" maxlength="64" />
                </label>
            </div>
            <div class="medium-6 columns">
                <label>
                    <input type="text" name="last_name" value="<?php echo $last_name; ?>" placeholder="Last Name" maxlength="64" />
                </label>
            </div>
        </div>
        <div class="row">
            <div class="medium-6 columns">
                <button type="submit" class="button"><?php echo $mode_header; ?></button>
            </div>
        </div>
        <?php
       		break;
		case 'update_person':
		?>
        <div class="row">
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <div class="medium-12 columns">
                <label>
                    <input type="text" name="middle_name" value="<?php echo $middle_name; ?>" placeholder="Middle Name" maxlength="64" />
                </label>
            </div>
        </div>
        <div class="row">
            <div class="medium-6 columns">
                <button type="submit" class="button"><?php echo $mode_header; ?></button>
            </div>
        </div>
        <?php
       		break;
		case 'delete_person':
		?>
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <div class="row">
            <div class="medium-6 columns">
                <button type="submit" class="alert button"><?php echo $mode_header; ?></button>
            </div>
        </div>
        <?php
        	break;
		endswitch;
		?>
    
    </form>
    <?php endif; ?>
</div>
<footer>
    <div class="row">
        <div class="medium-6 columns">
            <div class="menu">
            </div>
        </div>
        <div class="medium-6 columns">
            <div class="float-right">
                <div><span>Built by <a href="http://www.buildyoursite.com">BuildYourSite.com</a></span>  |  <span>Powered by <a href="http://nationbuilder.com">NationBuilder</a></span></div>
            </div>
        </div>
    </div>
</footer>
<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/foundation/6.2.3/foundation.min.js"></script>
<script>
	$( document ).foundation( );
</script>
</body>
</html>
