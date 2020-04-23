(function ($) {
    'use strict';
    var tabIndex = 0;
    var accountId = "5dd48e2324f0994c740192e7";
    var token = "GPZDVPFRFAGPZIVT"; // live = IFWKRODYUFWLASDC, clone = GPZDVPFRFAGPZIVT
    var menus;

    function renderMomentfeedMenus(location_id) {
        var proxy = '';
        var apiUrl = "https://momentfeed-prod.apigee.net/menu/v1/location/" + location_id+ "/menus?auth_token=" + token;
        //var apiUrl = "https://momentfeed-prod.apigee.net/menu/account/" + accountId + "/menus?auth_token=" + token;
        $('#menu_tabs').addClass('loading');
        if (window.location.href.includes('truefoodkitchen.com') == false) proxy = 'https://cors-anywhere.herokuapp.com/';
        $.getJSON(proxy + apiUrl).done(
            function (data) {
                $('#menu_tabs').removeClass('loading');
                menus = data.data;
                $.each(menus, function (i, e) { handleMenu(menus[i], i); });
            }).fail(function (xhr, status, error) { console.error("error: " + xhr.responseText); });
    }
    function handleMenu(menu, menuId) {
        $("ul#menu_tabs").append('<li class="primary_menu_tab ' + (tabIndex === 0 ? "active" : "inactive") + '" role="tab" aria-selected="' + (tabIndex === 0 ? "true" : "false") + '" rel="' + menuId + '" tabindex="' + tabIndex + '" aria-controls="' + menuId + '">' + menu.displayName + '</li > ');
        $("#output").append('<div id="' + menuId + '" class="tabpanel active row" role="tabpanel" aria-hidden="' + (tabIndex === 0 ? "true" : "false") + '" style="' + (tabIndex === 0 ? "display:block" : "display:none") + '" ></div>');
        $("#" + menuId).append("<fieldset class='category_root' id='menu-" + menuId + "' style='padding:2px;'>" + "<legend>" + menu.displayName + "</legend>" + "<div class='category_sections'></div>" + "</fieldset>");
        tabIndex = -1;
        $.each(menu.sections, function (i, e) { handleSection(menu.sections[i], i); });
    }
    function handleSection(section, menuId) {
        console.log(section);
        $("#menu-" + menuId + " .category_sections").append("<div class='col-md-12 col-sm-12 pb-2'>" + "<h3 class='section_name' style='color:#487426; font-size:18px; font-weight:600; margin:4px; padding:4px;'>" + section.displayName + "</h3>" + "<p class='small'>" + section.description + "</p>" + "<ul id='section-" + section.id + "' class='section_items'>" + "</ul>" + "</div>");
        $.each(section.items, function (i, e) { handleItem(section.items[i], section.id); });
    }
    function handleItem(item, sectionId) {
        $("#section-" + sectionId + "").append("<li>" + "<div class='item_container'>" + "<p class='item_name'>" + (item.displayName.indexOf('**') == 0 ? "<img id='seasonal-img' src='/wp-content/uploads/2020/03/icon_menu_seasonal.png'>" : "") + item.displayName.replace('**', '') + "</p>" + ((item.price.amount === "") ? "" : "<p>$" + item.price.amount + "</p>") + "</div>" + "<p class='item_description'>" + item.description + "</p>" + "</li>");
    }
    $('#menu_tabs').on('keydown', function (e) {
        if (e.which === 37 || e.which === 38 || (e.which === 33 && e.ctrlKey)) { //left/up/ctrl+pageup
            var prevItem = $('.primary_menu_tab.active').prev("li");
            if (prevItem.length > 0) {
                prevItem.trigger("click");
                focusTab(prevItem);
                e.preventDefault();
            }
            else {
                var lastItem = $('.primary_menu_tab.active').siblings("li").last();
                lastItem.trigger("click");
                focusTab(lastItem);
                e.preventDefault();
            }
        }
        else if (e.which === 39 || e.which === 40 || (e.which === 34 && e.ctrlKey)) { //right/down/ctrl+pagedown
            var nextItem = $('.primary_menu_tab.active').next("li");
            if (nextItem.length > 0) {
                nextItem.trigger("click");
                focusTab(nextItem);
                e.preventDefault();
            }
            else {
                var firstItem = $('.primary_menu_tab.active').siblings("li").first();
                firstItem.trigger("click");
                focusTab(firstItem);
                e.preventDefault();
            }
        }
    });
    $('#menu_tabs').delegate('.primary_menu_tab', 'focus click', function (event) {
        var target = $(this).attr('rel');
        $("#output").children().hide().attr('aria-hidden', true);
        $("#menu_tabs").children().removeClass('active').addClass('inactive').attr('tabindex', -1).attr('aria-selected', false);
        $(this).removeClass('inactive').addClass('active').attr('tabindex', 0).attr('aria-selected', true);
        $('#' + target).show().attr('aria-hidden', false);
    });
    var initTabPanel = function() {
        $('.primary_menu_tab').keydown(tabListKeyPress).click(tabListClick);
    }
    var tabListKeyPress = function(event) {
        if (event.which === 37 || event.which === 38 || 
            (event.which === 33 && event.ctrlKey)) { //left/up/ctrl+pageup
            var prevItem = $('.primary_menu_tab.active').prev("li");
            if (prevItem.length > 0) {
                focusTab(prevItem);
                event.preventDefault(); 
            }
            else {
                var lastItem = $('.primary_menu_tab.active').siblings("li").last();
                focusTab(lastItem);
                event.preventDefault(); 
            }
        } 
        else if (event.which === 39 || event.which === 40 || (event.which === 34 && event.ctrlKey)) { //right/down/ctrl+pagedown
            var nextItem = $('.primary_menu_tab.active').next("li");
            if (nextItem.length > 0) {
                focusTab(nextItem);
                event.preventDefault(); 
            } 
            else {
                //go to the first one
                var firstItem = $('.primary_menu_tab.active').siblings("li").first();
                focusTab(firstItem);
                event.preventDefault(); 
            }
        }
    }
    var tabListClick = function(e) {
        focusTab($('.primary_menu_tab.active')), $(this).addClass('clicked');
    }
    var focusTab = function(newTab) {
        var activeTab = $('.primary_menu_tab.active');
        activeTab.addClass("inactive").removeClass("active").attr("aria-selected", "false").attr("tabindex", "-1");
        $("#" + activeTab.attr("aria-controls")).addClass("inactive").removeClass("active").attr("aria-hidden", "true");
        newTab.addClass("active").removeClass("inactive").attr("aria-selected", "true").attr("tabindex", "0");
        $("#" + newTab.attr("aria-controls")).addClass("active").removeClass("inactive").attr("aria-hidden", "false");
        newTab.focus();
    }
    $(document).ready(function(){
        // menusArray is defined before this script is run
        if (location_id != null) renderMomentfeedMenus(location_id);
    })
})(jQuery);