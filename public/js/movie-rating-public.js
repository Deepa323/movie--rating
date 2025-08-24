/**
 * Simple Movie Rating JavaScript (Database Storage)
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initSliders();
        initStarRating();
        loadExistingRatings();
    });

    
    function loadExistingRatings() {
        $('.mr-movie-card').each(function() {
            var $card = $(this);
            var $stars = $card.find('.mr-stars');
            var userRating = parseInt($stars.data('user-rating')) || 0;
            var average = parseFloat($stars.data('average')) || 0;

            if (userRating > 0) {
              
                updateStarDisplay($stars, userRating);
                $stars.addClass('user-rated clickable');
            } else {
              
                updateStarDisplay($stars, average);
                $stars.addClass('clickable').removeClass('user-rated');
            }
        });
    }

    
    function initStarRating() {
        
        $(document).on('mouseenter', '.mr-stars.clickable i', function() {
            var rating = $(this).data('rating');
            var $stars = $(this).closest('.mr-stars');
            updateStarDisplay($stars, rating);
        });

       
        $(document).on('mouseleave', '.mr-stars.clickable', function() {
            var $stars = $(this);
            var userRating = parseInt($stars.attr('data-user-rating')) || 0;
            var average = parseFloat($stars.attr('data-average')) || 0;
            
           
            var displayRating = userRating > 0 ? userRating : average;
            updateStarDisplay($stars, displayRating);
        });

      
        $(document).on('click', '.mr-stars.clickable i', function() {
            var $this = $(this);
            var rating = $this.data('rating');
            var $stars = $this.closest('.mr-stars');
            var movieId = $stars.data('movie');
            var $card = $this.closest('.mr-movie-card');
            
            if ($stars.hasClass('submitting')) {
                return;
            }
            
           
            updateStarDisplay($stars, rating);
            
           
            $stars.attr('data-user-rating', rating);
            
            $stars.addClass('submitting');
            
            submitRating(movieId, rating, $stars, $card);
        });
    }

    
    function submitRating(movieId, rating, $stars, $card) {
        $.ajax({
            url: mr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mr_submit_rating',
                movie_id: movieId,
                rating: rating,
                nonce: mr_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    updateRatingDisplay($card, data);
                  
                    $stars.removeClass('submitting').addClass('user-rated clickable');
                    showMessage($card, data.message || 'Rating updated!', 'success');
                } else {
                  
                    var userRating = parseInt($stars.attr('data-user-rating')) || 0;
                    var average = parseFloat($stars.attr('data-average')) || 0;
                    var displayRating = userRating > 0 ? userRating : average;
                    updateStarDisplay($stars, displayRating);
                    showMessage($card, response.data || 'Error submitting rating', 'error');
                }
            },
            error: function() {
               
                var userRating = parseInt($stars.attr('data-user-rating')) || 0;
                var average = parseFloat($stars.attr('data-average')) || 0;
                var displayRating = userRating > 0 ? userRating : average;
                updateStarDisplay($stars, displayRating);
                showMessage($card, 'Network error. Please try again.', 'error');
            },
            complete: function() {
                $stars.removeClass('submitting');
            }
        });
    }

    
    function updateStarDisplay($stars, rating) {
        $stars.find('i').each(function(index) {
            var $star = $(this);
            var starPosition = index + 1;
            
           
            $star.removeClass('fas far');
            
          
            if (starPosition <= Math.floor(rating) || (starPosition <= Math.ceil(rating) && rating % 1 >= 0.5)) {
                $star.addClass('fas');
            } else {
                $star.addClass('far');
            }
        });
    }

    
    function updateRatingDisplay($card, data) {
        var $average = $card.find('.mr-average');
        $average.html('Rating: <span class="mr-rating-value">' + data.average + '</span> (<span class="mr-vote-count">' + data.total_votes + '</span> votes)/5<br><small class="user-rating-text" style="color: #0073aa;">Your rating: ' + data.user_rating + '/5</small>');
        
        var $stars = $card.find('.mr-stars');
        updateStarDisplay($stars, data.user_rating);
        $stars.attr('data-user-rating', data.user_rating);
        $stars.attr('data-average', data.average);
    }

    function showMessage($card, message, type) {
        $card.find('.rating-message').remove();
        
        var $message = $('<div class="rating-message rating-' + type + '">' + message + '</div>');
        $card.find('.mr-movie-info').prepend($message);
        
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    function initSliders() {
        $('.mr-movie-slider-wrapper').each(function() {
            var $wrapper = $(this);
            var $container = $wrapper.find('.mr-movie-container');
            var $leftBtn = $wrapper.find('.mr-slider-left');
            var $rightBtn = $wrapper.find('.mr-slider-right');

            function getScrollAmount() {
                var cardWidth = $container.find('.mr-movie-card').first().outerWidth(true);
                return cardWidth || 270;
            }

            $leftBtn.on('click', function() {
                var scrollAmount = getScrollAmount();
                var newScroll = $container.scrollLeft() - scrollAmount;
                $container.animate({ scrollLeft: newScroll }, 300);
            });

            $rightBtn.on('click', function() {
                var scrollAmount = getScrollAmount();
                var newScroll = $container.scrollLeft() + scrollAmount;
                $container.animate({ scrollLeft: newScroll }, 300);
            });
        });
    }

})(jQuery);