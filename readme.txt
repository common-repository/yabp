=== Yet Another bol.com Plugin ===
Contributors: Mitchel Troost
Donate link: https://tromit.nl/diensten/wordpress-plugins/
Tags: bol.com, deals, affiliate, responsive, cronjob, post, page, shortcode
Requires at least: 5.0
Tested up to: 5.6
Stable tag: 1.4
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful plugin to easily integrate bol.com products and deals in your blog posts or at your pages to earn money with the bol.com Partner Program.

== Description ==

The next generation bol.com plugin for WordPress is here. This powerful plugin is designed to allow you to easily display selected products in your blog posts and on your pages. At the control panel, you enter your bol.com Open API Access key and your siteid from the bol.com Partner Program. The next step is to search in the bol.com Catalog for the products you want to display. After the desired products have been added, you retrieve their shortcodes that allow you to display these products anywhere on your website.

Displaying bol.com products in blog posts and on pages is nothing new. Therefore this plugin takes this to the next level. The product data as price, delivery time and rating are updated automatically at a selected interval. Its display and style can be highly customized. You can choose to show the (new) buttons of the Partner Program, and you can choose to record the impressions of your products on your website in the Program. And last but not least, the display of products is designed to be integrated in desktop, mobile and responsive themes.


= Features =

* Easily display bol.com products anywhere in blog posts or at pages
* User-friendly process to add products and deals to your database from the Catalog
* Search for products in either the Dutch or the Belgium Catalog
* Prices, delivery times and ratings are automatically updated
* Receive email notifications when a product expires from the Catalog
* Highly customizable layout, and you can choose between product links and the new bol.com buttons
* Choose products have to be opened in a new tab and/or be put directly in the bol.com Cart
* Easily set up your shortcodes using the shortcode generator
* Support for use in desktop, mobile and responsive themes
* Full Dutch translation included

= Demo =

