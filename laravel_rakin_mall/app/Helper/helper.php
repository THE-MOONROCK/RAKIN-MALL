<?php
/*
 *  Used to set default configuration
 */

use App\Helper\UUID;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

function setDefault(){

}

/*
 *  Used to write in .env file
 *  @param
 *  $data as array of .env key & value
 *  @return nothing
 */

function envu($data = array()){
    foreach($data as $key => $value){
        if(env($key) == $value)
            unset($data[$key]);
    }

    if(!count($data))
        return false;

    // write only if there is change in content

    $env = file_get_contents(base_path() . '/.env');
    $env = explode("\n",$env);
    foreach((array)$data as $key => $value){
        foreach($env as $env_key => $env_value){
            $entry = explode("=", $env_value, 2);
            if($entry[0] == $key)
                $env[$env_key] = $key . "=" . (is_string($value) ? '"'.$value.'"' : $value);
            else
                $env[$env_key] = $env_value;
        }
    }
    $env = implode("\n", $env);
    file_put_contents(base_path() . '/.env', $env);
    return true;
}

/*
 *  Used to generate Unique Ids
 */

function generateUuid(){
    return UUID::uuid4();
  }

/*
 *  Used to check whether date is valid or not
 *  @param
 *  $date as timestamp or date variable
 *  @return true if valid date, else if not
 */

function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/*
 *  Used to get date with start midnight time
 *  @param
 *  $date as timestamp or date variable
 *  @return date with start midnight time
 */

function getStartOfDate($date){
    return date('Y-m-d',strtotime($date)).' 00:00';
}

/*
 *  Used to get date with end midnight time
 *  @param
 *  $date as timestamp or date variable
 *  @return date with end midnight time
 */

function getEndOfDate($date){
    return date('Y-m-d',strtotime($date)).' 23:59';
}

/*
 *  Used to convert slugs into human readable words
 *  @param
 *  $word as string
 *  @return string
 */

function toWord($word){
    $word = str_replace('_', ' ', $word);
    $word = str_replace('-', ' ', $word);
    $word = ucwords($word);
    return $word;
}

/*
 *  Used to generate random string of certain lenght
 *  @param
 *  $length as numeric
 *  $type as optional param, can be token or password or username. Default is token
 *  @return random string
 */

function randomString($length,$type = 'token'){
    if($type == 'password')
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    elseif($type == 'username')
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    else
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $token = substr( str_shuffle( $chars ), 0, $length );
    return $token;
}

/*
 *  Used to calculate date difference between two dates
 */

function dateDiff($date1,$date2){
    if($date2 > $date1)
        return date_diff(date_create($date1),date_create($date2))->days;
    else
        return date_diff(date_create($date2),date_create($date1))->days;
}

/**
 * date1 > date2 ?
 * @param $date1
 * @param $date2
 * @return mixed
 */
function dateDiff2($date1,$date2) {
    if(is_string( $date1)) $date1 = date_create( $date1);
    if(is_string( $date2)) $date2 = date_create( $date2);

    $diff = date_diff($date1, $date2, false);
    return (int) $diff->format("%R%a");
}

/*
 *  Used to generate select option for vue.js multiselect plugin
 *  @param
 *  $data as array of key & value pair
 *  @return select options
 */

function generateSelectOption($data){
    $options = array();
    foreach($data as $key => $value)
        $options[] = ['name' => $value, 'id' => $key];
    return $options;
}

/*
 *  Used to generate translated select option for vue.js multiselect plugin
 *  @param
 *  $data as array of key & value pair
 *  @return select options
 */

function generateTranslatedSelectOption($data){
    $options = array();
    foreach($data as $key => $value)
        $options[] = ['name' => trans('list.'.$value), 'id' => $value];
    return $options;
}

/*
 *  Used to generate select option for default select box
 *  @param
 *  $data as array of key & value pair
 *  @return select options
 */

function generateNormalSelectOption($data){
    $options = array();
    foreach($data as $key => $value)
        $options[] = ['text' => $value, 'value' => $key];
    return $options;
}

/*
 *  Used to generate select option for default select box where value is same as text
 *  @param
 *  $data as array of key & value pair
 *  @return select options
 */

function generateNormalSelectOptionValueOnly($data){
    $options = array();
    foreach($data as $value)
        $options[] = ['text' => $value, 'value' => $value];
    return $options;
}

