<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function img_filetype($filename, $fix_filename = false)
{
	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	$filetype = exif_imagetype($filename);
	
	if ( ! in_array($filetype, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG)))
		return false;
	
	$to_rename = false;
	$ext_std = '';
	
	switch ($filetype) {
		case IMAGETYPE_GIF:
			$ext_std = 'gif';
			if ($ext != $ext_std) $to_rename = true;
			break;
		case IMAGETYPE_PNG:
			$ext_std = 'png';
			if ($ext != $ext_std) $to_rename = true;
			break;
		case IMAGETYPE_JPEG:
			$ext_std = 'jpg';
			if ($ext != $ext_std && $ext != 'jpeg') $to_rename = true;
	}
	
	if ($fix_filename && $to_rename) {
		$newfilename = preg_replace('/'.$ext.'$/', $ext_std, $filename);
		if (rename($filename, $newfilename))
			$filename = $newfilename;
	}
	return array('filetype' => $filetype, 'filetype_ext' => $ext_std, 'filename' => $filename, 'ext' => $ext);
}

function img_resize($filename, $maxwidth, $minwidth = 0, $maxheight = 0, $minheight = 0, $fix_filename = true, $frame_aspect_ratio = null, $upscaleToMin = false)
{
	//log_message('debug', __METHOD__.' $filename = '.var_export($filename, true));
	list($width, $height) = getimagesize($filename);
	
	if ($width <= $maxwidth && $width >= $minwidth && $height >= $minheight) {
		if (empty($maxheight) || $height <= $maxheight)
			return $filename;
	}
	
	$newwidth = empty($maxwidth) ? $width : $maxwidth;
	if ($upscaleToMin && ! empty($minwidth) && $width < $minwidth) {
		$newwidth = $minwidth;
	}
	$newheight = ceil($height * ($newwidth/$width));
	//logmes('$newwidth = ', $newwidth, __FUNCTION__.'-log');
	//logmes('$newheight = ', $newheight, __FUNCTION__.'-log');
	
	if ( ! empty($maxheight) && $newheight > $maxheight)
		return false;
	
	if ($upscaleToMin && ! empty($minheight) && $newheight < $minheight) {
		$newheight = $minheight;
		$newwidth = ceil($width * ($newheight/$height));
		//logmes('- $newwidth = ', $newwidth, __FUNCTION__.'-log');
		//logmes('- $newheight = ', $newheight, __FUNCTION__.'-log');
		if ($newwidth < $minwidth ||  ! empty($maxwidth) && $newwidth > $maxwidth) 
			return false;
	}
	//logmes('-- $newwidth = ', $newwidth, __FUNCTION__.'-log');
	//logmes('-- $newheight = ', $newheight, __FUNCTION__.'-log');
	
	$frame_width = $newwidth > $minwidth ? $newwidth : $minwidth;
	//$frame_height = $newheight > $minheight ? $newheight : $minheight;
	if ( ! empty($frame_aspect_ratio)) {
		$frame_height = ceil($frame_width / $frame_aspect_ratio);
		if ($frame_height < $newheight) {
			$frame_height = $newheight > $minheight ? $newheight : $minheight;
			$frame_width = ceil($frame_height * $frame_aspect_ratio);
		}
	}
	else {
		$frame_height = $newheight > $minheight ? $newheight : $minheight;
	}
	//logmes('-- $frame_width = ', $frame_width, __FUNCTION__.'-log');
	//logmes('-- $frame_height = ', $frame_height, __FUNCTION__.'-log');
	
	//logmes('$filename = ', $filename, __FUNCTION__.'-log');
	//$filetype = preg_replace("/.*?\./", '', $filename);
	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	$filetype = exif_imagetype($filename);
	//logmes('$ext = ', $ext, __FUNCTION__.'-log');
	//logmes('$filetype = ', $filetype, __FUNCTION__.'-log');
	
	if ( ! in_array($filetype, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG)))
		return false;
	 
	$img_props = img_filetype($filename, $fix_filename);
	$filename = $img_props['filename'];
	
	switch ($filetype) {
		case IMAGETYPE_GIF:
			$source = imagecreatefromgif($filename);
			break;
		case IMAGETYPE_PNG:
			$source = imagecreatefrompng($filename);
			break;
		case IMAGETYPE_JPEG:
			$source = imagecreatefromjpeg($filename);
			$exif_data = exif_read_data($filename); //Reads the EXIF headers from JPEG or TIFF
			logmes('$filename = ', $filename, __FUNCTION__.'-log');
			logmes('$exif_data = ', $exif_data, __FUNCTION__.'-log');
	}
	 
	if (isset($source)) {
		//$dst = imagecreatetruecolor($newwidth, $newheight);
		$dst = imagecreatetruecolor($frame_width, $frame_height);
		
		imagesavealpha($dst, true);
		
		$trans_colour = imagecolorallocatealpha($dst, 255, 255, 255, 127);
		imagefill($dst, 0, 0, $trans_colour);
		
		//imagecopyresized($dst, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagecopyresampled($dst, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); 
		/*
		imagecopyresampled copies a rectangular portion of one image to another image, 
		smoothly interpolating pixel values so that, in particular, reducing the size of an image still retains a great deal of clarity. 
		*/

		switch ($filetype) {
			case IMAGETYPE_GIF:
				imagegif($dst, $filename);
				break;
			case IMAGETYPE_PNG:
				imagepng($dst, $filename);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($dst, $filename);
		}
		return $filename;
	}
	else 
		return false;
}	
	
/* End of file */