<?php
class Url
{
    /**
     * Check if an url is existed
     *
     * @param  string    $url
     * @access static
     * @return bool      True if the url is accessible and false if the url is unaccessible or does not exist
     * @throws Exception An exception will be thrown when Curl session fails to start
     */
    public static function exists($url) {
        if (null === $url || '' === trim($url))
        {
            throw new Exception('The url to check must be a not empty string');
        }
       
        $handle   = curl_init($url);

        if (false === $handle)
        {
            throw new Exception('Fail to start Curl session');
        }

        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);

        // grab Url
        $connectable = curl_exec($handle);

        // close Curl resource, and free up system resources
        curl_close($handle);   
        return $connectable;
    }
}
?>