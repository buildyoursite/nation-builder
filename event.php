<?php


define( 'NATIONBUILDER_SLUG', 'atac' ); 
define( 'NATIONBUILDER_API_TOKEN', 'api token' ); 

class EVENT
{
	var $error;
	var $slug;
	var $api_token;
	var $status;
	var $event_id;
	
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
	
	public function create( $event = array( ) )
	{
		if ( !$this->is_valid_nationbuilder( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/sites/' . $this->slug . '/pages/events/?access_token=' . $this->api_token;
		if ( empty( $event ) )
			return false;
		$create = $this->curl( $endpoint, $event, 'POST' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->event_id = $create->event->id;
			return true;
		}
		return false;
	}
	
	public function update( $id, $event = array( ) )
	{
		if ( !$this->is_valid_nationbuilder( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/sites/' . $this->slug . '/pages/events/' . $id . '/?access_token=' . $this->api_token;
		if ( empty( $event ) )
			return false;
		$update = $this->curl( $endpoint, $event, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->event_id = $update->event->id;
			return true;
		}
		return false;
	}
	
}
$mode_type		= 'create_event';
$mode_header	= 'Create';
if ( $_POST ):
	$mode	= $_POST['mode'];
	$event = new EVENT( );
	switch ( $mode ):
		case 'create_event':
			$event_name	= $_POST['event_name'];
			if ( $event_name ):
				$start_time	= date( 'c', strtotime( date( 'Y-m-d H:i:s' ) ) );
				$end_time	= date( 'c', strtotime( date( 'Y-m-d H:i:s' ) . ' +1 day' ) );
				$details = array(
					'event' => array(
						'status'		=> 'unlisted',
						'name'			=> $event_name,
						'start_time'	=> $start_time,
						'end_time'		=> $end_time,
					)
				);
				$new_event = $event->create( $details );
				if ( $new_event ):
					$id				= $event->event_id;
					$mode_type		= 'update_event';
					$mode_header	= 'Update';
				endif;
			else:
			
			endif;
			break;
		case 'update_event':
			$mode_type		= 'update_event';
			$mode_header	= 'Update';
			$id				= $_POST['id'];
			$event_name		= $_POST['event_name'];
			$start_time		= $_POST['start_time'];
			$end_time		= $_POST['end_time'];
			$headline		= $_POST['headline'];
			$intro			= $_POST['intro'];
			$status			= $_POST['status'];
			if ( $id && $headline && $intro ):
				$details = array(
					'event' => array(
						'status'		=> $status,
						'name'			=> $event_name,
						'start_time'	=> $start_time,
						'end_time'		=> $end_time,
						'headline'		=> $headline,
						'intro'			=> $intro,
					)
				);
				$update_event = $event->update( $id, $details );
				if ( $update_event ):
					$complete 		= true;
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
<title>NationBuilder | Event</title>
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
            <div>NationBuilder | Event</div>
        </div>
    </div>
    <br />
    <?php if ( $complete ): ?>
    <div class="row">
        <div class="medium-12 columns">
            <div class="callout success">
                <h5>! Completed</h5>
                <p>Your Event have been created and updated on your NationBuilder Account.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="medium-12 columns">
            <h1><?php echo $mode_header; ?> an Event</h1>
            <p class="subheader">This is a sample form to showcase how to create and update an Event to NationBuilder.</p>
        </div>
    </div>
    
    <br />
    <form action="" method="post">
    <input type="hidden" name="mode" value="<?php echo $mode_type; ?>" />
    
    	<?php
        switch ( $mode_type ):
        	case 'create_event':
		?>
        <div class="row">
            <div class="medium-12 columns">
                <label>Event Name *
                    <input type="text" name="event_name" value="<?php echo $event_name; ?>" placeholder="Enter the name of your event" maxlength="64" />
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
		case 'update_event':
		?>
        <div class="row">
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="hidden" name="event_name" value="<?php echo $event_name; ?>" />
            <input type="hidden" name="start_time" value="<?php echo $start_time; ?>" />
            <input type="hidden" name="end_time" value="<?php echo $end_time; ?>" />
            <div class="medium-12 columns">
                <label>Headline *
                    <input type="text" name="headline" value="<?php echo $headline; ?>" placeholder="Enter a Headline for your event" maxlength="64" />
                </label>
            </div>
            <div class="medium-12 columns">
                <label>Intro *
                    <input type="text" name="intro" value="<?php echo $intro; ?>" placeholder="Enter an Intro for your event" maxlength="64" />
                </label>
            </div>
            <div class="medium-12 columns">
                <label>Status *
                    <select name="status">
                    	<option value="unlisted" <?php if ( 'unlisted' == $status ): ?>selected<?php endif; ?>>Unlisted</option>
                        <option value="published" <?php if ( 'published' == $status ): ?>selected<?php endif; ?>>Published</option>
                    </select>
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
		endswitch;
		?>
    </form>
</div>
<footer>
    <div class="row">
        <div class="medium-6 columns">
            <div class="menu">
            </div>
        </div>
        <div class="medium-6 columns">
            <div class="float-right">
                <div><span>Built by <a href="http://www.BuildYourSite.com">BuildYourSite.com</a></span>  |  <span>Powered by <a href="http://nationbuilder.com">NationBuilder</a></span></div>
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