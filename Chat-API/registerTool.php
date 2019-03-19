<?php

require_once('src/Registration/Registration.php');

$debug = true;

echo "####################\n";
echo "#                  #\n";
echo "# WA Register Tool #\n";
echo "#                  #\n";
echo "####################\n";

// echo "\n\nUsername (country code + number, do not use + or 00): ";
// $username = str_replace('+', '', trim(fgets(STDIN)));
// if (!preg_match('!^\d+$!', $username)) {
//     echo "Wrong number. Do NOT use '+' or '00' before your number\n";
//     exit(0);
// }

$settings = new Settings("../src/wadata/$argv[1]/settings-$argv[1].dat");
$identityExists = $settings->get('recovery_token');

$w = new Registration($argv[1], $debug);

if (is_null($identityExists)) {
    // echo "\n\nType sms or voice: ";
    // $option = fgets(STDIN);

    if ($argv[2]=="req"){

        try {
            $w->codeRequest("sms");
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            exit(0);
        }
        echo "Requested OTP";
    }

    // echo "\n\nEnter the received code: ";
    // $code = str_replace('-', '', fgets(STDIN));
    else{
        try {
          $w->codeRegister($argv[2]);
        } catch(Exception $e) {
          echo $e->getMessage() . "\n";
          exit(0);
        }
        $w->checkCredentials();
    }
} else {
    try {
        $result = $w->checkCredentials();
    } catch (Exception $e) {
        echo $e->getMessage()."\n";
        exit(0);
    }
}
