/**
 * Arie Nugraha 2009
 * SLiMS admin related functions
 *
 * Require : jQuery library
 * Require : updater.js
 * Require : gui.js
 * Require : form.js
 */



/* AJAX plugins for SLiMS */
jQuery.fn.registerAdminEvents = function(params) {
    // set some options
    var options = {
        ajaxifyLink: true,
        ajaxifyForm: true
    };
    jQuery.extend(options, params);

    // cache AJAX container
    var container = $(this);

    if (options.ajaxifyLink) {
        // change all anchor behaviour to AJAX in main content
        container.find('a:not(.notAJAX)').click(function(evt) {
            evt.preventDefault();
            var anchor = $(this);
            // get anchor href
            var url = anchor.attr('href');
            var postData = anchor.attr('postdata');
            var loadContainer = anchor.attr('loadcontainer');
            if (loadContainer) {
                container = jQuery('#'+loadContainer);
            }
            // set ajax
            if (postData) {
                container.simbioAJAX(url, {method: 'post', addData: postData});
            } else {
                container.simbioAJAX(url, {addData: {ajaxload: 1}});
            }
        });
    }

    // set all table with class datagrid
    container.find('table.datagrid,#dataList').each(function() {
        var datagrid = $(this);
        datagrid.simbioTable();
        // register uncheck click event
        $('.uncheck-all').click(function() {
            jQuery.unCheckAll('.datagrid,#dataList');
        });
        // register check click event
        $('.check-all').click(function() {
            jQuery.checkAll('.datagrid,#dataList');
        });
        // set all row to show detail when double clicked
        datagrid.children('thead,tbody,tfoot').children('tr').each( function() {
            var tRow = $(this);
            var rowLink = tRow.css({'cursor' : 'pointer'}).find('.editLink');
            if (rowLink[0] != undefined) {
                tRow.dblclick(function() {$(rowLink[0]).trigger('click')});
            }
        });
        // unregister event for table-header
        $('.table-header', datagrid).parent().unbind();
    });

    // change all search form submit behaviour to AJAX
    container.find('form.disabled').disableForm();

    // change all search form submit behaviour to AJAX
    container.find('.editFormLink').click(function(evt) {
        evt.preventDefault();
        var theForm = $(this).parents('form').enableForm().find('input,textarea').not(':submit,:button').first().focus();
        $('.makeHidden').removeClass('makeHidden');
        // enable hidden delete form
        container.find('#deleteForm').enableForm();
    });

    if (options.ajaxifyForm) {
        // change all search form submit behaviour to AJAX
        container.find('.menuBox form:not(.notAJAX)').submit(function(evt) {
            var theForm = $(this);
            if (theForm.attr('target')) {
                theForm[0].submit();
                return;
            }
            evt.preventDefault();
            var formAction = theForm.attr('action');
            var formMethod = theForm.attr('method');
            var formData = theForm.serialize();
            var loadContainer = theForm.attr('loadcontainer');
            if (loadContainer) {
                container = jQuery('#'+loadContainer);
            }
            container.simbioAJAX(formAction, {method: formMethod, addData: formData});
        });
    }

    // focus first element
    container.find('input[type=text]:first').focus();
    // focus first form element
    var mainForm = container.find('#mainForm'); if (mainForm.length > 0) { mainForm.find('input,textarea').not(':submit,:button').first().focus(); }
    // disable form marked with 'disabled' class
    container.find('form.disabled').disableForm().find('.cancelButton').removeAttr('disabled').click(function() {
        jQuery.ajaxPrevious(2);
    });

    container.find('input.tab').click(function() {
        container.find('input.tab').removeClass('tabSelected');
        var tabButton = $(this).addClass('tabSelected');
        var tabSrc = tabButton.attr('src');
        if (tabSrc) {
            // set iframe content
            setIframeContent('listsFrame', tabSrc);
        }
    });

    return container;
}

/**
 * Register all events
 */
$('document').ready(function() {
    // register menu and submenu event
    $('.mod-menu,.mod-submenu-item').not('.notAJAX').click(function(evt) {
        evt.preventDefault();
        var menus = $(this)
        if ($(this).hasClass('mod-menu')) {
            $('.mod-submenu').hide();
            $(this).next('.mod-submenu').slideDown('fast');
            // remove other menu class
            $('.mod-menu').removeClass('curr-module');
            menus.addClass('curr-module');
        } else {
            $('.mod-submenu-item').removeClass('current');
            menus.addClass('current');
        }
        var menuHREF = menus.attr('href');
        $('#mainContent').simbioAJAX(menuHREF, {method: 'get'});
    });

    $('.curr-module').next('.mod-submenu').slideDown('slow');

    // Register admin event for AJAX event
    $('#mainContent,#pageContent').bind('simbioAJAXloaded', function(evt) {
        $(this).registerAdminEvents({ajaxifyLink: true, ajaxifyForm: true});
        // report filter
        $('#filterForm').children('.divRow:gt(0)').wrapAll('<div class="hiddenFilter"></div>');
        var hiddenFilter = $('.hiddenFilter').hide();
        $('[name=moreFilter]').toggle(function() { hiddenFilter.fadeIn(); }, function() { hiddenFilter.hide(); });
    });

    // disable form with class "disabled"
    $('form.disabled').disableForm();
    $(document).registerAdminEvents({ajaxifyLink: false, ajaxifyForm: false});
});
