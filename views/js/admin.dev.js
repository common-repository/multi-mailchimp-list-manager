/**
 * MultiMailChimp admin JavaScript library
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.3
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/JavaScript
 */

/**
 * Fetch lists for API Key
 */
var mmc_fetchLists = function(settings) {
    settings = jQuery.extend({
        apiKey: '',
        containerId: '#mmc_listsContainer',
        ajaxLoaderId: '#mmc_ajaxLoader',
        optionName: 'mmc_lists_ids',
        descriptionOptionName: 'mmc_list_descriptions',
        checkedValues: []
    }, settings);
    jQuery(settings.containerId).empty();
    jQuery(settings.ajaxLoaderId).show();
    jQuery.ajax({
            dataType: 'json',
            type: 'POST',
            data: {'ajax':1, 'mmc_api_key': settings.apiKey},
            success: function(data) {
                jQuery(settings.ajaxLoaderId).hide();
                if (data.success==1) {
                var elem, i;
                for (i in data.data) {
                    elem = jQuery('<input/>').attr('type', 'checkbox')
                        .attr('value', i)
                        .attr('name', 'options['+settings.optionName+'][]')
                        .attr('id', 'mmc_option_'+i);
                    if (jQuery.inArray(i, settings.checkedValues)>-1)
                        elem.attr('checked', 'checked');
                    jQuery(settings.containerId).append(elem).append('<label class="mmc_listName_label" for="mmc_option_'+i+'">'+data.data[i].name+",</label> ")
                    .append('<label class="mmc_listDescription_label" for="mmc_option_description_'+i+'">Description:</label><input type="text" name="options['+settings.descriptionOptionName+']['+i+']" value="'+data.data[i].description+'" /> <br />');
                }
                } else {
                    jQuery(settings.containerId).html('<div class="error">'+data.message+'</div>');
                }
            }
        });
}