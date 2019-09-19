#!/bin/bash
echo $(ssh $1@$2 -i $3 -q -o "StrictHostKeyChecking=no" "grep -w 'DHCPACK\|DHCPREQUEST' ${4}")