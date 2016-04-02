<?

if(!function_exists('fnmatch')) {
    define('FNM_PATHNAME', 1);
    define('FNM_NOESCAPE', 2);
    define('FNM_PERIOD', 4);
    define('FNM_CASEFOLD', 16);

    function fnmatch($pattern, $string, $flags = 0) {
        return pcre_fnmatch($pattern, $string, $flags);
    }
}

function pcre_fnmatch($pattern, $string, $flags = 0) {
    $modifiers = null;
    $transforms = array(
        '\*'    => '.*',
        '\?'    => '.',
        '\[\!'    => '[^',
        '\['    => '[',
        '\]'    => ']',
        '\.'    => '\.',
        '\\'    => '\\\\'
    );

    // Forward slash in string must be in pattern:
    if ($flags & FNM_PATHNAME) { $transforms['\*'] = '[^/]*'; }

    // Back slash should not be escaped:
    if ($flags & FNM_NOESCAPE) { unset($transforms['\\']); }

    // Perform case insensitive match:
    if ($flags & FNM_CASEFOLD) { $modifiers .= 'i'; }

    // Period at start must be the same as pattern:
    if ($flags & FNM_PERIOD) { if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) return false; }

    $pattern = '#^'
        . strtr(preg_quote($pattern, '#'), $transforms)
        . '$#'
        . $modifiers;

    return (boolean)preg_match($pattern, $string);
}

function fnreplace($pattern, $replacement, $subject, $flags = 0) {
    $modifiers = null;
    $transforms = array('\*' => '.*', '\?' => '.', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\.' => '\.', '\\' => '\\\\');
    if ($flags & FNM_PATHNAME) { $transforms['\*'] = '[^/]*'; }
    if ($flags & FNM_NOESCAPE) { unset($transforms['\\']); }
    if ($flags & FNM_CASEFOLD) { $modifiers .= 'i'; }
    if ($flags & FNM_PERIOD) { if(strpos($pattern, '.') === 0 && strpos($pattern, '.') !== 0) return false; }

    if(is_array($pattern)) {
        foreach($pattern as &$p) {
            if(strpos($p, "regex:") === false) $p = '#'.strtr(preg_quote($p, '#'), $transforms).'#'.$modifiers;
            else $p = str_replace("regex:", "", $p);
        }
    }
    else {
        if(strpos($pattern, "regex:") === false) $pattern = '#'.strtr(preg_quote($pattern, '#'), $transforms).'#'.$modifiers;
        else $pattern = str_replace("regex:", "", $pattern);
    }

    return preg_replace($pattern, $replacement, $subject);
}

function extract_meta_data($template, $value) {
    $metadata = array();
    if(preg_match_all('/<(.*?)>/', $template, $keys) !== false) {
        if(empty($keys[1])) return $metadata;
        else $keys = $keys[1];
        $patterns = preg_split('/<(.*?)>/', $template, -1, PREG_SPLIT_NO_EMPTY);
        $value=preg_replace("/[\r\n]+[\s\t]*[\r\n]+/", "", $value);
        $values = explode("\xA0", trim(fnreplace($patterns, "\xA0", $value, FNM_NOESCAPE), "\xA0"));
        foreach($values as $idx => $data) {
            if($data != $value) $metadata[$keys[$idx]] = $data;
        }
    }
    return $metadata;
}

function parse_options($options) {
    $result = array();
    if($options == "") return $result; //quick fix TODO: cleanup way wich internal modules get and parses options
    foreach(explode(";", $options) as $segment) {
        $parts = explode("=", $segment);
        $result[$parts[0]] = $parts[1];
    }
    return $result;
}

function normalize_strings($strings, $enclose = "", $append = "") {
    $result = array();
    if(is_string($strings)) $strings = array($strings);
    foreach($strings as $string) {
        $result[] = normalize_string($string, $enclose, $append);
    }
    return $result;
}

/* TODO: change replace array to regex that replace all except that is mysql acceptable */
function normalize_string($string, $enclose = "", $append = "") {
    $string = strtolower($string);
    $string = str_replace(array(" ", "-", "(", ")", "@", "#"), "_", $string);
    $string = str_replace(".", "", $string);
    $string = trim($string, "\x00..\x1F\xA0");
    $string = $enclose.$string.$enclose.$append;
    return $string;
}

function trim_strings($strings) {
    $result = array();
    if(is_string($strings)) $strings = array($strings);
    foreach($strings as $string) $result[] = trim($string, " \x00..\x1F\xA0");
    return $result;
}

if (!function_exists('str_getcsv')) {
    function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = null) {
        $temp = fopen("php://memory", "rw");
        fwrite($temp, $input);
        fseek($temp, 0);
        $result = fgetcsv($temp, 1048576, $delimiter, $enclosure);
        fclose($temp);
        return $result;
    }
}

