<?php

/**
 * How-To use SimpleImage Class.
 *
 * SimpleImage::load it's a factory method which returns the instance or
 * null, when the path to the picture is not found.
 *
 * Picture from: http://browse.deviantart.com/?qh=&section=&q=cat#/djdwb2
 */

	require('SimpleImage.class.php');

	$image = SimpleImage::load('./picture.jpg'); 
	
	if($image)
	{
		$image->flip();
		$image->save('picture2.jpg');

		$image->resizeToWidth(400);
		$image->save('picture3.jpg');

		$image->flip();
		$image->resize(100, 100);
		$image->save('picture4.jpg');
		
		$image->convertToBlackAndWhite();
		$image->save('picture5.jpg');
		
		// The next method just stream to the browser the actual state of the image.
		// $image->output();
	}
	else
	{
		echo 'Image can\'t be loaded.';
	}

?>