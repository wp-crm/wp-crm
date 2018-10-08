#### 1.1.7 ( October 8, 2018 )
* Fixed issue with file upload input field
* Added Compatibility with WP-Stateless plugin
* Added Support tab

#### 1.1.6 ( April 30, 2018 )
* Fixed pagination function for messages on a user page, which crashed property list pagination in WP-Property plugin.
* Turned on Google Visualization Feature.
* Removed duplicated options.
* Security fix added. Scripts no longer can be sent via a contact form or admin message.

#### 1.1.5 ( March 22, 2018 )
* Added French translation.
* Added data sanitization of the form filling.
* Added url_redirect option to the form settings.
* Fixed issue with attribute not changing its name in the form after it was changed in Data tab.
* Updated drag icon in Shortcode form tab to prevent confusing.
* Updated jQuery live() function to on().

#### 1.1.4 ( February 9, 2018 )
* Fixed connection to WP-Invoice.
* Updated libraries.
* Fixed Radio field type.
* Added Feedback form.
* Fixed Warnings.

#### 1.1.3 ( November 16, 2017 )
* Added radio field type.
* Other fixes and improvements.

#### 1.1.2 ( September 6, 2017 )
* Fixed jquery error on submitting the form.

#### 1.1.1 ( August 18, 2017 )
* Took away the opportunity of minimum user level which can manage WP-CRM to change roles and passwords of other users.
* Improved Google reCAPTCHA options and added integration with WP-Invoice plugin.
* Fixed conflict with Contact Form 7.
* Fixed empty fields issue on new contact form.

#### 1.1.0 ( June 28, 2017 )
* Fixed JS error on submitting the form.
* Fixed Network issues.
* Fixed issue with file upload field.
* Fixed Bulk action in All People tab.
* Fixed Network messages were not appearing from same profile.
* Fixed issue when the notification wasn't sending without Textarea field.
* Added captcha to the forms.
* Added new compatibility to wp-invoice plugin - "Add invoice from wp-crm user profile".
* Added new parameter to the shortcode redirect_url.

#### 1.0.6
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

#### 1.0.5
* Updated libraries.
* Fixed permission issues.
* Fixed conflict with wpMandrill plugin.

#### 1.0.4
* Updated plugin initialisation logic.
* Removed unwanted libraries.

#### 1.0.3 ( September 21, 2015 )
* Added new column to messages list with Source association.
* Fixed warnings and notices.

#### 1.0.2 ( September 1, 2015 )
* Fixed the bug when UsabilityDynamics Admin Notices could not be dismissed.
* Fixed warnings and notices.
* Fixed localizations.

#### 1.0.1 ( August 21, 2015 )
* Fixed incorrect behaviour on custom 'Install Plugins' page after depended plugins ( Add-ons ) activation.
* Fixed the way of widgets initialization. Compatibility with WordPress 4.3 and higher.
* Fixed issue for forms submissions.
* Fixed Warnings and Notices.

#### 1.0.0 ( August 3, 2015 )
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