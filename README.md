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
