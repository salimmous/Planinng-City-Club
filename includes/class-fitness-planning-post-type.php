<?php
/**
 * Classe pour gérer le type de publication personnalisé (Custom Post Type) pour les plannings
 *
 * @since      1.0.0
 */

class Fitness_Planning_Post_Type {

    /**
     * Le nom du type de publication personnalisé
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $post_type    Le nom du type de publication personnalisé
     */
    private $post_type = 'fitness_planning';

    /**
     * Initialise la classe
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Ajouter le support pour les vignettes
        add_theme_support('post-thumbnails');
    }

    /**
     * Enregistre le type de publication personnalisé
     *
     * @since    1.0.0
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Plannings', 'Post Type General Name', 'fitness-planning-manager'),
            'singular_name'         => _x('Planning', 'Post Type Singular Name', 'fitness-planning-manager'),
            'menu_name'             => __('Plannings Fitness', 'fitness-planning-manager'),
            'name_admin_bar'        => __('Planning', 'fitness-planning-manager'),
            'archives'              => __('Archives des plannings', 'fitness-planning-manager'),
            'attributes'            => __('Attributs du planning', 'fitness-planning-manager'),
            'parent_item_colon'     => __('Planning parent:', 'fitness-planning-manager'),
            'all_items'             => __('Tous les plannings', 'fitness-planning-manager'),
            'add_new_item'          => __('Ajouter un nouveau planning', 'fitness-planning-manager'),
            'add_new'               => __('Ajouter', 'fitness-planning-manager'),
            'new_item'              => __('Nouveau planning', 'fitness-planning-manager'),
            'edit_item'             => __('Modifier le planning', 'fitness-planning-manager'),
            'update_item'           => __('Mettre à jour le planning', 'fitness-planning-manager'),
            'view_item'             => __('Voir le planning', 'fitness-planning-manager'),
            'view_items'            => __('Voir les plannings', 'fitness-planning-manager'),
            'search_items'          => __('Rechercher un planning', 'fitness-planning-manager'),
            'not_found'             => __('Aucun planning trouvé', 'fitness-planning-manager'),
            'not_found_in_trash'    => __('Aucun planning trouvé dans la corbeille', 'fitness-planning-manager'),
            'featured_image'        => __('Image du club', 'fitness-planning-manager'),
            'set_featured_image'    => __('Définir l\'image du club', 'fitness-planning-manager'),
            'remove_featured_image' => __('Supprimer l\'image du club', 'fitness-planning-manager'),
            'use_featured_image'    => __('Utiliser comme image du club', 'fitness-planning-manager'),
            'insert_into_item'      => __('Insérer dans le planning', 'fitness-planning-manager'),
            'uploaded_to_this_item' => __('Téléversé sur ce planning', 'fitness-planning-manager'),
            'items_list'            => __('Liste des plannings', 'fitness-planning-manager'),
            'items_list_navigation' => __('Navigation de la liste des plannings', 'fitness-planning-manager'),
            'filter_items_list'     => __('Filtrer la liste des plannings', 'fitness-planning-manager'),
        );
        $args = array(
            'label'                 => __('Planning', 'fitness-planning-manager'),
            'description'           => __('Plannings des clubs de fitness', 'fitness-planning-manager'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'taxonomies'            => array('planning_city', 'planning_type'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-calendar-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type($this->post_type, $args);
    }

    /**
     * Enregistre les taxonomies
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        // Taxonomie pour les villes
        $labels = array(
            'name'                       => _x('Villes', 'Taxonomy General Name', 'fitness-planning-manager'),
            'singular_name'              => _x('Ville', 'Taxonomy Singular Name', 'fitness-planning-manager'),
            'menu_name'                  => __('Villes', 'fitness-planning-manager'),
            'all_items'                  => __('Toutes les villes', 'fitness-planning-manager'),
            'parent_item'                => __('Ville parente', 'fitness-planning-manager'),
            'parent_item_colon'          => __('Ville parente:', 'fitness-planning-manager'),
            'new_item_name'              => __('Nouvelle ville', 'fitness-planning-manager'),
            'add_new_item'               => __('Ajouter une nouvelle ville', 'fitness-planning-manager'),
            'edit_item'                  => __('Modifier la ville', 'fitness-planning-manager'),
            'update_item'                => __('Mettre à jour la ville', 'fitness-planning-manager'),
            'view_item'                  => __('Voir la ville', 'fitness-planning-manager'),
            'separate_items_with_commas' => __('Séparer les villes avec des virgules', 'fitness-planning-manager'),
            'add_or_remove_items'        => __('Ajouter ou supprimer des villes', 'fitness-planning-manager'),
            'choose_from_most_used'      => __('Choisir parmi les plus utilisées', 'fitness-planning-manager'),
            'popular_items'              => __('Villes populaires', 'fitness-planning-manager'),
            'search_items'               => __('Rechercher des villes', 'fitness-planning-manager'),
            'not_found'                  => __('Aucune ville trouvée', 'fitness-planning-manager'),
            'no_terms'                   => __('Aucune ville', 'fitness-planning-manager'),
            'items_list'                 => __('Liste des villes', 'fitness-planning-manager'),
            'items_list_navigation'      => __('Navigation de la liste des villes', 'fitness-planning-manager'),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        register_taxonomy('planning_city', array($this->post_type), $args);

        // Taxonomie pour les types de planning
        $labels = array(
            'name'                       => _x('Types de planning', 'Taxonomy General Name', 'fitness-planning-manager'),
            'singular_name'              => _x('Type de planning', 'Taxonomy Singular Name', 'fitness-planning-manager'),
            'menu_name'                  => __('Types de planning', 'fitness-planning-manager'),
            'all_items'                  => __('Tous les types de planning', 'fitness-planning-manager'),
            'parent_item'                => __('Type de planning parent', 'fitness-planning-manager'),
            'parent_item_colon'          => __('Type de planning parent:', 'fitness-planning-manager'),
            'new_item_name'              => __('Nouveau type de planning', 'fitness-planning-manager'),
            'add_new_item'               => __('Ajouter un nouveau type de planning', 'fitness-planning-manager'),
            'edit_item'                  => __('Modifier le type de planning', 'fitness-planning-manager'),
            'update_item'                => __('Mettre à jour le type de planning', 'fitness-planning-manager'),
            'view_item'                  => __('Voir le type de planning', 'fitness-planning-manager'),
            'separate_items_with_commas' => __('Séparer les types de planning avec des virgules', 'fitness-planning-manager'),
            'add_or_remove_items'        => __('Ajouter ou supprimer des types de planning', 'fitness-planning-manager'),
            'choose_from_most_used'      => __('Choisir parmi les plus utilisés', 'fitness-planning-manager'),
            'popular_items'              => __('Types de planning populaires', 'fitness-planning-manager'),
            'search_items'               => __('Rechercher des types de planning', 'fitness-planning-manager'),
            'not_found'                  => __('Aucun type de planning trouvé', 'fitness-planning-manager'),
            'no_terms'                   => __('Aucun type de planning', 'fitness-planning-manager'),
            'items_list'                 => __('Liste des types de planning', 'fitness-planning-manager'),
            'items_list_navigation'      => __('Navigation de la liste des types de planning', 'fitness-planning-manager'),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        register_taxonomy('planning_type', array($this->post_type), $args);
    }
}
