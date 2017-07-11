<?php
   $dbhost = 'localhost:3036';
   $dbuser = 'wiki';
   $dbpass = 'hrhk';
   
   $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   if(! $conn ) {
      die('Could not connect: ' . mysql_error());
   }
   
   $sql = 'select * from moderation where mod_id='$this->id'';
   mysql_select_db('my_wiki');
   $retval = mysql_query( $sql, $conn );
   
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
      echo "EMP ID :{$row['mod_id']}  <br> ".
	"USER NAME :{$row['mod_user_text']} <br> ".
         "CREATED PAGE : {$row['mod_title']} <br>".
         "PAGE REJECTED : {$row['mod_rejected']} <br>";
   }
   
   echo "Fetched data successfully\n";
   
   mysql_close($conn);
?>
