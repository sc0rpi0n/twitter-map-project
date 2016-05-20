# twitter-map-project
Mapping tweets to a Map


Installation:

1. Make sure you have installed Composer and Laravel Framework version 5.2.32
2. Rename the ".env_sample" to ".env"
3. Run command "php artisan key:generate" to generate new application key
4. Add the database information in the new ".env" file
4. Browse to the folder with composer.json and run "composer install" command to install all dependency vendors in command line
5. Run "php artisan migrate" to create the database tables.


Customization:

To change the radius of tweet search for the search location , set the tweet_radius in config/customsettings.php file to desired radius.

To change the cache time , set the cache_hour in config/customsettings.php file to desired number or hours.
