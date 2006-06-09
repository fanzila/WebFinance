#!/usr/bin/perl
#
# This file is part of « Webfinance »
# 
# Copyright (c) 2004-2006 NBI SARL
# Author : Nicolas Bouthors <nbouthors@nbi.fr>
# 
# You can use and redistribute this file under the term of the GNU GPL v2.0
#
# $Id$
#
# TODO : Remove system() calls, use standard perl functions
#        Make it work with -w and use strict

use DBI;

$db = $ARGV[0]||"webfinance";
$host = $ARGV[1]||"localhost";
$verbose = $ARGV[2]||0;
$pid =  $$;

$dsn = "DBI:mysql:database=$db;host=$host";
$dbh = DBI->connect($dsn, $login, $pass) or die("Can't connect $host $db $login $pass");

# Check existence of cvslog table
$s = $dbh->prepare("SHOW TABLES LIKE 'cvslog'");
$s->execute();
if ($s->rows == 0) {
  print "Table cvslog does not exist in $db at $host, creating\n";
  $q = "CREATE TABLE cvslog (
          id int(11) NOT NULL auto_increment,
          file varchar(255),
          date datetime,
          added int(11) NOT NULL default '0',
          deleted int(11) NOT NULL default '0',
          state varchar(50),
          author varchar(100),
          revision varchar(100),
           KEY(file),
           KEY(author),
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
  $s = $dbh->prepare($q);
  $s->execute() or die("Could not create table cvslog in $db at $host\n");
} 

mkdir("/tmp/cvslog2sql.$pid");
system("cp -a CVS /tmp/cvslog2sql.$pid");
chdir("/tmp/cvslog2sql.$pid");

print "Getting first release of each file (this might take some time in Madagascar)...\n";
system("cvs up -r 1.1 -d > /dev/null 2>&1 ");

# Should UPDATE table /me jumps
print "Truncating table\n";
$s = $dbh->prepare("TRUNCATE TABLE cvslog");
$s->execute();

if (! -f "logfile") {
  print "Getting output of CVS log\n";
  system("cvs log > logfile 2>/dev/null");
}
%files = ();
$current_file = "";
$current_revision = "";

open(LOG, "logfile");
while ($ligne = <LOG>) {
  chomp($ligne);


  if ($ligne =~ m!Working file: (.*)!) {
    $current_file = $1;
    $files{$current_file} = 1;
    printf("Working on : %-80s", $current_file) if ($verbose);
  }

  if ($ligne =~ m!revision ([0-9.]+)!) {
    $current_revision = $1;
    print "." if ($verbose);
  } elsif ($ligne =~ m!date: ([^;]+);  author: (\w+);  state: (Exp);  lines: ([+0-9-]+) ([+0-9-]+)!) {
    ($date, $author, $state, $added, $deleted) = ($1, $2, $3, $4, $5);
    $query = "INSERT INTO cvslog (file, date, author, added, deleted, state, revision) values('$current_file', '$date', '$author', '$added', '$deleted', '$state', '$current_revision');";

    $s = $dbh->prepare($query);
    $s->execute();
  }  elsif ($ligne =~ m!date: ([^;]+);  author: (\w+);  state: (\w+);$!) {
    # Find number of lines of very first commit (not shown in cvs log)
    # FIXME : exclude binary files from this 
    ($date, $author, $state) = ($1, $2, $3);

    $orig_lines = qx{ wc -l $current_file };
    $orig_lines =~ m!^([0-9]+)!;
    $lines = $1;

    $query = "INSERT INTO cvslog (file, date, author, added, deleted, state, revision) values('$current_file', '$date', '$author', '$lines', 0, '$state', '$current_revision')";
    $s = $dbh->prepare($query);
    $s->execute();

    print "Initial release for $current_file counted $lines lines\n" if ($verbose);
  } else {
#     print "$ligne\n";
  }
}

my @binary_extensions = (
    '.dia', '.doc', '.gif', '.jpg', '.ods', '.png', '.ps',
    '.psd', '.svgz', '.txt', '.xls', '.gz', '.bz2');

for $ext (@binary_extensions) {
  $s = $dbh->prepare("DELETE FROM cvslog WHERE file LIKE '\%$ext'");
  $s->execute();
}

chdir("/tmp");
system("rm -rf cvslog2sql.$pid");
