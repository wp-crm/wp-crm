### Using WooCommerce Billing and Shipping Fields

When a customer places an order using WooCommerce a user account is create for them. The user information is stored in what's called "User Meta". Other plugins, including WP-CRM, can use this user meta as well. 

To have the WooCommerce's user meta be accessible in WP-CRM you will need to add the fields you are interested in on the CRM -> Settings -> Data page. Below is an example of me having added the "Shipping Address" and working on the "Shipping Postcode". 

![](https://storage.googleapis.com/media.usabilitydynamics.com/2016/10/crm-woo.png)

Notice that I have not yet fixed the Title of the Shipping Postcode to make it human-readable. We must first create the new fields using the same exact naming convention as used by WooCommerce, save the settings, and then we can change the Titles to whatever we want.

Below is a list of the User Meta fields that WooCommerce stores information in.

**Billing Information:**

* `billing_first_name`
* `billing_last_name`
* `billing_phone`
* `billing_email`
* `billing_company`
* `billing_address_1`
* `billing_address_2`
* `billing_postcode`
* `billing_city`
* `billing_state`
* `billing_country`

**Shipping Information**

*   shipping_first_name
*   shipping_last_name
*   shipping_phone
*   shipping_email
*   shipping_company
*   shipping_address_1
*   shipping_address_2
*   shipping_postcode
*   shipping_city
*   shipping_state
*   shipping_country

Depending on your needs, you could also select some of those attributes to show up on the WP-CRM user overview table.

![](https://storage.googleapis.com/media.usabilitydynamics.com/2016/10/crm-woo2.png)

Enabling Attribute Grouping
To keep things nicely organized in the CRM, you may also want to enable "Attribute Grouping", which you may do on the Main WP-CRM Settings tab.

![](https://storage.googleapis.com/media.usabilitydynamics.com/2016/10/crm-woo3.png)

In the above example you can see that I've added two new "Attribute Groups", one for Shipping and one for Billing. Since there are a bunch of fields for each, we can put all of our new fields into the appropriate group.

![](https://storage.googleapis.com/media.usabilitydynamics.com/2016/10/crm-woo4.png)

When you pull up a user's CRM profile, you will now see that the data is organized much better.

Thanks for using WP-CRM, and please feel free to reach-out to us if you find anything to be inaccurate.