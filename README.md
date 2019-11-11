# php_CRedirector
Redirect urls by regular expression based on XML list with cache

**Перенаправление по страницам по списку 
 
@version 1.4
  
  \spbitec\CRedirector::redirectFromFileXML('../../redirect.xml');
  
  Example: 
``<?xml version="1.0"?>
``<data>
`` 		<rule from="^/tickets/abonement/(.*)/b/(.*)/" to="^/tickets/abonement/(.*)/b/(.*)/" code="301"/>
`` 		<rule from="^/tickets/abonement/(.*)/s/(.*)/" to="^/tickets/abonement/(.*)/small/(.*)/" code="301"/>
`` 		<rule from="#\/sync\/(.*)#" to="$1" code="301"/>
``</data>
 

