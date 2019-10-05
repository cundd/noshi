![NoShi Logo][logo]

NoShi
=====

The no shi! CMS

NoShi isn't a usual content management system, because it doesn't show you a backend. But it too isn't a static site generator. It simply renders your Markdown files into your template. That's it!

Get started by downloading [noshi-boilerplate-1.0.0.tgz](/Resources/Public/Downloads/noshi-boilerplate-1.0.0.tgz), unpacking, firing up the PHP 5.4 server by running `vendor/bin/noshi-server` and opening [http://localhost:9000](http://localhost:9000) in your browser. Then add or edit Markdown files in the `data` folder, change the template in `Resources/Private/Templates/Page.html` and be amazed how easy you created a new website.

Tip: You can put your NoShi website under version control and keep a history of your changes.

Question: Do you need NoShi to generate static sites? Add a comment to [https://github.com/cundd/noshi/issues/1](https://github.com/cundd/noshi/issues/1)!

Features
--------

- Markdown equipped
- File based
- Open Source (MIT License)


Free from
---------

- Application framework
- Backend login
- Web Services
- Shop integration
- Database (SQL and NoSQL)
- Templating engine (< 0.2%)


Installation
------------

    composer create-project -s dev cundd/noshi-boilerplate target/path/

Start the development server (on PHP 5.4 and higher):

	cd root/of/noshi/installation/;
	./vendor/bin/noshi-server

or run NoShi on heroku: [noshi.herokuapp.com](http://noshi.herokuapp.com/)


Configuration
-------------

The main configuration file can be found in `Configurations/Configuration.json`. It is the control center of your installation, but you may doesn't even have to touch. Nevertheless it is the place to make advanced configurations in your NoShi website.

	{
		"theme": "cundd/noshi-boilerplate",
		"routing": {
			"alias": {
				"/": "/Home/",
				"/Source/": "/Target/"
			}
		},
		"pages": {
			"MyPage": {
				"meta": {
					"title": "My Page",
					"sorting": 40
				}
			},
			...
		}
	}

- `theme` defines the theme to use (`cundd/noshi-boilerplate` says: "search the resources in `root/of/noshi/installation/Resources/`")
- `routing`
	- `alias` define aliases of source URLs that will be translated to the targets


Authoring
---------

### About URLs, page and files

| URL               | Page identifier   | Markdown file     | Meta file         |
| :---------------- | :---------------- | :---------------- | :---------------- |
| About             | About             | About.md          | About.json        |
| About/            | About             | About.md          | About.json        |
| Hidden/           | Hidden            | _Hidden.md        | Hidden.json       |
| Folder/Item/      | Folder/Item       | Folder/Item.md    | Folder/Item.json  |


### Adding a page

To add a page in NoShi you only have to add a Markdown file to the `data` directory:

	cd root/of/noshi/installation/;
	cd data;
	
	# Create a page (with a menu item):
	touch About.md
	
	# Create a 'hidden' page (without a menu item):
	touch _Details.md


### Adding page meta data

There are two different ways to add meta data to a page.

You either add a JSON file with the same identifier as the page. 

Example `MyPage.json` for `MyPage`:

	{
		"title": "My page",
		"sorting": 19
	}
	
Example `MyFolder/MyPage.json` for `MyFolder/MyPage`:

	{
		"title": "My page in a folder",
		"sorting": 35
	}

Alternatively you add meta data in the main configuration file `Configurations/Configuration.json`.

Example `MyPage`:

	{
		...
		"pages": {
			"MyPage": {
				"meta": {
					"title": "My page",
					"sorting": 19
				}
			},
			...
		}
	}

Example `MyFolder/MyPage.json` for `MyFolder/MyPage`:

	{
		...
		"pages": {
			"MyFolder/MyPage": {
				"meta": {
					"title": "My page in a folder",
					"sorting": 35
				}
			},
			...
		}
	}


### Used page meta data

- `title` Overwrite the pages title
- `sorting` Sorting of menu items
- `url` Link to an external URL (you can create a meta JSON file without an Markdown file)


Themes
------

The boilerplate's template file is saved in `Resources/Private/Templates/Page.html` and the themes resources under `Resources/Public/`,
both relative to the themes path.

The following variables inside the template file will be replaced:

- `{content}` The current page's content
- `{resourcePath}` The path to the public resources
- `{meta.title}` The current page's title (can be read from the page's meta file; i.e.: `About.json`)
- `{Cundd\Noshi\Ui\Menu}` The navigation menu (a simple plugin)


Plugins
-------

### What is a plugin?
Everything

### Really?
Okay, actually every class' instance that can be transformed to a string

### Creating a plugin
Simply create a PHP class, that composer can autoload and require it in your template, in the format
`{// Namespace\ClassName}`. NoShi will then unfold an instance of the class and transform it to a string.

If the class implements `\Cundd\Noshi\Ui\UiInterface` to method `setContext()` will be invoked before rendering.
`setContext()` receives the parent view as it's argument. The parent view's context as an example is the core Dispatcher.



[logo]: /Resources/Public/Graphics/noshi-logo.png
