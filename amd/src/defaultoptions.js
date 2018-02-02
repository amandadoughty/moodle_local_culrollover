/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module local_culrollover/defaultoptions
  */

define(['jquery'], function($) {
    return {
        initialise: function () {
            $('#fitem_id_defaultmigrateondate').hide();
            $('#fitem_id_defaultvisibleondate').hide();

            $('#id_defaultwhen_1').change(function(){
                var c = this.checked ? $('#fitem_id_defaultmigrateondate').show() : $('#fitem_id_defaultmigrateondate').hide();
            });
            $('#id_defaultwhen_0').change(function(){
                var c = this.checked ? $('#fitem_id_defaultmigrateondate').hide() : $('#fitem_id_defaultmigrateondate').show();
            });

            $('#id_defaultvisible_2').change(function(){
                var c = this.checked ? $('#fitem_id_defaultvisibleondate').show() : $('#fitem_id_defaultvisibleondate').hide();
            });
            $('#id_defaultvisible_1').change(function(){
                var c = this.checked ? $('#fitem_id_defaultvisibleondate').hide() : $('#fitem_id_defaultvisibleondate').show();
            });
            $('#id_defaultvisible_0').change(function(){
                var c = this.checked ? $('#fitem_id_defaultvisibleondate').hide() : $('#fitem_id_defaultvisibleondate').show();
            });
        }
    };
});