=== MCBoards ===
Contributors: MC_Will
Tags: mailchimp, pinterest, email, campaigns, marketing, pin, pins, pinterest board
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv2
Donate link: 

A different way to show your MailChimp Campaign Archive: Not just as a list of text links but actual screenshots shown a'la Pinterest(c).

== Description ==

MCBoards is a different way to show your campaign archive. It displays your campaign links not just as a list of text links, but with actual screenshoots of the campaigns, in a way that is pretty popular these days [wink, wink](http://pinterest.com).

You can define and include as many MCBoards as you need in any page or post, each one with its own properties and conditions. These conditions allow you to filter which campaigns to show in a certain board.

The content will be automatically updated every hour or so, saving the screenshots in your own *UPLOADS/mcboards* folder.

Once you've created your boards, update their content and insert the provided shortcode in a page or post. That's it!

= How does it work? =

This plugin helps you show your Campaign Archive as an Image Board. These boards can be inserted into any page or post by using the following shortcode:
[mcboard id="board_id"]

Each board has a set of "conditionals" that must be met by any given campaign in order to be shown on the board. They filter your campaigns.

Additionally, you can specify certain parameters such as the width of the images, their maximum height, and number of campaigns to show per page.

Once a board is defined, its content must be updated. This is done by clicking its update link (found in the Boards tab). You should update the content of a board whenever you create it or update its properties or conditionals.

Then insert the provided shortcode in any page or post. That's it!

Spanish translation available.

== Installation ==

1. Upload *MCBoards* to the */wp-content/plugins/* directory
1. Activate the plugin through the _Plugins_ menu in WordPress
1. Configure plugin in _Settings > MCBoards_

== Frequently Asked Questions ==

= Do I need a MailChimp account? =

Yes, this plugin only works with MailChimp accounts. [Get one free!](https://mailchimp.com/ "MailChimp")

= I'm gettings campaigns that don't meet the conditionals I defined. =

Once a board is created, you should update its content. Same applies when you modify any of its properties. This ensures two things:

1. The board will show campaigns consistent with your conditions.
1. The board will load faster for your visitors.

So go ahead and update it. It's a good thing.

= The board takes forever to load on my page/post! =

Once a board is created, you should update its content. If you don't do this, the board will be updated live when someone visits your website. This might be painfully slow.

To prevent this, update its content on the background once you create or edit boards.

= Do I need to manually update the content of my boards to keep them up-to-date? =

No, once a board is created, there's a process running in background that will update its content hourly-ish.

If you are in a hurry, you can manually update its content though.

= I just sent a new campaign and I can't see it in my board. =

Campaigns are not automatically updated once you send them through MailChimp. It takes up to one hour to get them updated by the plugin.

If you are in a rush, you can always update it manually.

== Request ==

If you find that a part of this plugin isn't working, please don't simply click the Wordpress "It's broken" button. Let us know what's broken in [its support forum](http://wordpress.org/tags/mcboards?forum_id=10) so we can make it better. Our [mind-reading device](http://www.youtube.com/watch?v=cCTlonSwePs) still needs some tweaking.

== Localizations ==

MCBoards is currently localized in the following languages:

* Spanish (es_ES)

== Screenshots ==

1. Settings Screen
2. Board List
3. Create a New Board
4. Edit a Board
5. Manual Content Updating
6. MCBoard in Action

== Upgrade Notice == 

Nothing to report!

== Changelog ==

= 1.04 =
* FIXED: Changing the label 'More Campaigns' to just 'More'. Less is more, ya know. 

= 1.03 =
* Using wp_remote_get instead of file_get_contents

= 1.02 =
* Replacing Isotope in favor of Masonry

= 1.01 =
* FIXED: Removing external link to jquery.
* FIXED: Links to FAQ and Support Board 
* ADDED: RSS News icons

= 1.0 =
* Public release
