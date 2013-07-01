# SandCastle - CMS
SandCastle is a collection of tools for creating a community website/portal. It is currently under heavy development, kickstarted by the [mozilla.org.uk](http://www.mozilla.org.uk/) website revamp in 2012. Built ontop of the [CodeIgniter](http://ellislab.com/codeigniter/) for ease of use, however each componenet should provide a good starting point for those wishing to use another framework (or even pure PHP).

This [Spark](http://getsparks.org/) provides a simple flat file CMS similar to [Pico](http://pico.dev7studios.com/).

I'm going to assume that you're using [CodeIgniter](http://ellislab.com/codeigniter/), and thus you have a basic understanding of PHP. These are both prerequisites if you want to follow the instructions from now on.

## Installation
First you will need to install the [Spark Package Manager](http://getsparks.org/install/). Once you have move into the root of your CodeIgniter application and issue the following command:

	php tools/spark install sandcastle-cms 

To load the cms into your application use the following in your default controller, replacing `x.x.x` with a version number (see [tags](http://github.com/fuzzyfox/sandcastle-cms/tags)).

	$this->load->spark('sandcastle-cms/x.x.x');

### Automated Install
At this stage navigate to your installation in your browser of choice. The cms will attempt to install itself, copying needed files into the correct places for you. Once this is done you should be able to point your browser to `www.yourdomain.com/path/to/codeigniter/cms/test` and be greated with a document outlining [markdown](http://daringfireball.net/projects/markdown/).

If this is not the case you will need to do a manual installation.

### Manual Install
Should the automated installation not work (usual due to permissions) you can manually install by using the following script from the root of your CodeIgniter insall. **Note:** You will need to replace `x.x.x` with the version of sandcastle-cms you installed via the Spark Pacakge Manager.

	#!/bin/bash
	VERSION='x.x.x'
	APPPATH='application'
	
	cp -R ./sparks/sandcastle-cms/$VERSION/themes ./themes
	cp -R ./sparks/sandcastle-cms/$VERSION/content ./content
	cp ./sparks/sandcastle-cms/$VERSION/controllers/cms.php ./$APPPATH/controllers/cms.php
	
	CONFIG=`cat ./sparks/sandcastle-cms/$VERSION/config/sandcastle_cms.php`
	echo ${$CONFIG/"['is_installed'] = FALSE"/"['is_installed'] = TRUE"} > ./sparks/sandcastle-cms/$VERSION/config/sandcastle_cms.php

Once you've done that you should be able to point your browser to `www.yourdomain.com/path/to/codeigniter/cms/test` and be greated with a document outlining [Markdown](http://daringfireball.net/projects/markdown/).

## Usage
### Creating Content
To create new content/pages simple create a `.md` file in the `./content` directory.

If you create a folder within `./content` (e.g. `./content/sub`) and put and `index.md` file in it you can access it at the URL `www.yourdomain.com/path/to/codeigniter/cms/sub`

Below are some examples of content locations and the URLs:

<table>
	<thead>
		<th>Physical Location</th>
		<th>URL</th>
	</thead>
	<tbody>
		<tr>
			<td>./content/index.md</td>
			<td>/cms/</td>
		</tr>
		<tr>
			<td>./content/sub.md</td>
			<td>/cms/sub</td>
		</tr>
		<tr>
			<td>./content/sub/index.md</td>
			<td>/cms/sub <small>(same as above)</small></td>
		</tr>
		<tr>
			<td>./content/sub/page.md</td>
			<td>/cms/sub/page</td>
		</tr>
		<tr>
			<td>./content/a/very/long/url.md</td>
			<td>/cms/a/very/long/url</td>
		</tr>
	</tbody>
</table>

### Markdown
All text files are marked up using [Markdown](http://daringfireball.net/projects/markdown/). They can contain regular HTML. At the top of the files you can place a block comment and specify certain attributes of the page.

	/*
		Title: Welcome
		Description: This description will go in the meta description tag
		Author: Joe Bloggs
		Date: 2013/01/01
		Robots: noindex,nofollow
	 */

These values will then be stored in the `{{ meta }}` variable in themes *(see below)*. In addition you can use %base_url% in your files and it will be replaced with the URL for your site automagically.

### Themeing
Much like Pico, sandcastle-cms uses [Twig](http://twig.sensiolabs.org/documentation) for its templating engine. Infact sandcastle-cms was designed to be 100% compatible with Pico themes. Thus for more information on themeing visit their [documentation on theming](http://twig.sensiolabs.org/documentation).

### Plugins
At this time sandcastle-cms does not support plugns, nor is this planned. Due to being built ontop of the CodeIgniter framework this doesn't make much sense.

## License
### CodeIgniter
For more information on the CodeIgniter License read it over at [http://ellislab.com/codeigniter/user-guide/license.html](http://ellislab.com/codeigniter/user-guide/license.html).

### SandCastle
This Source Code Form is subject to the terms of the Mozilla Public
License, v. 2.0. If a copy of the MPL was not distributed with this file,
You can obtain one at [mozilla.org/MPL/2.0](http://mozilla.org/MPL/2.0/).
