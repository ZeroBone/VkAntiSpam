# VkAntiSpam
Intelligent, integrated and self-learning antispam system for filtering spam in VK groups.
# Usage
* Drop the project files except the `install` directory in your webserver's public directory (Make sure that PHP 7.2 support is enabled).

* Create a MySQL database with `utf8_general_ci` collation.

* Run the query from `install/tables.sql` on your MySQL database. This will create all nessesary tables. 

* Specify your database credentials as well as security keys in `config.php`. 

* Go to `/account/register` and create your initial account with the administrator priveleges.

* **Important**: Remove or comment-out the `define('VAS_IN_INSTALLATION', true);` line in `config.php`.