<?php
/**
 * Version: 0.0.1
 * Author: Konnektiv
 * Author URI: http://konnektiv.de/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class BadgeOS_Activity_Progress_Shortcode {

	/**
	 * @var BadgeOS_Activity_Progress_Shortcode
	 */
	private static $instance;

	/**
	 * Main BadgeOS_Activity_Progress_Shortcode Instance
	 *
	 * Insures that only one instance of BadgeOS_Activity_Progress_Shortcode exists in memory at
	 * any one time. Also prevents needing to define globals all over the place.
	 *
	 * @since BadgeOS_Activity_Progress_Shortcode (0.0.1)
	 *
	 * @staticvar array $instance
	 *
	 * @return BadgeOS_Activity_Progress_Shortcode
	 */
	public static function instance( ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BadgeOS_Activity_Progress_Shortcode;
			self::$instance->setup_shortcodes();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent loading more than one instance
	 *
	 * @since BadgeOS_Activity_Progress_Shortcode (0.0.1)
	 */
	private function __construct() { /* Do nothing here */
	}


	/**
	 * Setup the short codes to be used in templates
	 *
	 * @since BadgeOS_Activity_Progress_Shortcode (0.0.1)
	 * @access private
	 *
	 * @uses add_shortcode() to add various shortcodes
	 */
	private function setup_shortcodes() {
		add_shortcode( 'badgeos_activity_progress', array( $this, 'shortcode' ) );
	}

	function get_level_info($user_points, $achievement_type) {
		$current_achievement = false;
		$next_points = 0;
		$current_points = 0;

		$achievements = get_posts(array(
			'post_type' 		=> $achievement_type,
			'posts_per_page'   	=> -1,
			'meta_query'		=> array(
				array(
					'key'	=> '_badgeos_earned_by',
					'value' =>  'points'
				)
			)
		));

		foreach($achievements as $achievement){

			$points_required = absint( get_post_meta( $achievement->ID, '_badgeos_points_required', true ) );

			if ( $points_required > $user_points && (!$next_points || $next_points > $points_required ) ) {
				$next_points = $points_required;
			} elseif ($points_required < $user_points && (!$current_points || $current_points < $points_required ) ) {
				$current_points = $points_required;
				$current_achievement = $achievement->ID;
			}

		}

		if (!$next_points && !empty($achievements))
			$next_points = $user_points;
		return array('next_points' => $next_points, 'current_achievement' => $current_achievement);
	}

    public function shortcode($atts) {

        $atts = shortcode_atts( array(
            'achievement_type'	=> badgeos_get_achievement_types_slugs(),    // achievement type to show progress for
			'format'			=> 'simple',	// output format, possible values: 'simple', 'extended'
			'user'				=> 0
        ), $atts );

		$points = absint(badgeos_get_users_points($atts['user']));
		$level = $this->get_level_info($points, $atts['achievement_type']);

		// no progress bar
		if (!$level['next_points'])
			return '';

        $progress = ($points/$level['next_points'] * 100) . "%";

		$output = $this->wppb_get_progress_bar(false, false, $progress, false, $progress, true,
											   sprintf(__("%d/%d Points", 'badgeos-activity-progress'), $points, $level['next_points'] ));

		if ($atts['format'] == 'extended') {
			$progress = $output;
			$output = __('Current activity level:', 'badgeos-activity-progress');
			if ($level['current_achievement']) {
				$output .= '<div class="badgeos-badge-wrap">';
				$output .= badgeos_get_achievement_post_thumbnail( $level['current_achievement'] );
				$output .= '<span class="badgeos-title-wrap"><a href="' . get_permalink( $level['current_achievement'] ) . '">' .
					get_the_title( $level['current_achievement'] ) . '</a></span>';
				$output .= '</div>';
			} else {
				$output .= ' ' . __('No activity level reached.', 'badgeos-activity-progress') . '<br>';
			}
			$output .= '<p>' . sprintf(__("Activity points needed for next level: %d", 'badgeos-activity-progress'), $level['next_points'] ) . '</p>';
			$output .= $progress;
		}

		return $output;
    }

	/**
	 * WPPB Get Progress Bar
	 * gets all the parameters passed to the shortcode and constructs the progress bar
	 * @param $location - inside, outside, null (default: null)
	 * @param $fullwidth - any value (default: null)
	 * @param $text - any custom text (default: null)
	 * @param $progress - the progress to display (required)
	 * @param $option - any applicable options (default: null)
	 * @param $title - custom title text (default: null)
	 * @param $width - the width of the progress bar, based on $progress (required)
	 * @param $color - custom color for the progress bar (default: null)
	 * @param $gradient - custom gradient value, in decimals (default: null)
	 * @param $gradient_end gradient end color, based on the endcolor parameter or $gradient (default: null)
	 * @author Chris Reynolds
	 * @since 2.0
	 */
	function wppb_get_progress_bar($location = false, $text = false, $progress, $option = false, $width, $fullwidth = false, $title = null, $color = false, $gradient = false, $gradient_end = false) {
		/**
		 * here's the html output of the progress bar
		 */
		$wppb_output	= "<div class=\"wppb-wrapper $location"; // adding $location to the wrapper class, so I can set a width for the wrapper based on whether it's using div.wppb-wrapper.after or div.wppb-wrapper.inside or just div.wppb-wrapper
		if ( $fullwidth ) {
			$wppb_output .= " full";
		}
		$wppb_output .= "\"";
		if ( $title ) {
			$wppb_output .= 'title="'.$title.'"';
		}
		$wppb_output .= ">";
		if ( $location && $text) { // if $location is not empty and there's custom text, add this
			$wppb_output .= "<div class=\"$location\">" . wp_kses($text, array()) . "</div>";
		} elseif ( $location && !$text ) { // if the $location is set but there's no custom text
			$wppb_output .= "<div class=\"$location\">";
			$wppb_output .= $progress;
			$wppb_output .= "</div>";
		} elseif ( !$location && $text) { // if the location is not set, but there is custom text
			$wppb_output .= "<div class=\"inside\">" . wp_kses($text, array()) . "</div>";
		}
		$wppb_output 	.= 	"<div class=\"wppb-progress";
		if ($fullwidth) {
			$wppb_output .= " full";
		} else {
			$wppb_output .= " fixed";
		}
		$wppb_output 	.= "\">";
		$wppb_output	.= "<span";
		if ($option) {
			$wppb_output .= " class=\"{$option}\"";
		}
		if ($color) { // if color is set
			$wppb_output .= " style=\"width: $width; background: {$color};";
			if ($gradient_end) {
				$wppb_output .= "background: -moz-linear-gradient(top, {$color} 0%, $gradient_end 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,{$color}), color-stop(100%,$gradient_end)); background: -webkit-linear-gradient(top, {$color} 0%,$gradient_end 100%); background: -o-linear-gradient(top, {$color} 0%,$gradient_end 100%); background: -ms-linear-gradient(top, {$gradient} 0%,$gradient_end 100%); background: linear-gradient(top, {$color} 0%,$gradient_end 100%); \"";
			}
		} else {
			$wppb_output .= " style=\"width: $width;";
		}
		if ($gradient && $color) {
			$wppb_output .= "background: -moz-linear-gradient(top, {$color} 0%, $gradient_end 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,{$color}), color-stop(100%,$gradient_end)); background: -webkit-linear-gradient(top, {$color} 0%,$gradient_end 100%); background: -o-linear-gradient(top, {$color} 0%,$gradient_end 100%); background: -ms-linear-gradient(top, {$gradient} 0%,$gradient_end 100%); background: linear-gradient(top, {$color} 0%,$gradient_end 100%); \"";
		} else {
			$wppb_output .= "\"";
		}
		$wppb_output	.= "><span></span></span>";
		$wppb_output	.=	"</div>";
		$wppb_output	.= "</div>";
		/**
		 * now return the progress bar
		 */
		return $wppb_output;
	}
}

BadgeOS_Activity_Progress_Shortcode::instance();