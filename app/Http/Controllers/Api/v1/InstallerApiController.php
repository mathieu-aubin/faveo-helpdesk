<?php

namespace App\Http\Controllers\Api\v1;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Model\helpdesk\Settings\System;
// models
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\User;
use Artisan;
// classes
use Config;
use File;
use Hash;
use Illuminate\Http\Request;

/**
 * |=======================================================================
 * |Class: InstallController
 * |=======================================================================.
 *
 *  Class to perform the first install operation without this the database
 *  settings could not be started
 *
 *  @author     Ladybird <info@ladybirdweb.com>
 */
class InstallerApiController extends Controller
{
    /**
     * config_database
     * This function is to configure the database and install the application via API call.
     *
     * @return type Json
     */
    public function config_database(Request $request)
    {
        $validator = \Validator::make(
            [
                'database'     => $request->database,
                'host'         => $request->host,
                'databasename' => $request->databasename,
                'dbusername'   => $request->dbusername,
                'port'         => $request->port,
            ],
            [
                'database'     => 'required|min:1',
                'host'         => 'required',
                'databasename' => 'required|min:1',
                'dbusername'   => 'required|min:1',
                'port'         => 'integer|min:0',
            ]
        );
        if ($validator->fails()) {
            $jsons = $validator->messages();
            $val = '';
            foreach ($jsons->all() as $key => $value) {
                $val .= $value;
            }
            $return_data = rtrim(str_replace('.', ',', $val), ',');

            return ['response' => 'fail', 'reason' => $return_data, 'status' => '0'];
        }
        $path1 = base_path().DIRECTORY_SEPARATOR.'.env';
        $path2 = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'database.php';
        $path3 = base_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'routes.php';
        $path4 = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'lfm.php';
        $f1 = substr(sprintf('%o', fileperms($path1)), -3);
        $f2 = substr(sprintf('%o', fileperms($path2)), -3);
        $f3 = substr(sprintf('%o', fileperms($path3)), -3);
        $f4 = substr(sprintf('%o', fileperms($path4)), -3);
        if ($f1 != '777' || $f2 != '777' || $f3 != '777' || $f4 != '777') {
            return ['response' => 'fail', 'reason' => 'File permission issue.', 'status' => '0'];
        }
        // dd($validator->messages());
        // error_reporting(E_ALL & ~E_NOTICE);
        // Check for pre install
        if (\Config::get('database.install') == '%0%') {
            $default = $request->database;
            $host = $request->host;
            $database = $request->databasename;
            $dbusername = $request->dbusername;
            $dbpassword = $request->dbpassword;
            $port = $request->port;
            if (isset($default) && isset($host) && isset($database) && isset($dbusername)) {
                // Setting environment values
                $ENV['APP_ENV'] = 'local';
                $ENV['APP_DEBUG'] = 'false';
                $ENV['APP_KEY'] = 'SomeRandomString';
                $ENV['DB_TYPE'] = $default;
                $ENV['DB_HOST'] = $host;
                $ENV['DB_PORT'] = $port;
                $ENV['DB_DATABASE'] = $database;
                $ENV['DB_USERNAME'] = $dbusername;
                $ENV['DB_PASSWORD'] = $dbpassword;
                $ENV['MAIL_DRIVER'] = 'smtp';
                $ENV['MAIL_HOST'] = 'mailtrap.io';
                $ENV['MAIL_PORT'] = '2525';
                $ENV['MAIL_USERNAME'] = 'null';
                $ENV['MAIL_PASSWORD'] = 'null';
                $ENV['CACHE_DRIVER'] = 'file';
                $ENV['SESSION_DRIVER'] = 'file';
                $ENV['QUEUE_DRIVER'] = 'sync';

                $config = '';
                foreach ($ENV as $key => $val) {
                    $config .= "{$key}={$val}\n";
                }
                // Write environment file
                $fp = fopen(base_path().'/.env', 'w');
                fwrite($fp, $config);
                fclose($fp);

                return ['response' => 'success', 'status' => '1'];
            } else {
                return ['response' => 'fail', 'reason' => 'insufficient parameters', 'status' => '0'];
            }
        } else {
            return ['response' => 'fail', 'reason' => 'this system is already installed', 'status' => '0'];
        }
    }