if (!function_exists('str_putcsv')) {
    function str_putcsv($input, $delimiter = ',', $enclosure = '"', $escape = null) {
        $temp = fopen('php://temp', 'r+');
        fputcsv($temp, $input, $delimiter, $enclosure);
        rewind($temp);
        $data = fread($temp, 1048576);
        fclose($temp);
        return rtrim($data, "\n");
    }
}

function datediff($date1, $date2 = "NOW") {
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $seconds = $date1 - $date2;
    $minutes = floor(($date1 - $date2)/60);
    $hours = floor(($date1 - $date2)/3600);
    $days = floor(($date1-$date2)/86400);
    $months = floor(($date1-$date2)/2628000);
    $years = floor(($date1-$date2)/31536000);

    return (($years == 0) ? "" : $years." years ").(($months == 0) ? "" : $months." months ").(($days == 0) ? "" : $days." days ").
            (($hours == 0) ? "" : $hours." hours ").(($minutes == 0) ? "" : $minutes." min ").(($seconds == 0) ? "" : $seconds." sec");
}

function timediff($time1, $time2 = "", $decimals = 3) {
    if($time2 == "") $time2 = microtime();

    list ($msec, $sec) = explode(' ', $time1);
    $microtime1 = (float)$msec + (float)$sec;
    list ($msec, $sec) = explode(' ', $time2);
    $microtime2 = (float)$msec + (float)$sec;

    $result = $microtime2 - $microtime1;

    $integral = floor($result);
    $fractional = $result - floor($result);

    return number_format($result, $decimals)." s";
}

function is_date($date) {
    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
        if(checkdate($parts[2],$parts[3],$parts[1])) return true;
        else return false;
    }
    else return false;
}

function is_archive($filename) {
    $extensions = array (
        '.zip', '.tar', '.gz', '.gzip', '.bzip', '.bz', '.bzip2', '.bz2', '.tgz', '.tgzip', '.tbzip', '.tbz', '.tbzip2', '.tbz2', '.rar');
    $parts = pathinfo($filename);
    if(in_array('.'.$parts['extension'], $extensions)) return true;
    else return false;
}

function stop($fails, $mail = array()) {
    $text = "<span style='font-family: Verdana; font-size: 13px;'>";
    $text .= "<span style='color: #ff0000'>process stopped, the following fatal errors are occured:</span><br /><br />";

    foreach($fails as $fail) {
        error_log($fail);
        $text .= $fail."<br />";
    }

    $text .= "</span>";

    if(empty($mail) == false) {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: '.$mail["sender"] . "\r\n";
        $headers .= 'X-Mailer: xrms' . "\r\n";

        mail($mail["recepients"], $mail["title"]." [ ".date("d-M-Y")." @ ".$mail["host"]." ]", $text, $headers);
    }

    die($text);
}

function get_data_from_html($html, $rules, $clean = false) {
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;

    if($clean) {
        $html = preg_replace('/\<br(\s*)?\/?\>/i', "", $html);
    }

    if($dom->loadHTML($html) == false)
        trigger_error("facebook: error parsing html data", E_USER_ERROR);
    $xpath = new DOMXPath($dom);

    $result = array();
    foreach($rules as $name => $rule) {
        if(isset($rule["search"])) {
            $items =  $xpath->query($rule["search"]);
            foreach ($items as $item) {
                $result[$name] = $item->nodeValue;
            }
        }
        else echo "error";
        if(isset($rule["extract"])) {
            $result = array_merge($result , extract_meta_data($rule["extract"], $result[$name]));
        }
    }
    return $result;
}

/* EOF */