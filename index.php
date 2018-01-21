<?php
/***********************************************************************************
MiniGallery v1.1

Gallery is works based on alone (this) file which adds linked php-files into
subfolders (which includes THIS file)

This gallery uses the FancyBox java-scripts to allow interactive preview of the images.

To define order and it's direction use a _sortby.txt file
To show another title instead filename of specific file, use the "_desc.txt" file.

Required extension: PDO-SQLite

====================================================================================
The MIT License (MIT)

Copyright (c) 2016 Vitaly Novichkov "Wohlstand" <admin@wohlnet.ru>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
***********************************************************************************/

require_once("index.options.php");

/****************************************************************/
if(file_exists(dirname(__FILE__) . "/index.lang.php"))
    require_once(dirname(__FILE__) . "/index.lang.php");
else
{
    define("LANG_RENAME", "Rename");
    define("LANG_RENAME_FILE", "Rename file");
    define("LANG_DELETE", "Delete");
    define("LANG_FOLDER_EMPTY", "Folder is empty");
    define("LANG_ADMIN_RIGHTS", "Administrator rights");

    define("LANG_SORT_BY", "Sort by");
    define("LANG_SB_DATE", "date");
    define("LANG_SB_NAME", "name");
    define("LANG_SB_DESC", "backward");

    define("LANG_PARENT_DIR", "Parent directoty...");
    define("LANG_REFRESH_THUMBS", "Refresh thumbnails");
    define("LANG_PHOTO", "Image");

    function totalElementsLabel($count)
    {
        $counter_one = $count % 10;//Units
	    $counter_ten = $count % 100 - $count % 10;//Tens
	    $counter_hng = $counter_ten + $counter_one;//Summ of Tens and Units
        echo "Totally " . $count . " element" . (($counter_one == 1) && (($counter_ten != 1) && ($counter_one != 1) ) ? "" : "s");
    }
}
/****************************************************************/

/******Simple check admin rights. You cloud improve this function to take more security than IP-address check*********/
/*
Admins are allowed:
- Rename files
- Delete files
- Change order sequence and direction from web (instead of _sortby.txt usage)
*/
function isAdminIP()
{
	global $AdminIp, $AdminIPs;
	if(isset($AdminIPs))
	{
	    foreach($AdminIPs as $ip)
	    {
	        $ret = strstr($_SERVER['REMOTE_ADDR'], $ip);
	        //echo "$ip == $ret<br>\n";
	        if($ret)
	        {
	            //echo "YES!";
	            return $ret;
            }
	    }
	}
	return strstr($_SERVER['REMOTE_ADDR'], $AdminIp);
}

/****************************************************************/

if((isset($_GET['old']))&&(isset($_GET['new']))&&(isAdminIP()))
{
	rename(utf2fs($_GET['old']), utf2fs($_GET['new']));
	header("location: ."); die();
}

if((isset($_GET['delfile']))&&(isAdminIP()))
{
	unlink(utf2fs($_GET['delfile']));
	header("location: ."); die();
}

if((isset($_GET['sorttype']))&&(isAdminIP()))
{
	if(file_exists("_gallyry.db"))
	$db = sqlite_open("_gallyry.db");

	$sortby_check_Q = sqlite_query($db, "SELECT * FROM settings WHERE option = 'sortby';");
	$sortby_check = sqlite_fetch_array($sortby_check_Q);
	if($sortby_check==NULL)
	{
		sqlite_query($db, "INSERT INTO settings (option, `value1`, `value2`) values('sortby', '".$_GET['value1']."', '".$_GET['value2']."');");
	}
	else
	{
		sqlite_query($db, "UPDATE settings SET `value1` = '".$_GET['value1']."', `value2` ='".$_GET['value2']."' WHERE option='sortby';");
	}
	header("location: ."); die();
}

