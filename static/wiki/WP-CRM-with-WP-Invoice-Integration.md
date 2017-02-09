## [WP-Invoice plugin](https://www.usabilitydynamics.com/product/wp-invoice) integration:

### WP-Invoice custom field

Advanced option WP-Invoice custom field may be used for adding custom user data fields for payments forms.

![](https://storage.googleapis.com/media.usabilitydynamics.com/2016/10/crm-invoice-custom-fields.png)

### WP-Invoice Notifications

For your notifications on any of these Trigger actions — WPI: Invoice Paid (Client Receipt), WPI: Invoice Paid (Notify Administrator), WPI: Invoice Paid (Notify Creator) — you can use these shortcodes:

* [user_email] 
* [user_name] 
* [user_id] 
* [invoice_id] 
* [invoice_title] 
* [permalink] 
* [total]
* [default_currency_code] 
* [total_payments] 
* [creator_name] 
* [creator_email] 
* [creator_id] 
* [site] 
* [business_name]
* [from] 
* [admin_name]
* [admin_email]
* [admin_id]

### Storing WP-Invoice Data

When a customer pays an invoice using WP-Invoice a user account is create for them. The user information is stored in what's called "User Meta". Other plugins, including WP-CRM, can use this user meta as well. 

To have the WP-Invoice's user meta be accessible in WP-CRM you will need to add the fields you are interested in on the CRM -> Settings -> Data page. Below is list of WP-Invoice fileds:

_Note! Slug of attributes should be as provided below. You will have ability to change titles after attributes will be saved._

* last_name
* first_name
* city
* state
* zip
* streetaddress
* phonenumber
* country