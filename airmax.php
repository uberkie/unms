<?php
 
// airOS-getFile_HTTP() from Omniflux:
// https://community.ubnt.com/t5/airMAX-AC/Login-to-web-interface-with-PHP/m-p/2291860#M29858
 
// an example of fetching status.cgi from an AF24 (and probably any other UBNT radio)
// the output is a bunch of JSON with the current radio stats
 
// airOS_getFile_HTTP ($username, $password, $file, $address, $schema)
 
#$out = airOS_getFile_HTTP ("root", "xxx", "status.cgi", "10.210.48.2", "https"); // CPE mng
#$out = airOS_getFile_HTTP ("galileo", "xxx", "status.cgi", "10.210.12.247", "http"); // settore
// change details here
$out = airOS_getFile_HTTP ("ubnt", "ubnt", "status.cgi?", "192.168.0.20", "https"); // CPE
print $out;
 
function airOS_getFile_HTTP ($username, $password, $file, $address, $schema)
 
{
        $ch = curl_init();
 
        // Setup CURL
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, null);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, Array ('Expect: '));
 
        // Login AirOS >= 8.5.0+ OR get cookie with session ID for AirOS < 8.5.0
        curl_setopt ($ch, CURLOPT_URL, "$schema://$address/api/auth");
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, Array ('username' => $username, 'password' => $password));
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        $response = curl_exec ($ch);
// uncomment to see raw response
//print "resp: $response\n";
        curl_setopt ($ch, CURLOPT_HEADER, 0); 
 
        // AirOS >= 8.5.0 request and return file
 
        if (curl_getinfo ($ch, CURLINFO_HTTP_CODE) == 200)
        {
                // Get X-CSRF-ID value
                preg_match ('/X-CSRF-ID: .*/', substr ($response, 0, curl_getinfo ($ch, CURLINFO_HEADER_SIZE)), $XCSRFID);
 
                curl_setopt ($ch, CURLOPT_URL, "$schema://$address/$file");
                curl_setopt ($ch, CURLOPT_POST, 0);
                $retfile = curl_exec ($ch);
 
                curl_setopt ($ch, CURLOPT_URL, "$schema://$address/logout.cgi");
                curl_setopt ($ch, CURLOPT_HTTPHEADER, Array (trim ($XCSRFID[0]), 'X-AIROS-LUA: 1'));
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, Array());
                curl_exec ($ch);
        }
 
        // Login failed, try AirOS < 8.5.0 login, request, and return file
        else
        {
                curl_setopt ($ch, CURLOPT_URL, "$schema://$address/login.cgi");
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, Array ('username' => $username, 'password' => $password));
                curl_exec ($ch);
 
                curl_setopt ($ch, CURLOPT_URL, "$schema://$address/$file");
                curl_setopt ($ch, CURLOPT_POST, 0);
                $retfile = curl_exec ($ch);
        }
 
        curl_close ($ch);

         $json = json_decode($retfile, true);
         $value = $json['wireless']['throughput']  ?? '' ;

         $tx = $value['tx'] /1024;
         $rx = $value['rx'] /1024;

         $rows['name'] = 'Tx';
         $rows['data'][] = $tx;
         $rows2['name'] = 'Rx';
         $rows2['data'][] = $rx;

         $result = array();
         array_push($result,$rows);
         array_push($result,$rows2);
         print json_encode($result, JSON_NUMERIC_CHECK);



};

?>
