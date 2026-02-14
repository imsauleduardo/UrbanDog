<?php
require_once('wp-load.php');

// 1. Create the Search Page
$page_title = 'Buscar';
$page_content = '';
$page_check = get_page_by_title($page_title);
$page_id = 0;

if (!isset($page_check->ID)) {
    $page_id = wp_insert_post([
        'post_title' => $page_title,
        'post_content' => $page_content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'buscar'
    ]);
    update_post_meta($page_id, '_wp_page_template', 'page-search.php');
} else {
    $page_id = $page_check->ID;
}

// 2. Create a Mock Walker User
$username = 'paseador_demo';
$email = 'demo@urbandog.com';
$user_id = username_exists($username);

if (!$user_id && false == email_exists($email)) {
    $user_id = wp_create_user($username, 'password123', $email);
    $user = new WP_User($user_id);
    $user->set_role('ud_walker');
}

// Verify Walker
update_user_meta($user_id, 'ud_walker_verification_status', 'approved');

// 3. Create Walker Profile
$profile_title = 'Juan Pérez';
$profile_check = get_page_by_title($profile_title, OBJECT, 'ud_walker_profile');

if (!isset($profile_check->ID)) {
    $profile_id = wp_insert_post([
        'post_title' => $profile_title,
        'post_content' => 'Amante de los perros con 5 años de experiencia.',
        'post_status' => 'publish',
        'post_type' => 'ud_walker_profile',
        'post_author' => $user_id
    ]);
    update_post_meta($profile_id, 'ud_walker_zone', 'Los Olivos');
    update_post_meta($profile_id, 'ud_walker_price_30', 25);
    update_post_meta($profile_id, 'ud_walker_price_60', 40);
}

echo "Setup completed. Page ID: $page_id, User ID: $user_id";