/*
 *  Used to generate slug from string
 *  @param
 *  $string as string
 *  @return slug
 */

function createSlug($string){
    if(checkUnicode($string))
        $slug = str_replace(' ', '-', $string);
    else
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return $slug;
}

/**
 * @param string $prefix
 * @return mixed
 */
function generateRandomCode($prefix='') {
    return str_replace('.','', uniqid($prefix, true));
}

function formatToDateTime($date) {
    if ($date) {
        return $date->format('Y-m-d H:i:s');
    }
    return null;
}

function formatToDate($date) {
    if ($date) {
        return $date->format('Y-m-d');
    }
    return null;
}

/*
 *  Used to get date in desired format
 *  @return date format
 */

function getDateFormat(){
    if(config('config.date_format') == 'D-MM-YYYY')
        return 'd-m-Y';
    elseif(config('config.date_format') == 'MM-D-YYYY')
        return 'm-d-Y';
    elseif(config('config.date_format') == 'D-MMM-YYYY')
        return 'd-M-Y';
    elseif(config('config.date_format') == 'MMM-D-YYYY')
        return 'M-d-Y';
    else
        return 'd-m-Y';
}

function getFullDate($date, $date_format = '%a %d %b %y') {
    if(!$date)
        return;

    if (app()->getLocale() == 'km') {
        // Specific built for this project, see translation in list / general to reuse it
        $day = trans('general.day1') . ' ' . trans('list.' . strtolower(strftime('%A', strtotime($date)))) . ' ';
        $day2 = trans('general.day2') . ' ' . strftime('%e', strtotime($date)) . ' ';
        $month = trans('general.month1') . ' ' . trans('general.' . strtolower(strftime('%B', strtotime($date)))) . ' ';
        $year = trans('general.year1') . ' ' . strftime('%Y', strtotime($date));
        return $day . $day2 . $month . $year;
    }
    return strftime($date_format, strtotime($date));
}

function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

function displayCurrentDateTime() {
    return showDateTime(date('Y-m-d H:i:s'));
}

function getCurrentDate($day=0) {
    $dt = date('Y-m-d');
    if ($day != 0) {
        return date( "Y-m-d", strtotime( '$dt ' . $day . ' day' ) );
    }
    return $dt;
}

/*
 *  Used to convert date in desired format
 *  @param
 *  $date as date
 *  @return date
 */

function showDate($date){
    if(!$date)
        return;

    $date_format = getDateFormat();
    return date($date_format,strtotime($date));
}

/*
 *  Used to convert time in desired format
 *  @param
 *  $datetime as datetime
 *  @return datetime
 */

function showDateTime($time = ''){
    if(!$time)
        return;

    $date_format = getDateFormat();
    if(config('config.time_format') == 'H:mm')
        return date($date_format.',H:i',strtotime($time));
    else
        return date($date_format.',h:i a',strtotime($time));
}

/*
 *  Used to convert time in desired format
 *  @param
 *  $time as time
 *  @return time
 */

function showTime($time = ''){
    if(!$time)
        return;

    if(config('config.time_format') == 'H:mm')
        return date('H:i',strtotime($time));
    else
        return date('h:i a',strtotime($time));
}

/*
 *  Used to whether string contains unicode
 *  @param
 *  $string as string
 *  @return boolean
 */

function checkUnicode($string)
{
    if(strlen($string) != strlen(utf8_decode($string)))
    return true;
    else
    return false;
}

/*
 *  Used to round number
 *  @param
 *  $number as numeric value
 *  $decimal_place as integer for round precision
 *  @return number
 */

function formatNumber($number,$decimal_place = 2){
    return round($number,$decimal_place);
}

function isImageFile($filename) {
    $extensions = array(
        '.jpg',
        '.jpeg',
        '.gif',
        '.png'
    );
    if (!$filename || !in_array(strtolower(strrchr($filename, '.')), $extensions)) {
        return false;
    }
    return true;
}

/*
 *  Used to check whether IP is in range
 */

