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
The page template is saved in `vendor/cundd/noshi-website/Resources/Private/Templates/Page.html` and the themes resources under `vendor/cundd/noshi-website/Resources/Public/`.

The following variables inside the template file will be replaced:

- `{ content }` The current page's content
- `{ title }` The current page's title (can be read from the page's meta file; i.e.: `About.json`)
- `{ resourcePath }` The path to the public resources
- `{ menu }` The navigation menu


[logo]: /Resources/Public/Graphics/noshi-logo.png