if((isset($_GET['comment']))&&(isAdminIP()))
{
	if(file_exists("_gallyry.db"))
	$db = sqlite_open("_gallyry.db");

	$sortby_check_Q = sqlite_query($db, "SELECT * FROM comments WHERE filename = '".$_GET['filename']."';");
	$sortby_check = sqlite_fetch_array($sortby_check_Q);
	if($sortby_check==NULL)
	{
		sqlite_query($db, "INSERT INTO comments (`filename`, `title`, `comment`) values('sortby', '".$_GET['value1']."', '".$_GET['value2']."');");
	}
	else
	{
		sqlite_query($db, "UPDATE comments SET `title` = '".$_GET['value1']."', `comment` ='".$_GET['value2']."' WHERE filename='".$_GET['filename']."';");
	}

	header("location: ."); die();
}

//Full clean-up of cache by request
if(isset($_GET['clean_thumbs']))
{
	$files = glob('_Thumbs/*'); // get all file names
	foreach($files as $file)
	{ // iterate files
	  if(is_file($file))
		@unlink($file); // delete file
	}
	header("location: ."); die();
}

//Convert file system charset into UTF-8
function fs2utf($fname)
{
	global $FSCharset;
	if($FSCharset=="utf-8")
		return $fname;
	else
		return iconv($FSCharset,"utf-8",$fname);
}

//Convert UTF-8 charset into native file system charset
function utf2fs($fname)
{
	global $FSCharset;
	if($FSCharset=="utf-8")
		return $fname;
	else
		return iconv("utf-8", $FSCharset, $fname);
}

function sqlite_open($location,$mode=0)
{
    $handle = new SQLite3($location);
    return $handle;
}

function sqlite_query($dbhandle,$query)
{
    $array['dbhandle'] = $dbhandle;
    $array['query'] = $query;
    $result = $dbhandle->query($query);
    return $result;
}

function sqlite_fetch_array(&$result,$type=0)
{
    #Get Columns
    $i = 0;
    while ($result->columnName($i))
    {
        $columns[ ] = $result->columnName($i);
        $i++;
    }

    $resx = $result->fetchArray(SQLITE3_ASSOC);
    return $resx;
}

function renameform($filename, $AdminIp)
{
	if(isAdminIP())
	{
		return "<form method=\"get\" action=\".\">\n ".LANG_RENAME_FILE."<br><input name=\"old\" type=\"hidden\" value=\"".
		$filename."\" />\n<input name=\"new\" type=\"text\" style=\"width: 304px; height: 22px\" value=\"".
		$filename."\" />\n<br>\n<input name=\"Button1\" type=\"submit\" value=\"".LANG_RENAME."\" style=\"height: 18px; width: 105px\">\n</form>".
		"<br><form method=\"get\">".
		"<input name=\"delfile\" type=\"hidden\" value=\"".$filename."\" />".
		"<input type=\"submit\" value=\"".LANG_DELETE."\" style=\"height: 18px; width: 105px\">".
		"</form>";
	}
	else return "";
}

function img_resize($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;

  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  if (($width==80)&&($size[0]<80)&&($size[1]<80))
	{
	$width = $size[0];
	}

  $height = ($width * $size[1]) / $size[0];

  if (($width==80)&&($height > 80))
  {
  $height = 80;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }

  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);

  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);

  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);

  imagejpeg($idest, $dest, $quality);

  imagedestroy($isrc);
  imagedestroy($idest);

  return true;

}

function img_resize_gif($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  if (($width==140)&&($size[0]<=140)&&($size[1]<=140))
	{
	$width = $size[0];
	}

  if (($width==80)&&($size[0]<=80)&&($size[1]<=80))
	{
	$width = $size[0];
	if (($size[0]<=80)&&($size[1]<=80))
	{
		$width = $size[0];
		copy($src, $dest);
		return true;
	}
	}

  $height = ($width * $size[1]) / $size[0];


  if (($width==80)&&($height > 80))
  {
  $height = 80;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }

  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);

  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

  $isrc = $icfunc($src);
  $idest = imagecreate($width, $height);

  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagegif($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);

  return true;

}

