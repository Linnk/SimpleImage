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
	public function __destruct()
	{
		imagedestroy($this->image);
	}
	public function save($filename, $image_type = null, $compression = 100, $permissions = null)
	{
		if(is_null($image_type))
			$image_type = $this->image_type;

		if($image_type == IMAGETYPE_JPEG)
		{
			imagejpeg($this->image, $filename, $compression);
		}
		elseif($image_type == IMAGETYPE_GIF)
		{
			imagegif($this->image, $filename);         
		}
		elseif($image_type == IMAGETYPE_PNG)
		{
			imagepng($this->image, $filename);
		}

		if($permissions != null)
			chmod($filename, $permissions);
	}
	public function output($send_headers = true, $image_type = null)
	{
		if(is_null($image_type))
			$image_type = $this->image_type;

		if($send_headers === true)
			header('Content-Type: '.$image_type);

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
	public function resizeToWidth($width)
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getHeight() * $ratio;

		$this->__resize($width,$height);
	}
	public function resizeToHeight($height)
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;

		$this->__resize($width,$height);
	}
	public function scale($scale)
	{
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100; 

		$this->__resize($width,$height);
	}
	private function __resize($width, $height)
	{
		$new_image = imagecreatetruecolor($width, $height);

		imagealphablending($new_image, true);
		imagesavealpha($new_image, true);
		imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 244, 244, 244, 127));
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

		$this->image = $new_image;
	}
	public function resize($width, $height, $cropped = true)
	{
		if($cropped === true)
		{
			$new_image = imagecreatetruecolor($width, $height);

			$ratio = $width / $this->getWidth();
			$new_height = $this->getHeight() * $ratio;

			if($new_height >= $height)
			{
				$this->resizeToWidth($width);
				$x = 0;
 				$y = ($height/2 - ($this->getHeight()-$height)/2);
			}
			else
			{
				$this->resizeToHeight($height);
 				$x = ($width/2 - ($this->getWidth()-$width)/2);
				$y = 0;
			}

			imagecopy($new_image, $this->image, 0, 0, $x, $y, $this->getWidth(), $this->getHeight());

			$this->image = $new_image;
		}
		else
		{
			$this->__resize($width,$height);
		}
	}
	public function convertToBlackAndWhite()
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		$bwimage = imagecreate($width, $height); 

		for ($c=0;$c<256;$c++) 
			$palette[$c] = imagecolorallocate($bwimage,$c,$c,$c);

		for ($y=0;$y<$height;$y++)
		{
			for ($x=0;$x<$width;$x++)
			{
				$rgb = imagecolorat($this->image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$gs = ($r*0.299)+($g*0.587)+($b*0.114);
				imagesetpixel($bwimage,$x,$y,$palette[$gs]);
			}
		}

		$this->image = $bwimage;
	}
	public function flip($mode = 'both')
	{
		$src_width = $width = $this->getWidth();
		$src_height = $height = $this->getHeight();

	    $src_y = $src_x = 0;

	    switch ($mode)
	    {
	        case 'vertical':
	            $src_y = $height - 1;
	            $src_height = $height * (-1);
				break;

	        case 'horizontal':
	            $src_x = $width - 1;
	            $src_width = $width * (-1);
				break;

	        case 'both':
	            $src_x = $width - 1;
	            $src_y = $height - 1;
	            $src_width = $width * (-1);
	            $src_height = $height * (-1);
				break;

	        default:
	            return false;
	    }

	    $new_image = imagecreatetruecolor($width, $height);

	    if($success = imagecopyresampled($new_image, $this->image, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height))
			$this->image = $new_image;

		return $success;
	}
}
