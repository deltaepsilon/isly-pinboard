<?php
/*
Plugin Name: ISLY Pinboard
Plugin URI: http://christopheresplin.com/isly-pinboard
Description: Take a pinboard from your Pinterest account and embed an image gallery anywhere that will accept a widget
Version: 0.1
Author: Christopher Esplin
Author URI: http://christopheresplin.com
License: GPL2
*/

/*  Copyright 2012  Christopher Esplin  (email : christopher.esplin@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class IslyPinboard extends WP_Widget
{
	public function __construct() {
		parent::__construct(
			'isly_pinboard',
			'IslyPinboard',
			array(
				'description' => __('Display a Pinterest pinboard as an image gallery', 'text_domain'),
			)
		);
	}

	public function widget($args, $instance) {
		extract($args);
		$title = apply_filters( 'widget_title', $instance['title'] );
		$username = $instance['username'];
		$pinboard = $instance['pinboard'];

		echo $before_widget;
		if (!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		if (!empty($username) && !empty($pinboard)) {
			$parsedPinboard = $this->getParsedPinboard($instance);
?>
        	<script type='text/javascript' src='<?php echo plugins_url('isly-pinboard/scripts/isly-pinboard.js'); ?>'></script>
        	<link rel='stylesheet' href='<?php echo plugins_url('isly-pinboard/styles/isly-pinboard.css'); ?>' type='text/css'/>
			<script>
				jQuery(document).ready(function() {
					return new window.ISLY.IslyPinboard({
						pinboard: <?php echo json_encode($parsedPinboard); ?>
					});
				});
			</script>
			<div class="isly-pinboard">
				<img class="isly-pinboard-placeholder" src="<?php echo plugins_url('isly-pinboard/images/placeholder.png'); ?>" />
			</div>
<?php
		} else {
			echo <<<ISLY
Pinterest Username or Pinboard values are missing!
ISLY;

		}
		echo $after_widget;
	}

	private function getParsedPinboard($instance) {
		if (isset($instance['cache']) && isset($instance['cacheDate']) && $instance['cacheDate']->getTimestamp() > time() ) {
			return $instance['cache'];
		} else { // Time to get our parse on
			$username = $instance['username'];
			$pinboard = $instance['pinboard'];
			$instance = $this->update($instance, null);
			$regex = '/href="\/pin\/(\d+)\/"[\w\s\d=">]+<img src="(http.+\.jpg)" alt="([^"]+)"/';

			$cache = array();
			$result = array();

			$htmlDirty = @file_get_contents("http://pinterest.com/$username/$pinboard/");
			preg_match_all($regex, $htmlDirty, $htmlMatches);
			foreach ($htmlMatches[0] as $key => $match) {
				$result[] = array(
//					'html' => $match,
					'pinID' => $htmlMatches[1][$key],
					'image' => $htmlMatches[2][$key],
					'description' => $htmlMatches[3][$key]
				);
			}

			$cache['pinboard'] = $result;

			$instance['cache'] = $cache;
			$this->save_settings(array(2 => $instance));

			$cache['cacheDate'] = $instance['cacheDate']; // Make sure the object passed out to the JS has the right cacheDate
			return $cache;
		}


	}



	public function update($newInstance, $oldInstance) {
		$instance = array();
		$instance['title'] = strip_tags($newInstance['title']);
		$instance['username'] = strip_tags($newInstance['username']);
		$instance['pinboard'] = strip_tags($newInstance['pinboard']);
		$instance['cacheHours'] = max(1, intval($newInstance['cacheHours']));
		$instance['cacheDate'] = new DateTime('+'.$instance['cacheHours'].' days');
		unset($instance['cache']);
		return $instance;
	}

	public function form($instance) {
		if (isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __('New title', 'text_domain');
		}

		$username = '';
		if (isset($instance['username'])) {
			$username = $instance['username'];
		}

		$pinboard = '';
		if (isset($instance['pinboard'])) {
			$pinboard= $instance['pinboard'];
		}

		$cacheHours = 72;
		if (isset($instance['cacheHours'])) {
			$cacheDays = $instance['cacheHours'];
		}

		$cacheBust = false;
		if (isset($instance['cacheBust'])) {
			$cacheBust = $instance['cacheBust'];
		}

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:'); ?>
				</label>
				<input
						class="widefat"
						id="<?php echo $this->get_field_id('title'); ?>"
						name="<?php echo $this->get_field_name('title') ?>"
						type="text"
						value="<?php echo esc_attr($title) ?>"
				/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('username'); ?>">
					<?php _e('Pinterest Username:'); ?>
				</label>
				<input
						class="widefat"
						id="<?php echo $this->get_field_id('username'); ?>"
						name="<?php echo $this->get_field_name('username') ?>"
						type="text"
						value="<?php echo esc_attr($username) ?>"
						/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('pinboard'); ?>">
					<?php _e('Pinterest Pinboard:'); ?>
				</label>
				<input
						class="widefat"
						id="<?php echo $this->get_field_id('pinboard'); ?>"
						name="<?php echo $this->get_field_name('pinboard') ?>"
						type="text"
						value="<?php echo esc_attr($pinboard) ?>"
						/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('cacheDays'); ?>">
					<?php _e('For how many hours would you like to cache your pinboard? (minimum 1)'); ?>
				</label>
				<input
						class="widefat"
						id="<?php echo $this->get_field_id('cacheHours'); ?>"
						name="<?php echo $this->get_field_name('cacheHours') ?>"
						type="number"
						step="1"
						value="<?php echo esc_attr($cacheDays) ?>"
						/>
			</p>

		<p>
			<label for="<?php echo $this->get_field_id('cacheBust'); ?>">
				<?php _e('Clear pinboard cache?'); ?>
			</label>
			<input
					class="widefat"
					id="<?php echo $this->get_field_id('cacheBust'); ?>"
					name="<?php echo $this->get_field_name('cacheBust') ?>"
					type="checkbox"
					/>
		</p>
		<?php

		if (!empty($username) && !empty($pinboard)) {
			$link = "http://pinterest.com/$username/$pinboard/";
			echo "<a href='$link' target='_blank'>View Pinboard ($link)</a>";
		}
	}
}
add_action( 'widgets_init', create_function('', 'return register_widget("IslyPinboard");') );
?>