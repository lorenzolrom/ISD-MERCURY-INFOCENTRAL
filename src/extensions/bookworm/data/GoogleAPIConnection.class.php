<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 3/24/2019
 * Time: 7:20 PM
 */


namespace extensions\bookworm\data;


use extensions\bookworm\BookWormConfig;

class GoogleAPIConnection
{
    /**
     * Searches a title
     *
     * @param string $title
     * @return array
     */
    public static function searchTitle(string $title): array
    {
        $response = self::curlRequest("?q=" . urlencode($title) .
            "&fields=items(id,volumeInfo/title,volumeInfo/authors,volumeInfo/publisher,volumeInfo/publishedDate,volumeInfo/imageLinks/thumbnail)");

        $response = json_decode($response, TRUE);

        if(!is_array($response))
            return array();
        else
            return $response;
    }

    /**
     * Returns details on a specific volume
     *
     * @param string $id
     * @return array
     */
    public static function getVolume(string $id): array
    {
        $response = self::curlRequest("/" . $id, FALSE);

        return json_decode($response, TRUE);
    }

    /**
     * @param string $url
     * @param bool $noGet Should key be preceded by ? or &
     * @return bool|string
     */
    private static function curlRequest(string $url, bool $noGet = TRUE)
    {
        // Add title to query
        $ch = curl_init(BookWormConfig::CONFIG['APIURL'] . $url .
            ($noGet ? "&" : "?") . "key=" . BookWormConfig::CONFIG['APIKey']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        return curl_exec($ch);
    }
}