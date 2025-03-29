/**
 * Script pour la partie publique du plugin Fitness Planning Manager
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Variables globales pour le PDF
    var currentPdf = null;
    var currentPage = 1;
    var totalPages = 0;

    // Lorsque le DOM est prêt
    $(document).ready(function() {
        console.log('FPM Public JS: Document ready.'); // DEBUG

        // Vérifier si PDF.js est chargé
        if (typeof pdfjsLib === 'undefined') {
            console.error('FPM Public JS: PDF.js n\'est pas chargé.');
        } else {
             console.log('FPM Public JS: PDF.js seems loaded.'); // DEBUG
        }

        // Configurer PDF.js
        if (typeof pdfjsLib !== 'undefined' && typeof pdfjsLib.GlobalWorkerOptions !== 'undefined') {
             console.log('FPM Public JS: Configuring PDF.js worker. fpm_vars:', typeof fpm_vars !== 'undefined' ? fpm_vars : 'undefined'); // DEBUG
             pdfjsLib.GlobalWorkerOptions.workerSrc = (typeof fpm_vars !== 'undefined' && fpm_vars.pdfjs_worker_url) ? fpm_vars.pdfjs_worker_url : 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js'; // Fallback CDN
             console.log('FPM Public JS: PDF.js worker source set to:', pdfjsLib.GlobalWorkerOptions.workerSrc); // DEBUG
        } else {
             console.warn('FPM Public JS: pdfjsLib or GlobalWorkerOptions not fully available for worker setup.'); // DEBUG
        }


        // Variables pour le filtrage
        var activeFilters = {
            city: 'all',
            type: 'all',
            sort: 'popularity' // Default sort
        };

        // Gestion des filtres
        $('.filter-option').on('click', function() {
            var $this = $(this);
            var filterType = $this.data('filter');
            var filterValue = $this.data('value');
            activeFilters[filterType] = filterValue;
            $this.closest('.filter-options').find('.filter-option').removeClass('active');
            $this.addClass('active');
            applyFiltersAndSort();
        });

        // Gestion de la recherche
        $('#planning-search').on('keyup', function() {
            applyFiltersAndSort();
        });

        // Fonction pour appliquer les filtres ET trier
        function applyFiltersAndSort() {
            var searchTerm = $('#planning-search').val().toLowerCase();
            var $container = $('.fitness-planning-list');
            var $items = $container.children('.facility-card');
            var visibleItems = [];
            console.log('FPM Public JS: Applying filters & sort. Search:', searchTerm, 'Active:', activeFilters); // DEBUG

            $items.each(function() {
                var $item = $(this);
                var cityMatch = activeFilters.city === 'all' || $item.data('city') === activeFilters.city;
                var typeMatch = activeFilters.type === 'all' || $item.data('type') === activeFilters.type;
                var titleMatch = true;
                if (searchTerm) {
                    var title = $item.find('.facility-name').text().toLowerCase();
                    titleMatch = title.includes(searchTerm);
                }
                if (cityMatch && typeMatch && titleMatch) {
                    $item.show();
                    visibleItems.push(this);
                } else {
                    $item.hide();
                }
            });

            $('.planning-results-count .count').text(visibleItems.length);
            if (visibleItems.length === 0) {
                $('.no-plannings').show();
            } else {
                 $('.no-plannings').hide();
            }

            var $sortedItems = $(visibleItems).sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);
                if (activeFilters.sort === 'popularity') {
                    return 0;
                } else if (activeFilters.sort === 'date') {
                    var dateTextA = $a.find('.last-updated').text().replace(/Mis à jour le /i, '').trim();
                    var dateTextB = $b.find('.last-updated').text().replace(/Mis à jour le /i, '').trim();
                    var dateA = parseFrenchDate(dateTextA);
                    var dateB = parseFrenchDate(dateTextB);
                    if (dateA && dateB) {
                        return dateB - dateA;
                    }
                    return 0;
                } else if (activeFilters.sort === 'name') {
                    var nameA = $a.find('.facility-name').text().toLowerCase();
                    var nameB = $b.find('.facility-name').text().toLowerCase();
                    return nameA.localeCompare(nameB);
                }
                return 0;
            });
            $container.append($sortedItems);
        }

        // Helper function to parse French dates
        function parseFrenchDate(dateString) {
            const months = {
                'janvier': 0, 'février': 1, 'mars': 2, 'avril': 3, 'mai': 4, 'juin': 5,
                'juillet': 6, 'août': 7, 'septembre': 8, 'octobre': 9, 'novembre': 10, 'décembre': 11
            };
            const parts = dateString.toLowerCase().split(' ');
            if (parts.length === 3) {
                const day = parseInt(parts[0], 10);
                const month = months[parts[1]];
                const year = parseInt(parts[2], 10);
                if (!isNaN(day) && month !== undefined && !isNaN(year)) {
                    return new Date(year, month, day);
                }
            }
            let date = new Date(dateString);
            if (!isNaN(date.getTime())) { return date; }
            return null;
        }

        // Afficher/masquer les filtres au clic sur le bouton
        $('.filter-button').on('click', function() {
             console.log('FPM Public JS: Filter button clicked.'); // DEBUG
            $('.fitness-planning-filters').slideToggle();
            $(this).toggleClass('active');
        });

        // Initialiser les filtres et le tri au chargement
        if ($('.fitness-planning-list').length > 0) {
             console.log('FPM Public JS: Initializing filters and sort.'); // DEBUG
            applyFiltersAndSort();
        }

        // --- Modal Logic ---

        // Créer le modal (si pas déjà dans le DOM)
        if ($('#planning-preview-modal').length === 0) {
            console.log('FPM Public JS: Creating preview modal dynamically.'); // DEBUG
            $('body').append(`
                <div class="planning-modal" id="planning-preview-modal" style="display: none;">
                    <div class="planning-modal-content">
                        <div class="planning-modal-header">
                            <h2 id="planning-modal-title">Titre du Planning</h2>
                            <button class="planning-modal-close" title="Fermer">&times;</button>
                        </div>
                        <div class="planning-modal-body">
                             <div class="planning-modal-info">
                                <div class="planning-modal-image">
                                    <img id="planning-modal-img" src="" alt="Image du planning">
                                    <div class="planning-modal-badge" id="planning-modal-badge">Type</div>
                                    <div class="planning-modal-update" id="planning-modal-date">Date mise à jour</div>
                                </div>
                                <div style="padding: 0 25px;">
                                    <div class="planning-modal-location" style="margin-bottom: 15px;">
                                        <span class="city-icon"></span>&nbsp;
                                        <span id="planning-modal-city">Ville</span>
                                    </div>
                                    <div class="planning-modal-description">
                                        <h3>Description</h3>
                                        <p id="planning-modal-desc">Description...</p>
                                    </div>
                                    <div class="planning-modal-preview">
                                        <h3>Aperçu du planning</h3>
                                        <div id="planning-modal-pdf-container" class="pdf-container">
                                            <div class="pdf-loading" style="display: none;">Chargement du PDF...</div>
                                            <div class="pdf-error" style="display: none;">Erreur de chargement.</div>
                                            <div class="pdf-not-available" style="display: none;">Aperçu non disponible</div>
                                        </div>
                                        <div class="pdf-navigation" style="display: none;">
                                            <button id="prev-page" disabled>< Précédent</button>
                                            <span class="page-info">Page <span id="page-num">1</span> / <span id="page-count">1</span></span>
                                            <button id="next-page" disabled>Suivant ></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="planning-modal-footer">
                             <button class="planning-modal-bookmark bookmark-btn" title="Bookmark">
                                 <span class="bookmark-icon"></span>
                             </button>
                             <a id="planning-modal-download" href="#" class="action-btn download-btn" download style="display: none;">
                                 <span class="action-icon download-icon"></span> Télécharger
                             </a>
                        </div>
                    </div>
                </div>
            `);
        } else {
             console.log('FPM Public JS: Preview modal already exists in DOM.'); // DEBUG
        }

        // Gestion du clic sur le bouton d'aperçu (Event Delegation) - SIMPLIFIED FOR TESTING
        $('.fitness-planning-container').on('click', '.preview-btn', function(e) {
            console.log('--- FPM Public JS: Preview button CLICK DETECTED ---'); // Log 1

            e.preventDefault();
            console.log('--- FPM Public JS: preventDefault() called. ---'); // Log 2

            e.stopPropagation();
            console.log('--- FPM Public JS: stopPropagation() called. ---'); // Log 3

            console.log('--- FPM Public JS: Event handling test complete. Modal logic skipped. ---'); // Log 4

            // --- TEMPORARILY COMMENTED OUT MODAL LOGIC ---
            /*
            var $button = $(this);
            var $item = $button.closest('.facility-card');
            if ($item.length === 0) {
                 console.error('FPM Public JS: Could not find parent .facility-card for preview button.');
                 return false; // Stop execution
            }

            // --- Récupérer les données depuis la carte ---
            var pdfUrl = $button.data('pdf-url');
            var planningId = $button.data('planning-id');
            var planningTitle = $item.find('.facility-name').text().trim() || 'Titre inconnu';
            var planningCity = $item.find('.facility-location').contents().filter(function() { return this.nodeType === 3; }).text().trim() || 'Ville inconnue';
            var planningTypeSlug = $item.data('type') || 'standard';
            var planningType = $item.find('.facility-tag').text().trim() || planningTypeSlug.charAt(0).toUpperCase() + planningTypeSlug.slice(1);
            var planningImage = $item.find('.facility-image').attr('src') || (typeof fpm_vars !== 'undefined' ? fpm_vars.placeholder_image : '');
            var planningDateText = $item.find('.last-updated').text().trim() || 'Date inconnue';
            var planningDesc = $item.data('description') || 'Aucune description disponible.';

             console.log('FPM Public JS: Modal Data - PDF URL:', pdfUrl);
             console.log('FPM Public JS: Modal Data - Title:', planningTitle);

            // --- Mettre à jour le contenu du modal ---
            var $modal = $('#planning-preview-modal');
            console.log('FPM Public JS: Modal element found:', $modal);
            console.log('FPM Public JS: Modal length:', $modal.length);
            if ($modal.length === 0) {
                console.error('FPM Public JS: Modal element #planning-preview-modal NOT FOUND in the DOM!');
                return false; // Stop execution
            }

            $modal.find('#planning-modal-title').text(planningTitle);
            $modal.find('#planning-modal-city').text(planningCity);
            $modal.find('#planning-modal-img').attr('src', planningImage).attr('alt', planningTitle);
            $modal.find('#planning-modal-badge').text(planningType).attr('class', 'planning-modal-badge ' + planningTypeSlug.toLowerCase());
            $modal.find('#planning-modal-date').text(planningDateText);
            $modal.find('#planning-modal-desc').text(planningDesc);

            // Reset PDF container state
            var $pdfContainer = $modal.find('#planning-modal-pdf-container');
            $pdfContainer.children().hide();
            $pdfContainer.find('canvas').remove();
            $modal.find('.pdf-navigation').hide();

            // Gérer le bouton de téléchargement
            var $downloadBtn = $modal.find('#planning-modal-download');
            if (pdfUrl) {
                $downloadBtn.attr('href', pdfUrl).show();
            } else {
                $downloadBtn.hide();
            }

            // Afficher le modal
            console.log('FPM Public JS: Attempting to show modal #planning-preview-modal');
            $modal.show();
            console.log('FPM Public JS: Modal shown via .show(). Check visibility.');

            var displayStyle = $modal.css('display');
            console.log('FPM Public JS: Modal display style after .show():', displayStyle);

            if (displayStyle === 'none') {
                 console.warn('FPM Public JS: Modal display is STILL none after .show()! Check CSS.');
            }

             // --- Load PDF logic ---
             if ($modal.is(':visible')) {
                 console.log('FPM Public JS: Modal is visible, proceeding to load PDF.');
                 if (pdfUrl) {
                     console.log('FPM Public JS: Calling loadPdf for URL:', pdfUrl);
                     loadPdf(pdfUrl);
                 } else {
                     console.log('FPM Public JS: No PDF URL provided, showing "not available".');
                     $pdfContainer.find('.pdf-not-available').show();
                 }
             } else {
                 console.warn('FPM Public JS: Modal is NOT visible after .show(), PDF will not be loaded.');
             }
             */
             // --- END COMMENTED OUT MODAL LOGIC ---

            return false; // Explicitly return false to be sure
        });

        // Fermer le modal
        $(document).on('click', '#planning-preview-modal .planning-modal-close', function() {
             console.log('FPM Public JS: Modal close button clicked.');
             $('#planning-preview-modal').hide();
             console.log('FPM Public JS: Modal hidden via .hide()');
             currentPdf = null;
             $('#planning-modal-pdf-container').empty().append(`
                <div class="pdf-loading" style="display: none;">Chargement du PDF...</div>
                <div class="pdf-error" style="display: none;">Erreur de chargement.</div>
                <div class="pdf-not-available" style="display: none;">Aperçu non disponible</div>
             `);
        });

        // Fermer le modal en cliquant en dehors
        $(document).on('click', '#planning-preview-modal', function(e) {
            if ($(e.target).is('#planning-preview-modal')) {
                 console.log('FPM Public JS: Click outside modal content detected.');
                 $('#planning-preview-modal .planning-modal-close').trigger('click');
            }
        });

        // --- PDF Navigation ---
        $(document).on('click', '#prev-page', function() {
            if (currentPage > 1) { currentPage--; renderPage(currentPage); }
        });
        $(document).on('click', '#next-page', function() {
            if (currentPage < totalPages) { currentPage++; renderPage(currentPage); }
        });

        // --- PDF Loading and Rendering ---
        function loadPdf(pdfUrl) {
            if (!pdfUrl || typeof pdfjsLib === 'undefined') {
                 console.error('FPM Public JS: loadPdf - PDF.js not available or no URL.');
                 $('#planning-modal-pdf-container .pdf-error').text('Erreur: PDF Viewer non chargé ou URL manquante.').show();
                 return;
            }
            currentPage = 1; totalPages = 0; currentPdf = null;
            var $pdfContainer = $('#planning-modal-pdf-container');
            var $nav = $('.pdf-navigation');
            $pdfContainer.children().hide();
            $pdfContainer.find('canvas').remove();
            $pdfContainer.find('.pdf-loading').show();
            $nav.hide().find('#prev-page, #next-page').prop('disabled', true);
            $nav.find('#page-num, #page-count').text('...');
            console.log('FPM Public JS: loadPdf - Loading document:', pdfUrl);
            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                console.log('FPM Public JS: loadPdf - PDF loaded. Pages:', pdf.numPages);
                currentPdf = pdf; totalPages = pdf.numPages;
                $pdfContainer.find('.pdf-loading').hide();
                $nav.find('#page-count').text(totalPages);
                if (totalPages > 0) { renderPage(currentPage); }
                else { $pdfContainer.find('.pdf-error').text('Le fichier PDF est vide.').show(); }
            }).catch(function(error) {
                console.error('FPM Public JS: loadPdf - Error loading PDF:', error);
                var errorMessage = 'Impossible de charger le PDF. (' + error.message + ')';
                $pdfContainer.children().hide();
                $pdfContainer.find('.pdf-error').text(errorMessage).show();
                $nav.hide();
            });
        }

        function renderPage(pageNumber) {
            if (!currentPdf) { return; }
            console.log('FPM Public JS: renderPage - Rendering page:', pageNumber);
            var $pdfContainer = $('#planning-modal-pdf-container');
            var $nav = $('.pdf-navigation');
            $nav.find('#prev-page, #next-page').prop('disabled', true);
            currentPdf.getPage(pageNumber).then(function(page) {
                var $canvas = $pdfContainer.find('canvas');
                if ($canvas.length === 0) {
                    $canvas = $('<canvas class="pdf-canvas"></canvas>');
                    $pdfContainer.append($canvas);
                }
                var canvas = $canvas[0]; var context = canvas.getContext('2d');
                var containerWidth = $pdfContainer.width() || 500; // Fallback width
                var viewport = page.getViewport({scale: 1});
                var scale = containerWidth / viewport.width;
                viewport = page.getViewport({scale: scale});
                canvas.height = viewport.height; canvas.width = viewport.width;
                $canvas.show();
                var renderContext = { canvasContext: context, viewport: viewport };
                console.log('FPM Public JS: renderPage - Starting render task for page', pageNumber);
                page.render(renderContext).promise.then(function() {
                    console.log('FPM Public JS: renderPage - Page', pageNumber, 'rendered.');
                    $nav.find('#page-num').text(pageNumber);
                    updateNavigationButtons();
                    $nav.show();
                }).catch(function(error) {
                    console.error('FPM Public JS: renderPage - Error rendering page', pageNumber, ':', error);
                    $pdfContainer.children().hide();
                    $pdfContainer.find('.pdf-error').text('Erreur affichage page.').show();
                    $nav.hide();
                });
            }).catch(function(error) {
                console.error('FPM Public JS: renderPage - Error getting page', pageNumber, ':', error);
                 $pdfContainer.children().hide();
                 $pdfContainer.find('.pdf-error').text('Impossible charger page.').show();
                 $nav.hide();
            });
        }

        function updateNavigationButtons() {
            if (totalPages <= 0) { $('.pdf-navigation').hide(); return; }
            console.log('FPM Public JS: updateNavigationButtons - Current:', currentPage, 'Total:', totalPages);
            $('#prev-page').prop('disabled', currentPage <= 1);
            $('#next-page').prop('disabled', currentPage >= totalPages);
        }

        // --- Other UI interactions ---
        $(document).on('click', '.bookmark-btn', function() {
            $(this).toggleClass('bookmarked');
            var isBookmarked = $(this).hasClass('bookmarked');
            console.log('Bookmark toggled:', isBookmarked);
            $(this).attr('title', isBookmarked ? 'Remove Bookmark' : 'Bookmark');
        });

        $('.view-toggle button').on('click', function() {
            var $this = $(this);
            if ($this.hasClass('active')) return;
            $this.siblings().removeClass('active'); $this.addClass('active');
            if ($this.hasClass('view-map')) {
                console.log('Switch to Map View (Not Implemented)');
                $('.fitness-planning-list').hide();
            } else {
                 console.log('Switch to Grid View');
                 $('.fitness-planning-list').show();
            }
        });

    }); // End document ready

})(jQuery);
