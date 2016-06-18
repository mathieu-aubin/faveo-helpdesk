<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
/*  Include Sys_user Model  */
use App\Http\Requests\helpdesk\ProfilePassword;
/* For validation include Sys_userRequest in create  */
use App\Http\Requests\helpdesk\ProfileRequest;
/* For validation include Sys_userUpdate in update  */
use App\Http\Requests\helpdesk\Sys_userRequest;
/*  include guest_note model */
use App\Http\Requests\helpdesk\Sys_userUpdate;
// models
use App\Model\helpdesk\Agent_panel\Organization;
use App\Model\helpdesk\Agent_panel\User_org;
use App\User;
// classes
use Auth;
use Exception;
use Hash;
use Input;
use Redirect;

/**
 * UserController
 * This controller is used to CRUD an User details, and proile management of an agent.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class UserController2 extends Controller
{
    /**
     * Create a new controller instance.
     * constructor to check
     * 1. authentication
     * 2. user roles
     * 3. roles must be agent.
     *
     * @return void
     */
    public function __construct()
    {
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        $this->middleware('role.agent');
    }

    /**
     * Display all list of the users.
     *
     * @param type User $user
     *
     * @return type view
     */
    public function index()
    {
        try {
            /* get all values in Sys_user */
            return view('themes.default1.agent.helpdesk.user.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * This function is used to display the list of users using chumper datatables.
     *
     * @return datatable
     */
    public function user_list()
    {
        // displaying list of users with chumper datatables
        return \Datatable::collection(User::where('role', '!=', 'admin')->where('role', '!=', 'agent')->get())
                        /* searchable column username and email*/
                        ->searchColumns('user_name', 'email', 'phone')
                        /* order column username and email */
                        ->orderColumns('user_name', 'email')
                        /* column username */
                        ->addColumn('user_name', function ($model) {
                            if (strlen($model->user_name) > 20) {
                                $username = substr($model->user_name, 0, 30);
                                $username = substr($username, 0, strrpos($username, ' ')).' ...';
                            } else {
                                $username = "<a href='".route('user.edit', $model->id)."'>".$model->user_name.'</a>';
                            }

                            return $username;
                        })
                        /* column email */
                        ->addColumn('email', function ($model) {
                            $email = "<a href='".route('user.edit', $model->id)."'>".$model->email.'</a>';

                            return $email;
                        })
                        /* column phone */
                        ->addColumn('phone', function ($model) {
                            $phone = '';
                            if ($model->phone_number) {
                                $phone = $model->ext.' '.$model->phone_number;
                            }
                            $mobile = '';
                            if ($model->mobile) {
                                $mobile = $model->mobile;
                            }
                            $phone = $phone.'&nbsp;&nbsp;&nbsp;'.$mobile;

                            return $phone;
                        })
                        /* column account status */
                        ->addColumn('status', function ($model) {
                            $status = $model->active;
                            if ($status == 1) {
                                $stat = '<button class="btn btn-success btn-xs">Active</button>';
                            } else {
                                $stat = '<button class="btn btn-danger btn-xs">Inactive</button>';
                            }

                            return $stat;
                        })
                        /* column ban status */
                        ->addColumn('ban', function ($model) {
                            $status = $model->ban;
                            if ($status == 1) {
                                $stat = '<button class="btn btn-danger btn-xs">Banned</button>';
                            } else {
                                $stat = '<button class="btn btn-success btn-xs">Active</button>';
                            }

                            return $stat;
                        })
                        /* column last login date */
                        ->addColumn('lastlogin', function ($model) {
                            $t = $model->updated_at;

                            return TicketController::usertimezone($t);
                        })
                        /* column actions */
                        ->addColumn('Actions', function ($model) {
                            return '<a href="'.route('user.edit', $model->id).'" class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp; <a href="'.route('user.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>';
                        })
                        ->make();
    }

    /**
     * Show the form for creating a new users.
     *
     * @return type view
     */
    public function create()
    {
        try {
            return view('themes.default1.agent.helpdesk.user.create');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * Store a newly created users in storage.
     *
     * @param type User            $user
     * @param type Sys_userRequest $request
     *
     * @return type redirect
     */
    public function store(User $user, Sys_userRequest $request)
    {
        /* insert the input request to sys_user table */
        /* Check whether function success or not */
        $user->email = $request->input('email');
        $user->user_name = $request->input('full_name');
        $user->mobile = $request->input('mobile');
        $user->ext = $request->input('ext');
        $user->phone_number = $request->input('phone_number');
        $user->active = $request->input('active');
        $user->internal_note = $request->input('internal_note');
        $user->role = 'user';
        try {
            $user->save();
            /* redirect to Index page with Success Message */
            return redirect('user')->with('success', 'User  Created Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * Display the specified users.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type view
     */
    public function show($id, User $user)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();

            return view('themes.default1.agent.helpdesk.user.show', compact('users'));
        } catch (Exception $e) {
            return view('404');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type Response
     */
    public function edit($id, User $user)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();

            return view('themes.default1.agent.helpdesk.user.edit', compact('users'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param type int            $id
     * @param type User           $user
     * @param type Sys_userUpdate $request
     *
     * @return type Response
     */
    public function update($id, User $user, Sys_userUpdate $request)
    {
        /* select the field where id = $id(request Id) */
        $users = $user->whereId($id)->first();
        /* Update the value by selected field  */
        /* Check whether function success or not */
        try {
            $users->fill($request->input())->save();
            /* redirect to Index page with Success Message */
            return redirect('user')->with('success', 'User  Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * get agent profile page.
     *
     * @return type view
     */
    public function getProfile()
    {
        $user = Auth::user();
        try {
            return view('themes.default1.agent.helpdesk.user.profile', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * get profile edit page.
     *
     * @return type view
     */
    public function getProfileedit()
    {
        $user = Auth::user();
        try {
            return view('themes.default1.agent.helpdesk.user.profile-edit', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * post profile edit.
     *
     * @param type int            $id
     * @param type ProfileRequest $request
     *
     * @return type Redirect
     */
    public function postProfileedit(ProfileRequest $request)
    {
        // geet authenticated user details
        $user = Auth::user();
        $user->gender = $request->input('gender');
        $user->save();
        // checking availability of agent profile ppicture
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
        // checking if the post system includes agent profile picture upload
        if (Input::file('profile_pic')) {
            // fetching picture name
            $name = Input::file('profile_pic')->getClientOriginalName();
            // fetching upload destination path
            $destinationPath = 'lb-faveo/media/profilepic';
            // adding a random value to profile picture filename
            $fileName = rand(0000, 9999).'.'.$name;
            // moving the picture to a destination folder
            Input::file('profile_pic')->move($destinationPath, $fileName);
            // saving filename to database
            $user->profile_pic = $fileName;
        } else {
            try {
                $user->fill($request->except('profile_pic', 'gender'))->save();

                return Redirect::route('profile')->with('success', 'Profile Updated sucessfully');
            } catch (Exception $e) {
                return Redirect::route('profile')->with('success', $e->errorInfo[2]);
            }
        }
        if ($user->fill($request->except('profile_pic'))->save()) {
            return Redirect::route('profile')->with('success', 'Profile Updated sucessfully');
        }
    }

    /**
     * Post profile password.
     *
     * @param type int             $id
     * @param type ProfilePassword $request
     *
     * @return type Redirect
     */
    public function postProfilePassword($id, ProfilePassword $request)
    {
        // get authenticated user
        $user = Auth::user();
        // checking if the old password matches the new password
        if (Hash::check($request->input('old_password'), $user->getAuthPassword())) {
            $user->password = Hash::make($request->input('new_password'));
            try {
                $user->save();

                return redirect('profile-edit')->with('success1', 'Password Updated sucessfully');
            } catch (Exception $e) {
                return redirect('profile-edit')->with('fails', $e->errorInfo[2]);
            }
        } else {
            return redirect('profile-edit')->with('fails1', 'Password was not Updated. Incorrect old password');
        }
    }

    /**
     * Assigning an user to an organization.
     *
     * @param type $id
     *
     * @return type boolean
     */
    public function UserAssignOrg($id)
    {
        $org = Input::get('org');
        $user_org = new User_org();
        $user_org->org_id = $org;
        $user_org->user_id = $id;
        $user_org->save();

        return 1;
    }

    /**
     * creating an organization in user profile page via modal popup.
     *
     * @param type $id
     *
     * @return type
     */
    public function User_Create_Org($id)
    {
        // checking if the entered value for website is available in database
        if (Input::get('website') != null) {
            // checking website
            $check = Organization::where('website', '=', Input::get('website'))->first();
        } else {
            $check = null;
        }
        // checking if the name is unique
        $check2 = Organization::where('name', '=', Input::get('name'))->first();
        // if any of the fields is not available then return false
        if (\Input::get('name') == null) {
            return 'Name is required';
        } elseif ($check2 != null) {
            return 'Name should be Unique';
        } elseif ($check != null) {
            return 'Website should be Unique';
        } else {
            // storing organization details and assigning the current user to that organization
            $org = new Organization();
            $org->name = Input::get('name');
            $org->phone = Input::get('phone');
            $org->website = Input::get('website');
            $org->address = Input::get('address');
            $org->internal_notes = Input::get('internal');
            $org->save();

            $user_org = new User_org();
            $user_org->org_id = $org->id;
            $user_org->user_id = $id;
            $user_org->save();
            // for success return 0
            return 0;
        }
    }
}
