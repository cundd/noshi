![NoShi Logo][logo]

NoShi
=====

The no shi! CMS


Features
--------

- Markdown equipped
- File based
- Open Source


Free from
---------

- Templating engine
- Application framework
- Backend login
- Web Services
- Shop integration
- Database (SQL and NoSQL)


Installation
------------

    php composer.phar create-project -s dev cundd/noshi target/path/


Start the development server:

	cd root/of/noshi/installation/;
	php -S localhost:9000 -t .


Authoring
---------

Added markdown files to the `data` directory

	cd root/of/noshi/installation/;
	cd data;
	
	# Create a page (with a menu item):
	touch About.md
	
	# Create a 'hidden' page (without a menu item):
	touch _Details.md
	

Themes
------

The default theme `noshi-website` is stored under `vendor/cundd/noshi-website/`.
The page template is saved in `Resources/Private/Templates/Page.html` and the themes resources under `Resources/Public/`,
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
`{Namespace\ClassName}`. NoShi will then unfold an instance of the class and transform it to a string.

If the class implements `\Cundd\Noshi\Ui\UiInterface` to method `setContext()` will be invoked before rendering.
`setContext()` receives the parent view as it's argument. The parent view's context as an example is the core Dispatcher.



[logo]: /Resources/Public/Graphics/noshi-logo.png
