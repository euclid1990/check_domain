<?php
    const API_URI = 'https://instantdomainsearch.com/all/%s?tldTags=popular&limit=20&hilite=strong';

    const DOT_COM = 'com';
    const DOT_NET = 'net';

    const DELIMITER = ';';
    const AVAILABLE = '%s: Available';
    const UNAVAILABLE = '%s: Unavailable';

    $reader = fopen(__DIR__ . '/domain.csv', 'r');

    if ($reader !== false) {
        $row = 0;
        echo 'Starting !!!'.PHP_EOL;
        while (($data = fgetcsv($reader)) !== false) {
            echo $data[0].PHP_EOL;
            checkDomain('i'.$data[0]);
            echo PHP_EOL;
        }
        echo 'Completed !!!';
        fclose($reader);
    } else {
        exit();
    }

    function requestCheck($domain)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(API_URI, $domain));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: https://instantdomainsearch.com/']);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        $response = split("\n",$response);
        curl_close($ch);
        return $response;
    }

    function checkDomain($domainName)
    {
        $writer = fopen(__DIR__ . '/result-i.csv', 'a+');

        $result = [];
        $domains = requestCheck($domainName);
        if (empty($domains)) {
            fputcsv($writer, $result, DELIMITER);
            fclose($writer);
            return;
        }
        foreach ($domains as $domain) {
            $data = json_decode($domain, true);
            if (isset($data['label']) &&
                    $data['label'] == $domainName &&
                        ($data['tld'] == DOT_COM || $data['tld'] == DOT_NET) &&
                            isset($data['isRegistered']) && !$data['isRegistered']) {
                $result[] = sprintf(AVAILABLE, $domainName.'.'.$data['tld']);
            } elseif (isset($data['label']) &&
                    $data['label'] == $domainName &&
                        ($data['tld'] == DOT_COM || $data['tld'] == DOT_NET) &&
                            isset($data['isRegistered']) && $data['isRegistered']) {
                $result[] = sprintf(UNAVAILABLE, $domainName.'.'.$data['tld']);
            }
        }
        fputcsv($writer, $result, DELIMITER);
        fclose($writer);
    }

?>