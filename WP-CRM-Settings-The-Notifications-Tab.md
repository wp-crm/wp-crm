This tutorial will cover the options present under the ‘Notifications’ Tab in WP-CRM Settings.

* * *

On the very right, under Trigger Actions, there is a list of forms from which you can choose any to set a notification for. You can have multiple forms located at different places on your website. You will be able to see a list of all these forms. Along with the forms you will also be able to see a list of notification actions if you have set any in the wp_crm_send_notifications() function. Select the appropriate form or action.

[![](https://storage.googleapis.com/media.usabilitydynamics.com/2012/02/8c578f3b-wp-crm-notifications.png)](//storage.googleapis.com/media.usabilitydynamics.com/2012/02/8c578f3b-wp-crm-notifications.png)

### The Header

Under there, you can set the,

*   Subject: what the notification subject you want set every time a user uses the form, E.g. Subject: General Contact Message, Products A inquiry and so on.
*   To: Here you put the email of the person you want notified. It need not necessarily be you. It can be some person from you group or company with deals with requests coming for that form. E.g. [tech_support@abc.com](mailto:tech_support@abc.com)
*   BCC: Set an email address if you want to send a blind carbon copy to someone, E.g. [lead_tech_support@abc.com](mailto:lead_tech_support@abc.com)
*   Send From: Enter an email address or a name and email address using the format: John Smith

### Available tags

Which tags are available depend on the trigger event, but in most cases all user data slugs can be used. On a shortcode form message, **[message_content]**, **[profile_link]** and **[trigger_action]** variables are also available.

**Some other available tags:**

*   [post_id]
*   [post_title]
*   [post_link]


Also you will find some notifications which already set up for you as examples.

### Trigger Actions

This section gives you a list of options to choose from, which act as the trigger for that particular notification to be fired. E.g. You can set up a custom message to be sent out to your users, say, 'Thank You! ', when they contact you through a particular form, in this case, say, ' The Feedback Form'.

To see list of variables you can use in notifications open up the "Help" tab and view the user data structure. Any variable you see in there can be used in the subject field, to field, BCC field, and the message body. Example: [user_email] would include the recipient's e-mail.

To add notification actions use the **wp_crm_notification_actions** filter, then call the action within **wp_crm_send_notification()** function, and the messages association with the given action will be fired off.

As always, ‘Add Row’ can be used to add as many notifications as you would like and Delete can be used to delete a particular notification.

### Messages

The messages option lets you set a custom message that can be sent out to you users or to you everytime an email with a certain subject or certain parameters is received