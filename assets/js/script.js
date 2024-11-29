 
"use strict";
(function() {
    var userAgent = navigator.userAgent.toLowerCase(),
        initialDate = new Date(),
        $document = $(document),
        $window = $(window),
        $html = $("html"),
        $body = $("body"),
        isDesktop = $html.hasClass("desktop"),
        isIE = userAgent.indexOf("msie") !== -1 ? parseInt(userAgent.split("msie")[1], 10) : userAgent.indexOf("trident") !== -1 ? 11 : userAgent.indexOf("edge") !== -1 ? 12 : false,
        isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
        windowReady = false,
        isNoviBuilder = false,
        loaderTimeoutId, plugins = {
            bootstrapTooltip: $("[data-toggle='tooltip']"),

            stepper: $("input[type='number']"),
            wow: $('.wow'),
            owl: $('.owl-carousel'),
            swiper: $('.swiper-slider'),
            slick: $('.slick-slider'),
            search: $('.rd-search'),
            searchResults: $('.rd-search-results'),

            preloader: $('.preloader'),

            customWaypoints: $('[data-custom-scroll-to]'),
            multitoggle: document.querySelectorAll('[data-multitoggle]'),
            themeSwitcher: $('[data-theme-name]')
        };

    

    function lazyInit(element, func) {
        var scrollHandler = function() {
            if ((!element.hasClass('lazy-loaded') && (isScrolledIntoView(element)))) {
                func.call(element);
                element.addClass('lazy-loaded');
            }
        };
        scrollHandler();
        $window.on('scroll', scrollHandler);
    }
    $window.on('load', function() {
        if (plugins.preloader.length && !isNoviBuilder) {
            pageTransition({
                target: document.querySelector('body'),
                delay: 0,
                duration: 500,
                classIn: 'fadeIn',
                classOut: 'fadeOut',
                classActive: 'animated',
                conditions: function(event, link) {
                    return link && !/(\#|javascript:void\(0\)|callto:|tel:|mailto:|:\/\/)/.test(link) && !event.currentTarget.hasAttribute('data-lightgallery');
                },
                onTransitionStart: function(options) {
                    setTimeout(function() {
                        plugins.preloader.removeClass('loaded');
                    }, options.duration * .75);
                },
                onReady: function() {
                    plugins.preloader.addClass('loaded');
                    windowReady = true;
                }
            });
        }   
        if (plugins.search.length || plugins.searchResults) {
            var handler = "bat/rd-search.php";
            var defaultTemplate = '<h5 class="search-title"><a target="_top" href="#{href}" class="search-link">#{title}</a></h5>' +
                '<p>...#{token}...</p>' +
                '<p class="match"><em>Terms matched: #{count} - URL: #{href}</em></p>';
            var defaultFilter = '*.html';
            if (plugins.search.length) {
                for (var i = 0; i < plugins.search.length; i++) {
                    var searchItem = $(plugins.search[i]),
                        options = {
                            element: searchItem,
                            filter: (searchItem.attr('data-search-filter')) ? searchItem.attr('data-search-filter') : defaultFilter,
                            template: (searchItem.attr('data-search-template')) ? searchItem.attr('data-search-template') : defaultTemplate,
                            live: (searchItem.attr('data-search-live')) ? searchItem.attr('data-search-live') : false,
                            liveCount: (searchItem.attr('data-search-live-count')) ? parseInt(searchItem.attr('data-search-live'), 10) : 4,
                            current: 0,
                            processed: 0,
                            timer: {}
                        };
                    var $toggle = $('.rd-navbar-search-toggle');
                    if ($toggle.length) {
                        $toggle.on('click', (function(searchItem) {
                            return function() {
                                if (!($(this).hasClass('active'))) {
                                    searchItem.find('input').val('').trigger('propertychange');
                                }
                            }
                        })(searchItem));
                    }
                    if (options.live) {
                        var clearHandler = false;
                        searchItem.find('input').on("input propertychange", $.proxy(function() {
                            this.term = this.element.find('input').val().trim();
                            this.spin = this.element.find('.input-group-addon');
                            clearTimeout(this.timer);
                            if (this.term.length > 2) {
                                this.timer = setTimeout(liveSearch(this), 200);
                                if (clearHandler === false) {
                                    clearHandler = true;
                                    $body.on("click", function(e) {
                                        if ($(e.toElement).parents('.rd-search').length === 0) {
                                            $('#rd-search-results-live').addClass('cleared').html('');
                                        }
                                    })
                                }
                            } else if (this.term.length === 0) {
                                $('#' + this.live).addClass('cleared').html('');
                            }
                        }, options, this));
                    }
                    searchItem.submit($.proxy(function() {
                        $('<input />').attr('type', 'hidden').attr('name', "filter").attr('value', this.filter).appendTo(this.element);
                        return true;
                    }, options, this))
                }
            }
             
           
        }
         
        
    });
}());



 
/**
 * @module       PageTransition
 * @author       Roman Kravchuk (JeremyLuis)
 * @license      MIT
 * @version      1.1.3
 * @description  Smooth transition between pages
 * @requires     module:jQuery
 */
function pageTransition(t){t=t||{},t.target=t.target||null,t.delay=t.delay||500,t.duration=t.duration||1e3,t.classIn=t.classIn||null,t.classOut=t.classOut||null,t.classActive=t.classActive||null,t.onReady=t.onReady||null,t.onTransitionStart=t.onTransitionStart||null,t.onTransitionEnd=t.onTransitionEnd||null,t.conditions=t.conditions||function(t,n){return!/(\#|callto:|tel:|mailto:|:\/\/)/.test(n)},t.target&&(setTimeout(function(){t.onReady&&t.onReady(t),t.classIn&&t.target.classList.add(t.classIn),t.classActive&&t.target.classList.add(t.classActive),t.duration&&(t.target.style.animationDuration=t.duration+"ms"),t.target.addEventListener("animationstart",function(){setTimeout(function(){t.classIn&&t.target.classList.remove(t.classIn),t.onTransitionEnd&&t.onTransitionEnd(t)},t.duration)})},t.delay),$("a").click(function(n){var a=n.currentTarget.getAttribute("href");if(t.conditions(n,a)){var s=this.href;n.preventDefault(),t.onTransitionStart&&t.onTransitionStart(t),t.classIn&&t.target.classList.remove(t.classIn),t.classOut&&t.target.classList.add(t.classOut),setTimeout(function(){window.location=s,/firefox/i.test(navigator.userAgent)&&setTimeout(function(){t.onReady&&t.onReady(t),t.classOut&&t.target.classList.remove(t.classOut)},1e3),/safari/i.test(navigator.userAgent)&&!/chrome/i.test(navigator.userAgent)&&(t.onReady&&t.onReady(t),t.classOut&&t.target.classList.remove(t.classOut))},t.duration)}}))}
 
