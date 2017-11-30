=== PDF Form Filler ===
Contributors: pdffillerintegrations
Donate link: http://pdffiller.com
Tags: pdf,document,form,email,pdffiller,widget,plugin
Requires at least: 4.5
Tested up to: 4.7
Stable tag: 0.1.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and embed HTML forms of fillable PDF templates on your website.

== Description ==

**Easily embed surveys, order forms, registration templates and applications on your WordPress site and securely collect information from customers and partners**

Make it easy for your visitors to submit their data directly from your blog or website. Embed fillable fields from interactive fillable forms stored in your PDFfiller account on a WordPress-powered website in just a few clicks. Your customers and partners can quickly fill out surveys, order forms, registration templates and applications from any internet connected device. No more pens, clipboards or illegible handwriting.

**How PDF Form Filler plugin works:**

* Connect your PDFfiller account to a WordPress site to access interactive fillable forms
* Embed fillable fields from interactive fillable forms on your WordPress website using shortcodes
* Choose how your customers and partners will access the fillable form from your WordPress site: via a button or a customized widget
* Receive an instant email notification after a customer completes your form. The completed PDF copy will be attached to the email as well as stored on your secure PDFfiller account.
* Don’t waste time exporting data from fillable forms. Collect information from filled in documents and easily add it to a CRM or export it to an Excel spreadsheet.
* Easily integrate PDF Form Filler with Contact Form 7 to expand the functionality of both plugins

== Installation ==

1. In your Wordpress dashboard, select Plugins > Add New
2. Use Search to find PDF Form Filler
3. Activate the plugin from Plugins > Installed Plugins
4. Plugin Configuration:
   4.1. In your Wordpress dashboard, select PDFForms > Settings
   4.2. Select **Main** and enter your authorization data from your PDFfiller account.
   To view your authorization data go to https://www.pdffiller.com/. Log in or register at PDFfiller and go to **For Business > For Developers**, or just follow the link https://www.pdffiller.com/en/developers.
   Click **CREATE NEW APP** or choose the existing app. Copy Client ID and Client Secret and paste this authorization data into the corresponding fields of the plugin’s Settings tab.
   Enter your PDFfiller account’s email address and password into the PDF Form Filler plugin’s email and password fields.
   Click **Save**. That’s it, now your WordPress website is synchronized with your PDFfiller account.
   4.3. Select **Messages** to customize messages for successful and failed form submission. **Submit Message** allows you to customize the text and title of the submission message and button.
   4.4. Select **Mail** to customize the subject and text of the email you’re sending.
   4.5. Select **Integrations** to enable and disable available integrations.
   The basic PDF Forms Filler package is integrated with the Contact Form 7 plugin. Ensure that Contact Form 7 has been installed before using.


== Screenshots ==

1. Main settings
2. Messages settings
3. Mail settings
4. Integrations settings
5. PDF forms list
6. Editing form
7. Editing form (Settings)
8. Insert field
9. Insert field (result)
10. Insert form
11. Insert form (result)
12. Link To Fill widget
13. PDF form widget
14. Integration with Contact form 7
15. PDF Form in content (frontend)
16. Widgets in sidebar

== Changelog ==

= 0.1.9 =
Changed cache timeout

= 0.1.8 =
Updated embedded js-client`s url.

= 0.1.7 =
Better api cache.
Add icon and cover image.

= 0.1.6 =
Change description.

= 0.1.5 =
Security: Disabled uploading document to media library.
Easier integration with CF7
Changed description

= 0.1.4 =
Changed message logic.
Changed cf7 integration.
And some small fix

= 0.1.3 =
Fix fields list

= 0.1.2 =
Change screenshotss

= 0.1.1 =
Small fix

= 0.1 =
First version

== Frequently Asked Questions ==

= How can I create a new form and embed it on the website? =
1. To create a new form go to your Wordpress dashboard and select **PDFForms > Add new**.
Enter a title for your form.
2. Select the required fillable document from your PDFfiller account from the drop-down menu on the right. If the list is empty, go to https://www.pdffiller.com/en/forms.htm and add fillable fields to the required document using PDFfiller’s drag and drop wizard or upload a new document.
2.1. Click on Publish. Refresh the page.
2.2. The fillable fields from the uploaded PDF are now available for inserting.
3. Click **Add Form Field** in the third row of the tools panel. The window with available fillable fields opens instantly.
3.1. Select the fields to be embedded in your Wordpress content.
3.2. Close the window and you’ll see the shortcodes of fields with specific parameters. This content can contain text, HTML layout and more.
     4. The content field features the following meta boxes applicable for a given form:
           4.1.Send document to email:
Select options for sending a PDF file to a specific email address.
           4.2. Submit button location:
   Select the position of the Submit button.
          4.3. Success message:
Customize the text for the successful submission of your form.
         4.4. Fail message:
Customize the text for the failed submission of your form.
Click **Update** when you have finished with fillable fields and other content.

  5. To publish the new form on your Wordpress website:
5.1. Add a new page or post.
5.2. Click **Add Fillable Form** in the third row of the tools panel.
5.3. Select the form you want to embed on your website.
5.4. Insert the shortcode of the form anywhere in the content.
5.5. Publish the page and check the created form on your website.

= How can I embed my form in a widget area (sidebar)? =
To place your form in a widget area and make it publicly accessible for filling:
1. Go to the standard widget section in your WordPress admin panel and add the PDF Form Filler widget to any sidebar.
2. Enter the title in Settings and select a form from the drop-down menu.
3. This form should now appear on your website.

= Can I use PDFFiller’s LinkToFill feature? =
Yes, you can. The PDF Form FIller plugin enables users to add widgets with a LinkToFill button. Clicking on it will open a popup window with a fillable form in the PDFfiller editor. You can also adjust the size of the popup window and add CSS class for the button styling.

To add the widget to the sidebar go to the standard widget section in the WordPress admin panel:
1. Place the PDFFiller ‘LinkToFill’ widget on any sidebar.
2. Open the Settings page and enter your titles for the form and button.
3. Enter the client_id and choose the LinkToFill document from your PDFfiller account.
           **Reminder**: the app with a specified client_id must include the required settings of an Embedded JS Client with the activation for your domain name.

To embed the LinkToFill button using the Text widget:
Simply insert the HTML code from the LinkToFill activation page on PDFfiller website.

= How can I integrate the PDF Form Filler plugin with the Contact Form 7 plugin? =
To integrate the PDF Form Filler plugin with Contact Form 7:
1. Ensure that the Contact Form 7 plugin has been installed.
2. Go to **PDF Form Filler > Settings** and click Integrations.
3. Enable the integration with Contact Form 7.
4. Now you can create a new form using this plugin.
**Reminder:** The field names must correspond with the fields in the document stored in your PDFfiller account.
To connect or disconnect with the specified document and enable the attachment of your created PDF document сlick on the **PDF Form Filler** tab.

== Upgrade Notice ==

= 0.1 =
First version
