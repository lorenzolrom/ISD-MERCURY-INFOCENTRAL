#!/usr/bin/perl

#
# show_dhcp_fixed_ACKs.pl - script to show the most recent DHCP ACKs per IP address for ISC DHCPd,
#   from a log file. Originally written for Vyatta routers that just show the dynamic leases.
#
# To use this, you need to have dhcpd logging to syslog, and your syslog server putting the log file at
# /var/log/user/dhcpd (or a file path specified by the $logfile variable below.
#
# To accomplish this on Vyatta 6.3, run:
# set service dhcp-server global-parameters "log-facility local2;"
# set system syslog file dhcpd facility local2 level debug
# set system syslog file dhcpd archive files 5
# set system syslog file dhcpd archive size 3000
# commit
#
# Copyright 2011 Jason Antman <jason@jasonantman.com> All Rights Reserved.
# This script is free for use by anyone anywhere, provided that you comply with the following terms:
# 1) Keep this notice and copyright statement intact.
# 2) Send any substantial changes, improvements or bog fixes back to me at the above address.
# 3) If you include this in a product or redistribute it, you notify me, and include my name in the credits or changelog.
#
# The following URL always points to the newest version of this script. If you obtained it from another source, you should
# check here:
# $HeadURL$
# $LastChangedRevision$
#
# CHANGELOG:
# 2011-12-24 jason@jasonantman.com:
#    initial version of script
#
#

use strict;
use warnings;

use lib '/opt/vyatta/share/perl5';
use Vyatta::Config;

my $logfile = "/var/log/user/dhcpd";

my %data = ();

open DF, $logfile or die $!;
while ( my $line = <DF> ) {
    if ( $line !~ m/dhcpd: DHCPACK/) { next;}
    chomp $line;
    if ($line =~ m/([A-Za-z]+\s+[0-9]+ [0-9]{1,2}:[0-9]{2}:[0-9]{2}) [^\/x]+ dhcpd: DHCPACK on (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) to ((?:[0-9a-f]{2}[:-]){5}[0-9a-f]{2}) via (.+)/) {
        $data{"$2"}->{'mac'} = lc("$3");
        $data{"$2"}->{'date'} = "$1";
        $data{"$2"}->{'if'} = "$4";
        $data{"$2"}->{'ip'} = "$2";    
        $data{"$2"}->{'name'} = '';    
    } elsif ($line =~ m/([A-Za-z]+\s+[0-9]+ [0-9]{1,2}:[0-9]{2}:[0-9]{2}) [^\/x]+ dhcpd: DHCPACK on (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) to ((?:[0-9a-f]{2}[:-]){5}[0-9a-f]{2}) \(([^\)]+)\) via (.+)/) {
        $data{"$2"}->{'mac'} = lc("$3");
        $data{"$2"}->{'date'} = "$1";
        $data{"$2"}->{'if'} = "$5";
        $data{"$2"}->{'ip'} = "$2";    
        $data{"$2"}->{'name'} = "$4";    
    } elsif ($line =~ m/([A-Za-z]+\s+[0-9]+ [0-9]{1,2}:[0-9]{2}:[0-9]{2}) [^\/x]+ dhcpd: DHCPACK to (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) \(([^\)]+)\) via (.+)/) {
        $data{"$2"}->{'mac'} = lc("$3");
        $data{"$2"}->{'date'} = "$1";
        $data{"$2"}->{'if'} = "$4";
        $data{"$2"}->{'ip'} = "$2";    
        $data{"$2"}->{'name'} = '';    
    } elsif ($line =~ m/([A-Za-z]+\s+[0-9]+ [0-9]{1,2}:[0-9]{2}:[0-9]{2}) [^\/x]+ dhcpd: DHCPACK to (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) ((?:[0-9a-f]{2}[:-]){5}[0-9a-f]{2}) \(([^\)]+)\) via (.+)/) {
        $data{"$2"}->{'mac'} = lc("$3");
        $data{"$2"}->{'date'} = "$1";
        $data{"$2"}->{'if'} = "$5";
        $data{"$2"}->{'ip'} = "$2";    
        $data{"$2"}->{'name'} = "$4";    
    } else {
        print "regex err [$line]\n";
    }
}
close DF;


my %map_hash = ();
my $config = new Vyatta::Config;
my $path = 'service dhcp-server shared-network-name';
$config->setLevel($path);
my @networks = $config->listOrigNodes();
foreach my $net (@networks) {
   $config->setLevel("$path $net subnet");
   my @subnets = $config->listOrigNodes();
   foreach my $sub (@subnets) {
      $config->setLevel("$path $net subnet $sub static-mapping");
      my @mappings = $config->listOrigNodes();
      foreach my $map (@mappings) {
         $config->setLevel("$path $net subnet $sub static-mapping $map");
         my $ip = $config->returnOrigValue('ip-address');
         my $mac = $config->returnOrigValue('mac-address');
         $mac = lc($mac);
         $map_hash{$mac}{'ip'} = $ip;
         $map_hash{$mac}{'name'} = $map; 
      }
   }
}

my $format = "%-15s %-17s %-18s %-15s %s\n";
printf($format, "IP Address", "Hardware Address", "Date", ,"Name", "Interface");
printf($format, "----------", "----------------", "----", ,"----", "---------");

# begin sort by IP address
my @keys =
  map  substr($_, 4) =>
  sort
  map  pack('C4' =>
    /(\d+)\.(\d+)\.(\d+)\.(\d+)/)
    . $_ => (keys %data);
# end sort by IP address

my $tot_count = 0;
my $static_count = 0;
foreach my $key (@keys) {
    my $mac = $data{$key}{'mac'};
    if (defined $map_hash{$mac}{'ip'}) {
       $static_count++;
       my $name = $map_hash{$mac}{'name'};
       if ($data{$key}{'name'} eq '') {
          $data{$key}{'name'} = "$name (static)";
       } else {
          $name = $data{$key}{'name'};
          print "hit $name\n";
          $data{$key}{'name'} = "$name (static)";
       }
    }
    printf($format, $data{$key}{'ip'}, $mac, $data{$key}{'date'}, $data{$key}{'name'}, $data{$key}{'if'});
    $tot_count++;
}
print "\nStatic leases: $static_count\n";
print   "Total leases : $tot_count\n\n";