function img_resize_png($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;

  $size = getimagesize($src);

  if ($size === false) return false;

  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  if (($width==80)&&($size[0]<140)&&($size[1]<140))
	{
	$width = $size[0];
	}

  $height = ($width * $size[1]) / $size[0];

  if (($width==80)&&($height > 80))
  {
  $height = 80;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }

  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);

  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);

  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);

  imagepng($idest, $dest);

  imagedestroy($isrc);
  imagedestroy($idest);

  return true;

}

function img_resize_1280($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<1280)&&($size[1]<1024))
	{
	$width = $size[0];
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 1024)
  {
  $height = 1024;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagejpeg($idest, $dest, $quality);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_1280_gif($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<1280)&&($size[1]<1024))
	{
	$width = $size[0];
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 1024)
  {
  $height = 1024;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreate($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagegif($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_1280_png($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<1280)&&($size[1]<1024))
	{
	$width = $size[0];
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 1024)
  {
  $height = 1024;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagepng($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_140($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<=140)&&($size[1]<=140))
	{
	$width = $size[0];
	}

  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 140)
  {
  $height = 140;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagejpeg($idest, $dest, $quality);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_140_gif($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  if (($size[0]<=140)&&($size[1]<=140))
	{
	$width = $size[0];
	copy($src, $dest);
	return true;
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 140)
  {
  $height = 140;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreate($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagegif($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_140_png($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<140)&&($size[1]<140))
	{
	$width = $size[0];
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 140)
  {
  $height = 140;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagepng($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_100($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;
  if (($size[0]<=100)&&($size[1]<=100))
	{
	$width = $size[0];
	}

  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 100)
  {
  $height = 100;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagejpeg($idest, $dest, $quality);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_100_gif($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  if (($size[0]<=100)&&($size[1]<=100))
	{
	$width = $size[0];
	copy($src, $dest);
	return true;
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 100)
  {
  $height = 100;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  $isrc = $icfunc($src);
  $idest = imagecreate($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagegif($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}

function img_resize_100_png($src, $dest, $width, $rgb=0xFFFFFF, $quality=100)
{
  if (!file_exists($src)) return false;
  $size = getimagesize($src);
  if ($size === false) return false;
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if(($size[0]>3000)||($size[1]>3000))
  {
     copy(dirname(__FILE__)."/_img/huge.png", $dest);
     return false;
  }
  if (!function_exists($icfunc)) return false;
  if (($size[0]<100)&&($size[1]<100))
	{
	$width = $size[0];
	}
  $height = ($width * $size[1]) / $size[0];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  if ($height > 100)
  {
  $height = 100;
  $width = ($height * $size[0]) / $size[1];
  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];
  }
  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  //echo filesize($src);
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  imagepng($idest, $dest);
  imagedestroy($isrc);
  imagedestroy($idest);
  return true;
}



function is_hidden_file($fn){}

if(!file_exists("_Thumbs"))
{
        mkdir("_Thumbs");
}

if(file_exists("_desc.txt"))
{
	$data = file_get_contents("_desc.txt");
	$filedescription = explode("\r\n", $data);
}
else
{
        $filedescription = "none";
}

if(file_exists("_sortby.txt"))
{
	$sortfilesby = file_get_contents("_sortby.txt");
	$sortfby = explode("\r\n", $sortfilesby);
}
else
{
	$sortfby[0] = "date";
	$sortfby[1] = "desc";
}

$create_table = 0;
if(!file_exists("_gallyry.db"))
        $create_table = 1;

$db_is_support=0;
$db = sqlite_open("_gallyry.db");
if ($db) $db_is_support = 1;

if(($db_is_support))
{
	$query_table = sqlite_query($db, "CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, ".
					"filename VARCHAR(1024), ".
					"title VARCHAR(1024), ".
					"username VARCHAR(1024), ".
					"uploaded DATETIME, ".
									"comment TEXT)");

	sqlite_query($db, "CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, ".
					"option VARCHAR(1024), ".
					"`value1` VARCHAR(1024),".
					"`value2` VARCHAR(1024))");


	$sort_db_q = sqlite_query($db, "SELECT * FROM settings WHERE option = 'sortby';");
	$sort_db = sqlite_fetch_array($sort_db_q);

	if($sort_db!=NULL)
	{
		$sortfby[0] = $sort_db['value1'];
		$sortfby[1] = $sort_db['value2'];
		//if(isAdminIP())echo "есть сортировка";
	}
	//else
	//if(isAdminIP())echo "нет сортировки";
}

?><!DOCTYPE html>
<html>
<head>
<style>
body
{
	font-style:normal;
	font-variant:normal;
	font-weight:normal;
	line-height:1.2em;
	font-size:small;
	font-family:arial, helvetica, clean, sans-serif;
	background-image:url('<?php echo $ImgBackground;?>');
	background-repeat: no-repeat;
	background-attachment:fixed;
	background-position: right bottom;
}
</style>
<title><?php echo ( ($_SERVER['REQUEST_URI']==$Photosfolder) ? $PageTitle : urldecode(basename($_SERVER['REQUEST_URI'])));?></title>
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery-1.9.0.min.js"></script>
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery.mousewheel-3.0.6.pack.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $Photosfolder;?>js/jquery.fancybox.css?v=2.1.4" media="screen" />
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery.fancybox.pack.js?v=2.1.4"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $Photosfolder;?>js/jquery.fancybox-buttons.css?v=1.0.5"/>
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery.fancybox-media.js?v=1.0.5"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $Photosfolder;?>js/jquery.fancybox-thumbs.css?v=1.0.7" />
<script type="text/javascript" src="<?php echo $Photosfolder;?>js/jquery.fancybox-thumbs.js?v=1.0.7"></script>


<script type="text/javascript">
	$(document).ready(function() {
				$(".fancybox").fancybox({
				overlayColor	: '#020202',
				padding	: 5,
			    	helpers : {
    				title : {
	    			type : 'inside'
			    		},
				//media : {},
				//thumbs : {
				//		width  : 50,
				//		height : 50
				//	}
			    	},

				afterLoad : function() {
					this.title = '<?=LANG_PHOTO?> ' + (this.index + 1) + ' из ' + this.group.length + (this.title ? '<br/>' + this.title : '') +
				(document.getElementById(this.title+"_desc").innerHTML ? '<hr/>' + document.getElementById(this.title+"_desc").innerHTML:"");
				}

				});

				$(".textfile").fancybox({
				maxWidth	: 800,
				maxHeight	: 600,
				fitToView	: false,
				width		: '70%',
				height		: '70%',
				autoSize	: false,
				closeClick	: false,
			    	helpers :{
    				title : {
	    			type : 'inside'
			    	}
				},
				afterLoad : function() {
					this.title = (this.title ? '<br/><b>' + this.title + "</b>" : '') +
				(document.getElementById(this.title+"_desc").innerHTML ? '<hr/>' + document.getElementById(this.title+"_desc").innerHTML:"");
				}

		});
	});
</script>
</head>
<body>
<?php
$dir = ".";
$counter = 0; //Count of printed files/folders

//Reading folder list begin
$folders = array();
foreach(scandir($dir) as $file)
        $folders[$file] = "$dir/$file";
asort($folders);
$folders = array_keys($folders);
//Reading folder list end

//Reading files list begin
$files = array();
if($sortfby[0]=="date")
{
        foreach (scandir($dir) as $file)
                $files[$file] = filemtime("$dir/$file");
}
else
if($sortfby[0]=="name")
{
        foreach (scandir($dir) as $file)
                $files[$file] = "$dir/$file";
}
//Reading files list end

//File sorting Begin
if($sortfby[1]=="desc")
        arsort($files);
else
        asort($files);
$files = array_keys($files);
//File sorting End

//Clean-up old thumbnails Begin
$thumbs = array();
$dirthumbs = "./_Thumbs";
foreach (scandir($dirthumbs) as $file)
        $thumbs[$file] = "$dirthumbs/$file";
$thumbs = array_keys($thumbs);
for($i=0; $i<count($thumbs);$i++)
if(!file_exists($thumbs[$i]))
{
	unlink("_Thumbs/".$thumbs[$i]);
}
//Clean-up old thumbnails End


?>
<div style="text-align: center">
				<em><span style="font-size: xx-large"><?php echo ( ($_SERVER['REQUEST_URI']==$Photosfolder) ? $PageTitle : urldecode(basename($_SERVER['REQUEST_URI'])));?></span><br/>
				<?php if(isAdminIP()){ echo "<small>[".LANG_ADMIN_RIGHTS."]</small><br/>";?>
				<?=LANG_SORT_BY?>: <a href="?sorttype=1&value1=date&value2=asc"><?=LANG_SB_DATE?></a>
				(<a href="?sorttype=1&value1=date&value2=desc"><?=LANG_SB_DESC?></a>)
				<a href="?sorttype=1&value1=name&value2=asc"><?=LANG_SB_NAME?></a>
				(<a href="?sorttype=1&value1=name&value2=desc"><?=LANG_SB_DESC?></a>)
				<br/>
				<?php }?>
				<span style="font-size: small"><a href="../"><span style="color: #000080"><img src="/sysimage/icons/UPALEVEL.GIF" border="0"><?=LANG_PARENT_DIR?></span></a></span></em>
				&nbsp;&nbsp;&nbsp;
				<span style="font-size: small"><a href="?clean_thumbs"><span style="color: #000080"><img src="/sysimage/icons/refresh.gif" border="0"><?=LANG_REFRESH_THUMBS?></span></a></span></em>
<div style="width:100%;display: block;float:left;margin: 25;">
<?php
for($i=0; $i<count($folders);$i++)
{
	if(is_dir($folders[$i]) == "true")
	{
	    if(substr($folders[$i], 0, 1 ) === ".")
            continue; //Don't show hidden files and directories

		if(($folders[$i]!=".")&&($folders[$i]!="..")&&($folders[$i]!="_img")&&($folders[$i]!="js")&&($folders[$i]!="_Thumbs"))
		{
			if(!file_exists($folders[$i]."/index.php"))
			{
				$source = utf2fs("<?php require_once \"".$PhotosPath."index.php\";?>");
				$Saved_File = fopen($folders[$i]."/index.php", 'a+');
				fwrite($Saved_File, $source);
				fclose($Saved_File);
			}

			$showname = fs2utf($folders[$i]);
			if($filedescription!="none")
			for($j=0; $j<count($filedescription); $j++)
			{
				$desc01 = explode("|", $filedescription[$j]);
				if($folders[$i]==$desc01[0])
					$showname = iconv("Windows-1251","UTF-8", $desc01[1]);
			}
			echo '<div style="float: left; width: 150px; height: 150px;">';
				echo "<center><a href=\"".fs2utf($folders[$i]).
				"\"><img border=0 alt=\"".fs2utf($folders[$i])."\" src=\"".$Photosfolder."_img/folder.png".
				"\"></a><br><i><u><small>".$showname."</small></u></i></center>\n";
				$counter++;
			echo '</div>';
		}
	}
}


for($i=0; $i < count($files);$i++)
{
	$showname = fs2utf($files[$i]);
    if(substr($files[$i], 0, 1 ) === ".")
        continue; //Don't show hidden files and directories

	if($filedescription != "none")
	{
	    for($j=0; $j<count($filedescription); $j++)
	    {
		    $desc01 = explode("|", $filedescription[$j]);
		    if($files[$i]==$desc01[0])
		    $showname = iconv("Windows-1251","UTF-8", $desc01[1]);
	    }
	}

	if(preg_match('/\.(jpg|jpeg|png|gif)$/i',$files[$i]))
	{?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		if(!file_exists("_Thumbs/".$files[$i]))
		{
		if(preg_match('/\.(jpg|jpeg)$/i',$files[$i]))
		img_resize_100($files[$i], "_Thumbs/".$files[$i], 100);
		if(preg_match('/\.(png)$/i',$files[$i]))
		img_resize_100_png($files[$i], "_Thumbs/".$files[$i], 100);
		if(preg_match('/\.(gif)$/i',$files[$i]))
		img_resize_100_gif($files[$i], "_Thumbs/".$files[$i], 100);
		}

		echo "<center><a class=\"fancybox\" rel=\"photoalboom\" title=\"".preg_replace("/.png|.jpg|.gif|.jpeg/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img style=\"border-color:silver; background-color:silver\" border=1 alt=\"".fs2utf($files[$i])."\" src=\"_Thumbs/".fs2utf($files[$i])."?".rand().
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.png|.jpg|.gif|.jpeg/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]),$AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	//else if(((strstr($files[$i], ".txt"))||(strstr($files[$i], ".TXT")))
	else if( preg_match('/\.(cpp|txt)$/i',$files[$i])
	&&
	($files[$i]!="_desc.txt")
	&&
	($files[$i]!="_sortby.txt")
	)
	{
		?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a class=\"textfile\" data-fancybox-type=\"iframe\" title=\"".preg_replace("/.txt/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/text.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.txt/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if( preg_match('/\.(swf)$/i',$files[$i])
	)
	{
		?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a class=\"textfile\" title=\"".preg_replace("/.swf/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/swf.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.swf/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if((preg_match('/\.(zip)$/i',$files[$i]))||(preg_match('/\.(7z)$/i',$files[$i])))
	{
	?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a target=\"_blank\" class=\"archive\" title=\"".preg_replace("/.zip/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/arch.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.zip$/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if(preg_match('/\.(ods)$/i',$files[$i]))
	{
		?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a target=\"_blank\" class=\"archive\" title=\"".preg_replace("/.ods/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/ods.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.ods$/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if(preg_match('/\.(xls)$/i',$files[$i]))
	{
		?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a target=\"_blank\" class=\"archive\" title=\"".preg_replace("/.xls/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/xls.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.xls$/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if(preg_match('/\.(xlsx)$/i',$files[$i]))
	{
	?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a target=\"_blank\" class=\"archive\" title=\"".preg_replace("/.xlsx/i","", fs2utf($files[$i]))."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/xlsx.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.xlsx$/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}
	else if(
		(!is_dir($files[$i]))&&
		(!preg_match('/\.(db)$/i',$files[$i]))&&
		(!preg_match('/\.(htaccess)$/i',$files[$i]))&&
		(!preg_match('/\.(php)$/i',$files[$i]))&&
		($files[$i]!="_desc.txt")
	)
	{
	?>
		<div style="float: left; width: 150px; height: 150px;">
		<?php
		echo "<center><a target=\"_blank\" class=\"anyfile\" title=\"".fs2utf($files[$i])."\" href=\"".fs2utf($files[$i]).
		"\"><img border=0 alt=\"".fs2utf($files[$i])."\" src=\"".$Photosfolder."_img/anyfile.png".
		"\"></a><br><i><u><small>".$showname.
		"</small></u></i></center>\n<div id=\"".preg_replace("/.xlsx$/i","", fs2utf($files[$i]))."_desc\" style=\"display: none;\">".renameform(fs2utf($files[$i]), $AdminIp)."</div>\n";
		$counter++;
		?>
		</div>
		<?php
	}

}
?>

</div>
</div>
<?php
if($counter > 0)
{
    //Print number of listed elements with support of right Russian grammar of the "element(s)" word
	echo "<p><br>" . totalElementsLabel($counter) . "</p>";
}
else
{?>
	<center>
	<table style="text-align: center; width: 100%; vertical-align: middle; display: inline; ">
	<tr><td style="padding-top: 150px">
	<img src=<?php echo $Photosfolder."_img/magnifier.png";?>><br>
	<span style="font-size: x-large"><?=LANG_FOLDER_EMPTY?></span>
	</td></tr>
	</table>
	</center>
	<?php
}?>

</body>
</html>
