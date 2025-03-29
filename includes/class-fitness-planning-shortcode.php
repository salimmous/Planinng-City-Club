<?php
/**
 * Classe pour gérer les shortcodes du plugin
 *
 * @since      1.0.0
 */

class Fitness_Planning_Shortcode {

    /**
     * Enregistre le shortcode
     *
     * @since    1.0.0
     */
    public function register_shortcode() {
        add_shortcode('fitness_planning', array($this, 'render_planning_shortcode'));
        add_shortcode('fitness_planning_list', array($this, 'render_planning_list_shortcode'));
    }

    /**
     * Affiche un planning spécifique
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode
     * @return   string            Le contenu HTML du shortcode
     */
    public function render_planning_shortcode($atts) {
        // ... (render_planning_shortcode remains unchanged) ...
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'fitness_planning');

        if (empty($atts['id'])) {
            return '<p>' . __('Veuillez spécifier un ID de planning.', 'fitness-planning-manager') . '</p>';
        }

        $planning = get_post(intval($atts['id']));

        if (!$planning || $planning->post_type !== 'fitness_planning') {
            return '<p>' . __('Planning non trouvé.', 'fitness-planning-manager') . '</p>';
        }

        // Récupérer les métadonnées
        $club_name = get_the_title($planning->ID);
        $description = get_the_content(null, false, $planning->ID);
        $update_date = get_post_meta($planning->ID, '_planning_update_date', true);
        $pdf_file = get_post_meta($planning->ID, '_planning_pdf_file', true);
        
        // Récupérer les termes des taxonomies
        $cities = get_the_terms($planning->ID, 'planning_city');
        $city_name = !empty($cities) ? $cities[0]->name : '';
        
        $types = get_the_terms($planning->ID, 'planning_type');
        $type_name = !empty($types) ? $types[0]->name : '';
        
        // Construire l'affichage
        $output = '<div class="fitness-planning-single">';
        
        // En-tête avec le nom du club et le type de planning
        $output .= '<div class="fitness-planning-header">';
        $output .= '<h2>' . esc_html($club_name) . '</h2>';
        if (!empty($type_name)) {
            $output .= '<span class="planning-type">' . esc_html($type_name) . '</span>';
        }
        $output .= '</div>';
        
        // Image principale
        if (has_post_thumbnail($planning->ID)) {
            $output .= '<div class="fitness-planning-image">';
            $output .= get_the_post_thumbnail($planning->ID, 'large');
            if (!empty($update_date)) {
                 // Format date if possible
                 $formatted_date = $update_date;
                 try {
                     $date_obj = new DateTime($update_date);
                     // Example format: 15 mars 2023 (adjust locale/format as needed)
                     $formatter = new IntlDateFormatter(get_locale(), IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                     $formatted_date = $formatter->format($date_obj);
                 } catch (Exception $e) {
                     // Keep original string if formatting fails
                 }
                 $output .= '<span class="update-date">Mis à jour le ' . esc_html($formatted_date) . '</span>';
            }
            $output .= '</div>';
        }
        
        // Informations sur la ville
        if (!empty($city_name)) {
            $output .= '<div class="fitness-planning-city">';
            // Use an icon font like FontAwesome or an SVG/image
            // Example with FontAwesome: $output .= '<i class="fas fa-map-marker-alt"></i> ';
            $output .= '<span class="city-icon"></span> ' . esc_html($city_name);
            $output .= '</div>';
        }
        
        // Description
        if (!empty($description)) {
            $output .= '<div class="fitness-planning-description">';
            $output .= wpautop($description);
            $output .= '</div>';
        }
        
        // Aperçu du PDF
        $output .= '<div class="fitness-planning-pdf-preview">';
        $output .= '<h3>' . __('Aperçu du planning', 'fitness-planning-manager') . '</h3>';
        
        if (!empty($pdf_file)) {
            // Option 1: Use PDF.js viewer in an iframe (as before)
            $viewer_url = FPM_PLUGIN_URL . 'public/pdfjs/web/viewer.html';
            $output .= '<div class="pdf-container" data-pdf-url="' . esc_url($pdf_file) . '">';
            $output .= '<iframe src="' . esc_url($viewer_url) . '?file=' . urlencode($pdf_file) . '" width="100%" height="600px" style="border: none;"></iframe>';
            $output .= '</div>';

            // Option 2: Placeholder for JS-driven canvas rendering (if you implement that for single view too)
            // $output .= '<div class="pdf-container" data-pdf-url="' . esc_url($pdf_file) . '">';
            // $output .= '<canvas id="single-pdf-canvas"></canvas>'; // JS would target this
            // $output .= '</div>';

            $output .= '<div class="pdf-download">';
            $output .= '<a href="' . esc_url($pdf_file) . '" class="download-button" download>' . __('Télécharger le planning', 'fitness-planning-manager') . '</a>';
            $output .= '</div>';
        } else {
            $output .= '<div class="pdf-not-available">';
            $output .= __('Aperçu du planning non disponible', 'fitness-planning-manager');
            $output .= '</div>';
        }
        
        $output .= '</div>'; // Fin de fitness-planning-pdf-preview
        $output .= '</div>'; // Fin de fitness-planning-single
        
        return $output;
    }

