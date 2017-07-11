<?php

$wgExtensionCredits['other'][] = array(
        'name' => 'EnforceStrongPassword',
        'version' => '0.2',
        'author' => 'Ger Apeldoorn',
        'url' => 'http://www.mediawiki.org/wiki/Extension:EnforceStrongPassword',
        'description' => 'Enforces a strong password.',
);


function isStrongPassword($password, &$return, $user) {

  //Remember to set this variable in LocalSettings.php
  global $wgMinimalPasswordLength;
  if(
    ctype_alnum($password) // numbers & digits only
    && strlen($password)>=$wgMinimalPasswordLength // at least xx chars
    && strlen($password)<17 // at most 16 chars
    && preg_match('`[A-Z]`',$password) // at least one upper case
    && preg_match('`[a-z]`',$password) // at least one lower case
    && preg_match('`[0-9]`',$password) // at least one digit
    ){
    // valid
    $return = true;
   } else {
    // not valid
    $return = false;
   }

   // This hook REPLACES the original code.
  return false;

}