    /**
     * config_database
     * This function is to configure the database and install the application via API call.
     *
     * @return type Json
     */
    public function config_system(Request $request)
    {
        $validator = \Validator::make(
            [
                'firstname' => $request->firstname,
                'lastname'  => $request->lastname,
                'email'     => $request->email,
                'username'  => $request->username,
                'password'  => $request->password,
                'timezone'  => $request->timezone,
                'datetime'  => $request->datetime,
            ],
            [
                'firstname' => 'required|alpha|min:1',
                'lastname'  => 'required|alpha|min:1',
                'email'     => 'required|email|min:1',
                'username'  => 'required|min:4',
                'password'  => 'required|min:6',
                'timezone'  => 'required|min:1',
                'datetime'  => 'required|min:1',
            ]
        );
        if ($validator->fails()) {
            $jsons = $validator->messages();
            $val = '';
            foreach ($jsons->all() as $key => $value) {
                $val .= $value;
            }
            $return_data = rtrim(str_replace('.', ',', $val), ',');

            return ['response' => 'fail', 'reason' => $return_data, 'status' => '0'];
        }
        // Check for pre install
        if (\Config::get('database.install') == '%0%') {
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $email = $request->email;
            $username = $request->username;
            $password = $request->password;
            $timezone = $request->timezone;
            $datetime = $request->datetime;

            // Migrate database
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            // checking requested timezone for the admin and system
            $timezones = Timezones::where('name', '=', $timezone)->first();
            if ($timezones == null) {
                Artisan::call('migrate:reset', ['--force' => true]);

                return ['response' => 'fail', 'reason' => 'Invalid time-zone', 'status' => '0'];
            }
            // checking requested date time format for the admin and system
            $date_time_format = Date_time_format::where('format', '=', $datetime)->first();
            if ($date_time_format == null) {
                Artisan::call('migrate:reset', ['--force' => true]);

                return ['response' => 'fail', 'reason' => 'invalid date-time format', 'status' => '0'];
            }
            // Creating minum settings for system
            $system = new System();
            $system->status = 1;
            $system->department = 1;
            $system->date_time_format = $date_time_format->id;
            $system->time_zone = $timezones->id;
            $system->save();

            // Creating user
            $user = User::create([
                        'first_name'   => $firstname,
                        'last_name'    => $lastname,
                        'email'        => $email,
                        'user_name'    => $username,
                        'password'     => Hash::make($password),
                        'active'       => 1,
                        'role'         => 'admin',
                        'assign_group' => 1,
                        'primary_dpt'  => 1,
            ]);

            // Setting database installed status
            $value = '1';
            $install = app_path('../config/database.php');
            $datacontent = File::get($install);
            $datacontent = str_replace('%0%', $value, $datacontent);
            File::put($install, $datacontent);

            // Applying email configuration on route
            $smtpfilepath = "\App\Http\Controllers\Common\SettingsController::smtp()";
            $lfmpath = "url('photos').'/'";
            $path22 = app_path('Http/routes.php');
            $path23 = base_path('config/lfm.php');
            $content23 = File::get($path22);
            $content24 = File::get($path23);
            $content23 = str_replace('"%smtplink%"', $smtpfilepath, $content23);
            $content24 = str_replace("'%url%'", $lfmpath, $content24);
            $link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $pos = strpos($link, 'api/v1/system-config');
            $link = substr($link, 0, $pos);
            $app_url = app_path('../config/app.php');
            $datacontent2 = File::get($app_url);
            $datacontent2 = str_replace('http://localhost', $link, $datacontent2);
            File::put($app_url, $datacontent2);
            File::put($path22, $content23);
            File::put($path23, $content24);
            Artisan::call('key:generate');
            // If user created return success
            if ($user) {
                return ['response' => 'success', 'status' => '1'];
            }
        } else {
            return ['response' => 'fail', 'reason' => 'this system is already installed', 'status' => '0'];
        }
    }
}
