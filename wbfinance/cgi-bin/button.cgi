#!/usr/bin/perl -w

# $Id$

use POSIX "strftime";
use Data::Dumper;
use MIME::Base64;
use CGI;
use Image::Magick;

use vars qw{ $cache_file $site $font $fontsize $align $margin $datadir };
$font = "andale";
$fontsize = "12";
$align = "cx1";
$margin = 3;

sub fail {
  my $message = shift;

  print "Content-type: text/plain\n\n";
  print "Error";
  print ":".$message if ($message);
  exit();
}

sub make_image {
  my ($label, $type, $cache_file) = @_;

  $font = "../client_data/ttf/".$font.".ttf";
  unless (-f $font) {
    print STDERR "$! ".$font;
    fail('No such font');
  }
  
  my ($y, $x);
  my $cache = 1;

  # Read background image
  my $filename="../client_data/boutons/".$type.".png";
  my $image = new Image::Magick->new();
  $image->Set(dither=>'False');
  my $err = $image->ReadImage($filename);
  unless ($err eq "") {
    print STDERR "$! $filename $err";
    fail('');
  }
  ($width, $height) = $image->get('columns', 'rows');

  # Font métrics
  my $dummy = new Image::Magick;
  $dummy->Read('null:white');
  my @metric = $dummy->QueryFontMetrics( 
                                   text=>$label, 
                                   font=>$font, 
                                   pointsize=>$fontsize,
                                 );
  undef $dummy;
  # Metric contains : 
  #   0 character width
  #   1 character height
  #   2 ascender
  #   3 descender
  #   4 text width
  #   5 text height
  #   6 maximum horizontal advance
  my $text_height = $metric[2] - $metric[3];
  my $text_width = $metric[4];

  %tmp = (
      text => $label,
      font => $font,
      pointsize => $fontsize,
      fill => '#000000'
   );

  if ($align =~ /([^x]+)x([^x]+)/) {
    my ($pos_x, $pos_y) = ($1, $2);

    if ($pos_y eq "t") { $y = $margin+$text_height; }
    elsif ($pos_y eq "m") { $y = int( $height / 2 ); }
    elsif ($pos_y eq "b") { $y = $height-$margin; }
    elsif ($pos_y =~ m/^[0-9]+$/) { $y = $pos_y+$text_height; }
    else {$y = $margin; }

    if ($pos_x eq "l") { $x = $margin; }
    elsif ($pos_x eq "r") { $x = $width-$text_width-$margin; }
    elsif ($pos_x eq "c") { $x = int( ($width-$text_width) / 2 ); }
    elsif ($pos_x =~ m/^[0-9]+$/)  { $x = $pos_x; }
    else { $x = $margin; }

    $tmp{'geometry'} = sprintf("+%d+%d", $x, $y);
  }

  $image->Annotate( %tmp );

  binmode STDOUT;
  $image->Write(file=>\*STDOUT, "image.png");
  if ($cache) {
    $image->Write($cache_file);
  }
  close(STDOUT);

  undef $image;

  return 1;
}

my $cgi = new CGI;
my $missing = "";
unless(defined $cgi->param('data')) { fail('no data'); }

my $data = decode_base64($cgi->param('data'));
my @data = split(/:/, $data);

print "Content-Type: image/png\n\n";

$data[$#data+1] = "../htdocs/imgs/boutons/".$cgi->param("data").".png";
make_image( @data );
