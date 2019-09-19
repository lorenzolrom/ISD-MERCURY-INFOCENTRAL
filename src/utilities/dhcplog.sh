#!/bin/bash
echo $(ssh $1@$2 -q -i $3 "grep -w 'DHCPACK\|DHCPREQUEST' ${4}")