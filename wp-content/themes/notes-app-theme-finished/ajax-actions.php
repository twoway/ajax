<?php 

/*
 *  AJAX Actions for notes App
 */

$fail = json_encode(array('message' => 'fail'), JSON_FORCE_OBJECT);

function generate_response($message, $id = null, $text = null){
  $response = array(
    'message' => $message,
    'id'      => $id,
    'text'    => $text
  );

  echo json_encode($response, JSON_FORCE_OBJECT);
}

function notes_add_user(){
  if(current_user_can( 'add_users' )){

    extract($_POST);
    $user_name = substr($text, 0, strpos($text, "@"));

    $user_id = username_exists( $user_name );

    if ( !$user_id and email_exists($text) == false ) {

      $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
      $user_id = wp_create_user( $user_name, $random_password, $text );

      $user = new WP_User($user_id);
      $user->set_role( 'editor' );

      //wp_new_user_notification( $user_id, $random_password );

      generate_response('user-added', $user_id, $text);

    } else { echo $fail; }

  } else { echo $fail; }
  exit;
}


function notes_delete_user(){
  if(current_user_can( 'remove_users' )){
    
    extract($_POST);

    if(wp_delete_user($id))
      generate_response('user-deleted', $id);


  } else { echo $fail; }
  exit;
}

function notes_clear_all(){
  if(current_user_can( 'delete_posts' )){
    $posts = get_posts(array('posts_per_page' => 9999));
    foreach($posts as $post) wp_delete_post( $post->ID );

    generate_response('all-deleted');

  } else { echo $fail; }
  exit;
}

function notes_new_post(){
  if(current_user_can( 'publish_posts' )){
    extract($_POST);

    $p = array(
      'post_title'    => $text,
      'post_author'   => get_current_user_id(),
      'post_content'  => '&nbsp;',
      'post_status'   => 'publish'
    );

    $post_id = wp_insert_post($p);

    if($post_id != 0){

      generate_response( 'post-added', $post_id, $text );

    } else { echo $fail; }

  } else { echo $fail; }
  exit;
}

function notes_update_post(){
  if(current_user_can( 'edit_posts' )){
    extract($_POST);

    $p = array(
      'ID'            => $id,
      'post_title'    => $text,
      'post_author'   => get_current_user_id(),
      'post_content'  => '&nbsp;',
      'post_status'   => 'publish'
    );

    $post_id = wp_update_post($p);

    if($post_id) generate_response('post-updated', $id, $text);

  } else { echo $fail; }
  exit;
}

function notes_delete_post(){
  if(current_user_can( 'delete_posts' )){
    extract($_POST);

    $result = wp_delete_post( $id );

    if(!false) generate_response('post-deleted', $id);

  } else { echo $fail; }
  exit;
}

add_action( 'wp_ajax_notes-add-user', 'notes_add_user' );
add_action( 'wp_ajax_notes-delete-user', 'notes_delete_user' );
add_action( 'wp_ajax_notes-clear-all', 'notes_clear_all' );
add_action( 'wp_ajax_notes-new-post', 'notes_new_post' );
add_action( 'wp_ajax_notes-update-post', 'notes_update_post' );
add_action( 'wp_ajax_notes-delete-post', 'notes_delete_post' );

