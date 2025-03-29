<?php
/**
 * Plugin Name: Fitness Planning Manager
 * Plugin URI: https://example.com/fitness-planning-manager
 * Description: Un plugin pour gérer et afficher les plannings des clubs de fitness avec filtrage par ville et type de planning.
 * Version: 1.0.0
 * Author: Trae AI
 * Author URI: https://example.com
 * Text Domain: fitness-planning-manager
 * Domain Path: /languages
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('FPM_VERSION', '1.0.0');
define('FPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FPM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FPM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Inclure les fichiers nécessaires
require_once FPM_PLUGIN_DIR . 'includes/class-fitness-planning-manager.php';
require_once FPM_PLUGIN_DIR . 'includes/class-fitness-planning-post-type.php';
require_once FPM_PLUGIN_DIR . 'includes/class-fitness-planning-shortcode.php';
require_once FPM_PLUGIN_DIR . 'admin/class-fitness-planning-admin.php';

// Initialiser le plugin
function fitness_planning_manager_init() {
    $plugin = new Fitness_Planning_Manager();
    $plugin->run();
}

// Activer le plugin
register_activation_hook(__FILE__, 'fitness_planning_manager_activate');
function fitness_planning_manager_activate() {
    // Créer les custom post types
    $post_type = new Fitness_Planning_Post_Type();
    $post_type->register_post_type();
    $post_type->register_taxonomies();
    
    // Vider le cache des permaliens
    flush_rewrite_rules();
}

// Désactiver le plugin
register_deactivation_hook(__FILE__, 'fitness_planning_manager_deactivate');
function fitness_planning_manager_deactivate() {
    // Vider le cache des permaliens
    flush_rewrite_rules();
}

// Lancer le plugin
fitness_planning_manager_init();
