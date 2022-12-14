<?php
/*
Plugin Name: Test Likes
Author: Alex Shack
Author URI: https://alexshack.ru
*/
?>
<?php




if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Likes_Table_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'like',
            'plural' => 'likes',
        ));
    }

    function column_default($item, $column_name) {
        return $item[$column_name];
    }

    function column_post_id($item) {
        return '<a href="' . get_permalink($item['post_id']) . '" target="_blank">' . get_the_title($item['post_id']) . '</a>';
    }

    function column_like_time($item) {
        return mysql2date('d.m.Y H:i', $item['like_time'], true);
    }
    function column_like($item) {
    	if( $item['like_plus'] > 0 ) {
    		$icon = 'plus';
    		$color = '#008000';
    	} else {
    		$icon = 'minus';
    		$color = '#800000';
    	}
        return sprintf('<svg width="22" height="22" style="color: ' . $color . '"><use xlink:href="' . plugin_dir_url( __FILE__ ) . '/assets/img/icons.svg#%s"></use></svg>', $icon);
    }        

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }      

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'post_id' => 'Запись', 
            'client_ip' => 'IP',            
            'like_time' => 'Дата',
            'like' => 'Действие',
        );
        return $columns;
    }

	function get_sortable_columns() {
        $sortable_columns = array(
            'client_ip' => array('client_ip', false),
            'post_id' => array('post_id', false),
            'like_time' => array('like_time', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Удалить'
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM wp_test_likes WHERE id IN($ids)");
            }
        }
    }

    function prepare_items() {
        global $wpdb;
 
        $per_page = 40; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM wp_test_likes");

        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_test_likes ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page, 
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
} 

class Post_Likes_Table_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'post_like',
            'plural' => 'post_likes',
        ));
    }

    function column_default($item, $column_name) {
        return $item[$column_name];
    }


    function column_post_name($item) {
        return '<a href="' . get_permalink($item['post_id']) . '" target="_blank">' . $item['post_name'] . '</a>';
    }

    function column_rating($item) {
    	$color="inherit";
    	if($item['rating'] != 0) {
	    	if($item['rating'] > 0) {
	    		$color = '#008000';
	    	} else {
	    		$color = '#800000';
	    	}
	    }
        return '<strong style="color:' . $color . ';">' . $item['rating'] . '</strong>';
    }
  

    function get_columns() {
        $columns = array(
            'post_name' => 'Запись', 
            'like_plus' => 'Лайки',            
            'like_minus' => 'Дизлайки',
            'rating' => 'Рейтинг',
        );
        return $columns;
    }

	function get_sortable_columns() {
        $sortable_columns = array(
            'post_name' => array('post_name', false),
            'like_plus' => array('like_plus', false),
            'like_minus' => array('like_minus', true),
            'rating' => array('likes', false),
        );
        return $sortable_columns;
    }


    function prepare_items() {
        global $wpdb;
 
        $per_page = -1; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = '';//$this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);


        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'rating';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        $items = array();				
        $posts = get_posts(array(
			'post_type'			=> 'post',
			'post_status'    => 'publish',
			'posts_per_page'	=> -1,
			'orderby'			=> 'ID',
			'order'				=> 'DESC',
			'suppress_filters' => true,
		));
		foreach ($posts as $post) {
			setup_postdata( $post );
			$likes = $wpdb->get_results(sprintf("SELECT * FROM wp_test_likes WHERE post_id=%u AND like_plus > 0", $post->ID) );
			$dislikes = $wpdb->get_results(sprintf("SELECT * FROM wp_test_likes WHERE post_id=%u AND like_minus > 0", $post->ID) );
			$rating = count($likes)	- count($dislikes);
       		array_push($items, array(
        			'post_id' => $post->ID,
        			'post_name' => get_the_title($post->ID),
        			'like_plus' => (string)count($likes),
        			'like_minus' => (string)count($dislikes),
        			'rating' => $rating
        		));			
		}
		wp_reset_postdata();
		$total_items = count($items); 
        $this->items = $items;
        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page, 
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function likes_table_admin_menu()
{
    add_menu_page('Likes', 'Likes', 'activate_plugins', 'likes', 'likes_table_page_handler', 'dashicons-plus-alt', 26);
    add_submenu_page('likes', 'Likes', 'Likes', 'activate_plugins', 'likes', 'likes_table_page_handler');
    add_submenu_page('likes', 'Post Likes', 'Post Likes', 'activate_plugins', 'post_likes', 'post_likes_table_page_handler');
}
add_action('admin_menu', 'likes_table_admin_menu');

function likes_table_page_handler()
{
    global $wpdb;

    $table = new Likes_Table_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf('Лайков удалено:', count($_REQUEST['id'])) . '</p></div>';
    }
 ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2>Likes </h2>
    <?php echo $message; ?>

    <form id="likes-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

function post_likes_table_page_handler()
{
 
    $table = new Post_Likes_Table_List_Table();
    $table->prepare_items();

    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2>Рейтинг записей</h2>

    <form id="likes-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}