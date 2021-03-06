<?php
/**
 * SimpleImage Class.
 *
 * This is class for simple manipulation of images with PHP.
 * Original Author: Unknow.
 * Rescued and code repaired: Linnk.
 */
class SimpleImage
{
	/**
	 * Image.
	 *
	 * @var object
	 */
	private $image;

	/**
	 * Image type.
	 *
	 * @var string
	 */
	private $image_type;

	/**
	 * Load with failover return.
	 *
	 * @param  string $filename File name.
	 * @return SimpleImage
	 */
	public static function load($filename)
	{
		try
		{
			return new SimpleImage($filename);
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Constructor.
	 *
	 * @throws Exception The file doesn't exists.
	 * @param  string $filename File name.
	 * @return void
	 */
	public function __construct($filename)
	{
		if (!file_exists($filename))
		{
			throw new Exception('The file doesn\'t exists.');
		}

		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];

		if ($this->image_type == IMAGETYPE_JPEG)
		{
			$this->image = imagecreatefromjpeg($filename);
		}
		elseif ($this->image_type == IMAGETYPE_GIF)
		{
			$this->image = imagecreatefromgif($filename);
		}
		elseif ($this->image_type == IMAGETYPE_PNG)
		{
			$this->image = imagecreatefrompng($filename);
		}
		else
		{
			throw new Exception();
		}
	}

	/**
	 * Destructor.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		imagedestroy($this->image);
	}

	/**
	 * Save the current image untouched or modified.
	 *
	 * @param  string $filename    File name.
	 * @param  string $image_type  Image type.
	 * @param  int    $compression Compression.
	 * @param  int    $permissions Permissions.
	 * @return void
	 */
	public function save($filename, $image_type = null, $compression = 100, $permissions = null)
	{
		if (is_null($image_type))
		{
			$image_type = $this->image_type;
		}
		if ($image_type == IMAGETYPE_JPEG)
		{
			imagejpeg($this->image, $filename, $compression);
		}
		elseif ($image_type == IMAGETYPE_GIF)
		{
			imagegif($this->image, $filename);
		}
		elseif ($image_type == IMAGETYPE_PNG)
		{
			imagepng($this->image, $filename);
		}
		if ($permissions != null)
		{
			chmod($filename, $permissions);
		}
	}

	/**
	 * Output the actual binnary of the image.
	 *
	 * @param  bool   $send_headers Send headers.
	 * @param  string $image_type   Image type.
	 * @return void
	 */
	public function output($send_headers = true, $image_type = null)
	{
		if (is_null($image_type))
		{
			$image_type = $this->image_type;
		}
		if ($image_type == IMAGETYPE_JPEG)
		{
			if ($send_headers === true)
			{
				header('Content-Type: image/jpg');
			}
			imagejpeg($this->image);
		}
		elseif ($image_type == IMAGETYPE_GIF)
		{
			if ($send_headers === true)
			{
				header('Content-Type: image/gif');
			}
			imagegif($this->image);
		}
		elseif ($image_type == IMAGETYPE_PNG)
		{
			if ($send_headers === true)
			{
				header('Content-Type: image/png');
			}
			imagepng($this->image);
		}
	}

	/**
	 * Returns the current width of the image.
	 *
	 * @return int
	 */
	public function getWidth()
	{
		return imagesx($this->image);
	}

	/**
	 * Returns the current height of the image.
	 *
	 * @return int
	 */
	public function getHeight()
	{
		return imagesy($this->image);
	}

	/**
	 * Returns the image type.
	 *
	 * @return string
	 */
	public function getImageType()
	{
		return $this->image_type;
	}

	/**
	 * Resize an image proportionally to the new given width.
	 *
	 * @param  int $width New width.
	 * @return void
	 */
	public function resizeToWidth($width)
	{
		$scale = $width / $this->getWidth();
		$height = $this->getHeight() * $scale;

		$this->__resize($width, $height);
	}

	/**
	 * Resize an image proportionally to the new given height.
	 *
	 * @param  int $height New height.
	 * @return void
	 */
	public function resizeToHeight($height)
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;

		$this->__resize($width, $height);
	}

	/**
	 * Scale an image by a given ratio in % units.
	 *
	 * @param  float $scale New scale in % units.
	 * @return void
	 */
	public function scale($scale)
	{
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getHeight() * $scale / 100;

		$this->__resize($width, $height);
	}

	/**
	 * Resize the current image to a given width and height.
	 *
	 * @param  int $width  Width.
	 * @param  int $height Height.
	 * @return void
	 */
	private function __resize($width, $height)
	{
		$new_image = imagecreatetruecolor($width, $height);
		imagealphablending($new_image, true);
		imagesavealpha($new_image, true);
		imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 244, 244, 244, 127));
		imagecopyresampled(
			$new_image,
			$this->image,
			0,
			0,
			0,
			0,
			$width,
			$height,
			$this->getWidth(),
			$this->getHeight()
		);
		$this->image = $new_image;
	}

	/**
	 * Resize the current image to a given width and height, and crop the image
	 * to avoid distortion by default (optional).
	 *
	 * @param  int  $width   Width.
	 * @param  int  $height  Height.
	 * @param  bool $cropped Crop the image to fit the potential new proportion.
	 * @return void
	 */
	public function resize($width, $height, $cropped = true)
	{
		if ($cropped === true)
		{
			$new_image = imagecreatetruecolor($width, $height);

			$scale = $width / $this->getWidth();
			$ratio = $this->getWidth() / $this->getHeight();
			$new_height = $this->getHeight() * $scale;
			$new_width = $this->getWidth() * $scale;

			if ($ratio <= 1)
			{
				$this->resizeToWidth($width);
				$y = ($this->getHeight() - $height) / 2;
				$x = 0;
			}
			else
			{
				$this->resizeToHeight($height);
				$x = ($this->getWidth() - $width) / 2;
				$y = 0;
			}
			imagecopy($new_image, $this->image, 0, 0, $x, $y, $this->getWidth(), $this->getHeight());

			$this->image = $new_image;
		}
		else
		{
			$this->__resize($width, $height);
		}
	}

	/**
	 * Conversion of the color of the image to black and white tones.
	 *
	 * @return void
	 */
	public function convertToBlackAndWhite()
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		$bwimage = imagecreate($width, $height);

		for ($c = 0; $c < 256; $c++)
		{
			$palette[$c] = imagecolorallocate($bwimage, $c, $c, $c);
		}
		for ($y = 0; $y < $height; $y++)
		{
			for ($x = 0; $x < $width; $x++)
			{
				$rgb = imagecolorat($this->image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$gs = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
				imagesetpixel($bwimage, $x, $y, $palette[$gs]);
			}
		}

		$this->image = $bwimage;
	}

	/**
	 * Flip the image vertical, horizontal or both.
	 *
	 * @param  string $mode Type of flip (vertical, horizontal or both).
	 * @return bool
	 */
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

		$success = imagecopyresampled(
			$new_image,
			$this->image,
			0,
			0,
			$src_x,
			$src_y,
			$width,
			$height,
			$src_width,
			$src_height
		);
		if ($success)
		{
			$this->image = $new_image;
		}

		return $success;
	}
}
