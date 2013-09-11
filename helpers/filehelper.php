<?php
/**
 * Initialize Filesystem object
 *
 * @param str $form_url - URL of the page to display request form
 * @param str $method - connection method
 * @param str $context - destination folder
 * @param array $fields - fileds of $_POST array that should be preserved between screens
 * @return bool/str - false on failure, stored text on success
 **/
 // Thanks to http://www.webdesignerdepot.com/2012/08/wordpress-filesystem-api-the-right-way-to-operate-with-local-files/
function namaste_filesystem_init($form_url, $method, $context, $fields = null) {
    global $wp_filesystem;    
    
    /* first attempt to get credentials */
    if (false === ($creds = request_filesystem_credentials($form_url, $method, false, $context, $fields))) {
        
        /** if we come here - we don't have credentials  so the request for them is displaying
         * no need for further processing **/
        return false;
    }
    
    /* now we got some credentials - try to use them*/        
    if (!WP_Filesystem($creds)) {        
        /* incorrect connection data - ask for credentials again, now with error message */
        request_filesystem_credentials($form_url, $method, true, $context);
        return false;
    }
    
    return true; //filesystem object successfully initiated
}