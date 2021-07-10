<?php
    include 'GIFEncoder.class.php';
    $today = new DateTime();

    $font_folder = '';

    /* Options  - START */
    $maxDifference = 30;
    $maxFrame = 60;
    $delay = 100;

    //text Options
    $default_date = new DateTime();
    $default_date = $default_date ->modify("+7day")->format('Y-n-j-G-i-s');
    $size_default = 100;
    $size_text_name_default = 20;
    $padding_top_name_default = 20;
    $padding_default = 40;
    $background_color_default = '#000000';
    $timer_color_default = '#ffffff';
    $name_color_default = '#ffffff';

    $name_days_default = 'DNI';
    $name_hours_default = 'GODZIN';
    $name_minutes_default = 'MINUT';
    $name_seconds_default = 'SEKUND';

    $template_default = 'default';
    $font_family = realpath(__DIR__ . '/../fonts/arial.ttf');

    /* GET */
    $date_to_countdown = isset($_GET['date_to_countdown']) ? ($_GET['date_to_countdown'] == '' ? $default_date : $_GET['date_to_countdown']) : $default_date;
    $size = isset($_GET['font_size_timer']) ? ($_GET['font_size_timer'] == '' ? $size_default : $_GET['font_size_timer']) : $size_default;
    $size_text_name = isset($_GET['font_size_name']) ? ($_GET['font_size_name'] == '' ? $size_text_name_default : $_GET['font_size_name']) : $size_text_name_default;
    $padding = isset($_GET['padding']) ? ($_GET['padding'] == '' ? $padding_default : $_GET['padding']) : $padding_default;
    $padding_top_name = isset($_GET['padding_top_name']) ? ($_GET['padding_top_name'] == '' ? $padding_top_name_default : $_GET['padding_top_name']) : $padding_top_name_default;
    $background_color = isset($_GET['background_color']) ? ($_GET['background_color'] == '' ? $background_color_default : '#'.$_GET['background_color']) : $background_color_default;
    $timer_color = isset($_GET['timer_color']) ? ($_GET['timer_color'] == '' ? $timer_color_default : '#'.$_GET['timer_color']) : $timer_color_default;
    $name_color = isset($_GET['name_color']) ? ($_GET['name_color'] == '' ? $name_color_default : '#'.$_GET['name_color']) : $name_color_default;

    $name_days = isset($_GET['name_days']) ? ($_GET['name_days'] == '' ? $name_days_default : $_GET['name_days']) : $name_days_default;
    $name_hours = isset($_GET['name_hours']) ? ($_GET['name_hours'] == '' ? $name_hours_default : $_GET['name_hours']) : $name_hours_default;
    $name_minutes = isset($_GET['name_minutes']) ? ($_GET['name_minutes'] == '' ? $name_minutes_default : $_GET['name_minutes']) : $name_minutes_default;
    $name_seconds = isset($_GET['name_seconds']) ? ($_GET['name_seconds'] == '' ? $name_seconds_default : $_GET['name_seconds']) : $name_seconds_default;
    $template = isset($_GET['template']) ? ($_GET['template'] == '' ? $template_default : $_GET['template']) : $template_default;

    /* Options - END */ 

    $delays = [];
    $frames = [];

    function createText($date){
        global $maxDifference, $today;
        $getDate = explode('-', strval($date));
        if(count($getDate) == 6){
        	list($y, $m, $d, $h, $i, $s) = $getDate;
        	$new_date = new DateTime();
        	$new_date->setDate(intval($y), intval($m), intval($d));
        	$new_date->setTime(intval($h), intval($i), intval($s));
       	
        	$date_diff = strtotime($new_date->format('Y-m-d H:i:s')) - strtotime($today->format('Y-m-d H:i:s'));
        	$days = floor($date_diff/86400);
        	$hours = floor(($date_diff - $days*86400)/3600);
        	$minutes = floor(($date_diff - ($days*86400 + $hours*3600))/60);
        	$seconds = $date_diff - ($days*86400 + $hours*3600 + $minutes*60);
        	//echo $days." ".$hours." ".$minutes." ".$seconds;
        	if($date_diff < 0){
        		return ['00', '00', '00', '00'];
        	} else if ($days > 30) {
        		return ['00', '00', '00', '00'];
        	} else {
        		$days = $days<10 ? '0'.$days : $days;
        		$hours = $hours<10 ? '0'.$hours : $hours;
        		$minutes = $minutes<10 ? '0'.$minutes : $minutes;
        		$seconds =  $seconds<10 ? '0'.$seconds : $seconds;
        		$today->modify('+1 seconds');
        		return [$days, $hours, $minutes, $seconds];
        	}
        } else {
        	return false;
        }
    }

    function returnMax(array $height){
        $max = 0;
        for($i = 0; $i<count($height); $i++){
            if($max < $height[$i]){
                $max = $height[$i];
            }
        }
        return $max;
    }

    function returnSizeText(int $size, string $text){
        global $font_family;
        $info_box = [];
        $box = imagettfbbox($size, 0, $font_family, $text);
        $info_box[0] = ($box[4] - $box[0]);
        $info_box[1] = $box[5] - $box[1];

        return $info_box;
    }


    function hexToRgb($hex, $alpha = false) {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
            if ( $alpha ) {
                $rgb['a'] = $alpha;
            }
        return $rgb;
    }


    function createImage(array $width, array $height, $background_color){
        $img_width = 0;
        $img_height = 0;
        $img;

        for($i = 0; $i < count($width); $i++){
            $img_width += $width[$i];
        }

        for($i = 0; $i < count($height); $i++){
            $img_height += $height[$i];
        }

        $img = imagecreatetruecolor($img_width, $img_height);
        $color = hexToRgb($background_color);
        $background = imagecolorallocate($img, $color['r'], $color['g'], $color['b']);
        imagefilledrectangle($img,0,0,$img_width,$img_height,$background);
        return $img;
    }

    function createFrames(int $numberOfFrames){
        global 
        $date_to_countdown,
        $size, 
        $font_family, 
        $padding, 
        $size_text_name, 
        $padding_top_name, 
        $frames, 
        $delays, 
        $delay, 
        $maxFrame, 
        $name_days, 
        $name_hours, 
        $name_minutes, 
        $name_seconds, 
        $background_color, 
        $name_color,
        $timer_color,
        $template;

        $get_timer_color = hexToRgb($timer_color);
        $get_name_color = hexToRgb($name_color);

        for($i = 0; $i < $maxFrame; $i++){
            $text = createText($date_to_countdown);

            list($days, $hours, $minutes, $seconds) = $text;
            
            switch($template){
                case 'dotted':
                    list($timer_width, $timer_height) = returnSizeText($size, strval('00 : 00 : 00 : 00'));
                    $img = createImage([$timer_width, $padding*2], [abs($timer_height), $padding*2], $background_color);

                    $timer_color_rgb = imagecolorallocate($img, $get_timer_color['r'], $get_timer_color['g'], $get_timer_color['b']);
                    imagettftext(
                        $img,
                        $size,
                        0, 
                        floatval($padding),
                        ((abs($timer_height)+$padding*2) - $timer_height)/2, 
                        $timer_color_rgb, 
                        $font_family,
                        strval($days.' : '.$hours.' : '.$minutes.' : '.$seconds)
                    );
                break;

                default:
                    list($timer_width, $timer_height) = returnSizeText($size, strval('00'));
    
                    list($name_days_width, $name_days_height) = returnSizeText($size_text_name, strval($name_days));
                    list($name_hours_width, $name_hours_height) = returnSizeText($size_text_name, strval($name_hours));
                    list($name_minutes_width, $name_minutes_height) = returnSizeText($size_text_name, strval($name_minutes));
                    list($name_seconds_width, $name_seconds_height) = returnSizeText($size_text_name, strval($name_seconds));
    
    
                    $height_name = returnMax([
                        abs($name_days_height),
                        abs($name_hours_height),
                        abs($name_minutes_height),
                        abs($name_seconds_height)
                    ]);
                    $img = createImage([$timer_width*4, $padding*5], [abs($timer_height), $padding*2, $padding_top_name + $height_name], $background_color);

                    $timer_color_rgb = imagecolorallocate($img, $get_timer_color['r'], $get_timer_color['g'], $get_timer_color['b']);
                    $name_color_rgb = imagecolorallocate($img, $get_name_color['r'], $get_name_color['g'], $get_name_color['b']);

                    // TIME 

                    //DAYS
                    imagettftext(
                        $img,
                        $size,
                        0, 
                        floatval(($padding*1) + ($timer_width*0)),
                        abs($timer_height)+$padding, 
                        $timer_color_rgb, 
                        $font_family,
                        $days
                    );

                    //HOURS
                    imagettftext(
                        $img,
                        $size,
                        0,
                        floatval(($padding*2) + ($timer_width*1)),
                        abs($timer_height)+$padding, 
                        $timer_color_rgb,
                        $font_family,
                        $hours
                    );

                    //MINUTES
                    imagettftext(
                        $img,
                        $size,
                        0, 
                        floatval(($padding*3) + ($timer_width*2)),
                        abs($timer_height)+$padding, 
                        $timer_color_rgb, 
                        $font_family, 
                        $minutes
                    );

                    //SECONDS
                    imagettftext(
                        $img,
                        $size,
                        0,
                        floatval(($padding*4) + ($timer_width*3)),
                        abs($timer_height)+$padding, 
                        $timer_color_rgb,
                        $font_family,
                        $seconds
                    );


                    //TIME NAME

                    //DAYS
                    imagettftext(
                        $img, 
                        $size_text_name, 
                        0, 
                        floatval(($padding*1) + ($timer_width*0) + (($timer_width-$name_days_width)/2)),
                        abs($timer_height) + $padding + $padding_top_name + $height_name, 
                        $name_color_rgb,
                        $font_family,
                        strval($name_days)
                    );

                    //HOURS
                    imagettftext(
                        $img, 
                        $size_text_name, 
                        0,
                        floatval(($padding*2) + ($timer_width*1) + (($timer_width-$name_hours_width)/2)),
                        abs($timer_height) + $padding + $padding_top_name + $height_name, 
                        $name_color_rgb,
                        $font_family,
                        strval($name_hours)
                    );

                    //MINUTES
                    imagettftext($img,
                        $size_text_name, 
                        0,
                        floatval(($padding*3) + ($timer_width*2) + (($timer_width-$name_minutes_width)/2)),
                        abs($timer_height) + $padding + $padding_top_name + $height_name, 
                        $name_color_rgb,
                        $font_family,
                        strval($name_minutes)
                    );

                    //SECONDS
                    imagettftext($img,
                        $size_text_name, 
                        0,
                        floatval(($padding*4) + ($timer_width*3) + (($timer_width-$name_seconds_width)/2)),
                        abs($timer_height) + $padding + $padding_top_name + $height_name, 
                        $name_color_rgb,
                        $font_family,
                        strval($name_seconds)
                    );
                break;
            }
            ob_start();
                imagegif($img);
                $frames[$i] = ob_get_contents();
                $delays[$i] = $delay;
            ob_end_clean();
            imagedestroy($img);
        }
    }

    function displayGif(){
        global $frames, $delays, $maxFrame;
        createFrames($maxFrame);
        $gif = new GIFEncoder ($frames,$delays, 0, 2, 0, 0, 0, "bin");
        Header ('Content-type:image/gif');
        echo $gif->GetAnimation();
    }

   	displayGif();
?>
