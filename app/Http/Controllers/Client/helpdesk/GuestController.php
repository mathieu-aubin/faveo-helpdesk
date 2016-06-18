<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\ProfilePassword;
use App\Http\Requests\helpdesk\ProfileRequest;
use App\Http\Requests\helpdesk\TicketRequest;
use App\Model\helpdesk\Manage\Help_topic;
// models
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\User;
use Auth;
// classes
use Exception;
use GeoIP;
use Hash;
use Illuminate\Http\Request;
use Input;
use Lang;

/**
 * GuestController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class GuestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->middleware('board');
        $this->PhpMailController = $PhpMailController;
        // checking authentication
        $this->middleware('auth');
    }

     /**
      * Get profile.
      *
      * @return type Response
      */
     public function getProfile(CountryCode $code)
     {
         $user = Auth::user();
         $location = GeoIP::getLocation('');
         $phonecode = $code->where('iso', '=', $location['isoCode'])->first();

         return view('themes.default1.client.helpdesk.profile', compact('user'))->with('phonecode', $phonecode->phonecode);
     }

    /**
     * Save profile data.
     *
     * @param type                $id
     * @param type ProfileRequest $request
     *
     * @return type Response
     */
    public function postProfile(ProfileRequest $request)
    {
        $user = User::where('id', '=', Auth::user()->id)->first();
        $user->gender = $request->get('gender');
        $user->save();
        if ($user->profile_pic == 'avatar5.png' || $user->profile_pic == 'avatar2.png') {
            if ($request->input('gender') == 1) {
                $name = 'avatar5.png';
                $destinationPath = 'lb-faveo/media/profilepic';
                $user->profile_pic = $name;
            } elseif ($request->input('gender') == 0) {
                $name = 'avatar2.png';
                $destinationPath = 'lb-faveo/media/profilepic';
                $user->profile_pic = $name;
            }
        }
        if (Input::file('profile_pic')) {
            //$extension = Input::file('profile_pic')->getClientOriginalExtension();
            $name = Input::file('profile_pic')->getClientOriginalName();
            $destinationPath = 'lb-faveo/media/profilepic';
            $fileName = rand(0000, 9999).'.'.$name;
            //echo $fileName;
            Input::file('profile_pic')->move($destinationPath, $fileName);
            $user->profile_pic = $fileName;
        } else {
            if ($request->get('country_code') == '' && ($request->get('phone_number') != '' || $request->get('mobile') != '')) {
                return redirect()->back()->with(['fails1'       => Lang::get('lang.country-code-required-error'),
                                                 'country_code' => 1, ])->withInput();
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $request->get('country_code'))->get();
                if (!count($code)) {
                    return redirect()->back()->with(['fails1'           => Lang::get('lang.incorrect-country-code-error'),
                                                         'country_code' => 1, ])->withInput();
                } else {
                    $user->country_code = $request->input('country_code');
                }
            }
            $user->fill($request->except('profile_pic', 'gender'))->save();

            return redirect()->back()->with('success1', Lang::get('lang.profile_updated_sucessfully'));
        }
        if ($user->fill($request->except('profile_pic'))->save()) {
            return redirect()->back()->with('success1', Lang::get('lang.profile_updated_sucessfully'));
        }
    }

    /**
     * Get Ticket page.
     *
     * @param type Help_topic $topic
     *
     * @return type Response
     */
    public function getTicket(Help_topic $topic)
    {
        $topics = $topic->get();

        return view('themes.default1.client.helpdesk.tickets.form', compact('topics'));
    }

    /**
     * getform.
     *
     * @param type Help_topic $topic
     *
     * @return type
     */
    public function getForm(Help_topic $topic)
    {
        if (\Config::get('database.install') == '%0%') {
            return \Redirect::route('license');
        }
        if (System::first()->status == 1) {
            $topics = $topic->get();

            return view('themes.default1.client.helpdesk.form', compact('topics'));
        } else {
            return \Redirect::route('home');
        }
    }

    /**
     * Get my ticket.
     *
     * @param type Tickets       $tickets
     * @param type Ticket_Thread $thread
     * @param type User          $user
     *
     * @return type Response
     */
    public function getMyticket()
    {
        return view('themes.default1.client.helpdesk.mytickets');
    }

    /**
     * Get ticket-thread.
     *
     * @param type Ticket_Thread $thread
     * @param type Tickets       $tickets
     * @param type User          $user
     *
     * @return type Response
     */
    public function thread(Ticket_Thread $thread, Tickets $tickets, User $user)
    {
        $user_id = Auth::user()->id;
        //dd($user_id);
        /* get the ticket's id == ticket_id of thread  */
        $tickets = $tickets->where('user_id', '=', $user_id)->first();
        //dd($ticket);
        $thread = $thread->where('ticket_id', $tickets->id)->first();
        //dd($thread);
        // $tickets = $tickets->whereId($id)->first();
        return view('themes.default1.client.guest-user.view_ticket', compact('thread', 'tickets'));
    }

    /**
     * ticket Edit.
     *
     * @return
     */
    public function ticketEdit()
    {
    }

    /**
     * Post porfile password.
     *
     * @param type                 $id
     * @param type ProfilePassword $request
     *
     * @return type Response
     */
    public function postProfilePassword(ProfilePassword $request)
    {
        $user = Auth::user();
        //echo $user->password;
        if (Hash::check($request->input('old_password'), $user->getAuthPassword())) {
            $user->password = Hash::make($request->input('new_password'));
            try {
                $user->save();

                return redirect()->back()->with('success2', Lang::get('lang.password_updated_sucessfully'));
            } catch (Exception $e) {
                return redirect()->back()->with('fails2', $e->getMessage());
            }
        } else {
            return redirect()->back()->with('fails2', Lang::get('lang.password_was_not_updated_incorrect_old_password'));
        }
    }

    /**
     * Ticekt reply.
     *
     * @param type Ticket_Thread $thread
     * @param type TicketRequest $request
     *
     * @return type Response
     */
    public function reply(Ticket_Thread $thread, TicketRequest $request)
    {
        $thread->ticket_id = $request->input('ticket_ID');
        $thread->title = $request->input('To');
        $thread->user_id = Auth::user()->id;
        $thread->body = $request->input('reply_content');
        $thread->poster = 'user';
        $thread->save();
        $ticket_id = $request->input('ticket_ID');
        $tickets = Tickets::where('id', '=', $ticket_id)->first();
        $thread = Ticket_Thread::where('ticket_id', '=', $ticket_id)->first();

        return Redirect('thread/'.$ticket_id);
    }

    /**
     * Get Checked ticket.
     *
     * @param type Tickets $ticket
     * @param type User    $user
     *
     * @return type response
     */
    public function getCheckTicket(Tickets $ticket, User $user)
    {
        return view('themes.default1.client.helpdesk.guest-user.newticket', compact('ticket'));
    }

    /**
     * Post Check ticket.
     *
     * @param type CheckTicket   $request
     * @param type User          $user
     * @param type Tickets       $ticket
     * @param type Ticket_Thread $thread
     *
     * @return type Response
     */
    public function PostCheckTicket(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                    'email'         => 'required|email',
                    'ticket_number' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput()
                            ->with('check', '1');
        }
        $Email = $request->input('email');
        $Ticket_number = $request->input('ticket_number');
        $ticket = Tickets::where('ticket_number', '=', $Ticket_number)->first();
        if ($ticket == null) {
            return \Redirect::route('form')->with('fails', Lang::get('lang.there_is_no_such_ticket_number'));
        } else {
            $userId = $ticket->user_id;
            $user = User::where('id', '=', $userId)->first();
            if ($user->role == 'user') {
                $username = $user->user_name;
            } else {
                $username = $user->first_name.' '.$user->last_name;
            }
            if ($user->email != $Email) {
                return \Redirect::route('form')->with('fails', Lang::get("lang.email_didn't_match_with_ticket_number"));
            } else {
                $code = $ticket->id;
                $code = \Crypt::encrypt($code);

                $company = $this->company();

                $this->PhpMailController->sendmail(
                        $from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $username, 'email' => $user->email], $message = ['subject' => 'Ticket link Request ['.$Ticket_number.']', 'scenario' => 'check-ticket'], $template_variables = ['user' => $username, 'ticket_link_with_number' => \URL::route('check_ticket', $code)]
                );

                return \Redirect::back()
                                ->with('success', Lang::get('lang.we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'));
            }
        }
    }

    /**
     * get ticket email.
     *
     * @param type $id
     *
     * @return type
     */
    public function get_ticket_email($id)
    {
        $id1 = \Crypt::decrypt($id);

        return view('themes.default1.client.helpdesk.ckeckticket', compact('id'));
    }

    /**
     * get ticket status.
     *
     * @param type Tickets $ticket
     *
     * @return type
     */
    public function getTicketStat(Tickets $ticket)
    {
        return view('themes.default1.client.helpdesk.ckeckticket', compact('ticket'));
    }

    /**
     * get company.
     *
     * @return type
     */
    public function company()
    {
        $company = Company::Where('id', '=', '1')->first();
        if ($company->company_name == null) {
            $company = 'Support Center';
        } else {
            $company = $company->company_name;
        }

        return $company;
    }
}
