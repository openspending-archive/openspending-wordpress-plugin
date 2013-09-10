<?php
  /*  Copyright 2013 Open Knowledge Foundation ( http://okfn.org )

      This program is free software; you can redistribute it and/or modify
      it under the terms of the GNU General Public License, version 2, as 
      published by the Free Software Foundation.

      This program is distributed in the hope that it will be useful,
      but WITHOUT ANY WARRANTY; without even the implied warranty of
      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
      GNU General Public License for more details.

      You should have received a copy of the GNU General Public License
      along with this program; if not, write to the Free Software
      Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  */

  // Load WordPress functions via the load-wordpress file
  require_once( dirname(__FILE__) .'/load-wordpress.php');

  // Get the user's layout (for the colors)
  global $user_ID;
  $layout = get_user_meta($user_ID, 'admin_color', true);

  // Enqueue the stylesheets and scripts we'll be needing
  wp_enqueue_style( "colors-{$layout}" );
  wp_enqueue_style( "buttons" );
  wp_enqueue_style( 'ie' );
  wp_enqueue_style( 'popup-specific',
                    plugins_url('openspending/css/popup.css') );
  wp_enqueue_script( 'jquery' );
?>
<!DOCTYPE html>
<head>
  <title>OpenSpending - Add your visualisation</title>
  <?php do_action('admin_print_styles'); ?>
  <?php do_action('admin_print_scripts'); ?>
  <script type="text/javascript" src="<?php echo includes_url('js/tinymce/tiny_mce_popup.js'); ?>"></script>
</head>
<body class="wp-core-ui">

<form id="openspending-shortcode">
  <p>
    <span class="type">
        <img src="../img/treemap-icon.png"><br>
        <label for="type">Treemap <input type="radio" name="type" value="treemap" checked></label>
    </span>
  </p>
  <p>
    <label for="dataset">Dataset:</label>
    <span class="url">http://openspending.org/</span>
    <input id="dataset" name="dataset" type="text" value="" placeholder="ukgov-25k-spending" />
  </p>
  <p id="drilldowns">
    <label for="drilldowns">Drilldowns:</label><br/>
    <a class="button button-disabled" href="#" id="fetch-drilldowns">Fetch possible drilldowns</a>
    <div id="dimensions"></div>

    <label for="dimension" id="dimension-label" style="display:none;">Chosen drilldowns:</label><br/>
    <div id="chosen-dimensions"></div>
  </p>
  <hr/>
  <p>
    <a id="add-visualisation" class="button button-primary button-large button-primary-disabled" href="#">
      Add visualisation
    </a>
  </p>
</form>

<script type="text/javascript">
  (function($) {
    // User presses "the big blue button" to add the shortcode to the page/post
    $('#add-visualisation').click(function (e) {
      // Get type of visualisation and do nothing if none is defined
      var visualisation = $('input[name=type]:checked').val();
      if (visualisation === '')
        return false;

      // Get dataset and do nothing if not defined
      var dataset = $('#dataset').val();
      if (dataset === '')
        return false;

      // Get all drilldowns into an array and do nothing if there are none
      var drilldowns = [];
      $.each($('input[name=drilldown]'), function(idx, item) {
        drilldowns.push($(this).val());
      });
      if (drilldowns.length === 0)
        return false;

      // Since we have everything we add the shortcode to page/post
      tinyMCEPopup.editor.execCommand('mceInsertContent', false,
        ['[openspending type="', visualisation,
         '" dataset="',dataset,
         '" drilldowns="', drilldowns.join(','),
         '"]'
        ].join('') );

      // Close the popup
      tinyMCEPopup.close();
      return false;
    });

    // Fetch drilldowns button plays a big part so we'll just search for it once
    var $fetcher = $('#fetch-drilldowns');

    // When a user presses a key in dataset we activate the fetch drilldown
    // button since the user is now able to fetch drilldowns
    $('#dataset').keypress(function(e) {
        $fetcher.removeClass('button-disabled');
    });

    // When a user clicks the button to fetch drilldowns a lot of stuff happens
    $fetcher.click(function(e) {
      // We add a spinner/loading gif to dimensions (to show we're working)
      var $dimensions = $('#dimensions');
      $dimensions.html('<img src="<?php echo includes_url('images/wpspin.gif'); ?>">');

      // Get the dataset, if the dataset field is empty (for example if the user
      // pressed the deactivated button) we tell the user, no dataset was
      // supplied
      var dataset = $('#dataset').val();
      if (dataset === '') {
          $dimensions.text('No dataset supplied');
      }
      // But if it has been supplied, we go out and fetch it's dimensions
      else {
        // Get the dimension via a specific call to openspending.org
        $.getJSON('http://openspending.org/'+dataset+'/dimensions.json')
          .done(function(data) { // Successful fetching
             // Disable the fetching button (it's been used)
            $fetcher.addClass('button-disabled');
              // Create an array of possible dimensions and fill it with
              // Dimension links (a tags). The links store the key itself
              // in a data attribute
              var possible_dimensions = [];
              $.each(data, function(idx, dimension) {
                var dimension_link = ['<a href="#" data-dimension="',
                                      dimension.key,
                                      '" class="possible-dimension">',
                                      dimension.label,
                                      '</a>'
                                     ];
                possible_dimensions.push(dimension_link.join(''));
              });

              // Add the possible dimensions along with a header to the page
              $dimensions.append('Choose dimensions in drilldown order<br/>');
              $dimensions.html(possible_dimensions.join(', '));
            })
            .fail(function() { // Something went boo boo!
              // Let the user know
              $dimensions.text('Could not fetch dimensions of dataset');
            });

        // Possible dimensions are clickable (but since they're added live
        // we need to treat them as such)
        $('.possible-dimension').live('click', function(e) {
          // We add the clicked dimension to a chosen dimensions field
          var $dimension = $(this);
          var $chosen = $('#chosen-dimensions');
          $('#dimension-label').show();

          // The real input of the form (we could use something simpler but
          // since we need something to hold the value, why not the proper
          // one)?
          var $input = $(['<input name="drilldown" type="text" value="',
                          $dimension.attr('data-dimension'),
                          '">'].join(''));

          // The label to show the user
          var $text = $('<span class="dimension-label"/>')
                        .append($dimension.text());

          // Make it possible for the user to remove the chosen dimension
          $erase = $('<a href="#" class="delete">&times;</a>');

          // Add the choice to the chosen dimensions
          $choice = $('<span class="chosen-dimension"/>')
                      .append($text)
                      .append($input)
                      .append($erase);

          $chosen.append($choice);

          // Since we've added something now we can activate the
          // "Add visualisations button"
          $('#add-visualisation').removeClass('button-primary-disabled');
        });

        // User can remove a chosen dimension (which is added live)
        $('.delete').live('click', function(e) {
          $(this).parent().remove();
          return false;
        });
      }
      return false;
    });
  })(jQuery);
</script>
</body>
</html>