function ipRange($network, $ip) {
    $network=trim($network);
    $orig_network = $network;
    $ip = trim($ip);
    if ($ip == $network) {
        return TRUE;
    }
    $network = str_replace(' ', '', $network);
    if (strpos($network, '*') !== FALSE) {
        if (strpos($network, '/') !== FALSE) {
            $asParts = explode('/', $network);
            $network = @ $asParts[0];
        }
        $nCount = substr_count($network, '*');
        $network = str_replace('*', '0', $network);
        if ($nCount == 1) {
            $network .= '/24';
        } else if ($nCount == 2) {
            $network .= '/16';
        } else if ($nCount == 3) {
            $network .= '/8';
        } else if ($nCount > 3) {
            return TRUE;
        }
    }

    $d = strpos($network, '-');
    if ($d === FALSE) {
        $ip_arr = explode('/', $network);
        if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)){
            $ip_arr[0].=".0";
        }
        $network_long = ip2long($ip_arr[0]);
        $x = ip2long($ip_arr[1]);
        $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
        $ip_long = ip2long($ip);
        return ($ip_long & $mask) == ($network_long & $mask);
    } else {
        $from = trim(ip2long(substr($network, 0, $d)));
        $to = trim(ip2long(substr($network, $d+1)));
        $ip = ip2long($ip);
        return ($ip>=$from and $ip<=$to);
    }
}

function ipExistsInRange(array $range, $ip)
{
    if (ip2long($ip) >= ip2long($range[0]) && ip2long($ip) <= ip2long($range[1]))
        return true;
    return false;
}

/*
 *  Used to get IP address of visitor
 *  @return date
 */

function getRemoteIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];

    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/*
 *  Used to get IP address of visitor
 *  @return IP address
 */

function getClientIp(){
    $ips = getRemoteIPAddress();
    $ips = explode(',', $ips);
    return !empty($ips[0]) ? $ips[0] : Request::getClientIp();
}

/*
 *  Used to check whether logged in user has default role or not
 *  @param
 *  $user as user instance
 *  @return boolean
 */


function defaultRole($user){
    if (!$user) return 0;
    if($user->hasRole(config('system.default_role.super')) || $user->hasRole(config('system.default_role.admin')))
        return 1;
    else
        return 0;
}

/*
 *  Used to get Authenticated user instance
 *  @return authenticated user
 */

function getAuthUser(){
    try {
        $auth_user = JWTAuth::parseToken()->authenticate();
    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        $auth_user = null;
    }

    return $auth_user;
}

function getAuthLocale() {
    return Session::get('locale', Config::get('app.locale'));
}

/*
 *  Used to check mode
 *  @return boolean
 */

function isTestMode(){
    if(!config('config.mode'))
        return true;
    else
        return false;
}

function getDesignationUser($user = null, $self = 0){
    $auth_user = getAuthUser();

    if(!$auth_user)
        return [];

    $user = ($user) ? : $auth_user;

    return $user->Profile->designation_id;
}

function queryFilterHeadUsers($query) {
    return $query->whereHas('roles', function($q) {
        $q->where('name', 'super')
            ->orWhere('name', 'admin')
            ->orWhere('name', 'assistant');
        })
        ->whereHas('profile', function($q) {
            $q->where('hide_organization', 0)
                ->whereHas('position', function($q1) {
                    $q1->where('hide_organization', 0);
                });
        });
}

/*
 * get Maximum post size of server
 */

function getPostMaxSize(){
    if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
        return (int) $postMaxSize;
    }

    $metric = strtoupper(substr($postMaxSize, -1));
    $postMaxSize = (int) $postMaxSize;

    switch ($metric) {
        case 'K':
            return $postMaxSize * 1024;
        case 'M':
            return $postMaxSize * 1048576;
        case 'G':
            return $postMaxSize * 1073741824;
        default:
            return $postMaxSize;
    }
}

/**
 *  Used to generate random color in parts
 */
function randomColorPart() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

/*
 *  Used to generate random color
 */

function generateRandomColor() {
    return randomColorPart() . randomColorPart() . randomColorPart();
}


/*
 *  Used to get task color
 */

function getTaskColor($status){
    if($status == 'unassigned')
        return '#727B84';
    elseif($status == 'pending')
        return '#009EFB';
    elseif($status == 'overdue')
        return '#F2F7F8';
    elseif($status == 'late')
        return '#FFD071';
    elseif($status == 'completed')
        return '#88DD92';
    else
        return;
}

