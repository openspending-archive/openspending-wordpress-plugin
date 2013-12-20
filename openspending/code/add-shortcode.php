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

  // This function takes care of loading the assets from WordPress via a
  // request to the site with a special query variable. The plugin itself
  // checks for this query variable and makes WordPress return the asset
  // if the plugin has defined it. To print the value it's enough to just
  // call print_asset('spinner'); to get the spinner asset (url to spinner)
  function print_asset($asset)
  {
    // The request submits the query var to the same server so we construct
    // the server protocol and name from the $_SERVER variable (we use
    // HTTP_HOST to include port number if there is one
    $home = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    // We print out the contents of the home page with the special plugin
    // query variable (with value we pass in)
    echo file_get_contents($home . '/?openspending-plugin-asset=' . $asset);
  }
?>
<!DOCTYPE html>
<head>
  <title>OpenSpending - Add your visualisation</title>
  <?php print_asset('styles'); ?>
  <?php print_asset('scripts'); ?>
  <script type="text/javascript" src="<?php print_asset('tinymce'); ?>"></script>
</head>
<body class="wp-core-ui">
<form id="openspending-shortcode">
  <p>
    <span class="type">
        <img src="../img/treemap-icon.png"><br>
        <label for="type">Treemap <input type="radio" name="type" value="treemap" checked></label>
    </span>
    <span class="type">
        <img src="../img/bubbletree-icon.png"><br>
        <label for="type">Bubbletree <input type="radio" name="type" value="bubbletree"></label>
    </span>
    <span class="type">
        <img src="../img/barchart-icon.png"><br>
        <label for="type">Barchart <input type="radio" name="type" value="barchart"></label>
    </span>
  </p>
  <p>
    <label for="dataset">Dataset:</label>
    <span class="url">http://openspending.org/</span>
    <input id="dataset" name="dataset" type="text" value="" placeholder="ukgov-25k-spending" />
  </p>
  <p id="drilldowns">
    <label for="drilldowns">Drilldowns</label><br/>
    <a class="button button-disabled" href="#" id="fetch-drilldowns">Fetch possible drilldowns</a>
    <div id="dimensions"></div>

    <label for="dimension" id="dimension-label" style="display:none;">Chosen drilldowns:</label><br/>
    <div id="chosen-dimensions"></div>
  </p>

  <p>
    <label for="year">Year <small>(optional)</small></label><br/>
    <a class="button button-disabled" href="#" id="fetch-years">Fetch available years</a>
    <div id="years"></div>
    <input id="year" type="hidden" name="year" value=""/>
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

      var year = $('#year').val();

      var shortcode_attrs = [' type="', visualisation, '"',
                             ' dataset="', dataset, '"',
                             ' drilldowns="', drilldowns.join(','), '"',
                            ];
      if (year !== '') {
        shortcode_attrs.push(' year="'+year+'"');
      }

      // Since we have everything we add the shortcode to page/post
      tinyMCEPopup.editor.execCommand('mceInsertContent', false,
        '[openspending'+shortcode_attrs.join('')+']');

      // Close the popup
      tinyMCEPopup.close();
      return false;
    });

    // Fetch buttons play a big part so we'll just search for them once
    var $drilldown_fetcher = $('#fetch-drilldowns');
    var $year_fetcher = $('#fetch-years');

    // When a user presses a key in dataset we activate the fetch drilldown
    // button since the user is now able to fetch drilldowns
    $('#dataset').keypress(function(e) {
        $drilldown_fetcher.removeClass('button-disabled');
        $year_fetcher.removeClass('button-disabled');
    });

    // When a user clicks the button to fetch drilldowns a lot of stuff happens
    $drilldown_fetcher.click(function(e) {
      // We add a spinner/loading gif to dimensions (to show we're working)
      var $dimensions = $('#dimensions');
      $dimensions.html('<img src="<?php print_asset('spinner'); ?>">');

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
        $.getJSON('https://openspending.org/'+dataset+'/dimensions.json')
          .done(function(data) { // Successful fetching
             // Disable the fetching button (it's been used)
            $drilldown_fetcher.addClass('button-disabled');
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

              // Sort the dimensions for the user
              possible_dimensions.sort(function(a,b) {
                // We sort them according to labels, not dimension.keys
                var label_a = a.split('>')[1];
                var label_b = b.split('>')[1];

                if (label_a < label_b) return -1;
                if (label_a > label_b) return 1;
                return 0;
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
          var $input = $(['<input name="drilldown" type="hidden" value="',
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

    // When a user clicks the button to fetch years we populate the field
    $year_fetcher.click(function(e) {
      // We add a spinner/loading gif to years (to show we're working)
      var $years = $('#years');
      $years.html('<img src="<?php print_asset('spinner'); ?>">');

      // Get the dataset, if the dataset field is empty (for example if the user
      // pressed the deactivated button) we tell the user, no dataset was
      // supplied
      var dataset = $('#dataset').val();
      if (dataset === '') {
        $years.text('No dataset supplied');
      }
      else {
        // Get the dimension via a specific call to openspending.org
        $.getJSON('https://openspending.org/'+dataset+'/time.distinct')
          .done(function(data) { // Successful fetching
            // Disable the fetching button (it's been used)
            $year_fetcher.addClass('button-disabled');
            // Create an array of possible years and fill it with
            // Year links (a tags).
            var possible_years = [];
            // Since the data can have many dates per year and we're only
            // interested in the year, we create a "set" (object) to hold
            // the years (years are found in year element of each obj
            // in the data.results)
            var year_data = {};
            $.each(data.results, function(idx, time) {
              year_data[time.year] = true;
            });

            // Now we can loop through the keys of year_data to get the
            // unique years
            $.each(year_data, function(year, value) {
              var year_link = ['<a href="#" class="possible-year">',
                               year, '</a>'];
              possible_years.push(year_link.join(''));
            });

            // Sort the years for better user experience
            possible_years.sort()

            // Add the possible dimensions along with a header to the page
            $years.append('Click on the year to use<br/>');
            $years.html(possible_years.join(' '));
          })
          .fail(function() { // Something went boo boo!
            // Let the user know
            $years.text('Could not fetch years for the dataset');
          });
      }
      $('.possible-year').live('click', function(e) {
        var $year = $(this);
        // Unselect if previously chosen
        if ($year.hasClass('chosen')) {
          // Not chosen anymore
          $year.removeClass('chosen');
          // Reset the value
          $('input[name="year"]').val('')
        }
        // Select this year
        else {
          // Unselect other years
          $('.possible-year').removeClass('chosen');
          // Select this one and use its value
          $year.addClass('chosen');
          $('input[name="year"]').val($year.text());
        }

        return false;
      });

      return false;
    });
  })(jQuery);
</script>
</body>
</html>
