<?php
/**
 * Classe pour gérer la partie admin du plugin
 *
 * @since      1.0.0
 */

class Fitness_Planning_Admin {

    /**
     * Initialise la classe
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Ajouter le support pour les médias
        add_theme_support('post-thumbnails');
    }

    /**
     * Enregistre les styles pour la partie admin
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('fitness-planning-admin', FPM_PLUGIN_URL . 'admin/css/fitness-planning-admin.css', array(), FPM_VERSION, 'all');
    }

    /**
     * Enregistre les scripts pour la partie admin
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Enregistrer les scripts nécessaires pour l'upload de médias
        wp_enqueue_media();
        
        wp_enqueue_script('fitness-planning-admin', FPM_PLUGIN_URL . 'admin/js/fitness-planning-admin.js', array('jquery'), FPM_VERSION, false);
        
        // Ajouter les variables locales pour le script
        wp_localize_script('fitness-planning-admin', 'fpm_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fitness-planning-admin-nonce'),
            'i18n' => array(
                'select_pdf' => __('Sélectionner un PDF', 'fitness-planning-manager'),
                'use_this_pdf' => __('Utiliser ce PDF', 'fitness-planning-manager'),
                'select_image' => __('Sélectionner une image', 'fitness-planning-manager'),
                'use_this_image' => __('Utiliser cette image', 'fitness-planning-manager'),
            ),
        ));
    }

    /**
     * Ajoute les meta boxes pour le type de publication personnalisé
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fitness_planning_details',
            __('Détails du planning', 'fitness-planning-manager'),
            array($this, 'render_planning_details_meta_box'),
            'fitness_planning',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fitness_planning_pdf',
            __('PDF du planning', 'fitness-planning-manager'),
            array($this, 'render_planning_pdf_meta_box'),
            'fitness_planning',
            'normal',
            'high'
        );
    }

    /**
     * Affiche le contenu de la meta box des détails du planning
     *
     * @since    1.0.0
     * @param    WP_Post    $post    L'objet post
     */
    public function render_planning_details_meta_box($post) {
        // Ajouter un nonce pour la sécurité
        wp_nonce_field('fitness_planning_details_meta_box', 'fitness_planning_details_meta_box_nonce');
        
        // Récupérer les valeurs existantes
        $update_date = get_post_meta($post->ID, '_planning_update_date', true);
        
        // Afficher les champs
        echo '<table class="form-table">';
        
        // Date de mise à jour
        echo '<tr>';
        echo '<th><label for="planning_update_date">' . __('Date de mise à jour', 'fitness-planning-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="date" id="planning_update_date" name="planning_update_date" value="' . esc_attr($update_date) . '" class="regular-text" />';
        echo '<p class="description">' . __('Date de la dernière mise à jour du planning.', 'fitness-planning-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
    }

    /**
     * Affiche le contenu de la meta box du PDF du planning
     *
     * @since    1.0.0
     * @param    WP_Post    $post    L'objet post
     */
    public function render_planning_pdf_meta_box($post) {
        // Ajouter un nonce pour la sécurité
        wp_nonce_field('fitness_planning_pdf_meta_box', 'fitness_planning_pdf_meta_box_nonce');
        
        // Récupérer les valeurs existantes
        $pdf_file = get_post_meta($post->ID, '_planning_pdf_file', true);
        $pdf_id = get_post_meta($post->ID, '_planning_pdf_id', true);
        
        // Afficher les champs
        echo '<table class="form-table">';
        
        // PDF du planning
        echo '<tr>';
        echo '<th><label for="planning_pdf_file">' . __('PDF du planning', 'fitness-planning-manager') . '</label></th>';
        echo '<td>';
        echo '<div class="pdf-upload-container">';
        echo '<input type="hidden" id="planning_pdf_id" name="planning_pdf_id" value="' . esc_attr($pdf_id) . '" />';
        echo '<input type="text" id="planning_pdf_file" name="planning_pdf_file" value="' . esc_attr($pdf_file) . '" class="regular-text" readonly />';
        echo '<button type="button" class="button upload-pdf-button">' . __('Téléverser un PDF', 'fitness-planning-manager') . '</button>';
        if (!empty($pdf_file)) {
            echo '<button type="button" class="button remove-pdf-button">' . __('Supprimer', 'fitness-planning-manager') . '</button>';
            echo '<div class="pdf-preview">';
            echo '<p>' . __('Aperçu du PDF:', 'fitness-planning-manager') . '</p>';
            echo '<iframe src="' . FPM_PLUGIN_URL . 'public/pdfjs/web/viewer.html?file=' . urlencode($pdf_file) . '" width="100%" height="300px" style="border: 1px solid #ddd;"></iframe>';
            echo '</div>';
        }
        echo '</div>';
        echo '<p class="description">' . __('Téléversez le PDF du planning. Format recommandé: PDF.', 'fitness-planning-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
    }

    /**
     * Sauvegarde les données des meta boxes
     *
     * @since    1.0.0
     * @param    int       $post_id    L'ID du post
     * @param    WP_Post   $post       L'objet post
     */
    public function save_meta_boxes($post_id, $post) {
        // Vérifier si c'est une sauvegarde automatique
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Vérifier le type de post
        if ($post->post_type !== 'fitness_planning') {
            return;
        }
        
        // Vérifier les permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Vérifier les nonces
        if (
            !isset($_POST['fitness_planning_details_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['fitness_planning_details_meta_box_nonce'], 'fitness_planning_details_meta_box')
        ) {
            return;
        }
        
        if (
            !isset($_POST['fitness_planning_pdf_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['fitness_planning_pdf_meta_box_nonce'], 'fitness_planning_pdf_meta_box')
        ) {
            return;
        }
        
        // Sauvegarder les données
        if (isset($_POST['planning_update_date'])) {
            update_post_meta($post_id, '_planning_update_date', sanitize_text_field($_POST['planning_update_date']));
        }
        
        if (isset($_POST['planning_pdf_file'])) {
            update_post_meta($post_id, '_planning_pdf_file', esc_url_raw($_POST['planning_pdf_file']));
        }
        
        if (isset($_POST['planning_pdf_id'])) {
            update_post_meta($post_id, '_planning_pdf_id', absint($_POST['planning_pdf_id']));
        }
    }
}
