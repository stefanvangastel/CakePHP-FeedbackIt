# FeedbackIt
- - -
# Intro

This CakePHP plugin provides a static feedback tab on the side of the screen that enables website visitor to submit feedback or bugreports.
Features pure client-side screenshot function including user-placed highlight / accent.

# Requirements

**Required:**

* [jQuery](http://jquery.com/)

**Optional:**

* [Bootstrap](http://getbootstrap.com/2.3.2/) TODO: Upgrade code to use Bootstrap 3.x

# Installation and Setup

1. Check out a copy of the FeedbackIt CakePHP plugin from the repository using Git :

	```git clone http://github.com/stefanvangastel/CakePHP-FeedbackIt.git```

or download the archive from Github: 

	```https://github.com/stefanvangastel/CakePHP-FeedbackIt/archive/master.zip```

You must place the FeedbackIt CakePHP plugin within your CakePHP 2.x app/Plugin directory.

2. Load the plugin in app/Config/bootstrap.php:

	```CakePlugin::load('FeedbackIt');```

3. Use the feedbackbar element in a view or layout to place the feedback tab on that (or those) pages. It doesn't matter where you place the following line since it uses absolute DOM element positioning.

	```<?php echo $this->element('FeedbackIt.feedbackbar');?>```

# Usage

Example controller and view are included!