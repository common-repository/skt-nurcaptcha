=== Skt NURCaptcha ===
Contributors: Sanskritforum
Donate link: https://skt-nurcaptcha.sanskritforum.org/donate/
Tags: security, login form, new user, captcha, spambots, reCAPTCHA, register form, bots, spam, register, anti-spam
Requires at least: 5.0
Tested up to: 6.1
Stable tag: 3.5.0

NURCaptcha inserts a reCAPTCHA on the Register Form of your site to protect it against spambots. 


== Description ==

NURCaptcha stands for *New User Register Captcha*. It has been designed for anyone who wishes to allow free registration of new users for a WP site, but want to keep bots outside. It uses Google's reCAPTCHA tools to give your site an extra protection against those spammer bots, adding security to the WP Register Form. With Skt NURCaptcha you may use a reCAPTCHA on your login form, also.

Skt NURCaptcha is easy to install and doesn't slow down your site. It is called into action just at the moment the "Register for This Site" form is requested. It prompts users with a checkbox they click on to prove they are not robots, and creates a challenge whenever needed, and checks the response given. If it is not valid, new user registration fails. If the response is valid, NURCaptcha leaves the scene and your site runs as if it was not there.

From version 3.4 on you can enable login form protection, also, as an extra fence against brute force attacks.

NURCaptcha also shows you each blocked attemptive. Log data may be toggled for you to see info on the attemptives blocked by the plugin. It shows date/time of trials, as well as usernames and e-mail addresses of those who failed registration (or login, if enabled) and were kept outside. Please note that attemptives in which the spambot suspends its attack when confronted by the reCAPTCHA challenge can not be logged. So logfile figures will never reflect the whole achievements of the plugin in securing your site.

Works smoothly with **WP Multisites** (Network) and **BuddyPress**.

