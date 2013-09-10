/**
 * Handle: wpYourPluginNameAdmin
 * Version: 0.0.1
 * Deps: jquery
 * Enqueue: true
 */
 
// JavaScript Document
(function() {
    tinymce.PluginManager.add('openspending', function(editor, url) {
        editor.addButton('openspending', {
            title: 'Add an OpenSpending visualisation',
            image: url+'/../img/os.png',
            onclick : function() {
                // Open window
                editor.windowManager.open({
                    title: 'OpenSpending - Add your visualisation',
                    file: url + '/../code/add-shortcode.php',
                    width: 450,
                    height: 450,
	            inline : 1
                }, {
	            plugin_url : url
	        });
            }
        });
    });
})();
