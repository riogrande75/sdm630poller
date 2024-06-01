#!/usr/bin/php
<?php

$sh_sdm6301 = shmop_open(0x6301, "a", 0644, 52);
if (!$sh_sdm6301) {
    echo "Couldn't open shared memory segment\n";
}
while(1){
echo "SDM630_ID1:\n";
$alles1=shmop_read($sh_sdm6301, 0, 52);

echo "Im ShMop0x6301 steht:*** ".$alles1." ***\n";
$PL1_1 = shmop_read($sh_sdm6301, 0, 6);
$PL2_1 = shmop_read($sh_sdm6301, 6, 6);
$PL3_1 = shmop_read($sh_sdm6301, 12, 6);
$P_1 =   shmop_read($sh_sdm6301, 18, 6);
$PC_1 =  shmop_read($sh_sdm6301, 24, 8);
$IC_1 =  shmop_read($sh_sdm6301, 32,10);
$EC_1 =  shmop_read($sh_sdm6301, 42,10);

echo date("D M j G:i:s")."\n";
echo "SDM630_ID1:\n";
echo "L1:  ".$PL1_1."W\n";
echo "L2:  ".$PL2_1."W\n";
echo "L2:  ".$PL3_1."W\n";
echo "P:   ".$P_1."W\n";
echo "PC:".$PC_1."Wh\n";
echo "IC:".$IC_1."Wh\n";
echo "EC:".$EC_1."Wh\n";
sleep(1);
}
shmop_close($sdm6301);
?>
