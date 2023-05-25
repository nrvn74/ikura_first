;(function($, window, document, undefined){

    "use strict";

    function moveHistory(){
        console.log('Подменяем историю...');
        history.pushState(null, null, window.top.location.pathname + window.top.location.search);
    }
    
    function preventLeavingSite(){
        moveHistory();
    
        document.addEventListener('backbutton', moveHistory, false);
        window.addEventListener('popstate', moveHistory);
    }
    
    preventLeavingSite();

    function setProperties(){
        $(':root').css({
            '--header-height': $('header').innerHeight() + 'px',
            '--header-inner-height': $('.header .header-inner').innerHeight() + 'px',
        });
    
        $('.menu-head').css('min-height', $('.menu-head').innerHeight() + 'px');
        
        setTimeout(() => {
            $('.menu-search').css('--search-width', $('.menu-search').innerWidth() + 'px');
        }, 500);
    }

    function initPromoSlider(){
        $('#promos-slider').slick({
            dots: true,
            appendDots: $('.promos-wrapper .slider-nav').find('.slider-dots'),
            arrows: true,
            prevArrow: $('.promos-wrapper .slider-nav').find('.prev'),
            nextArrow: $('.promos-wrapper .slider-nav').find('.next'),
            infinite: false,
            speed: 300,
            slidesToShow: 2,
            slidesToScroll: 1,
            swipeToSlide: true,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                    }
                }
            ]
        });
    }

    initPromoSlider();
    
    function scrollToElement(el){
        if(!el) return;
    
        let selPosition = $(el).offset().top - $('header').innerHeight();
        let smoothScrollSupport = 'scrollBehavior' in document.documentElement.style;
    
        if(smoothScrollSupport){
            setTimeout(() => {
                window.scrollTo({
                    top: selPosition,
                    behavior: 'smooth'
                });
            }, 300);
        } else{
            setTimeout(() => {
                $('html, body').animate({
                    scrollTop: selPosition
                }, 500);
            }, 300);
        }
    }
    
    function attachMenuCategories(){
        let categories = $('.menu-head-inner');
        let headerHeight = $('header').innerHeight();
        let offset = categories.offset().top - headerHeight;
        let isMenuTransparent = true, isAppended = false, isMenuHidden = false, lastScroll = 0, delta = 5;
    
        $(window).scroll(function(){
            let scroll = $(window).scrollTop();

            if(scroll >= headerHeight && isMenuTransparent){
                $('header').addClass('header-onmove');
                isMenuTransparent = false;
            } else if(scroll <= headerHeight && !isMenuTransparent){
                $('header').removeClass('header-onmove');
                isMenuTransparent = true;
            }

            if(scroll >= offset && !isAppended){
                $('header').append(categories);
                $('header').addClass('has-categories').addClass('menu-hidden').removeClass('header-open');
    
                isAppended = true;
                isMenuHidden = true;
            } else if(scroll < offset && isAppended){
                $('.menu-head').append(categories);
                $('header').removeClass('has-categories').removeClass('menu-hidden').removeClass('header-open');
    
                isAppended = false;
                isMenuHidden = false;
            }
            
            if(isAppended && Math.abs(lastScroll - scroll) >= delta){
                if (scroll > lastScroll && !isMenuHidden){
                    $('header').addClass('menu-hidden').removeClass('header-open');
                    isMenuHidden = true;
                } else if(scroll < lastScroll && isMenuHidden) {
                    $('header').removeClass('menu-hidden');
                    isMenuHidden = false;
               }
    
            lastScroll = scroll;
            }
    
        });
    }
    
    function makeCategoriesDraggable(){
        const el = document.querySelector('.menu-categories');
        let isDown = false;
        
        el.addEventListener('mousedown', () => { isDown = true; });
        el.addEventListener('mouseleave', () => { isDown = false; });
        el.addEventListener('mouseup', () => { isDown = false; });
    
        el.addEventListener('mousemove', (e) => {
          if(!isDown) return;
    
          e.preventDefault();
          el.scrollLeft -= e.movementX;
        });
    }
    
    makeCategoriesDraggable();
    
    function moveCategoriesOnScroll(){
        const targets = document.querySelectorAll('.dish-block');
    
        if(!targets) return;
    
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5
        };
    
        let observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    let id = entry.target.getAttribute('data-id');
                    let target = document.querySelector('.menu-category-selector[data-id="'+id+'"]');
    
                    $('.menu-category-selector.active').removeClass('active');
                    $(target).addClass('active');
                }
            })
        }, options);
        
        targets.forEach(el => {
            observer.observe(el);
        });
    }
    
    moveCategoriesOnScroll();

    function menuSearch(){
        let wrapper = $('.menu-search');
        let input = $('#menu-search-input');

        let restoreDishes = () => {
            $('#search-empty-text').remove();
            $('.dish-item').show();
            $('.dish-block').show();
        };

        $('.menu-search-toggle').click(() => {
            wrapper.toggleClass('active');

            // if('ontouchstart' in document.documentElement) return;
            input.focus();
        });
        $('.menu-search-clear').click(() => {
            input.val('');
            wrapper.removeClass('active');
            restoreDishes();
        });

        $(input).keyup(function(){
            restoreDishes();
            
            if(input.val().length < 3) return;

            let keyword = input.val();
            let categories = $('.dish-block');
            let visibleCounter = 0;

            if(!categories) return;

            categories.each(function(){
                let hasItems = false;
                let dishItems = $(this).find('.dish-item');

                dishItems.each(function(){
                    let title = $(this).find('div.dish-name').text().toLowerCase();

                    if(title.indexOf(keyword) !== -1){
                        hasItems = true;
                    } else{
                        $(this).hide();
                    }
                });

                if(hasItems === false){
                    $(this).hide()
                } else{
                    visibleCounter++;
                };
            });

            if(visibleCounter === 0 && $('#search-empty-text').length === 0){
                $('main').append('<div id="search-empty-text">Ничего не найдено :(</div>');
            }
        });
    }

    menuSearch();
    
    $(document).on('click', '.add-to-cart-button', function(){
        if($(this).hasClass('added')) return;
    
        $(this).addClass('added');
    });

    $(document).click(e => {
        if($(e.target).is('#nav-toggle') || $(e.target.parentElement).is('#nav-toggle')){
            $('header').toggleClass('header-open');
        }
    });
    
    $(document).ready(function(){
        setProperties();
        attachMenuCategories();

        Fancybox.bind('[data-fancybox]', {
            Toolbar: false,
            closeButton: 'top',
            placeFocusBack: false,
            dragToClose: false,
            animated: false,
            Image: {
                zoom: false,
                click: 'close',
            },
        });
    
        $('.menu-category-selector').click(function(){
            $('.menu-category-selector.active').removeClass('active');
            $(this).toggleClass('active');
    
            let id = $(this).data('id');
            let target = $('.dish-block[data-id="'+id+'"]').get(0);
    
            if(target && target !== undefined){
                scrollToElement(target);
            }
        });
    
        $('.dish-item').click(function(e){
            if($(e.target).hasClass('add-to-cart-button') || $(e.target).hasClass('currency')) return;
    
            let modal = $('.modal-dish .modal-inner');
    
            if(!modal) return;
    
            modal.empty().append($(this).find('.modal-content').clone());
            modal.parent().toggleClass('open').fadeToggle(300);
            $('body').css('overflow-y', 'hidden');
        })
    
        $('body').click(function(e){
            if($(e.target).is('.modal-inner')){
                $('body').css('overflow-y', 'scroll');
                $(e.target).parent().toggleClass('open').fadeToggle(300);
            }

            if($('header').hasClass('header-open') && $(e.target).is('button, nav, li') === false){
                $('header').removeClass('header-open');
            }
        });

        $(document).on('click', '.modal-dish .button-backward', function(){
            $('.modal-dish.open').removeClass('open').fadeToggle(300);
            $('body').css('overflow-y', 'scroll');
        });
    });

})(jQuery, window, document);