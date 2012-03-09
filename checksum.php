<html>
<body>
<?php

$str = "some random text www.tinyurl.com/9uxdwc some http://google.com random text http://tinyurl.com/787988 []sdflg3jh4uit3t4//gr9)()DAs..df>>>?S<SF,,,,";

$str = ereg_replace("\[]\(\),<>","",$str);

echo $str;


?>
</body>
</html>