Skt-NURCaptcha home site: [https://skt-nurcaptcha.sanskritforum.org/](https://skt-nurcaptcha.sanskritforum.org/).

**Languages included**.

* Dutch nl_NL - by  [SalviJansen](https://wordpress.org/support/profile/salvijansen/ "SalviJansen").
* Frech fr_FR - by  [ChezMat](http://chezmat.fr/ "ChezMat").


== Privacy Disclaimer ==

This plugin uses Google reCAPTCHA which uses cookies and collects usage data. Its privacy policy is under [Google's Privacy Policy](https://policies.google.com/privacy). 
NURCaptcha collects and records in a log table: IP, username and email address of each blocked register or login attemptive.

== Installation ==

1. Upload 'skt-nurcaptcha' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Don't forget to get the free Public and Private reCAPTCHA keys at [reCAPTCHA API Signup Page](https://www.google.com/recaptcha/admin#createsite "Get your free keys")
1. Go to the 'Settings' menu, locate Skt NURCaptcha Settings Page and drop your keys there.
1. That's it.

== Frequently Asked Questions ==

= The number of blocked attemptives is growing too slowly on the log. Does that mean that the plugin is not working? =

If you was facing a greater number of spambot registrations than the figures you see now in the log counter, that means the plugin is doing its job perfectly. Almost all the spambots give up their attack when confronted by the reCaptcha challenge, so they come and go silently. These bots may not trigger the counter, but they will not get inside your site walls, either. 

= Does NURCaptcha blocks spambots only? =

That is the main target of any captcha plugin: to catch bots and allow human beings to pass by. In order for it to somehow prevent you against badly mooded human intruders, their data (email & IP) are checked against trustable anti-spam databases (from plugin version 3.0.0 on).

= How can I help people who for any reason were not able to fill the captcha challenge? =

These people will leave their track on the log file. So check the log file regularly to see if there is someone you'd like to bring into your subscribers database. The log will give you 
that person's username, email and date/time when he tried to register and failed. Then it's up to you to add that user manually or send him an email with directions for an effective registration. 
The most common problem that keeps good people outside is not due to the reCaptcha, but illegal (non ASCII) characters in usernames. That is why we added a help box to the register form.

= Which versions of the reCAPTCHA are enabled by this plugin? =

This plugin works only with version 2 of the reCAPTCHA, released on december, 2014.

== Screenshots ==

1. Settings Page
2. This is how the Register Form will look like

== Changelog ==

= 3.5.0 =
* Upgrade in antispam databases querying routines and a few other small fixes
= 3.4.90 =
* Small adjustments to PHP7 affecting BuddyPress installs
= 3.4.8 =
* Fixed: bug affecting user registration in some sites
= 3.4.7 =
* Fixed: small issue affecting access to language files
= 3.4.6 =
* Fixed: call to undefined property $errors under BP
= 3.4.5 =
* Fixed: call to undefined variable $lang under BP
= 3.4.4 =
* Fixed: mispelled call to undefined function at line 85
= 3.4.3 =
* Added: enable reCAPTCHA on selected front side pages
* Fixed: register form not working when BuddyPress enabled over WPMU
= 3.4.2 =
* Fixed: bug blocking login when keys were not saved yet
= 3.4.1 =
* Fixed: small glitch in the code generating PHP warnings
= 3.4.0 =
* Added: Transition to the new version of Google's reCAPTCHA
* Added: login form captcha
= 3.1.8 =
* Fixed: removed duplication of div id in registration form when BuddyPress was active
= 3.1.7 =
* Fixed: challenge not displaying on https pages
= 3.1.6 =
* Added: French version - by ChezMat
* Fixed: field missing at uninstall.php, and small improvements in main code
= 3.1.5 =
* Added: action hook to allow extra check on username or email by other plugin
* Added: managing register form's help messages via Admin Panel
* Fixed: small glitch on register form's help display code
= 3.1.3 =
* Fixed: bug in MU that blocked new site registration
= 3.1.2 =
* Fixed: small bug in MU that blocked new user registration from admin panel
= 3.1.1 =
* Improved: a server-safe procedure to load external xml contents, needed to read anti-spam databases responses.
= 3.1.0 =
* Improved: log entries now stored in a database table - no more data will be lost when plugin upgrades.
= 3.0.2 =
* Fixed: anti-spam databases options didn't save changes.
= 3.0.1 =
* Fixed: small glitch affecting BuddyPress users only - no security issues involved.
= 3.0.0 =
* Added: StopForumSpam and BotScout databases assessment for extra security.
= 2.4.7 =
* Added: user customization of reCAPTCHA strings.
* Improved: better UI to choose native language for reCAPTCHA.
= 2.4.6 =
* Added: customized text for submit button on register form.
= 2.4.5 =
* Fixed: excluded ubused help button from BuddyPress and WPMU versions.
= 2.4.4 =
* Added: help on register form; 
* Fixed: logfile deletion drops log count to zero.
= 2.4.3 =
* Fixed: wrong data writing on logfile
= 2.4.2 =
* Added: improvements on javascript.
* Fixed: small glitch on textdomain call
= 2.4 =
* Added: support to BuddyPress.
* Fixed: double counting on WPMU
= 2.0 = 
* Added support to Multisites. 
= 1.3 = 
* Included Log File to show data of blocked attemptives, and a few other minor code improvements.
= 1.13 = 
* Register Form Page upgraded with javascript. No critical changes.
= 1.1 = 
* Admin Page upgraded with javascript. No critical changes.
= 1.0 =
* First version.

== Upgrade Notice ==

= 3.5.0 =
* Upgrade in antispam databases querying routines
= 3.4.90 =
* Small adjustments to PHP7 affecting BuddyPress installs
= 3.4.8 =
* Fixed: bug affecting user registration in some sites
= 3.4.7 =
* Fixed: small issue affecting access to language files
= 3.4.6 =
* Fixed: call to undefined property $errors under BP
= 3.4.5 =
* Fixed: call to undefined variable $lang under BP
= 3.4.4 =
* Fixed: mispelled call to undefined function at line 85
= 3.4.3 =
* This update will enable reCAPTCHA on selected front side pages
= 3.4.2 =
* Fixed: bug blocking login when keys were not saved yet
= 3.4.1 =
* Fixed: small glitch in the code generating PHP warnings
= 3.4.0 =
* IMPORTANT UPDATE: Transition to the new version of Google's reCAPTCHA (dec/2014)
= 3.1.8 =
* Fixed: removed duplication of div id in registration form when BuddyPress was active
= 3.1.7 =
* Fixed: reCAPTCHA challenge not displaying on https pages
= 3.1.5 =
* Added: now you can manage register form's help messages via Admin Panel
= 3.1.3 =
* Fixed: bug in MU that blocked new site registration
= 3.1.2 =
* Fixed: small bug in MU that blocked new user registration from admin panel
= 3.1.1 =
* Improved: a server-safe procedure to load external xml contents, needed to read anti-spam databases responses.
= 3.1.0 =
* Improved: log entries now stored in a database table - no more data will be lost when plugin upgrades.
= 3.0.2 =
* Fixed: anti-spam databases options didn't save changes.
= 3.0.1 =
* Fixed: small visual glitch affecting BuddyPress users only - no security issues involved.
= 3.0.0 =
* Added: StopForumSpam and BotScout databases assessment for extra security.
* Alert: we strongly recommend users to upgrade to version 3, as there are some evidences of spammers making a way through reCAPTCHA challenges - probably by tricking someone else to solve it for their bots.
= 2.4.7 =
* Added: user customization of reCAPTCHA strings.
* Improved: better UI to choose native language for reCAPTCHA.
= 2.4.6 =
* Added: customized text for submit button on register form.
= 2.4.5 =
* Fixed: excluded unused help button from BuddyPress and WPMU versions.
= 2.4.4 =
* Added: help on register form; Fixed: logfile deletion drops log count to zero.
= 2.4.3 =
* Fixed: wrong data writing on logfile
= 2.4.2 =
* Fixed: small glitch on textdomain call
= 2.4 =
* Fixed double counting in WPMU. Added BuddyPress support
= 2.0 =
* Added: support to Multisites; settings link (at Plugins List); Improvements on warnings.
= 1.3 =
* Added: Log data.
= 1.13 =
* Added: js shake effect to the error message when form fill fails.
= 1.1 =
* Some Javascript added to admin page. Visual help to change options improved.