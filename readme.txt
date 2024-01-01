=== GLS Labels ===
Contributors: caglarop
Tags: gls, labels, shipping, parcel, cancellation
Requires at least: 5.6
Tested up to: 6.4.2
Requires PHP: 7.4
Stable tag: 1.0.4
Version: 1.0.4
License: MIT License
License URI: https://opensource.org/licenses/MIT

== Description ==

The GLS Labels is a WordPress plugin that integrates with the GLS Web API for Parcel Processing and Parcel Cancellation, allowing the creation of GLS shipping labels directly from your WordPress dashboard.

= Compatibility =

The GLS Labels is compatible with PHP versions from 7.4 to 8.3.

= Features =

- Integration with GLS Web API for Parcel Processing and Parcel Cancellation.
- Creation of GLS shipping labels and return labels.
- User-friendly interface with a metabox in the order details page.
- Automatically adds a note to the order with the consignment number and a download link for the label.
- Translated into English, German, and Turkish.

== Installation ==

You can install the GLS Labels directly from the WordPress admin panel:

1. Go to the Plugins menu and click "Add New".
2. Search for "GLS Labels".
3. Click "Install Now" and then "Activate".

For manual installation:

1. Download the plugin and unzip it.
2. Upload the unzipped folder to your WordPress plugin directory (`/wp-content/plugins/`).
3. Activate the plugin via the WordPress dashboard.

== Usage ==

After activating the plugin, you can configure it by going to your WordPress dashboard and navigating to "Settings" -> "GLS Labels". Here you can enter your GLS user ID, your password, your shipper number, and your shipper address.

To create a shipping or return label, go to the order page and click on "Create Shipping Label" or "Create Return Label" in the GLS metabox. The selected label will then be created, and a note will be added to the order with the consignment number and a download link for the label.

== Support ==

If you need support or have any questions, please create a new issue in our GitHub repository.

== License ==

MIT License

Copyright (c) 2024 Candan Tombas

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.