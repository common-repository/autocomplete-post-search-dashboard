<?php
/*
Plugin Name: Jump to Post/Page
Plugin URI: http://hussainarsh.blogspot.in
Description: This plugin puts an autocomplete search box on a post/page/custom post type editor in the admin area and allows user to jump to any post or page
Version: 1.0.1
Author: Arshad Hussain
Author URI: http://hussainarsh.blogspot.in
License: GPL2
*/


class APSDSearchBox {
	
	public function __construct() {

		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			add_action( 'do_meta_boxes', array( &$this, 'addSearchBox' ), 10, 2 );
		}	
		
	}
	
	public function getPostsResult() {

		global $wpdb;
				
		$posts = array();
		
		$query  = "SELECT ID, post_title FROM {$wpdb->posts} ";
		if ( $this->post_type && isset( $_GET['post'] ) && isset( $_GET['action'] ) ) {
			$query .= "WHERE post_type = %s ";
		} else {
			$query .= "WHERE post_type != 'revision' ";
		}
		
		$post_results = $wpdb->get_results( $wpdb->prepare( $query, $this->post_type ) );
		if ( $post_results ) {
			foreach ( $post_results as $post_result ) {
				if ( $post_result->post_title ) {
					$posts[] = array( 'label' => 'ID = ' . $post_result->ID  . ' - ' . $post_result->post_title, 'value' => admin_url( 'post.php?post=' . $post_result->ID . '&action=edit' ) );
				}
			}	
		} else {
			$posts[] = array( 'label' => 'Nothing Found', 'value' => '' );
		}
		return json_encode( $posts );
		
	}
	
	public function showSearchBox() {
		
		global $post;
		$this->post_type = $post->post_type;
		$this->posts = $this->getPostsResult();
		?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			
			var currentPosts = <?php echo $this->posts; ?>;
			jQuery('#posts_search').autocomplete({
				source: currentPosts,
		        select: function( event, ui ) { 
		            window.location.href = ui.item.value;
		        }		
			});	
					
		});	
		</script>	
		<input type="text" name="posts_search" id="posts_search" style="width: 100%;">
		<?php
	
	}

	public function addSearchBox( $page, $context ) {
		
		add_meta_box( 'map-posts-search', 'Search', array( &$this, 'showSearchBox' ), $page, 'side', 'high' );
	
	}		
	
}

function do_searching() {
	$do_searching = new APSDSearchBox();
}

do_searching();
?>
