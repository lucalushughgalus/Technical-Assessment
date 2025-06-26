<?php
function isIpAllowed($ip, $allowedRanges)
{
    //Checking if IP is in a valid format
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }

    //Converting given IP into an integer, making it easier to check if it is in range
    $ipLong = ip2long($ip);

    //Looping through allowed ranges 
    foreach ($allowedRanges as $range) {
        $range = trim($range);

        //If range contains "/" it will be seen as a CIDR block
        if (strpos($range, '/') !== false) {
            list($subnet, $mask) = explode('/', $range);
            if (!filter_var($subnet, FILTER_VALIDATE_IP)) {
                continue;
            }

            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);

            if (($ipLong & $maskLong) === ($subnetLong & $maskLong)) {
                return true;
            }
        } elseif (strpos($range, '-') !== false) {
            list($start, $end) = array_map('trim', explode('-', $range));
            if (!filter_var($start, FILTER_VALIDATE_IP) || !filter_var($end, FILTER_VALIDATE_IP)) {
                continue;
            }

            $startLong = ip2long($start);
            $endLong = ip2long($end);

            if ($ipLong >= $startLong && $ipLong <= $endLong) {
                return true;
            }
        } else {
            if ($ip === $range) {
                return true;
            }
        }
    }

    return false;
}

// Test data
$allowedIps = [
    '192.168.1.1', //A standalone IP
    '192.168.1.10 - 192.168.1.100', // A range of IPs
    '10.0.0.0/8', // A CIDR block
];

// Get the IP of the current user
$clientIp = $_SERVER['REMOTE_ADDR'];

// Running the function with the test data
if (isIpAllowed($clientIp, $allowedIps)) {
    echo "Given IP is in the list: $clientIp";
} else {
    echo "Given IP is not in the list: $clientIp";
    exit;
}
?>