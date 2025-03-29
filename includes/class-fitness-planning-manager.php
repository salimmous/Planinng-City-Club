<?php
/**
 * La classe principale du plugin Fitness Planning Manager
 *
 * @since      1.0.0
 */

class Fitness_Planning_Manager {

    /**
     * Instance du post type
     *
     * @since    1.0.0
     * @access   protected
     * @var      Fitness_Planning_Post_Type    $post_type    Instance du post type
     */
    protected $post_type;

    /**
     * Instance du shortcode
     *
     * @since    1.0.0
     * @access   protected
     * @var      Fitness_Planning_Shortcode    $shortcode    Instance du shortcode
     */
    protected $shortcode;

    /**
     * Instance de l'admin
     *
     * @since    1.0.0
     * @access   protected
     * @var      Fitness_Planning_Admin    $admin    Instance de l'admin
     */
    protected $admin;

    /**
     * Initialise le plugin
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Charge les dépendances nécessaires au plugin
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        $this->post_type = new Fitness_Planning_Post_Type();
        $this->shortcode = new Fitness_Planning_Shortcode();
        $this->admin = new Fitness_Planning_Admin();
    }

    /**
     * Définit les hooks liés à la partie admin du plugin
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Enregistrer le post type et les taxonomies
        add_action('init', array($this->post_type, 'register_post_type'));
        add_action('init', array($this->post_type, 'register_taxonomies'));

        // Ajouter les scripts et styles admin
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));

        // Ajouter les meta boxes
        add_action('add_meta_boxes', array($this->admin, 'add_meta_boxes'));

        // Sauvegarder les meta boxes
        add_action('save_post', array($this->admin, 'save_meta_boxes'), 10, 2);
    }

    /**
     * Définit les hooks liés à la partie publique du plugin
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Enregistrer le shortcode
        add_action('init', array($this->shortcode, 'register_shortcode'));

        // Ajouter les scripts et styles publics
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Exécute le plugin
     *
     * @since    1.0.0
     */
    public function run() {
        // Rien à faire ici pour l'instant
    }

    /**
     * Enregistre les styles pour la partie publique du site
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('fitness-planning-manager', FPM_PLUGIN_URL . 'public/css/fitness-planning-manager-public.css', array(), FPM_VERSION, 'all');
    }

    /**
     * Enregistre les scripts pour la partie publique du site
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Enregistrer PDF.js
        wp_enqueue_script('pdfjs', 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js', array(), '2.10.377', false);
        
        // Enregistrer le script principal
        wp_enqueue_script('fitness-planning-manager', FPM_PLUGIN_URL . 'public/js/fitness-planning-manager-public.js', array('jquery', 'pdfjs'), FPM_VERSION, false);
        
        // Ajouter les variables locales pour le script
        wp_localize_script('fitness-planning-manager', 'fpm_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fitness-planning-manager-nonce'),
            'pdfjs_worker_url' => 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js',
        ));
    }
}