    /**
     * Affiche une liste de plannings avec filtres (Updated Layout)
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode
     * @return   string            Le contenu HTML du shortcode
     */
    public function render_planning_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'city' => '',
            'type' => '',
            'limit' => 12, // Keep limit, pagination could be added later
            'orderby' => 'date', // Default sort
            'order' => 'DESC',
        ), $atts, 'fitness_planning_list');

        // Enqueue les scripts et styles nécessaires
        // Make sure these handles match your wp_register_script/style calls
        wp_enqueue_script('fpm-public-script'); // Assuming 'fpm-public-script' is the handle for your public JS
        wp_enqueue_style('fpm-public-style'); // Assuming 'fpm-public-style' is the handle for your public CSS

        // Localize script for PDF.js worker and placeholder image if needed
        // Ensure fpm_vars is localized correctly in your main plugin file
        /* Example localization in main plugin file:
         wp_localize_script('fpm-public-script', 'fpm_vars', array(
            'pdfjs_worker_url' => FPM_PLUGIN_URL . 'public/pdfjs/build/pdf.worker.min.js', // Adjust path
            'placeholder_image' => FPM_PLUGIN_URL . 'public/images/placeholder.jpg',
            'ajax_url' => admin_url('admin-ajax.php') // If using AJAX later
         ));
        */


        // Construire la requête
        $args = array(
            'post_type' => 'fitness_planning',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'], // Use default from atts for initial load
            'order' => $atts['order'],
            'post_status' => 'publish', // Ensure only published posts are shown
        );

        // Ajouter les filtres de taxonomie initiaux si spécifiés in shortcode atts (less common now with UI filters)
        $tax_query = array('relation' => 'AND');
        if (!empty($atts['city'])) {
            $tax_query[] = array(
                'taxonomy' => 'planning_city',
                'field' => 'slug',
                'terms' => explode(',', $atts['city']),
            );
        }
        if (!empty($atts['type'])) {
            $tax_query[] = array(
                'taxonomy' => 'planning_type',
                'field' => 'slug',
                'terms' => explode(',', $atts['type']),
            );
        }
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Exécuter la requête
        $plannings = new WP_Query($args);

        // Start output buffer
        ob_start();
        ?>
        <div class="fitness-planning-container">

            <!-- Top Bar: Search and Controls -->
            <div class="fitness-planning-top-bar">
                <div class="fitness-planning-search">
                    <input type="text" id="planning-search" placeholder="<?php esc_attr_e('Rechercher un club...', 'fitness-planning-manager'); ?>">
                </div>
                <div class="fitness-planning-controls">
                    <div class="filter-toggle">
                        <button class="filter-button">
                            <span class="filter-icon"></span>
                            <?php esc_html_e('Filtres', 'fitness-planning-manager'); ?>
                            <!-- Optional: Add arrow icon for toggle state -->
                        </button>
                    </div>
                    <div class="view-toggle">
                        <button class="view-grid active" title="<?php esc_attr_e('Grid View', 'fitness-planning-manager'); ?>">
                            <!-- Use SVG or Font Icon -->
                            <svg class="view-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zM13 3h8v8h-8V3zm0 10h8v8h-8v-8z"/></svg>
                        </button>
                        <button class="view-map" title="<?php esc_attr_e('Map View', 'fitness-planning-manager'); ?>">
                             <!-- Use SVG or Font Icon -->
                            <svg class="view-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Area (Initially Hidden) -->
            <div class="fitness-planning-filters" style="display: none;">
                <?php
                // Filter: City
                $cities = get_terms(array('taxonomy' => 'planning_city', 'hide_empty' => true));
                if (!empty($cities) && !is_wp_error($cities)): ?>
                    <div class="filter-section">
                        <h3><?php esc_html_e('Ville', 'fitness-planning-manager'); ?></h3>
                        <div class="filter-options city-filter">
                            <button class="filter-option active" data-filter="city" data-value="all"><?php esc_html_e('Toutes les villes', 'fitness-planning-manager'); ?></button>
                            <?php foreach ($cities as $city): ?>
                                <button class="filter-option" data-filter="city" data-value="<?php echo esc_attr($city->slug); ?>"><?php echo esc_html($city->name); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Filter: Type
                $types = get_terms(array('taxonomy' => 'planning_type', 'hide_empty' => true));
                if (!empty($types) && !is_wp_error($types)): ?>
                    <div class="filter-section">
                        <h3><?php esc_html_e('Type de planning', 'fitness-planning-manager'); ?></h3>
                        <div class="filter-options type-filter">
                            <button class="filter-option active" data-filter="type" data-value="all"><?php esc_html_e('Tous les plannings', 'fitness-planning-manager'); ?></button>
                            <?php foreach ($types as $type): ?>
                                <button class="filter-option" data-filter="type" data-value="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php // Filter: Sort ?>
                <div class="filter-section">
                    <h3><?php esc_html_e('Trier par', 'fitness-planning-manager'); ?></h3>
                    <div class="filter-options sort-filter">
                        <button class="filter-option active" data-filter="sort" data-value="popularity"><?php esc_html_e('Popularité', 'fitness-planning-manager'); ?></button>
                        <button class="filter-option" data-filter="sort" data-value="date"><?php esc_html_e('Date de mise à jour', 'fitness-planning-manager'); ?></button>
                        <button class="filter-option" data-filter="sort" data-value="name"><?php esc_html_e('Nom', 'fitness-planning-manager'); ?></button>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="planning-results-count">
                <span class="count"><?php echo $plannings->found_posts; ?></span> <?php esc_html_e('Plannings trouvés', 'fitness-planning-manager'); ?>
            </div>

            <!-- Planning List -->
            <div class="fitness-planning-list">
                <?php if ($plannings->have_posts()): ?>
                    <?php while ($plannings->have_posts()): $plannings->the_post(); ?>
                        <?php
                        $planning_id = get_the_ID();
                        $update_date_raw = get_post_meta($planning_id, '_planning_update_date', true);
                        $pdf_file = get_post_meta($planning_id, '_planning_pdf_file', true);
                        $description = get_the_excerpt(); // Use excerpt for card description data

                        // Format date
                        $update_date_formatted = $update_date_raw;
                        if ($update_date_raw) {
                            try {
                                $date_obj = new DateTime($update_date_raw);
                                // Format: 15 mars 2023 (adjust locale/format as needed)
                                // Requires intl extension
                                if (class_exists('IntlDateFormatter')) {
                                     $formatter = new IntlDateFormatter(get_locale(), IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                                     $update_date_formatted = $formatter->format($date_obj);
                                } else {
                                     // Fallback format if intl is not available
                                     $update_date_formatted = date_i18n(get_option('date_format'), strtotime($update_date_raw));
                                }

                            } catch (Exception $e) {
                                // Keep original string if formatting fails
                                $update_date_formatted = $update_date_raw;
                            }
                        }


                        // Get terms
                        $city_terms = get_the_terms($planning_id, 'planning_city');
                        $city_name = !empty($city_terms) && !is_wp_error($city_terms) ? $city_terms[0]->name : '';
                        $city_slug = !empty($city_terms) && !is_wp_error($city_terms) ? $city_terms[0]->slug : '';

                        $type_terms = get_the_terms($planning_id, 'planning_type');
                        $type_name = !empty($type_terms) && !is_wp_error($type_terms) ? $type_terms[0]->name : '';
                        $type_slug = !empty($type_terms) && !is_wp_error($type_terms) ? $type_terms[0]->slug : 'standard'; // Default slug

                        // Image
                        $image_url = get_the_post_thumbnail_url($planning_id, 'medium_large'); // Use a suitable image size
                        if (!$image_url) {
                            // Use placeholder if no featured image - ensure placeholder URL is available via fpm_vars or define constant
                            $placeholder_url = defined('FPM_PLUGIN_URL') ? FPM_PLUGIN_URL . 'public/images/placeholder.jpg' : ''; // Adjust path
                            $image_url = $placeholder_url;
                        }
                        ?>
                        <div class="facility-card" data-city="<?php echo esc_attr($city_slug); ?>" data-type="<?php echo esc_attr($type_slug); ?>" data-description="<?php echo esc_attr(wp_strip_all_tags($description)); ?>">
                            <div class="facility-image-container">
                                <?php if ($image_url): ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="facility-image">
                                <?php endif; ?>

                                <?php if (!empty($type_name)): ?>
                                    <span class="facility-tag <?php echo esc_attr(strtolower($type_slug)); ?>"><?php echo esc_html($type_name); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($update_date_formatted)): ?>
                                    <span class="last-updated">
                                        <!-- Use SVG or Font Icon -->
                                        <svg class="clock-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/><path d="M13 7h-2v6l5.25 3.15.75-1.23-4-2.42V7z"/></svg>
                                        <?php printf(__('Mis à jour le %s', 'fitness-planning-manager'), esc_html($update_date_formatted)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="facility-details">
                                <h4 class="facility-name"><?php the_title(); ?></h4>
                                <?php if (!empty($city_name)): ?>
                                    <div class="facility-location">
                                        <!-- Use SVG or Font Icon -->
                                        <span class="city-icon"></span>
                                        <?php echo esc_html($city_name); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="facility-actions">
                                <button class="bookmark-btn" title="<?php esc_attr_e('Bookmark', 'fitness-planning-manager'); ?>">
                                    <span class="bookmark-icon"></span>
                                </button>
                                <div class="facility-actions-buttons">
                                    <button class="action-btn preview-btn" data-planning-id="<?php echo $planning_id; ?>" data-pdf-url="<?php echo esc_url($pdf_file); ?>">
                                        <span class="action-icon preview-icon"></span>
                                        <?php esc_html_e('Aperçu', 'fitness-planning-manager'); ?>
                                    </button>
                                    <?php if (!empty($pdf_file)): ?>
                                        <a href="<?php echo esc_url($pdf_file); ?>" class="action-btn download-btn" download>
                                            <span class="action-icon download-icon"></span>
                                            <?php esc_html_e('Télécharger', 'fitness-planning-manager'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <div class="no-plannings" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <?php esc_html_e('Aucun planning trouvé correspondant à vos critères.', 'fitness-planning-manager'); ?>
                    </div>
                <?php endif; ?>
            </div> <!-- .fitness-planning-list -->

        </div> <!-- .fitness-planning-container -->
        <?php
        // Return buffered content
        return ob_get_clean();
    }
}
