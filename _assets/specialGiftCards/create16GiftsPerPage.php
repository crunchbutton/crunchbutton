<?php

ini_set('max_execution_time', 300);

/*
	To create the gift cards the first thing we need to do is create a image with this gift card
	and after that we create a pdf file with this image inside.
	I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
	More info: http://us.moo.com/help/faq/using-my-own-artwork.html
*/

// Txt file with the codes
$codes = file( 'codes.txt' );

// Fonts that will be used
$fontAmbleBold = 'assets/fonts/Amble-Bold.ttf';
$fontAmbleRegular = 'assets/fonts/Amble-Regular.ttf';
$fontChunkfive = 'assets/fonts/Chunkfive.ttf';

$count = 1;
// Fist create all the images

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

	imagettftext( $image, 110, 0, 80, 205, $middleGrey, $fontAmbleBold, '$2' );
	
	imagettftext( $image, 17, 0, 54, 285, $lightGrey, $fontChunkfive, 'I Am a Gift Card' );

	imagesetthickness ( $image, 5 );

	// Remove this
	// imagettftext( $image, 30, 0, 600, 300, $middleGrey, $fontAmbleBold, $count );

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

	$count++;
}


//Second create the pdf
// PDF Library
require('lib/fpdf.php');

// Page size in millimetter - (Letter)
$pageWidth = 215;
$pageHeight = 279;

$giftCardWidth = 74;
$giftCardHeight = 32;

$collumns = 2;
$rows = 8;

$marginPageTop = 10;
$marginPageLeft = 32;

$marginTop = 0;
$marginLeft = 0;


$count = 500;
$giftCards = [];

// Order in stacks
for ($i = 0; $i < $count; $i++) { 
	$giftCards[ $i ] = 'temp/' . ( $i + 1 ) . '.png';
}

$slotsPerPage = ( $collumns * $rows );
$numberOfPages = ceil( $count / $slotsPerPage );

$numberOfPages = 16;
$totalGifts = $count;
$giftsPerPosition = ceil( $totalGifts / $numberOfPages );
$left = $totalGifts % $numberOfPages;
$perPosition = array();
$giftCardsOrdered = array();
if( $left != 0 ){
	for( $i = 0; $i < $numberOfPages; $i++ ){
		if( $left > 0 ){
			$perPosition[ $i ] = $giftsPerPosition;
			$left--;
		} else {
			$perPosition[ $i ] = $giftsPerPosition - 1;
		}
	}
} else {
	for( $i = 0; $i <= $numberOfPages; $i++ ){
		$perPosition[ $i ] = $giftsPerPosition ;
	}
}
$startsAt = array();
$sum = 0;
for( $i = 0; $i < sizeof( $perPosition ); $i ++ ){
	$startsAt[ $i ] = $sum;
	$sum = $sum + $perPosition[ $i ];
}
for( $i = 0; $i < $giftsPerPosition; $i++ ){
	for( $j = 1; $j <= $numberOfPages; $j++ ){
		if( sizeof( $giftCardsOrdered ) < sizeof( $giftCards ) ){
			$index = $startsAt[ $j - 1 ] + $i;
			$giftCardsOrdered[] = $giftCards[ $index ];
		}
	}
}

// Create the pdf
$pdf = new FPDF( 'P', 'mm', array( $pageWidth, $pageHeight ) );

$pdf->AddPage();

$row = 1;
$collumn = 1;

$page = 1;
$giftCardsOnThisPage = 0;
$giftCardsOnThisRow = 0;

// Draw vertical the cut lines 
$pdf->SetLineWidth( 0.01 );
$pdf->SetDrawColor( 175, 175, 175 );
for( $j = 0; $j <= $collumns; $j++ ){
	$l = $marginPageLeft + ( $j * $giftCardWidth );
	$pdf->Line( $l, 0, $l, $pageHeight);	
}
for( $j = 0; $j <= $rows; $j++ ){
	$t = $marginPageTop + ( $j * $giftCardHeight );
	$pdf->Line( 0, $t, $pageWidth, $t );
}


for ( $i = 0; $i < $count; $i++ ) { 
	
	$imgsrc = $giftCardsOrdered[ $i ];

	$positionY = ( ( ( $row - 1 ) * ( $giftCardHeight + $marginTop ) ) + $marginPageTop );
	$positionX = ( ( ( $collumn - 1 ) * ( $giftCardWidth + $marginLeft ) ) + $marginPageLeft );
	
	$pdf->Image( $imgsrc, $positionX, $positionY, -300, 'PNG' );

	$collumn = ( $collumn == 1 ) ? 2 : 1;
	if( $collumn == 1 ){
		$row++;	
	}
	
	$giftCardsOnThisPage++;
	
	if( $giftCardsOnThisPage == ( $collumns * $rows ) ){
		$pdf->AddPage();
		for( $j = 0; $j <= $collumns; $j++ ){
			$l = $marginPageLeft + ( $j * $giftCardWidth );
			$pdf->Line( $l, 0, $l, $pageHeight);	
		}
		for( $j = 0; $j <= $rows; $j++ ){
			$t = $marginPageTop + ( $j * $giftCardHeight );
			$pdf->Line( 0, $t, $pageWidth, $t );
		}
		$giftCardsOnThisPage = 0;
		$row = 1;
		$collumn = 1;
	}
}



$pdf->Output( 'pdfs/GiftCards.pdf' );

echo 'done!';
?>