## Translating plugins

We have added all of out plugins on poeditor.com, so you can easily translate them online, from any platform and without the need for extra software. The process is as follows:

Click on the plugin you want to translate:

*   [WP-Property](https://poeditor.com/join/project?hash=e6080cc6109f49084594177fe32a94fa) or [WP-Property 2.*.*](https://poeditor.com/join/project/R0aH1eYeoH)
*   [WP-Invoice](https://poeditor.com/join/project?hash=4fb815f048354b22ebbdd4d4073da478) or [WP-Invoice 4.*.*](https://poeditor.com/join/project/xmyL1ArmJv)
*   [WP-CRM](https://poeditor.com/join/project?hash=bcf9852ea63016bd72bb0c7ab1378f55) or [WP-CRM 1.*.*](https://poeditor.com/join/project/c0r4XOtYs6)

_The difference between old(WP-Property) and new(WP-Property 2.*.*) projects that old contains all strings from plugin and premium features. As far as for new projects it’s add-ons are separate plugins, so new project on Poeditor.com contain only plugin’s strings._

You will be taken to the project joining screen of poeditor.com. Click on the checkboxes for the languages you need, add your email and name and then click “Join”:

![poeditor project join screen](https://storage.googleapis.com/media.usabilitydynamics.com/2013/05/Screen-Shot-2013-05-31-at-12.06.57-PM.png)

**Note:** If a language you want to translate to does not exist in the list, [contact us](https://usabilitydynamics.com/contact-us/) including the plugin’s name and the language(s) you want and we’ll add it ASAP.

Poeditor.com will email you a password and an activation link. Save your password somewhere (we really like [1password](https://agilebits.com/onepassword) for this) and click on the activation link. You will be taken to a screen in which you will be able to [see the projects](https://poeditor.com/projects/?registered=contributor) you are collaborating on and the languages you have chosen to work on. On the right of each line, there is the percentage of the translation which is finished (you can take the translation from where another person left off):

[![Shared projects](https://storage.googleapis.com/media.usabilitydynamics.com/2013/05/Screen-Shot-2013-05-31-at-12.12.01-PM.png)](https://poeditor.com/projects/?registered=contributor)

Click on the language you wish to work on and you will see the language screen:

![Screen Shot 2013-05-31 at 1.09.54 PM](https://storage.googleapis.com/media.usabilitydynamics.com/2013/05/Screen-Shot-2013-05-31-at-1.09.54-PM.png)

Here you just translate the terms from the left to the fields on the right. As you do so, the percentage on the top left will get bigger and bigger. A couple of things to note:

*   Try to make the translation short. English, as a language, is shorter than some others, meaning that translations will mostly have more characters. If the translation is longer than the original, the text will turn red as you type. That’s ok, but try to keep it short so the styling does not break on the plugin UIs.
*   Some strings cannot be translated perfectly and could have different translations. If you encounter a string like this, mark it as “Fuzzy”, by clicking on the “F” button at the end of the string line (see the screenshot above, translations marked as fuzzy change the color of the “F” button to orange.
*   Feel free to improve translations by other users, but if you do, please add a comment in english describing why you did it, by using the comment button (on the left of the “F” button).
*   A great way to improve translations is to select “Fuzzy” from the “Show” dropdown on top of the page. This will show you translations users have marked as fuzzy, and will allow you to improve them.

Once you are finished, just click on the language options dropdown on the top right of the page and choose “Export”. It is suggested that you export to both mo and po. After this, just save the files to the languages folder of your plugin (either lang or languages) and WordPress should recognize it on the next refresh. Do not forget to set the installation language in the wp-options.php file in the root folder.

![Exporting terms](https://storage.googleapis.com/media.usabilitydynamics.com/2013/05/Screen-Shot-2013-05-31-at-1.24.08-PM.png)