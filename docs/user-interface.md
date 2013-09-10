# Adding an OpenSpending visualisation via the editor

After installing the OpenSpending WordPress plugin a small button with an OS logo appears in the editor:

![OS button in editor](openspending-button-tinymce.png)

Click this button to add a visualisation. You will be prompted by a popup window from which you can type in the needed configurations for your visualisation:

![OpenSpending configuration popup](openspending-popup-default.png)

Choose your type of visualisation (at the moment only the treemap is supported) and type in the identifier for your dataset. The identifier you can get from the OpenSpending url.

For example, if you're dataset lives at http://openspending.org/ukgov-25k-spending you would type in ukgov-25k-spending as the dataset.

When you have filled in your dataset click on the button called *Fetch drilldowns*. This will get the available drilldown dimensions for you:

![Drilldowns fetched for supplied dataset](openspending-drilldowns-fetched.png)

Now, click on the dimensions you want to drilldown into **in the order you want the drilldown to happen**. You will see the dimensions and the order below the list of available dimensions (as *Chosen dimensions*):

![Chosen drilldown dimensions](openspending-drilldowns-chosen.png)

After choosing your drilldown dimensions click *Add visualisations*. This will add the so-called *shortcode* to your blog post. The shortcode has the form:

    [openspending type="treemap" dataset="my-dataset" dimensions="from,to"]

This is what creates the visualisation on your page (so you can move that square bracket along with its content around the page if you want):

![Shortcode added to the page](openspending-shortcode-added.png)

Now you can publish your page or post and have a look. You'll see your visualisation added exactly where you want it to be:

![Beautifully rendered OpenSpending visualisation](openspending-bosnian-treemap.png)