A page with a demo product displayed with use of this plugin can be found [here](http://tromit.nl/naslagwerk/ipad/).

= Video tutorial (Dutch) =

[youtube http://www.youtube.com/watch?v=tktH1DvJIb8]

= Support =

Please take a look at the FAQ first. If you have another question or request, or if you found a bug, please contact the developer. You can send an [email](http://tromit.nl/diensten/wordpress-plugins/) or, send a tweet [@MarofNL](https://twitter.com/MarofNL).

= Disclaimer =

This is not an official plugin from bol.com, but it is safe to use as bol.com's Open API v4 is being used. No personal data is saved or forwarded. The names and images in this plugin belong to their respective owners. The developer is not responsible for any rejected clicks or other errors regarding the bol.com Partner Program while using this plugin. When you face any problems on this subject, please contact the developer.

== Installation ==

1. Upload the 'yabp' directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' page in WordPress
3. Go to the 'Options' page of the plugin by clicking on the YAbP tab
4. Enter your bol.com Open API Access key and siteid on the 'Options' page and save your data
5. Search for products on the 'Add product' page, and add them accordingly
6. Go to the 'Product list' page, and retrieve and copy the shortcodes of your products
7. Paste your shortcodes on any page or in any post
8. Customize your product's style and settings

== Frequently Asked Questions ==

= Is this plugin available in Dutch? =

Yes, a Dutch version is available if the WordPress language of your website has been set to Dutch (nl_NL).

= What API key type should I enter on the Options page? =

You should enter your bol.com Open API Access Key only.

= Can I use this plugin to show items in mobile or responsive themes? =

Yes, this plugin is designed for use with desktop, mobile and responsive themes.

= Can I assign different styles to the items? =

No, at this stage that is not possible. You can only adjust the global style. However, it is on the list that in a future release this functionality will be integrated.

= Can I use this plugin to show products in (sidebar) widgets? =

Probably yes, because of the responsive design. However, this plugin has not been developed to show products or products lists in a sidebar. It has been developed to easily display a related bol.com product in or underneath your blog posts or on your pages.

= Does this plugin work with plugins like the Page Builder Plugin? =

Yes, you can enter the shortcode of your products anywhere on your blog or website.

= Can I add a SubId to a product link to track my clicks in the bol.com Partner Program? =

Yes, you can by adding 'subid=your sub id' to your shortcodes. For example: [yabp 1 subid="homepage header"]

= When I select that a product should be put directly in the bol.com Cart and I click on the displayed link, it does show a different delivery time and/or price than that there is shown on my website. How come? =

Currently, bol.com only allows you to put products directly in the cart bol.com sells and ships themselves. Meaning, products from third party sellers cannot be put directly in the bol.com Cart at the moment. As an alternative, if bol.com does have the product included in its own catalog, it puts this product in the cart. This product may have a different delivery time and/or price than the product from the third party seller.

= Why are the email notifications not working? =

First check if you switched the email notifications to 'on'. Then try to send a test notification, and check the blog's set up the admin emailaddress for the test email (check your spam folder!). If you still cannot find any emails, you may contact the developer to troubleshoot.

= What options can I set up in my shortcodes? =

Check the shortcode generator for more information. If you need any further assistance, please contact the developer.

== Screenshots ==

1. Products displayed on a page
2. Options page of the plugin
3. Add a product (search process)
4. Add a product (results list)
5. Product list of the plugin

== Changelog ==

= 1.4 =
* WordPress 5.6 compatibility
* Removed obsolete deals-feature
* Bugfixes

= 1.3.6 =
* Bugfixes

= 1.3.5 =
* Bugfixes

= 1.3.4 =
* Major bugfix: updating strongly recommended. Note: deals-feature is currently unmaintained.

= 1.3.3 =
* Bugfixes (’Free shipping’-text can be changed again)

= 1.3.2 =
* Fixed SSL compatibility
* Fixed product rating stars (requires (auto-)update per product)

= 1.3.1 =
* Applied a quickfix for the broken rating display (this reverted the SSL compatibility improvement, a new SSL fix will be available after the weekend)

= 1.3 =
* Added the ability to adjust the product's layout (image and info width) and the product image width using their shortcodes
* Added a shortcode generator for easy shortcode setup: check this generator for the new shortcode options
* Improved SSL compatibility
* Updated deals categories

= 1.2 =
* Added product manager for expired products
* Added the ability to receive email notifications when a product expires from the Catalog
* Updated deals categories
* Updated Dutch translation

= 1.1 =
* Added the ability to display deals from bol.com in your posts or at your pages
* Fixed the option to display the thumbnail above the content
* Fixed minor bugs
* Applied major code cleanups

= 1.0.8 =
* Added the possibility to search for products in the Belgium product catalog
* Added the option to show a set up text when a product is shipped for free
* Fixed minor bugs

= 1.0.7 =
* Fixed the display of long titles and subtitles.
* Applied several code improvements (including better SQL compatibility).

= 1.0.6 =
* Improved responsive display of items.
* Improved display of unavailable items.
* Added option to replace an item in the database with a new one from the bol.com Catalog.
* Added option to put thumbnail above the product information.
* Applied small code improvements and interface tweaks.
* Fixed minor bugs.

= 1.0.5 =
* Added the ability to record impressions, open link in a new tab, put a product directly in the bol.com Cart and select the button type per item.
* Updated Dutch translation.
* Minor code and text improvements.

= 1.0.4 =
* Fixed a bug on blogs/posts where a shortcode is still present from a product that has been deleted.

= 1.0.3 =
* Updated Dutch translation.

= 1.0.2 =
* Fixed a bug with the rating translation.

= 1.0.1 =
* Added Dutch language.
* Updated texts to specify what API key type should be entered.
* Improved activate/deactivate handling of the plugin.
* Fixed timezone bug.
* Minor code and text improvements.
    
= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.3.7 =
WordPress 5.6 compatibility and bugfixes

= 1.3.6 =
Bugfixes

= 1.3.5 =
Bugfixes

= 1.3.4 =
Major bugfix: updating strongly recommended

= 1.3.3 =
Bugfixes

= 1.3.2 =
Fixed SSL compatibility and product rating stars

= 1.3.1 =
Applied a quickfix for the broken rating display

= 1.3 =
Added more layout options and a shortcode generator

= 1.2 =
Added product manager for expired products

= 1.1 =
Added the ability to display deals from bol.com

= 1.0.8 =
Search for products in the Belgium product catalog and show text when a product is shipped for free.

= 1.0.7 =
Fixed the display of long titles and subtitles, and applied several code improvements.

= 1.0.6 =
Improved responsive design and display of unavailable items. Added option to replace items and to put product thumbnails above the content.

= 1.0.5 =
More options per item are now available.

= 1.0.3 =
Updated Dutch translation.

= 1.0.2 =
Fixed a bug with the rating translation.

= 1.0.1 =
The Dutch language has been added, and some minor improvements have been completed.

= 1.0 =
Initial release.