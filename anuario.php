<?php
/*
Plugin Name: Anuario
Description: CRUD de Alumni con API REST
Version: 1.2
*/

if (!defined('ABSPATH')) exit;

define('ANUARIO_PATH', plugin_dir_path(__FILE__));
define('ANUARIO_URL', plugin_dir_url(__FILE__));

require_once ANUARIO_PATH . 'includes/activator.php';
require_once ANUARIO_PATH . 'includes/admin-menu.php';
require_once ANUARIO_PATH . 'includes/delete.php';
require_once ANUARIO_PATH . 'includes/rest-api.php';
require_once ANUARIO_PATH . 'includes/export-csv.php';

require_once ANUARIO_PATH . 'admin/list.php';
require_once ANUARIO_PATH . 'admin/form.php';
require_once ANUARIO_PATH . 'admin/bulk.php';
