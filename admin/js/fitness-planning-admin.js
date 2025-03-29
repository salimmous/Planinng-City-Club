/**
 * Script pour la partie admin du plugin Fitness Planning Manager
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Lorsque le DOM est prêt
    $(document).ready(function() {
        // Gestion de l'upload de PDF
        $('.upload-pdf-button').on('click', function(e) {
            e.preventDefault();

            var button = $(this);
            var container = button.closest('.pdf-upload-container');
            var pdfIdField = container.find('#planning_pdf_id');
            var pdfUrlField = container.find('#planning_pdf_file');

            // Créer un frame de média
            var frame = wp.media({
                title: fpm_admin_vars.i18n.select_pdf,
                button: {
                    text: fpm_admin_vars.i18n.use_this_pdf
                },
                multiple: false,
                library: {
                    type: 'application/pdf'
                }
            });

            // Lorsqu'un PDF est sélectionné
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                pdfIdField.val(attachment.id);
                pdfUrlField.val(attachment.url);

                // Ajouter un aperçu du PDF
                if (container.find('.pdf-preview').length === 0) {
                    container.append('<div class="pdf-preview"><p>' + fpm_admin_vars.i18n.pdf_preview + '</p><iframe src="' + fpm_plugin_url + 'public/pdfjs/web/viewer.html?file=' + encodeURIComponent(attachment.url) + '" width="100%" height="300px" style="border: 1px solid #ddd;"></iframe></div>');
                } else {
                    container.find('.pdf-preview iframe').attr('src', fpm_plugin_url + 'public/pdfjs/web/viewer.html?file=' + encodeURIComponent(attachment.url));
                }

                // Ajouter le bouton de suppression s'il n'existe pas
                if (container.find('.remove-pdf-button').length === 0) {
                    button.after('<button type="button" class="button remove-pdf-button">' + fpm_admin_vars.i18n.remove + '</button>');
                }
            });

            // Ouvrir le frame de média
            frame.open();
        });

        // Gestion de la suppression de PDF
        $(document).on('click', '.remove-pdf-button', function(e) {
            e.preventDefault();

            var button = $(this);
            var container = button.closest('.pdf-upload-container');
            var pdfIdField = container.find('#planning_pdf_id');
            var pdfUrlField = container.find('#planning_pdf_file');

            // Vider les champs
            pdfIdField.val('');
            pdfUrlField.val('');

            // Supprimer l'aperçu du PDF
            container.find('.pdf-preview').remove();

            // Supprimer le bouton de suppression
            button.remove();
        });
    });

})(jQuery);
