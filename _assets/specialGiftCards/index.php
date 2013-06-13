<?php

/*
	To create the gift cards the first thing we need to do is create a image with this gift card
	and after that we create a pdf file with this image inside.
	I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
	More info: http://us.moo.com/help/faq/using-my-own-artwork.html
*/

// PDF Library
require('lib/fpdf.php');

// Txt file with the codes
$codes = file( 'codes.txt' );

// Fonts that will be used
$fontAmbleBold = 'assets/fonts/Amble-Bold.ttf';
$fontAmbleRegular = 'assets/fonts/Amble-Regular.ttf';
$fontChunkfive = 'assets/fonts/Chunkfive.ttf';

$count = 1;
foreach ( $codes as  $code ){

	// First lets create the image
	$image = imagecreatefrompng( 'assets/giftcard.png' );

	// Antialiases
	imagealphablending( $image, true );
	imageantialias( $image, true );

	// Set the colors
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$black = imagecolorallocate( $image, 0, 0, 0 );
	$darkGrey = imagecolorallocate( $image, 33, 32, 32 );
	$middleGrey = imagecolorallocate( $image, 53, 53, 53 );
	$lightGrey = imagecolorallocate( $image, 105, 106, 106 );

	// Put the texts
	imagettftext( $image, 25, 0, 331, 170, $darkGrey, $fontAmbleBold, 'Claim your free food at' );
	imagettftext( $image, 20, 0, 331, 235, $darkGrey, $fontAmbleRegular, '_DOMAIN_/giftcard/' . $code );

	imagettftext( $image, 110, 0, 80, 205, $middleGrey, $fontAmbleBold, '$5' );
	imagettftext( $image, 17, 0, 54, 285, $lightGrey, $fontChunkfive, 'BIG GAY GIFT CARD' );

	imagesetthickness ( $image, 5 );

	// Path where the image wil be saved
	$imgsrc = 'temp/' . $count . '.png';
	imagepng( $image, $imgsrc );
	
	imagecolordeallocate( $image, $white );
	imagecolordeallocate( $image, $black );
	imagecolordeallocate( $image, $darkGrey );
	imagecolordeallocate( $image, $middleGrey );
	imagecolordeallocate( $image, $lightGrey );

	// Destroy the image
	imagedestroy( $image );

	// Create the pdf
	$pdf = new FPDF( 'L', 'mm', array( 74, 32 ) );
	$pdf->AddPage();
	$pdf->Image( $imgsrc ,0,0,-300, 'PNG' );
	$pdf->Output( 'pdfs/' . $count . '.pdf' );
	$count++;
}
echo 'done!';
?>