function getTaskProgress($task) {
    $progress = 0;
    if($task->progress_type === 'manual')
        $progress = $task->progress;
    else {
        $completedSubTask = 0;
        if ($task->subTask) {
            foreach ($task->subTask as $element) {
                $completedSubTask += ($element->status) ? 1 : 0;
            }
            $totalSubTasks = sizeof($task->subTask);
            $progress = $totalSubTasks > 0 ? formatNumber(($completedSubTask/$totalSubTasks)*100) : 0;
        }
    }

    return $progress;
}

function findProjectProgress($project) {
    $progress = $project->progress;
    $completedSub = 0;
    if ($project->project_indicators) {
        foreach ($project->project_indicators as $element) {
            $completedSub += $element->progress;
        }
        $totalSub = sizeof($project->project_indicators);
        $progress = $totalSub > 0 ? formatNumber($completedSub/$totalSub) : 0;
    }

    return $progress;
}

/*
 *  returns SQL with values in it
 */
function generateSqlString($model)
{
    $replace = function ($sql, $bindings)
    {
        $needle = '?';
        foreach ($bindings as $replace){
            $pos = strpos($sql, $needle);
            if ($pos !== false) {
                if (gettype($replace) === "string") {
                    $replace = ' "'.addslashes($replace).'" ';
                }
                $sql = substr_replace($sql, $replace, $pos, strlen($needle));
            }
        }
        return $sql;
    };
    $sql = $replace($model->toSql(), $model->getBindings());

    return $sql;
}

/**
 * For Query Display as SQL
 * @param $builder
 * @return mixed|string
 */
function generateSqlString2($builder) {
    $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
    $query = vsprintf($query, $builder->getBindings());
    return $query;
}

/**
 * Return the first day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param $period
 * @param DateTime|null $date
 * @return DateTime
 * @throws Exception
 */
function firstDayOf($period, DateTime $date = null)
{
    $period = strtolower($period);
    $validPeriods = array('year', 'quarter', 'month', 'week');

    if ( ! in_array($period, $validPeriods))
        throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

    $newDate = ($date === null) ? new DateTime() : clone $date;

    switch ($period) {
        case 'year':
            $newDate->modify('first day of january ' . $newDate->format('Y'));
            break;
        case 'quarter':
            $month = $newDate->format('n') ;

            if ($month < 4) {
                $newDate->modify('first day of january ' . $newDate->format('Y'));
            } elseif ($month > 3 && $month < 7) {
                $newDate->modify('first day of april ' . $newDate->format('Y'));
            } elseif ($month > 6 && $month < 10) {
                $newDate->modify('first day of july ' . $newDate->format('Y'));
            } elseif ($month > 9) {
                $newDate->modify('first day of october ' . $newDate->format('Y'));
            }
            break;
        case 'month':
            $newDate->modify('first day of this month');
            break;
        case 'week':
            $newDate->modify(($newDate->format('w') === '0') ? 'monday last week' : 'monday this week');
            break;
    }

    return $newDate;
}

function quarter_day($time = "") {

    $time = $time ? strtotime($time) : time();
    $date = intval(date("j", $time));
    $month = intval(date("n", $time));
    $year = intval(date("Y", $time));

    // get selected quarter as number between 1 and 4
    $quarter = ceil($month / 3);

    // get first month of current quarter as number between 1 and 12
    $fmonth = $quarter + (($quarter - 1) * 2);

    // map days in a year by month
    $map = [31,28,31,30,31,30,31,31,30,31,30,31];

    // check if year is leap
    if (((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)))) $map[1] = 29;

    // get total number of days in selected quarter, by summing the relative portion of $map array
    $total = array_sum(array_slice($map, ($fmonth - 1), 3));

    // get number of days passed in selected quarter, by summing the relative portion of $map array
    $map[$month-1] = $date;
    $day = array_sum(array_slice($map, ($fmonth - 1), ($month - $fmonth + 1)));

    return $quarter.'-'.$year;
}
/**
 * @param $search
 * @param $array
 * @param null $value
 * @return bool|mixed
 */
function multi_array_key_exists($search, $array, $value=null) {
    if ($array && is_array($array) && array_key_exists($search, $array)) {
        if ($value && !is_array($array[$search]) && $array[$search] == $value) return $array;
        else if ($value) return false;

        return $array;
    }

    foreach ($array as $key => $element) {
        if (is_array($element)) {
            if (multi_array_key_exists($search, $element, $value)) {
                unset($array[$key]);
                return $element;
            }
        }
    }
    return false;
}