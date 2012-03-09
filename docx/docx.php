<?php
session_start();
?>
<html>
<head>
<?php
include '../style.php';
?>
</head>
<body>
<?php 
include '../header.php';
require_once 'PHPWord.php';

$output = $_SESSION['output'];

// Create a new PHPWord Object
$PHPWord = new PHPWord();

// Every element you want to append to the word document is placed in a section. So you need a section:
$section = $PHPWord->createSection();

// After creating a section, you can append elements:
$section->addText($_POST['output']);

// You can directly style your text by giving the addText function an array:
// $section->addText('Hello world! I am formatted.', array('name'=>'Tahoma', 'size'=>16, 'bold'=>true));

// If you often need the same style again you can create a user defined style to the word document
// and give the addText function the name of the style:
// $PHPWord->addFontStyle('myOwnStyle', array('name'=>'Verdana', 'size'=>14, 'color'=>'1B2232'));
// $section->addText('Hello world! I am formatted by a user defined style', 'myOwnStyle');

// You can also putthe appended element to local object an call functions like this:
// $myTextElement = $section->addText('Hello World!');
// $myTextElement->setBold();
// $myTextElement->setName('Verdana');
// $myTextElement->setSize(22);

// At least write the document to webspace:
$filename = $_SESSION['user']."--".date("m-d-y")."--".date("H-i-s").".docx";
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$objWriter->save($filename);
echo "<a href=\"".$filename."\">".$filename."</a>";

?>

</body>
</html>