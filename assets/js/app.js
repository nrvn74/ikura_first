;(function($, window, document, undefined){

    "use strict";

    function setProperties(){
        $(':root').css({
            '--header-height': $('header').innerHeight() + 'px',
            '--header-innerHeight': $('header .header-inner').innerHeight() + 'px',
        });
    
        $('.menu-head').css('min-height', $('.menu-head').innerHeight() + 'px');
    }
    
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
        let offset = categories.offset().top - $('header').innerHeight();
        let isAppended = false, isMenuHidden = false, lastScroll = 0, delta = 5;
    
        $(window).scroll(function(){
            let scroll = $(window).scrollTop();

            if(scroll >= offset && !isAppended){
                $('header').append(categories);
                $('header').addClass('has-categories').addClass('menu-hidden');
    
                isAppended = true;
                isMenuHidden = true;
            } else if(scroll < offset && isAppended){
                $('.menu-head').append(categories);
                $('header').removeClass('has-categories').removeClass('menu-hidden');
    
                isAppended = false;
                isMenuHidden = false;
            }
            
            if(isAppended && Math.abs(lastScroll - scroll) >= delta){
                if (scroll > lastScroll && !isMenuHidden){
                    $('header').addClass('menu-hidden');
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
    
    $(document).on('click', '.add-to-cart-button', function(){
        if($(this).hasClass('added')) return;
    
        $(this).addClass('added');
    });
    
    $(document).ready(function(){
        setProperties();
    
        attachMenuCategories();
    
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
        })
    
        $('body').click(function(e){
            if($(e.target).is('.modal-inner')){
                $(e.target).parent().toggleClass('open').fadeToggle(300);
            }
        }); 
    });

})(jQuery, window, document);