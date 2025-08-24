/**
 * Movie Rating Plugin - Admin JavaScript
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        console.log('Movie Rating Admin initialized');
        
       
        initMediaUploader();
        initFormValidation();
        initGenreManagement();
    });

    
    function initMediaUploader() {
        
        $(document).on('click', '#mr_upload_poster', function(e) {
            e.preventDefault();
            
            var frame = wp.media({
                title: 'Select or Upload Movie Poster',
                button: { text: 'Use this poster' },
                multiple: false,
                library: { type: 'image' }
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#mr_poster_url').val(attachment.url);
                
                
                var $preview = $('#mr_poster_url').siblings('img');
                if ($preview.length) {
                    $preview.attr('src', attachment.url);
                } else {
                    $('#mr_poster_url').parent().append(
                        '<br><br><img src="' + attachment.url + '" style="max-width: 200px; max-height: 200px;" />'
                    );
                }
            });
            
            frame.open();
        });
    }

    
    function initFormValidation() {
        // Validate movie link URL
        $(document).on('blur', '#mr_movie_link', function() {
            var url = $(this).val();
            var $field = $(this);
            
            if (url && !isValidURL(url)) {
                $field.css('border-color', '#dc3232');
                if (!$field.siblings('.error-message').length) {
                    $field.after('<span class="error-message" style="color: #dc3232; font-size: 12px; display: block; margin-top: 5px;">Please enter a valid URL (including http:// or https://)</span>');
                }
            } else {
                $field.css('border-color', '#ddd');
                $field.siblings('.error-message').remove();
            }
        });

       
        $(document).on('change', '#mr_release_date', function() {
            var date = new Date($(this).val());
            var today = new Date();
            var $field = $(this);
            
            if (date > today) {
                if (!confirm('The release date is in the future. Is this correct?')) {
                    $field.focus();
                }
            }
        });
    }

    
    function initGenreManagement() {
       
        if ($('#taxonomy-genre').length) {
            var $genreBox = $('#taxonomy-genre');
            var commonGenres = [
                'Action', 'Comedy', 'Drama', 'Horror', 'Romance', 
                'Thriller', 'Sci-Fi', 'Adventure', 'Animation', 'Documentary'
            ];
            
            
            var $quickAdd = $('<div class="mr-quick-genres"><h4>Quick Add Common Genres:</h4></div>');
            commonGenres.forEach(function(genre) {
                $quickAdd.append(
                    '<button type="button" class="button button-small mr-quick-genre" data-genre="' + 
                    genre + '">' + genre + '</button> '
                );
            });
            
            $genreBox.append($quickAdd);
            
            
            $(document).on('click', '.mr-quick-genre', function(e) {
                e.preventDefault();
                var genreName = $(this).data('genre');
                var $newGenre = $('#taxonomy-genre input[name="newtag[genre]"]');
                
                if ($newGenre.length) {
                    $newGenre.val(genreName);
                    $('#taxonomy-genre .button[value="Add New Tag"]').click();
                }
            });
        }
    }

    
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    
    function initAutoSave() {
        var saveTimer;
        
        $(document).on('input', '#mr_movie_details input', function() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function() {
                if (typeof wp !== 'undefined' && wp.autosave) {
                    wp.autosave.server.triggerSave();
                }
            }, 3000);
        });
    }

   
    if (typeof wp !== 'undefined' && wp.autosave) {
        initAutoSave();
    }

})( jQuery );


jQuery(document).ready(function($){
			$('#mr_upload_poster').click(function(e){
				e.preventDefault();
				var frame = wp.media({
					title: 'Select Poster',
					button: { text: 'Use this poster' },
					multiple: false
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					$('#mr_poster_url').val(attachment.url);
				});
				frame.open();
			});
		});