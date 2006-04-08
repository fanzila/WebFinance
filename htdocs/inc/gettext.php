<?
if ($language!='en_US') {
  $gettext_dictionnary_filename=$_SERVER['DOCUMENT_ROOT'] . '../lang/' . substr($language,0,2) . '/LC_MESSAGES/webfinance';

  if (!file_exists("$gettext_dictionnary_filename.mo") or
      filemtime("$gettext_dictionnary_filename.po") > filemtime("$gettext_dictionnary_filename.mo")) {
    system("msgfmt $gettext_dictionnary_filename.po -o $gettext_dictionnary_filename.mo");
  }

  setlocale(LC_ALL, $language.".UTF-8")
    or die("local LC_ALL language failed $language");

  bindtextdomain('webfinance', $_SERVER['DOCUMENT_ROOT'] . '../lang')
    or die("Set gettext bindtextdomain language failed");

  textdomain('webfinance')
    or die("Set gettext textdomain language failed");
}
?>
