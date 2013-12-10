# OpenSpending - WordPress plugin

WordPress plugin to make openspendingjs visualisations easy to add to WordPress posts and pages.

## Installation

1. Add the openspending directory into your WordPress instance's plugin
   directory (usually *wp-content/plugins/*).
2. Go to your admin panel, navigate to plugins, find OpenSpending and
   activate it.

## How to Use

The OpenSpending WordPress plugin operates using the standard WordPress *shortcodes*, namely:

    [openspending]

Three attributes **must** be defined:

* *type* - The type of visualisation to use
* *dataset* - The dataset on openspending.org to use
* *drilldowns* - Dimensions of the dataset to drill down into

If you would like to add a *treemap* for the dimensions *from* and *to* in the [*ukgov-25k-spending* dataset](http://openspending.org/ukgov-25k-spending) on OpenSpending the only thing you need to add to your post or page is:

    [openspending type="treemap" dataset="ukgov-25k-spending" dimensions="from,to"]

## Supported Visualisations

* Treemap (type: "treemap")
* Bubbletree (type: "bubbletree")
    * Supported icons/colors for COFOG

## FAQ

###How to change the color of the main bubbles in the bubble chart?
This would require javascript overwrite. It would be difficult to add onto wordpress ui.

###How to change line height of the bubble titles?
Would require css overwrite. It would difficult to add onto wordpress ui.

###How to display amounts in Millions instead of Billions?
Would require javascript overwrite (is possible, but difficult to add onto wordpress ui)

###How to change the assignment of bubble background images?
Wordpress often stops users from adding javascripts files so it will require code changes to wordpress itself (or the theme)

###How to add a secondary currency, like US dollars and have it be shown in the bubbles or in the pop-up window?
Unfortunately you cannot do this at the moment in OpenSpending. This would need a customised improvement.You can however do a separate budget in USD dollars and visualise that.
