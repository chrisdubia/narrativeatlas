<?php
/**
 * Class of bbPress Actions Handler
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

use WP_Post;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * bbPress Actions Handler
 */
class BBP_Actions_Handler {
	/**
	 * Holds attached video ids of topic or reply.
	 *
	 * @var array
	 */
	private $video_ids = array();

	/**
	 * Boots class.
	 */
	public static function boot() {
		$self = new self();

		$self->setup();
	}

	/**
	 * Attaches callbacks to bbPress hooks.
	 */
	private function setup() {
		//add_action( 'bbp_delete_topic', array( $this, 'on_topic_delete' ) );
		//add_action( 'bbp_delete_reply', array( $this, 'on_reply_delete' ) );

		// Because BuddyBoss using wp_delete_post instead of bbp_delete_topic or bbp_delete_reply
		add_action( 'before_delete_post', array( $this, 'pre_post_deletion' ), 99, 2 );
		add_action( "deleted_post", array( $this, 'on_post_deletion' ), 99, 2 );
	}

	/**
	 * Before post deletion cached attached video ids to class properties.
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 */
	public function pre_post_deletion( $post_id, $post ) {

		if ( ! $this->is_valid_post_type( $post->post_type ) ) {
			return;
		}
		// Avoid multiple call.
		remove_action( 'before_delete_post', array( $this, 'pre_post_deletion' ), 99 );

		$video_ids = get_post_meta( $post_id, 'bp_video_ids', true );

		$this->video_ids = empty( $video_ids ) ? array() : explode( ',', $video_ids );
	}

	/**
	 * Before delete post cached attached video ids to class properties.
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 */
	public function on_post_deletion( $post_id, $post ) {

		if ( empty( $this->video_ids ) || ! $this->is_valid_post_type( $post->post_type ) ) {
			return;
		}

		if ( ! function_exists( 'bp_video_delete' ) ) {
			return;
		}

		// Avoid multiple call.
		remove_action( 'deleted_post', array( $this, 'on_post_deletion' ), 99 );

		foreach ( $this->video_ids as $video_id ) {
			bp_video_delete( array( 'id' => $video_id ) );
		}
	}

	/**
	 * Checks if valid post type or not.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return bool
	 */
	private function is_valid_post_type( string $post_type ): bool {

		if ( ! function_exists( 'bbp_get_reply_post_type' ) ) {
			return false;
		}

		return in_array(
			$post_type,
			array(
				bbp_get_reply_post_type(),
				bbp_get_topic_post_type(),
			),
			true
		);
	}
}
