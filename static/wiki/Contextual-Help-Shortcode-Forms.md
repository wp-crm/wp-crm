Shortcode Forms, which can be used for contact forms, or profile editing, are setup here, and then inserted using a shortcode into a page, or a widget. The available shortcode form attributes are taken from the WP-CRM attributes, and when filled out by a user, are mapped over directly into their profile. User profiles are created based on the e-mail address, if one does not already exist, for keeping track of users.

### Shortcode Forms Attributes

* `display_notes` = [ true | **false** ] — If a note exists for an attribute, it will be shown on the right.
* `require_login_for_existing_users` = [ **true** | false ]
* `use_current_user` = [ **true** | false ]
* `success_message` = "_custom text_" — default value is "Your message has been sent. Thank you.".
* `submit_text` = "_custom text_" — default value is "Submit wp-crm".
* `js_callback_function` = "_custom_function_name_" — default value is "false".
* `js_validation_function` = "_custom_function_name_" — default value is "false".

### Example Usage
The shortcode can be inserted into any WordPress section that renders shortcodes. 
```
[wp_crm_form form=example_from display_notes=true success_message="Your message was successfully sent!" submit_text="Send message!"]
```
If a new user fills out a form, an account will be created for them based on the specified role.

### Important Notes
The user's email attribute should have slug `user_email`.

