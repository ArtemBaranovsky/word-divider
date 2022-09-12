## Environment

Apache 2.4, PHP 7.4

### Sessions, caches, complex forms
* Run ```composer install``` inside root folder to generate autoloader.
* Run ```composer dump-autoload -o```.
* Breakdown into words according to the dictionary of the Russian language from \dicts\rus_dict.dic (frequencies and parts of speech are not used).
* `cache` folder must be writable if you aren't using Windows (just use script command:   ``` composer permission  ```).
* The implementation of words dividing is based on the dictionary.
* Sending messages is not fully implemented due to limitations of the API developer (Nexmo).
* Run test via `phpunit.xml` or using  script command:   ``` composer test  ```.