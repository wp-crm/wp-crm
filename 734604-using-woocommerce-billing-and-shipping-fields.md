When a customer places an order using WooCommerce a user account is create for them. The user information is stored in what's called "User Meta". Other plugins, including WP-CRM, can use this user meta as well. 

To have the WooCommerce's user meta be accessible in WP-CRM you will need to add the fields you are interested in on the CRM -> Settings -> Data page. Below is an example of me having added the "Shipping Address" and working on the "Shipping Postcode". 

![](https://i.embed.ly/1/image?url=http%3A%2F%2Fcontent.screencast.com%2Fusers%2FAndyPotanin%2Ffolders%2FJing%2Fmedia%2F2dc49c07-8ff9-48f1-9d64-d54e72ff3060%2F00000691.png&key=afea23f29e5a4f63bd166897e3dc72df)

Notice that I have not yet fixed the Title of the Shipping Postcode to make it human-readable. We must first create the new fields using the same exact naming convention as used by WooCommerce, save the settings, and then we can change the Titles to whatever we want.

Below is a list of the User Meta fields that WooCommerce stores information in:

*   billing_first_name
*   billing_last_name
*   billing_phone
*   billing_email
*   billing_company
*   billing_address_1
*   billing_address_2
*   billing_postcode
*   billing_city
*   billing_state
*   billing_country

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

![](https://i.embed.ly/1/image?url=http%3A%2F%2Fcontent.screencast.com%2Fusers%2FAndyPotanin%2Ffolders%2FJing%2Fmedia%2F23992417-7c9f-4e8f-b554-b2309b4e0707%2F00000692.png&key=afea23f29e5a4f63bd166897e3dc72df)

Enabling Attribute Grouping
To keep things nicely organized in the CRM, you may also want to enable "Attribute Grouping", which you may do on the Main WP-CRM Settings tab.

![](https://i.embed.ly/1/image?url=http%3A%2F%2Fcontent.screencast.com%2Fusers%2FAndyPotanin%2Ffolders%2FJing%2Fmedia%2F779a4eef-c0c1-4d80-b2d4-fa896633302b%2F00000693.png&key=afea23f29e5a4f63bd166897e3dc72df)

In the above example you can see that I've added two new "Attribute Groups", one for Shipping and one for Billing. Since there are a bunch of fields for each, we can put all of our new fields into the appropriate group.

![](https://i.embed.ly/1/image?url=http%3A%2F%2Fcontent.screencast.com%2Fusers%2FAndyPotanin%2Ffolders%2FJing%2Fmedia%2F8613b3dd-9bf9-46af-abb4-321527b24e11%2F00000694.png&key=afea23f29e5a4f63bd166897e3dc72df)

When you pull up a user's CRM profile, you will now see that the data is organized much better.

Thanks for using WP-CRM, and please feel free to reach-out to us if you find anything to be inaccurate.