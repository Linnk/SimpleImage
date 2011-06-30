<?php

/**
 * ImageNotFoundException Class.
 *
 * This is class for when the Image it's not found.
 */

	class ImageNotFoundException extends Exception { }

/**
 * SimpleImage Class.
 *
 * This is class for simple manipulation of images with PHP.
 * Original Author: Unknow.
 * Recent modification's author: Linnk.
 */

	class SimpleImage 
	{
		private $image;
		private $image_type;

		public static function load($filename)
		{
			try
			{
				return new SimpleImage($filename);
			}
			catch(ImageNotFoundException $e)
			{
				return null;
			}
		}

		public function __construct($filename)
		{
			if(!file_exists($filename))
				throw new ImageNotFoundException();

			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];

			if($this->image_type == IMAGETYPE_JPEG)
			{
				$this->image = imagecreatefromjpeg($filename);
			}
			elseif($this->image_type == IMAGETYPE_GIF)
			{
				$this->image = imagecreatefromgif($filename);
			}
			elseif($this->image_type == IMAGETYPE_PNG)
			{
				$this->image = imagecreatefrompng($filename);
			}
			else
				throw new ImageNotFoundException();
		}

		public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 100, $permissions = null)
		{
			if($image_type == IMAGETYPE_JPEG)
			{
				imagejpeg($this->image,$filename,$compression);
			}
			elseif($image_type == IMAGETYPE_GIF)
			{
				imagegif($this->image,$filename);         
			}
			elseif($image_type == IMAGETYPE_PNG)
			{
				imagepng($this->image,$filename);
			}
			
			if($permissions != null)
				chmod($filename,$permissions);
		}

		public function output($image_type = IMAGETYPE_JPEG)
		{
			if($image_type == IMAGETYPE_JPEG)
			{
				imagejpeg($this->image);
			}
			elseif($image_type == IMAGETYPE_GIF)
			{
				imagegif($this->image);         
			}
			elseif($image_type == IMAGETYPE_PNG)
			{
				imagepng($this->image);
			}   
		}

		public function getWidth()
		{
			return imagesx($this->image);
		}

		public function getHeight()
		{
			return imagesy($this->image);
		}
		
		public function getImageType()
		{
			return $this->image_type;
		}

		public function resizeToHeight($height)
		{
			$ratio = $height / $this->getHeight();
			$width = $this->getWidth() * $ratio;

			$this->resize($width,$height);
		}

		public function resizeToWidth($width)
		{
			$ratio = $width / $this->getWidth();
			$height = $this->getheight() * $ratio;

			$this->resize($width,$height);
		}

		public function scale($scale)
		{
			$width = $this->getWidth() * $scale/100;
			$height = $this->getheight() * $scale/100; 

			$this->resize($width,$height);
		}

		public function resize($width,$height)
		{
			$new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

			$this->image = $new_image;
		}
	}

?>