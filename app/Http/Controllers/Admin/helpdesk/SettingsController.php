<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\CompanyRequest;
use App\Http\Requests\helpdesk\EmailRequest;
use App\Http\Requests\helpdesk\RatingUpdateRequest;
use App\Http\Requests\helpdesk\StatusRequest;
use App\Http\Requests\helpdesk\SystemRequest;
// models
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Email\Template;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Notification\UserNotification;
use App\Model\helpdesk\Ratings\Rating;
use App\Model\helpdesk\Settings\Alert;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Settings\Responder;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Settings\Ticket;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Utility\Date_format;
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\Model\helpdesk\Workflow\WorkflowClose;
use DateTime;
// classes
use DB;
use Exception;
use File;
use Illuminate\Http\Request;
use Input;
use Lang;

/**
 * SettingsController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->smtp();
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * @param int $id
     * @param $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function getcompany(Company $company)
    {
        try {
            /* fetch the values of company from company table */
            $companys = $company->whereId('1')->first();
            /* Direct to Company Settings Page */
            return view('themes.default1.admin.helpdesk.settings.company', compact('companys'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int            $id
     * @param type Company        $company
     * @param type CompanyRequest $request
     *
     * @return Response
     */
    public function postcompany($id, Company $company, CompanyRequest $request)
    {
        /* fetch the values of company request  */
        $companys = $company->whereId('1')->first();
        if (Input::file('logo')) {
            $name = Input::file('logo')->getClientOriginalName();
            $destinationPath = 'lb-faveo/media/company/';
            $fileName = rand(0000, 9999).'.'.$name;
            Input::file('logo')->move($destinationPath, $fileName);
            $companys->logo = $fileName;
        }
        if ($request->input('use_logo') == null) {
            $companys->use_logo = '0';
        }
        /* Check whether function success or not */
        try {
            $companys->fill($request->except('logo'))->save();
            /* redirect to Index page with Success Message */
            return redirect('getcompany')->with('success', Lang::get('lang.company_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getcompany')->with('fails', Lang::get('lang.company_can_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * function to delete system logo.
     *
     *  @return type string
     */
    public function deleteLogo()
    {
        $path = $_GET['data1']; //get file path of logo image
        if (!unlink($path)) {
            return 'false';
        } else {
            $companys = Company::where('id', '=', 1)->first();
            $companys->logo = null;
            $companys->use_logo = '0';
            $companys->save();

            return 'true';
        }
        // return $res;
    }

    /**
     * get the form for System setting page.
     *
     * @param type System           $system
     * @param type Department       $department
     * @param type Timezones        $timezone
     * @param type Date_format      $date
     * @param type Date_time_format $date_time
     * @param type Time_format      $time
     *
     * @return type Response
     */
    public function getsystem(System $system, Department $department, Timezones $timezone, Date_format $date, Date_time_format $date_time, Time_format $time)
    {
        try {
            /* fetch the values of system from system table */
            $systems = $system->whereId('1')->first();
            /* Fetch the values from Department table */
            $departments = $department->get();
            /* Fetch the values from Timezones table */
            $timezones = $timezone->get();

            //$debug = \Config::get('app.debug');
            //dd($value);
            /* Direct to System Settings Page */
            return view('themes.default1.admin.helpdesk.settings.system', compact('systems', 'departments', 'timezones', 'time', 'date', 'date_time'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int           $id
     * @param type System        $system
     * @param type SystemRequest $request
     *
     * @return type Response
     */
    public function postsystem($id, System $system, SystemRequest $request)
    {
        try {
            // dd($request);
            /* fetch the values of system request  */
            $systems = $system->whereId('1')->first();
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $systems->fill($request->input())->save();
            /* redirect to Index page with Success Message */

            // dd($datacontent);
            //\Config::set('app.debug', $request->input('debug'));
            return redirect('getsystem')->with('success', Lang::get('lang.system_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getsystem')->with('fails', Lang::get('lang.system_can_not_updated').'<br>'.$e->getMessage());
        }
    }

    /**
     * get the form for Ticket setting page.
     *
     * @param type Ticket     $ticket
     * @param type Sla_plan   $sla
     * @param type Help_topic $topic
     * @param type Priority   $priority
     *
     * @return type Response
     */
    public function getticket(Ticket $ticket, Sla_plan $sla, Help_topic $topic, Ticket_Priority $priority)
    {
        try {
            /* fetch the values of ticket from ticket table */
            $tickets = $ticket->whereId('1')->first();
            /* Fetch the values from SLA Plan table */
            $slas = $sla->get();
            /* Fetch the values from Help_topic table */
            $topics = $topic->get();
            /* Direct to Ticket Settings Page */
            return view('themes.default1.admin.helpdesk.settings.ticket', compact('tickets', 'slas', 'topics', 'priority'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int     $id
     * @param type Ticket  $ticket
     * @param type Request $request
     *
     * @return type Response
     */
    public function postticket($id, Ticket $ticket, Request $request)
    {
        try {
            /* fetch the values of ticket request  */
            $tickets = $ticket->whereId('1')->first();
            /* fill the values to coompany table */
            $tickets->fill($request->except('captcha', 'claim_response', 'assigned_ticket', 'answered_ticket', 'agent_mask', 'html', 'client_update'))->save();
            /* insert checkbox to Database  */
            $tickets->captcha = $request->input('captcha');
            $tickets->claim_response = $request->input('claim_response');
            $tickets->assigned_ticket = $request->input('assigned_ticket');
            $tickets->answered_ticket = $request->input('answered_ticket');
            $tickets->agent_mask = $request->input('agent_mask');
            $tickets->html = $request->input('html');
            $tickets->client_update = $request->input('client_update');
            $tickets->collision_avoid = $request->input('collision_avoid');
            /* Check whether function success or not */
            $tickets->save();
            /* redirect to Index page with Success Message */
            return redirect('getticket')->with('success', Lang::get('lang.ticket_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getticket')->with('fails', Lang::get('lang.ticket_can_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Email setting page.
     *
     * @param type Email    $email
     * @param type Template $template
     * @param type Emails   $email1
     *
     * @return type Response
     */
    public function getemail(Email $email, Template $template, Emails $email1)
    {
        try {
            /* fetch the values of email from Email table */
            $emails = $email->whereId('1')->first();
            /* Fetch the values from Template table */
            $templates = $template->get();
            /* Fetch the values from Emails table */
            $emails1 = $email1->get();
            /* Direct to Email Settings Page */
            return view('themes.default1.admin.helpdesk.settings.email', compact('emails', 'templates', 'emails1'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int          $id
     * @param type Email        $email
     * @param type EmailRequest $request
     *
     * @return type Response
     */
    public function postemail($id, Email $email, EmailRequest $request)
    {
        try {
            /* fetch the values of email request  */
            $emails = $email->whereId('1')->first();
            /* fill the values to email table */
            $emails->fill($request->except('email_fetching', 'all_emails', 'email_collaborator', 'strip', 'attachment'))->save();
            /* insert checkboxes  to database */
            // $emails->email_fetching = $request->input('email_fetching');
            // $emails->notification_cron = $request->input('notification_cron');
            $emails->all_emails = $request->input('all_emails');
            $emails->email_collaborator = $request->input('email_collaborator');
            $emails->strip = $request->input('strip');
            $emails->attachment = $request->input('attachment');
            /* Check whether function success or not */
            $emails->save();
            /* redirect to Index page with Success Message */
            return redirect('getemail')->with('success', Lang::get('lang.email_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getemail')->with('fails', Lang::get('lang.email_can_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for cron job setting page.
     *
     * @param type Email    $email
     * @param type Template $template
     * @param type Emails   $email1
     *
     * @return type Response
     */
    public function getSchedular(Email $email, Template $template, Emails $email1, WorkflowClose $workflow)
    {
        // try {
        /* fetch the values of email from Email table */
        $emails = $email->whereId('1')->first();
        /* Fetch the values from Template table */
        $templates = $template->get();
        /* Fetch the values from Emails table */
        $emails1 = $email1->get();

        $workflow = $workflow->whereId('1')->first();

        return view('themes.default1.admin.helpdesk.settings.crone', compact('emails', 'templates', 'emails1', 'workflow'));
        // } catch {
        // }
    }

    /**
     * Update the specified resource in storage for cron job.
     *
     * @param type Email        $email
     * @param type EmailRequest $request
     *
     * @return type Response
     */
    public function postSchedular(Email $email, Template $template, Emails $email1, Request $request, WorkflowClose $workflow)
    {
        // dd($request);
        try {
            /* fetch the values of email request  */
            $emails = $email->whereId('1')->first();
            if ($request->email_fetching) {
                $emails->email_fetching = $request->email_fetching;
            } else {
                $emails->email_fetching = 0;
            }
            if ($request->notification_cron) {
                $emails->notification_cron = $request->notification_cron;
            } else {
                $emails->notification_cron = 0;
            }
            $emails->save();
            //workflow
            $work = $workflow->whereId('1')->first();
            if ($request->condition == 'on') {
                $work->condition = 1;
            } else {
                $work->condition = 0;
            }
            $work->save();
            /* redirect to Index page with Success Message */
            return redirect('job-scheduler')->with('success', Lang::get('lang.job-scheduler-success'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('job-scheduler')->with('fails', Lang::get('lang.job-scheduler-error').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Responder setting page.
     *
     * @param type Responder $responder
     *
     * @return type Response
     */
    public function getresponder(Responder $responder)
    {
        try {
            /* fetch the values of responder from responder table */
            $responders = $responder->whereId('1')->first();
            /* Direct to Responder Settings Page */
            return view('themes.default1.admin.helpdesk.settings.responder', compact('responders'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type Responder $responder
     * @param type Request   $request
     *
     * @return type
     */
    public function postresponder(Responder $responder, Request $request)
    {
        try {
            /* fetch the values of responder request  */
            $responders = $responder->whereId('1')->first();
            /* insert Checkbox value to DB */
            $responders->new_ticket = $request->input('new_ticket');
            $responders->agent_new_ticket = $request->input('agent_new_ticket');
            $responders->submitter = $request->input('submitter');
            $responders->participants = $request->input('participants');
            $responders->overlimit = $request->input('overlimit');
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $responders->save();
            /* redirect to Index page with Success Message */
            return redirect('getresponder')->with('success', Lang::get('lang.auto_response_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getresponder')->with('fails', Lang::get('lang.auto_response_can_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Alert setting page.
     *
     * @param type Alert $alert
     *
     * @return type Response
     */
    public function getalert(Alert $alert)
    {
        try {
            /* fetch the values of alert from alert table */
            $alerts = $alert->whereId('1')->first();
            /* Direct to Alert Settings Page */
            return view('themes.default1.admin.helpdesk.settings.alert', compact('alerts'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type         $id
     * @param type Alert   $alert
     * @param type Request $request
     *
     * @return type Response
     */
    public function postalert($id, Alert $alert, Request $request)
    {
        try {
            /* fetch the values of alert request  */
            $alerts = $alert->whereId('1')->first();
            /* Insert Checkbox to DB */
            $alerts->assignment_status = $request->input('assignment_status');
            $alerts->ticket_status = $request->input('ticket_status');
            $alerts->overdue_department_member = $request->input('overdue_department_member');
            $alerts->sql_error = $request->input('sql_error');
            $alerts->excessive_failure = $request->input('excessive_failure');
            $alerts->overdue_status = $request->input('overdue_status');
            $alerts->overdue_assigned_agent = $request->input('overdue_assigned_agent');
            $alerts->overdue_department_manager = $request->input('overdue_department_manager');
            $alerts->internal_status = $request->input('internal_status');
            $alerts->internal_last_responder = $request->input('internal_last_responder');
            $alerts->internal_assigned_agent = $request->input('internal_assigned_agent');
            $alerts->internal_department_manager = $request->input('internal_department_manager');
            $alerts->assignment_assigned_agent = $request->input('assignment_assigned_agent');
            $alerts->assignment_team_leader = $request->input('assignment_team_leader');
            $alerts->assignment_team_member = $request->input('assignment_team_member');
            $alerts->system_error = $request->input('system_error');
            $alerts->transfer_department_member = $request->input('transfer_department_member');
            $alerts->transfer_department_manager = $request->input('transfer_department_manager');
            $alerts->transfer_assigned_agent = $request->input('transfer_assigned_agent');
            $alerts->transfer_status = $request->input('transfer_status');
            $alerts->message_organization_accmanager = $request->input('message_organization_accmanager');
            $alerts->message_department_manager = $request->input('message_department_manager');
            $alerts->message_assigned_agent = $request->input('message_assigned_agent');
            $alerts->message_last_responder = $request->input('message_last_responder');
            $alerts->message_status = $request->input('message_status');
            $alerts->ticket_organization_accmanager = $request->input('ticket_organization_accmanager');
            $alerts->ticket_department_manager = $request->input('ticket_department_manager');
            $alerts->ticket_department_member = $request->input('ticket_department_member');
            $alerts->ticket_admin_email = $request->input('ticket_admin_email');

            if ($request->input('system_error') == null) {
                $str = '%0%';
                $path = app_path('../config/app.php');
                $content = \File::get($path);
                $content = str_replace('%1%', $str, $content);
                \File::put($path, $content);
            } else {
                $str = '%1%';
                $path = app_path('../config/app.php');
                $content = \File::get($path);
                $content = str_replace('%0%', $str, $content);
                \File::put($path, $content);
            }
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $alerts->save();
            /* redirect to Index page with Success Message */
            return redirect('getalert')->with('success', Lang::get('lang.alert_&_notices_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getalert')->with('fails', Lang::get('lang.alert_&_notices_can_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     *  Generate Api key.
     *
     *  @return type json
     */
    public function generateApiKey()
    {
        $key = str_random(32);

        return $key;
    }

    /**
     * Main Settings Page.
     *
     * @return type view
     */
    public function settings()
    {
        return view('themes.default1.admin.helpdesk.setting');
    }

    /**
     * @param int $id
     * @param $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function getStatuses()
    {
        try {
            /* fetch the values of company from company table */
            $statuss = \DB::table('ticket_status')->get();
            /* Direct to Company Settings Page */
            return view('themes.default1.admin.helpdesk.settings.status', compact('statuss'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * @param int $id
     * @param $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function getEditStatuses($id)
    {
        try {
            /* fetch the values of company from company table */
            $status = \DB::table('ticket_status')->where('id', '=', $id)->first();
            /* Direct to Company Settings Page */
            return view('themes.default1.admin.helpdesk.settings.status-edit', compact('status'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * @param int $id
     * @param $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function editStatuses($id, StatusRequest $request)
    {
        try {
            /* fetch the values of company from company table */
            $statuss = \App\Model\helpdesk\Ticket\Ticket_Status::whereId($id)->first();
            $statuss->name = $request->input('name');
            $statuss->icon_class = $request->input('icon_class');
            $statuss->email_user = $request->input('email_user');
            $statuss->sort = $request->input('sort');
            $delete = $request->input('deleted');
            if ($delete == 'yes') {
                $statuss->state = 'delete';
            } else {
                $statuss->state = $request->input('state');
            }
            $statuss->sort = $request->input('sort');
            $statuss->save();
            /* Direct to Company Settings Page */
            return redirect()->back()->with('success', Lang::get('lang.status_has_been_updated_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * create a status.
     *
     * @param \App\Model\helpdesk\Ticket\Ticket_Status  $statuss
     * @param \App\Http\Requests\helpdesk\StatusRequest $request
     *
     * @return type redirect
     */
    public function createStatuses(\App\Model\helpdesk\Ticket\Ticket_Status $statuss, StatusRequest $request)
    {
        try {
            /* fetch the values of company from company table */
            $statuss->name = $request->input('name');
            $statuss->icon_class = $request->input('icon_class');
            $statuss->email_user = $request->input('email_user');
            $statuss->sort = $request->input('sort');
            $delete = $request->input('delete');
            if ($delete == 'yes') {
                $statuss->state = 'deleted';
            } else {
                $statuss->state = $request->input('state');
            }
            $statuss->sort = $request->input('sort');
            $statuss->save();
            /* Direct to Company Settings Page */
            return redirect()->back()->with('success', Lang::get('lang.status_has_been_created_successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * delete a status.
     *
     * @param type $id
     *
     * @return type redirect
     */
    public function deleteStatuses($id)
    {
        try {
            if ($id > 5) {
                /* fetch the values of company from company table */
                \App\Model\helpdesk\Ticket\Ticket_Status::whereId($id)->delete();
                /* Direct to Company Settings Page */
                return redirect()->back()->with('success', Lang::get('lang.status_has_been_deleted'));
            } else {
                return redirect()->back()->with('failed', Lang::get('lang.you_cannot_delete_this_status'));
            }
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * get the page of notification settings.
     *
     * @return type view
     */
    public function notificationSettings()
    {
        return view('themes.default1.admin.helpdesk.settings.notification');
    }

    /**
     * delete a notification.
     *
     * @return type redirect
     */
    public function deleteReadNoti()
    {
        $markasread = UserNotification::where('is_read', '=', 1)->get();
        foreach ($markasread as $mark) {
            $mark->delete();
            \App\Model\helpdesk\Notification\Notification::whereId($mark->notification_id)->delete();
        }

        return redirect()->back()->with('success', Lang::get('lang.you_have_deleted_all_the_read_notifications'));
    }

    /**
     * delete a notification log.
     *
     * @return type redirect
     */
    public function deleteNotificationLog()
    {
        $days = Input::get('no_of_days');
        if ($days == null) {
            return redirect()->back()->with('fails', 'Please enter valid no of days');
        }
        $date = new DateTime();
        $date->modify($days.' day');
        $formatted_date = $date->format('Y-m-d H:i:s');
        $markasread = UserNotification::where('created_at', '<=', $formatted_date)->get();
        foreach ($markasread as $mark) {
            $mark->delete();
            \App\Model\helpdesk\Notification\Notification::whereId($mark->notification_id)->delete();
        }

        return redirect()->back()->with('success', Lang::get('lang.you_have_deleted_all_the_notification_records_since').$days.' days.');
    }

    /**
     * 	To display the list of ratings in the system.
     *
     *  @return type View
     */
    public function RatingSettings()
    {
        try {
            $ratings = Rating::orderBy('display_order', 'asc')->get();

            return view('themes.default1.admin.helpdesk.settings.ratings', compact('ratings'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * edit a rating.
     *
     * @param type $id
     *
     * @return type view
     */
    public function editRatingSettings($id)
    {
        try {
            $rating = Rating::whereId($id)->first();

            return view('themes.default1.admin.helpdesk.settings.edit-ratings', compact('rating'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * 	To store rating data.
     *
     *  @return type Redirect
     */
    public function PostRatingSettings($id, Rating $ratings, RatingUpdateRequest $request)
    {
        try {
            $rating = $ratings->whereId($id)->first();
            $rating->name = $request->input('name');
            $rating->display_order = $request->input('display_order');
            $rating->allow_modification = $request->input('allow_modification');
            $rating->rating_scale = $request->input('rating_scale');
//            $rating->rating_area = $request->input('rating_area');
            $rating->restrict = $request->input('restrict');
            $rating->save();

            return redirect()->back()->with('success', Lang::get('lang.ratings_updated_successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * get the create rating page.
     *
     * @return type redirect
     */
    public function createRating()
    {
        try {
            return view('themes.default1.admin.helpdesk.settings.create-ratings');
        } catch (Exception $ex) {
            return redirect('getratings')->with('fails', Lang::get('lang.ratings_can_not_be_created').'<li>'.$ex->getMessage().'</li>');
        }
    }

    /**
     * store a rating value.
     *
     * @param \App\Model\helpdesk\Ratings\Rating        $rating
     * @param \App\Model\helpdesk\Ratings\RatingRef     $ratingrefs
     * @param \App\Http\Requests\helpdesk\RatingRequest $request
     *
     * @return type redirect
     */
    public function storeRating(Rating $rating, \App\Model\helpdesk\Ratings\RatingRef $ratingrefs, \App\Http\Requests\helpdesk\RatingRequest $request)
    {
        $rating->name = $request->input('name');
        $rating->display_order = $request->input('display_order');
        $rating->allow_modification = $request->input('allow_modification');
        $rating->rating_scale = $request->input('rating_scale');
        $rating->rating_area = $request->input('rating_area');
        $rating->restrict = $request->input('restrict');
        $rating->save();
        $ratingrefs->rating_id = $rating->id;
        $ratingrefs->save();

        return redirect()->back()->with('success', Lang::get('lang.successfully_created_this_rating'));
    }

    /**
     *  To delete a type of rating.
     *
     * 	@return type Redirect
     */
    public function RatingDelete($slug, \App\Model\helpdesk\Ratings\RatingRef $ratingrefs)
    {
        $ratingrefs->where('rating_id', '=', $slug)->delete();
        Rating::whereId($slug)->delete();

        return redirect()->back()->with('success', Lang::get('lang.rating_deleted_successfully'));
    }
}
