#!/usr/bin/perl -w
#
#    This file is part of Webfinance.

#     Webfinance is free software; you can redistribute it and/or modify
#     it under the terms of the GNU General Public License as published by
#     the Free Software Foundation; either version 2 of the License, or
#     (at your option) any later version.

#     Webfinance is distributed in the hope that it will be useful,
#     but WITHOUT ANY WARRANTY; without even the implied warranty of
#     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#     GNU General Public License for more details.

#     You should have received a copy of the GNU General Public License
#     along with Webfinance; if not, write to the Free Software
#     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

# $Id: fill_ldap_tree.pl 531 2007-06-13 12:32:31Z thierry $
#
# FIXME : Complete pod

=h1 NAME fill_ldap.pl

Extrait de la base locale backoffice les personnes et se connecte à OpenLDAP
pour y entrer les adresses.

=cut

use strict;
use DBI;
use MIME::Base64;
use Net::LDAP;
use Data::Dumper;
use Getopt::Mixed "nextOption";
use vars qw{ %vars $dbh $ldap };

%vars = (
  'db-user'   => "",
  'db-pass'   => "",
  'db-host'   => "localhost",
  'db-name'   => "",
  'base-dn'   => '',
  'bind-dn'   => '',
  'ldap-host' => 'localhost',
  'bind-password' => '',
  'verbose'   => 0,
  'delete'    => 1,
);


=h2 OPTIONS

=h3 LDAP options

  --ldap-host           OpenLDAP server
  --base-dn             Root of the address book
  --bind-dn, -D         Bind to LDAP as this user
  --bind-password, -w   Password

  --db-host, -M         MySQL server
  --db-name             Base name
  --db-user             MySQL user
  --db-password, -p     MySQL password

=cut
Getopt::Mixed::init("ldap-host=s L>ldap-host "
                   ."bind-dn=s D>bind-dn "
                   ."base-dn=s "
                   ."db-user=s "
                   ."db-name=s "
                   ."db-pass=s p>db-pass "
                   ."db-host=s M>db-host "
                   ."bind-password=s w>bind-password "
                   ."verbose:i v>verbose "
                   ."delete:i d>delete "
);

while (my ($option, $value) = nextOption()) {
  if ($value eq "") { $value = 1 };
  $vars{$option} = $value;
}

foreach my $required ('db-user', 'db-pass', 'db-host', 'db-name', 'base-dn', 'bind-dn', 'ldap-host', 'bind-password') {
  die("--$required is a required parameter") unless ($vars{$required} ne "");
}


# Connexion à la base et à l'annuaire
$dbh = DBI->connect("DBI:mysql:".$vars{'db-name'}.";host=".$vars{'db-host'}, $vars{'db-user'}, $vars{'db-pass'});
if (!$dbh) {
  print "Couldn't connect to MySQL";
  exit(1);
}
$ldap = Net::LDAP->new( 'localhost' ) or die "$@";
if (!$ldap) {
  print "Could not connect to LDAP directory\n";
  exit(2);
}
my $mesg = $ldap->bind( $vars{'bind-dn'}, password => $vars{'bind-password'} );
if ($mesg->code != 0) {
  print "Unable to bind as admin\n";
  exit(3);
}

# $result = $ldap->add( 'cn=Nicolas Bouthors,dc=nbi,dc=fr',
#                       attr => [
#                        'cn'   => ['Nicolas Bouthors', 'Nico'],
#                        'sn'   => 'Nicolas Bouthors',
#                        'mail' => 'nbouthors@nbi.fr',
#                        'objectclass' => ['top', 'person',
#                                          'organizationalPerson',
#                                          'inetOrgPerson' ],
#                      ]
#                    );
# $result->code && warn "failed to add entry: ", $result->error ;
# exit();

$mesg = $ldap->search( # perform a search
                      base   => "dc=nbi,dc=fr",
                      filter => "ou=AB"
                    );

my @foo = $mesg->entries;

if ($#foo != 0) {
  print "Address Book root entry does not exist, creating\n";
  my $result = $ldap->add( 'ou=AB,dc=nbi,dc=fr',
                           attr => [ 'ou' => 'AB',
                                     'objectClass' => 'organizationalUnit' ]
                         );

  if ($result->code) {
    print "Failed to add entry: ".$result->error;
    die();
  }
}

$mesg = $ldap->search( # perform a search
                      base   => $vars{'base-dn'},
                      filter => "(objectClass=inetOrgPerson)"
                    );

if ($vars{delete}) {
  print "Deleting entries\n" if ($vars{verbose});
  for my $entry ($mesg->entries) {
    print "  Deleting ".$entry->dn()."\n" if ($vars{verbose});
    $ldap->delete( $entry->dn() );
  }
}

# Get all names from the database and add them to the directory. First delete them if they exists
my $s;
$s = $dbh->prepare("SELECT p.prenom as sn,
                           p.nom as givenName,
                           p.fonction as title,
                           p.email as mail,
                           p.mobile,
                           p.tel as telephoneNumber,
                           p.fax as facsimileTelephoneNumber,
                           c.nom as o,
                           c.cp as postalCode,
                           c.ville as l,
                           c.addr1, c.addr2, c.addr3
                    FROM personne p, client c
                    WHERE p.client=c.id_client
                    ORDER BY p.nom
                    ");
$s->execute();
while (my $row = $s->fetchrow_hashref()) {
  my @attrs = ();

  if ($row->{sn} ne "") {
    push(@attrs, 'cn'); push(@attrs, $row->{sn}." ".$row->{givenName});
    push(@attrs, 'objectClass'); push(@attrs, 'abzillaPerson');
    push(@attrs, 'objectClass'); push(@attrs, 'inetOrgPerson');
    foreach my $attribute (keys %$row) {
      if ((defined $row->{$attribute}) && ($row->{$attribute} ne "")) {
        if ($attribute =~ m/addr[0-9]/) {
          push(@attrs, 'street');
        } else {
          push(@attrs, $attribute);
        }
        push(@attrs, $row->{$attribute});
      }
    }
    my $status;
    my $result = $ldap->add( 'cn='.$row->{sn}." ".$row->{givenName}.",".$vars{'base-dn'}, attr => [ @attrs ] );
    if ($result->code) {
      $status = "ERR ".$result->error;
    } else {
      $status = "OK";
    }
    if (($status ne "OK") || ($vars{verbose})) {
      printf("%-40s %s\n", 'cn='.$row->{sn}.",".$vars{'base-dn'}, $status);
    }
  }
}
$s->finish();

$mesg = $ldap->unbind;   # take down session
