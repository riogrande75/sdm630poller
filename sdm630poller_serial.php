#!/usr/bin/php
<?php
// Include Php Serial Modbus Class
require 'PhpSerialModbus.php';
// Create an instance of the class
$modbus = new PhpSerialModbus;
// Initialize port
$modbus->deviceInit('/dev/ttyUSB0',19200,'none',8,1,'none');
// Open port
$modbus->deviceOpen();
// Enalbe debug
$modbus->debug = false;

$debug = 0;
$filename1 = "/tmp/ACTsdm630.txt";
$pause = 1;
$counter = 0;

// Create Shared Memory objects for data output
$sh_sdm6301 = shmop_open(0x6301, "c", 0777, 32);
if (!$sh_sdm6301) {echo "Couldn't create shared memory segment\n";}

//MAIN loop
$counter = 0;
while(1)
{
        //send query to SDM630 ID:1
        // Send raw query
        $rawquery="\x01\x04\x00\x00\x00\x4c";
        $modbus->sendRawQuery($rawquery.$modbus->crc16($rawquery),false);

        // Get response with raw data
        $byte=$modbus->getResponse(true);
        if($debug) print_r($byte);

        $wattl1_1 = round(hex2ieee754(ascii2hex(substr($byte,27,4))));
        $wattl2_1 = round(hex2ieee754(ascii2hex(substr($byte,31,4))));
        $wattl3_1 = round(hex2ieee754(ascii2hex(substr($byte,35,4))));
        $totalwatt_1 = round(hex2ieee754(ascii2hex(substr($byte,107,4))));
        $imported_1 = hex2ieee754(ascii2hex(substr($byte,147,4)));
        $exported_1 = hex2ieee754(ascii2hex(substr($byte,151,4)));

      if($debug){
        echo "1_HEX:".ascii2hex($byte)."\n";
        echo "\n SDM630 \n";
        echo "1_L1_VOLT: ".hex2ieee754(ascii2hex(substr($byte,3,4)))." V\n";
        echo "1_L2_VOLT: ".hex2ieee754(ascii2hex(substr($byte,7,4)))." V\n";
        echo "1_L3_VOLT: ".hex2ieee754(ascii2hex(substr($byte,11,4)))." V\n";
        echo "1_L1_AMPS: ".hex2ieee754(ascii2hex(substr($byte,15,4)))." A\n";
        echo "1_L2_AMPS: ".hex2ieee754(ascii2hex(substr($byte,19,4)))." A\n";
        echo "1_L3_AMPS: ".hex2ieee754(ascii2hex(substr($byte,23,4)))." A\n";
        echo "1_L1_WATT: ".hex2ieee754(ascii2hex(substr($byte,27,4)))." W\n";
        echo "1_L2_WATT: ".hex2ieee754(ascii2hex(substr($byte,31,4)))." W\n";
        echo "1_L3_WATT: ".hex2ieee754(ascii2hex(substr($byte,35,4)))." W\n";
        echo "1_FREQUEN: ".hex2ieee754(ascii2hex(substr($byte,143,4)))." Hz\n";
        echo "1_P_TOTAL: ".hex2ieee754(ascii2hex(substr($byte,107,4)))." W\n";
        echo "1_IMPO_WH: ".hex2ieee754(ascii2hex(substr($byte,147,4)))." Wh\n";
        echo "1_EXPO_WH: ".hex2ieee754(ascii2hex(substr($byte,151,4)))." Wh\n";
        }
        //write data to file
        $fd = fopen($filename1,"w");
        fprintf($fd,"1(%d*W)\n",$wattl1_1);
        fprintf($fd,"2(%d*W)\n",$wattl2_1);
        fprintf($fd,"3(%d*W)\n",$wattl3_1);
        fprintf($fd,"4(%d*W)\n",$totalwatt_1);
        fprintf($fd,"4(%d*Wh)\n",(1000*($imported_1-$exported_1)));
        fclose($fd);
        // write real values to shm obj
        shmop_write($sh_sdm6301, paddings($wattl1_1,6), 0);
        shmop_write($sh_sdm6301, paddings($wattl2_1,6), 6);
        shmop_write($sh_sdm6301, paddings($wattl3_1,6), 12);
        shmop_write($sh_sdm6301, paddings($totalwatt_1,6), 18);
        shmop_write($sh_sdm6301, paddings(round((1000*($imported_1-$exported_1))),8), 24);

        // Close device
//      $modbus->deviceClose();
        // Wait until next polling loop
        sleep($pause);
}
//END of Main
function ascii2hex($ascii) {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
                $byte = strtoupper(dechex(ord($ascii{$i})));
                $byte = str_repeat('0', 2 - strlen($byte)).$byte;
                $hex.=$byte." ";
        }
        return $hex;
}
function hex2ascii($hex){
        $ascii='';
        $hex=str_replace(" ", "", $hex);
        for($i=0; $i<strlen($hex); $i=$i+2) {
                $ascii.=chr(hexdec(substr($hex, $i, 2)));
        }
        return($ascii);
}
function hex2ieee754($strHex){
        $bin = str_pad(base_convert($strHex, 16, 2), 32, "0", STR_PAD_LEFT);
        $sign = $bin[0];
        $exp = bindec(substr($bin, 1, 8)) - 127;
        $man = (2 << 22) + bindec(substr($bin, 9, 23));
        $dec = $man * pow(2, $exp - 23) * ($sign ? -1 : 1);
        return($dec);
}
function paddings($wert,$leng){
        $neg = "-";
        $pos = strpos($wert, $neg);
        if(!(strpos($wert, $neg)===false)){
                $ohne = substr($wert,1,(strlen($wert-1)));
                $auff =  str_pad($ohne,$leng,'0',STR_PAD_LEFT);
                $final = substr_replace($auff,$neg,0,1);
        }
else    {
                $final = (str_pad($wert,$leng,'0',STR_PAD_LEFT));
        }
        return($final);
}
?>
