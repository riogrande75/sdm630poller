# sdm630poller
Reads data from SDM630 power meter

This script reads data from 2 modbus-rtu powermeters (SDM630) with modbus ID's 1+8. In my case ID 1 is for the household consumption and ID 8 is a own smartmeter for my heat pump.
This data gets written into 2 files in the ramdisk of my rasperry2 (/tmp/ACTsdm630.txt and tmp/ACTsdm630WP.txt) and also in share memory objects. Pls. use shared memory objects for inter-task communication - the textfiles are for manual verification only.
The data can be used e.g. with any logger programm (meterN,..) or also with my other scripts.

I added sdm630poller_serial.php for exemplary usage of this script via usb2serial adapter with the help of PhpSerialModbus
