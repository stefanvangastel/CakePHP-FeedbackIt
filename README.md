Website: [http://stefanvangastel.nl/feedbackitdemo/](http://stefanvangastel.nl/feedbackitdemo/)

##### Table of Contents  
* [Intro](#intro)  
* [Requirements](#requirements)  
* [Installation and setup](#installation)  
* [Usage / Demo](#usage) 
* [Examples](#examples)  

<a name="intro"/>
## Intro

This CakePHP plugin provides a static feedback tab on the side of the screen that enables website visitor to submit feedback or bugreports.
Features pure client-side screenshot function including user-placed highlight / accent.

**Currently saves the following on form submit:**

* Name of sender (optional, can work with AuthComponent)
* E-mail of sender (optional)
* Subject
* Message
* Current URL
* Screenshot of body DOM element
* Browser and browser version
* User OS flavor

<a name="saveoptions"/>
**Save options include (configurable):**

* Filesystem
* [Mantis Bugtracker](http://www.mantisbt.org/)
* [GitHub (repo issues)](https://help.github.com/articles/github-glossary#issue)
* Email
* [Bitbucket (repo issues)](https://confluence.atlassian.com/display/BITBUCKET/Use+the+issue+tracker)
* [Jira](https://www.atlassian.com/software/jira)
* [Redmine](http://www.redmine.org)

<a name="requirements"/>
## Requirements

This plugin is CakePHP Security component compatible.

**Required:**

* [jQuery](http://jquery.com/)

**Optional:**

* [Bootstrap](http://getbootstrap.com) (Bootstrap 2 and 3 compatible)

**Includes:**

* [html2canvas.js by niklasvh](https://github.com/niklasvh/html2canvas)

<a name="installation"/>
## Installation and Setup

1. Check out a copy of the FeedbackIt CakePHP plugin from the repository using Git :

	`git clone http://github.com/stefanvangastel/CakePHP-FeedbackIt.git`

	or download the archive from Github: 

	`https://github.com/stefanvangastel/CakePHP-FeedbackIt/archive/master.zip`

	You must place the FeedbackIt CakePHP plugin within your CakePHP 2.x app/Plugin directory.
	
	or load it with composer:
	
	`"stefanvangastel/feedback-it": "dev-master"`

2. Load the plugin in app/Config/bootstrap.php:

	`CakePlugin::load('FeedbackIt');`

3. Copy the default feedbackit-config file:

	Copy `../app/Plugin/FeedbackIt/Config/feedbackit-config.php.default` to `../app/Plugin/FeedbackIt/Config/feedbackit-config.php`

	And adjust it to your needs.

4. Use the feedbackbar element in a view or layout to place the feedback tab on that (or those) pages. It doesn't matter where you place the following line since it uses absolute DOM element positioning.

	`<?php echo $this->element('FeedbackIt.feedbackbar');?>`

<a name="usage"/>
## Usage

To testdrive this puppy; [http://stefanvangastel.nl/feedbackitdemo/](http://stefanvangastel.nl/feedbackitdemo) 

<a name="examples"/>
## Examples

![Example of form](https://raw.github.com/stefanvangastel/CakePHP-FeedbackIt/master/examples/feedbackit_1.png "Example of form")
![Example of result](https://raw.github.com/stefanvangastel/CakePHP-FeedbackIt/master/examples/feedbackit_2.png "Example of result")

End
