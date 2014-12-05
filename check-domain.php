<?php
    const API_URI = 'http://www.pavietnam.vn/whois.php?domain=%s';

    const DOMAIN_COM = '%s.com';
    const DOMAIN_NET = '%s.net';

    const DELIMITER = ';';
    const AVAILABLE = '%s: Available';
    const UNAVAILABLE = '%s: Unavailable';

    $reader = fopen(__DIR__ . '/domain.csv', 'r');

    if ($reader !== false) {
        $row = 0;
        echo 'Starting !!!'.PHP_EOL;
        while (($data = fgetcsv($reader)) !== false) {
            echo $data[0].PHP_EOL;
            checkDomain($data[0]);
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: http://www.pavietnam.vn/vn/kiem-tra-ten-mien.html']);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        echo $response;
        curl_close($ch);
        return $response;
    }

    function checkDomain($domainName)
    {
        $writer = fopen(__DIR__ . '/result.csv', 'a+');
        $domains = [];
        $domains[] = sprintf(DOMAIN_COM, $domainName);
        $domains[] = sprintf(DOMAIN_NET, $domainName);

        $result = [];
        foreach ($domains as $domain) {
            if (requestCheck($domain)) {
                $result[] = sprintf(AVAILABLE, $domain);
            } else {
                $result[] = sprintf(UNAVAILABLE, $domain);
            }
        }
        fputcsv($writer, $result, DELIMITER);
        fclose($writer);
    }

?>