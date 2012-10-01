<?php

/**
 * How-To use SimpleImage Class.
 *
 * SimpleImage::load it's a factory method which returns the instance or
 * null, when the path to the picture is not found.
 */

	require('SimpleImage.class.php');

	$image = SimpleImage::load('./picture.jpg'); // Picture from: http://browse.deviantart.com/?qh=&section=&q=cat#/djdwb2
	
	if($image)
	{
		$image->flip();
		$image->save('picture2.jpg');

		$image->resizeToWidth(500);
		$image->save('picture3.jpg');

		$image->scale(20);
		$image->save('picture4.jpg');
		
		// The next method just stream to the browser the actual state of the image.
		// $image->output();
	}
	else
	{
		echo 'Image can\'t be loaded.';
	}

?>