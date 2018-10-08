=== WP-CRM - Customer Relations Management for WordPress ===
Contributors: usability_dynamics, andypotanin, jbrw1984, maxim.peshkov, Anton Korotkoff, ideric, MariaKravchenko, smoot328
Donate link: http://www.usabilitydynamics.com/product/wp-crm/
Tags: wp-crm, users, CRM, user management, contact form, shortcode form, email, feedback, form, contact form plugin, WordPress CRM, contact form builder, newsletters, bbpress
Requires at least: 4.0
Tested up to: 4.9.8
Stable tag: 1.1.7
License: GPLv2 or later
Organize your business clients, improve user management.

== Description ==

This plugin is intended to significantly improve user management, easily create contact forms, and keep track of incoming shortcode form messages.

WordPress already comes with some basic user management functions - WP-CRM expands on this functionality by allowing you to organize your users using custom attributes, find them using filters, and keep track of correspondence.

Your WP control panel can effectively be used to manage all your customers, vendors, partners, affiliates, etc.

> WP-CRM on [GitHub](https://github.com/wp-crm/wp-crm)

= Features =

* Excellent user organization, filtering and editing.
* Ability to easily add new user data attributes (i.e. Company Name).
* Dynamic charts representing attributes with quantifiable data.
* Contact Forms for front-end.
* Notifications management.
* Group notifications (Add-on).
* User CSV Exporting.
* User activity and note tracking.
* WP-Invoice integration.

= Available Add-ons list =
If you found the default functionality of WP-CRM is not enough for your needs please take a look at the list of available [Add ons](https://www.usabilitydynamics.com/products#category-wp-crm)

= Group Messages =
Ability to send newsletters to users, or user groups.
[More about Add-on](https://www.usabilitydynamics.com/product/wp-crm-group-messages)

= Contact Forms Tutorial =

[vimeo http://vimeo.com/26983459]

= Attribute Management =

[vimeo http://vimeo.com/26984134]

== Installation ==

Immediately after activation you will see a new "CRM" section which will list your existing users.
Default data will be loaded on the first install, which can then be configured further on the CRM -> Settings page.
For additional help, please reference the contextual help dropdowns the different plugin pages.

== Frequently Asked Questions ==

[WP-CRM FAQ](https://www.usabilitydynamics.com/product/wp-crm/docs/home)

== Screenshots ==

1. Main view and filter
2. Manage user fields
3. Manage shortcode forms
4. Manage notifications
5. List of messages received
6. Edit user page

== Upgrade Notice ==

= 0.30.1 =
* Required input fields that also have dropdown options now require that a dropdown value is selected during validation, in addition to text.

= 0.08 =

* Initial public release.

== Changelog ==

= 1.1.7 =
* Fixed issue with file upload input field
* Added Compatibility with WP-Stateless plugin
* Added Support tab

= 1.1.6 =
* Fixed pagination function for messages on a user page, which crashed property list pagination in WP-Property plugin.
* Turned on Google Visualization Feature.
* Removed duplicated options.
* Security fix added. Scripts no longer can be sent via a contact form or admin message.

= 1.1.5 =
* Added French translation.
* Added data sanitization of the form filling.
* Added url_redirect option to the form settings.
* Fixed issue with attribute not changing its name in the form after it was changed in Data tab.
* Updated drag icon in Shortcode form tab to prevent confusing.
* Updated jQuery live() function to on().

= 1.1.4 =
* Fixed connection to WP-Invoice.
* Updated libraries.
* Fixed Radio field type.
* Added Feedback form.
* Fixed Warnings.

= 1.1.3 =
* Added radio field type.
* Other fixes and improvements.

= 1.1.2 =
* Fixed jquery error on submitting the form.
* Makes some strings translatable, such as "Messages" menu item.
* Removed pt_BR .po/.mo files since it's already and better done in GlotPress.
* Allows fields editors to use html for using commas in options.

= 1.1.1 =
* Took away the opportunity of minimum user level which can manage WP-CRM to change roles and passwords of other users.
* Improved Google reCAPTCHA options and added integration with WP-Invoice plugin.
* Fixed conflict with Contact Form 7.
* Fixed empty fields issue on new contact form.

= 1.1.0 =
* Fixed JS error on submitting the form.
* Fixed Network issues.
* Fixed issue with file upload field.
* Fixed Bulk action in All People tab.
* Fixed Network messages were not appearing from same profile.
* Fixed issue when the notification wasn't sending without Textarea field.
* Added captcha to the forms.
* Added new compatibility to wp-invoice plugin - "Add invoice from wp-crm user profile".
* Added new parameter to the shortcode redirect_url.

= 1.0.6 =
* Added Bulk actions to Messages.
* Added new options for WP-Property and Denali Integration. See Help tab.
* Added ability to use WP-CRM on Network.
* Added new option - Minimum user level to manage WP-CRM.
* Added [agent_email] and [author_email] to the list of available tags for notifications.
* Added ability to attach file to the forms.
* Fixed issue with not editable fields.
* Fixed issue with required fields.
* Fixed issue with long dropdown fields which displayed the background key instead of the descriptor.
* Fixed Warning and notices.
* Fixed issue that Save button was not translating.
* Fixed issue with Show button was reverting to EN translation.


= 1.0.5 =
* Updated libraries.
* Fixed permission issues.
* Fixed conflict with wpMandrill plugin.

= 1.0.4 =
* Updated plugin initialisation logic.
* Removed unwanted libraries.

= 1.0.3 =
* Added new column to messages list with Source association.
* Fixed warnings and notices.

= 1.0.2 =
* Fixed the bug when UsabilityDynamics Admin Notices could not be dismissed.
* Fixed warnings and notices.
* Fixed localizations.

= 1.0.1 =
* Fixed incorrect behaviour on custom 'Install Plugins' page after depended plugins ( Add-ons ) activation.
* Fixed the way of widgets initialization. Compatibility with WordPress 4.3 and higher.
* Fixed issue for forms submissions.
* Fixed Warnings and Notices.

= 1.0.0 =
* Changed plugin initialization functionality.
* Added Composer ( dependency manager ) modules and moved some functionality to composer modules ( vendors ).
* Added doing WP-CRM Settings backup on upgrade to new version. Get information about backup: get_option('wp_crm_settings_backup');
* Moved premium features to separate plugins.
* Cleaned up functionality of plugin.
* Refactored file structure of plugin.
* Refactored 'View All' page.
* Fixed performance issue.
* Fixed user profile page UI.
* Fixed Warnings and Notices.

= 0.36.3 =
* WordPress 4.0 compatibility.
* Fixed CSV export.
* Localization updated.
* Fixed Warnings and Notices.
* General code cleanup.

= 0.36.2 =
* Fixed showing logs data.
* Fixed form submitting issue with empty dropdowns and checkboxes.
* MultiSite support fix.
* Style fixes.

= 0.36.1 =
* Updated libraries.

= 0.36.0 =
* Added back-end styles compatibility with WordPress 3.8 and higher.
* Fixed default role on user creation.
* Fixed general send notification functionality.
* Fixed notification sending on 'User Registration' event.
* Fixed text/textarea/date attributes values with predefined options.
* Fixed some localization issues related to dynamic values.
* Updated Portuguese (BR) Localization.
* Updated Russian (RU) Localization.

= 0.35.1 =
* Fixed Activity Stream metabox.
* Fixed WP-Invoice connection ("From" notification field).
* Fixed CSV User Export.

= 0.35.0 =
* Added column sort functionality on overview ('All people') page
* Fixed activity stream.
* Improved notification's functionality.

= 0.34.2 =
* Fixed fatal error on new user creation.
* WordPress MU compatibility fixes.

= 0.34.1 =
* Fixed profile page metaboxes.

= 0.34.0 =
* Added WordPress MU compatibility.
* Fixed JavaScript error on edit user page.
* Fixed layout bug in contact form in Internet Explorer 7.
* Fixed bug with Activity Stream when all users see all activity items.
* Fixed profile page metaboxes layout.
* Fixed security issue with file with the name that contains 'cookie'.
* Updated Portuguese Localization.
* Updated Russian Localization.

= 0.33.2 =
* Added WordPress 3.5 compatibility.
* Added Detailed Activity log page which displayed when detailed activity tracking is enabled.
* Updated Russian Localization.

= 0.33.1 =
* Added ability to create user without email.
* Added Russian localization.
* Fixed prohibition of user updating without changing email.
* Fixed BCC functionality on notification sending.
* Added fix to hide blank user stream messages.
* Added fix to Color Scheme: option to not be shown when there are no color schemes available.

= 0.33.0 =
* Added WordPress 3.4-RC1 compatibility.
* Added ability to set Capability Role on user's edit page
* Added ability to view/remove uploaded files (File Uploader field).
* Added "Email" field Ajax validation on user's edit page.
* Fixed jQuery UI scripts and styles adding.
* Fixed File Uploader: If a user does not have the necessary capabilities the field will not be displayed.
* Fixed Quick Password Reset functionality.
* Fixed Access for non-admin users to their profile if common User management was replaced by WP-CRM
* Fixed All Peoples page Filter
* Fixed toggling of filter options on All Peoples page.
* Fixed selecting of active menu item when Profile user's page loaded.
* Screen Options and Metabox functionality fixes.
* Modified advanced actions.
* Deprecated and removed "Add a general note" Button.
* Standardize Display Names was fixed.
* Replaced "From name" on sending Group messages from default 'WordPress' to sender e-mail address which is set on CRM Settings page.
* Updated Contextual help.

= 0.32.0 =
* Added User Activity history message filtering and option to set number of messages to display per page.
* Added field "Do not require users with existing accounts to sign in first." to shortcode form settings tab
* Removed uploadify.php (http://packetstormsecurity.org/files/111628/waraxe-2012-SA083.txt).

= 0.31.0 =
* BuddyPress profile integration. Now you have the ability to make WP-CRM data fields to be displayed in users' BuddyPress profiles.
* User attribute grouping ability added. Grouped attributes will be separated between different metaboxes in the back-end.
* Activity stream limits. You can limit users' activity streams to be showing a specific number of entities.
* New Contextual Help. It became fully compatible with WordPress 3.3+. Every information block has it's tab now.
* Other small improvements of functionality and UI.

= 0.30.3 =
* Proper plugin PATH and URL determining.
* Added capabilities: "Change Passwords", "Change Color Scheme".
* Sender email address fixed in "CRM Group Messages" Premium Feature.
* Major fix to messaging function of "CRM Group Messages" Premium Feature.
* Fix for PDF report generating function of "CRM Group Messages" Premium Feature.
* Fix for option "Allow user accounts to be created without an e-mail address".

= 0.30.2 =
* Added Date Picker input option.
* Updated Notifications to send checkbox and dropdown values correctly, concatenating multiple values with a comma when they exist.
* Fixed issue with user activity messages not saving.
* Fixed "Required" validation for text areas.

= 0.30.1 =
* Fix for WP Network Compatibility.

= 0.30.0 =
* "Change Passwords" capability added. When WP-CRM is used as profile editor, users with this capability disabled cannot change passwords, even if they have rights to edit users.  On update WP-CRM will add the Change Passwords to all roles that have the edit_users capability.
* "Change Color Scheme" capability added, which if removed will not let a user change their, or any other user's, color scheme.
* Added better BuddyPress integration - link to a user's BuddyPress profile can be enabled to display on the overview table for quick access.
* Added Google Analytics Event tracking to shortcode forms.
* Added Admin Bar and Color Scheme selection UI to CRM profiles.
* Added option to standardize Display Names by using values from other user attributes.
* Improvements to WP-Property integration to allow properties associated with an inquiry to be displayed on the Messages page.
* Required input fields that also have dropdown options now require that a dropdown value is selected during validation, in addition to text.
* Added "My Profile" link to WP-CRM navigation menu.
* Added admin color picker to WP-CRM profile editor (located in Advanced User Settings)
* Disabled changing role of own profile.
* "Your Profile" link in header directs to CMR profile when "Replace default WordPress User page with WP-CRM." is enabled.
* Added support for User Avatar plugin to display avatar uploading functions in Special Actions metabox on profile page.
* Added new redirection rules for when default User management is set to be replaced by WP-CRM management
* Added option to disable automatic line-break conversion in Notification Messages content.
* Added actions: wp_crm_data_structure_attributes
* Added actions: show_user_profile, edit_user_profile same as on standard users page for third-party plugin hooks. API-added functionality is organized under 'Personal Options' and 'Additional Settings' metaboxes.
* Added WordPress 3.3 compatibility.
* Developer Note: added conditional body classes to profile page: 'wp_crm_existing' if current profile is for existing user and 'wp_crm_new_user' if new user creation.

= 0.21 =
* Ability to create user accounts without e-mail addresses.
* Shortcode forms verify that a user with an e-mail address does not already exist before processing form.
* Shortcode form notifications can be enabled even when no message has been filled out.
* Fake users created via the plugin can be deleted in one click.
* Bug fix with user fields not being able to be blanked out.
* User search filter automatically removes white space.
* Added visualization for incoming shortcode form messages.
* Added extra classes to shortcode form on the front-end.
* bbPress forum participation can be displayed on user overview.
* WP-Invoice integration - invoices can be seen in CRM profile.

= 0.20 =
* Added hook to add new user password reset link to user profile stream.
* Associated objects, such as properties, are now associated with messages when the shortcode form shortcode is filled out from a property page.

= 0.19 =
* Added graphs for displaying quantifiable attributes.
* Fixes to avoid deletion of existing users when reviewing received messages.
* Added default data that is installed on first run to include shortcode form messages and notifications.
* Added filtering options to shortcode form tab.
* Setup default UI settings for metabox layout.
* Added a setting for default "system" e-mail address.
* Added contextual help to user editing page.

= 0.18 =
* Added check to prevent self-deletion.
* Added hook to check if bbPress exists, and display user statistics in their CRM profile.
* Improved connection with WPI where "Total Sales" can not be seen for users from overview screen.

= 0.17 =
* Much improved capability management. New capabilities: View Profiles, View Overview, Manage Settings, Add User Messages, Send Group Message (premium)
* Fixed issue with capabilities not being added automatically on plugin activation (a refresh was necessary before

= 0.16.2 =
* When a user is deleted their posts and pages are kept by being reassigned to the user doing the deleting action, and then being trashed.
* Invoices from WP-Invoice are displayed in a metabox in the CRM profile.

= 0.16.1 =
* Added ability to force download premium features.
* Styling fixes to Settings page "Plugin" tab.

= 0.16 =
* User CSV Export.
* WP-Invoice items can be viewed on the user's CRM profile page.
* Minor UI Improvements.

= 0.15 =
* Renamed jquery.cookies.js to jquery.smookie.js to avoid issues with certain hosts that blocks files with the word 'cookie' in the name.
* Many updates to Contact Forms feature.  Shortcode can now have use_current_user and success_message arguments. use_current_user argument pre-fills the form with currently logged in user's information, and success_message sets the message to display upon successful submittal of form.
* Added ability to mark attributes as required.
* If a Shortcode Form does not have a message, it is not displayed on the "Messages" screen after submitted.  Shortcode Forms can effectively be used as front-end profile updating tools.
* Fields can be set to be "uneditable".  This can be used for adding a field such as "User Login" which is set by WordPress, and in most cases should not be directly editable.
* Added "Description" field, which allows descriptions to be displayed next to input fields on profile screen.
* Added function to delete log entries.
* Added wp_crm_after_{$slug}_input filter which is ran after an input field is rendered.
* Added wp_crm_render_input action which can be used for custom input types.
* Fixed Entry Log date picker styles.
* Minor UI Improvements on overview and settings pages.

= 0.14 =
* UI fixes to overview screen (roles are now collapsed too)
* Ability to build predefined attribute lists from taxonomies.
* Ability to add JavaScript callback function to shortcode forms.
* Ability to call shortcode forms via AJAX
* Few fixes to DataTable sorting.
* UI improvements to the way checkboxes are displayed.
* Minor change to collapsible filters UI on overview page.

= 0.13 =
* Added option to disable "All" users instead of paginating.
* Added a wp_crm_add_to_user_log() function for easy user log modification.
* Improved wp_crm_save_user_data() for better API.

= 0.12 =
* Fixed some issues with contact form user creation.
* Fixed issue with user deletion.
* Added option to have WP-CRM replace default User Management menu.

= 0.11 =
* Minor fix with message notifications.
* Better screenshots included.

= 0.10 =
* Added screenshots.

= 0.09 =
* Minor release.
* Adding tutorial videos to WordPress plugin page.
* Few UI fixes.

= 0.08 =
* Initial public release.
