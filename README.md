# Fever-Feed Hawk Adapter

Fever-Feed Hawk Adapter adds an API to [Fever](http://feedafever.com/) that makes 
it possible for [Feed Hawk](https://www.goldenhillsoftware.com/feed-hawk/) to subscribe 
to and unsubscribe from feeds.

## Requirements:

Fever-Feed Hawk Adapter requires:

* A Fever host running an Apache server with PHP. Any Apache server with PHP that can run Fever should 
be suitable, but PHP 5.5 or 5.6 is recommended.

* An SSL certificate signed by a certificate authority. Fever-Feed Hawk adapter does not accept 
accept requests that do not use HTTPS connections. Feed Hawk does not with services 
without HTTPS. Feed Hawk does not accept invalid or self-signed certificates. [Let's Encrypt](https://letsencrypt.org/)
provides free SSL certificates.

## Installation Instructions:

1. Copy the "adapter" directory to your Apache server. Put it in a location and set its 
permissions such that Apache will serve its contents.

2. Inside the "adapter" directory, rename "config-template.php" to "config.php". Edit 
"config.php", setting the value of the "FEVER_ROOT" configuration to the path of your 
fever directory.

3. Load the "adapter/adapter.php" file with a web browser. If configured correctly, it 
should return the following JSON: `{"adapter_api_version":1}`

4. Load the "adapter/index.html" file with a web browser. Enter your Fever credentials and 
click or tap "Test" to verify that your installation is working correctly.

After Fever-Feed Hawk adapter is installed and working correctly, [download Feed Hawk](https://itunes.apple.com/us/app/feed-hawk/id1093873777?ls=1&mt=8).
When Feed Hawk asks for the URL of Fever-Feed Hawk Adapter, enter the URL returned by the successful 
test result of Step 4.

# Support

Fever-Feed Hawk Adapter is written by John Brayton of Golden Hill Software. Please direct 
any support requests to [Golden Hill Software](mailto:support@goldenhillsoftware.com), and 
not to the developer of Fever.