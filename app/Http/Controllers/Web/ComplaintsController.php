<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Complaint;
use App\Models\Role;
use App\Models\Assigned;
use App\Models\MobileNotification;
use App\User;
use App\Events\ComplaintsEvent;
use App\Events\AssignedComplaintEvent;
use App\Events\AssignedWorkingComplaintEvent;
use Helper;

class ComplaintsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $slug = strtolower($user->roles()->first()->slug);
        $records = Complaint::with(['typeComplaint', 'complainer', 'complaintTrackings']);

        if($slug !== 'pegawai' && $slug !== 'admin') {
            $records = $records->whereHas('typeComplaint', function($q) use($user) {
                $q->where('role_id', $user->roles()->first()->id);
            })->where('on_assigned', '=' ,true);
        }
        else {
            if($slug === 'pegawai') {
                $records = $records->where('user_complaint_id', '=', $user->id);
            }
        }

        $records = $records->where('on_assigned', '=', false)->orderBy('updated_at', 'desc')->paginate(10);       
        return view('pages.complaints.index', compact('records'));
    }

    public function create() {
        $roles = Role::where('id', '!=', 1)->where('id', '!=', 2)->get();
        return view('pages.complaints.create', compact('roles'));
    }

    public function store(Request $req) 
    {

        $slug = strtolower(Auth::user()->roles()->first()->slug);

        if ($slug == 'pegawai') {

            $req->validate([
                'complaint_type_id' => 'required',
                'messages' => 'required|string',
                'urgent' => 'required',
            ]);

            $complaint = Complaint::create([
                'complaint_type_id' => $req->complaint_type_id,
                'messages' => $req->messages,
                'urgent' => $req->urgent,
                'user_complaint_id' => Auth::user()->id,
            ]);

            if(!$complaint) {
                return response(['message' => Helper::defaultMessage('Complaint')->CREATE_FAILED], 400);
            }

            $type = 'CREATE_COMPLAINT';
            $results = [
                'message' => User::find($complaint->user_complaint_id)->name . ' menambahkan pengaduan baru',
                'data' => $complaint,
                'receiveData' => 1
            ];

            $mobileNotif = MobileNotification::create([
                'type' => $type,
                'receiver_id' => 1,
                'data' => json_encode($results, true),
            ]);

            event(new ComplaintsEvent($complaint, 1, $mobileNotif));

            Helper::setNotification($type, 1, $results);

            return response(['message' => Helper::defaultMessage('Complaint')->CREATE_SUCCESS], 200);
        }

        return response(['message' => 'Sorry. You can not access this route'], 400); 
    }

    public function show($id)
    {
        $result = Complaint::with([
            'typeComplaint',
            'complainer',
            'complaintTrackings',
        ])->find($id);

        if(!$result) {
            return response(['message' => Helper::defaultMessage()->FOUND_ERROR,], 404);    
        }

        return response(['result' => $result], 200); 
    }

    public function destroy($id)
    {
        $result = Complaint::find($id);

        if(Auth::user()->roles()->first()->slug === 'admin') {
            $result->delete();
            return response(["message" => Helper::defaultMessage()->DELETE_SUCCESS], 200);
        }

        return response(['message' => 'Sorry. You can not access this route'], 400);
        
    }

    public function assignComplaint(Request $req) 
    {   
        $slug = strtolower(Auth::user()->roles()->first()->slug);
        
        if($slug === 'admin') {
            $req->validate([
                'id' => 'required',
                'user_perform_id' => 'required'
            ]);
            
    
            $complaint = Complaint::find($req->id);
            $complaint->on_assigned = true;
            $complaint->save();
    
            if(!$complaint) {
                return response()->json(['message' => 'Pengaduan tidak dapat di assign'], 400);
            }
    
            $assigned = Assigned::create([
                'complaint_id' => $complaint->id,
                'user_perform_id' => $req->user_perform_id,
                'status_id' => \App\Models\StatusProcess::where('slug', '=', 'mulai')->first()->id
            ]);
    
            if(!$assigned) {
                return response()->json(['message' => 'Assigned ditolak'], 400);
            }
            
            $user = \App\User::find($req->user_perform_id);
            $user->active = true;
            $user->save();
    
            $type = 'ASSIGNED_COMPLAINT';
            $results = [
                'message' => "Pengaduan ". $complaint->typeComplaint->title . " telah ditugaskan (assigned)",
                'data' => $assigned,
                'receiveData' => $req->user_perform_id
            ];

            //Set mobile notification
            $mobileNotif = MobileNotification::create([
                'type' => $type,
                'receiver_id' => $req->user_perform_id,
                'data' => json_encode($results, true),
            ]);

            //Set Complaint Event
            event(new AssignedComplaintEvent($assigned, $req->user_perform_id, $mobileNotif));
            
            //Set Notification
            Helper::setNotification($type, $req->user_perform_id, $results);
    
            return response([
                "message" => 'Assigned Complaint Success'
            ], 200);
        }

        return response([
            'message' => 'Sorry. You can not access this route'
        ], 400);
    }

    public function startWorkComplaint($assignedId)
    {
        $slug = strtolower(Auth::user()->roles()->first()->slug);

        if($slug !== 'admin' && $slug !== 'pegawai') 
        {
            $user = Auth::user();
            
            $assigned = Assigned::find($assignedId);
            $assigned->is_working = true;
            $assigned->start_work = \Carbon\Carbon::now()->toDateTimeString();
            $assigned->status_id = \App\Models\StatusProcess::where('slug', '=', 'dikerjakan')->first()->id;
            $assigned->save();

            if(!$assigned) {
                return response()->json(['message' => 'Pengaduan gagal diterima'], 400);
            }
            
            $complaint = Complaint::with([
                'typeComplaint', 'complainer', 'complaintTrackings', 'assigned'
            ])->where('id', $assigned->complaint_id)->first();

            //Set mobile notification
            $type = 'START_WORKING_ASSIGNED';
            $results = [
                'message' => $user->name. " Menerima dan melaksanakan Pengaduan ",
                'data' => $complaint,
                'receiveData' => [$complaint->user_complaint_id, 1]
            ];

            $mobileNotif = MobileNotification::insert([
                [
                    'type' => $type,
                    'receiver_id' => 1,
                    'data' => json_encode($results, true),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ],
                [
                    'type' => $type,
                    'receiver_id' => $complaint->user_complaint_id,
                    'data' => json_encode($results, true),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            ]);

            event(new AssignedWorkingComplaintEvent(
                $complaint, 
                [$complaint->user_complaint_id, 1],
                $mobileNotif
            ));

            //setNotification
            Helper::setNotification(
                'START_WORKING_ASSIGNED', 
                [$complaint->user_complaint_id, 1], 
                $results
            );

            return response()->json([
                "message" => "Complaint Accepted To Work"
            ], 200);
        }
        
        return response([
            'message' => 'Sorry. You can not access this route'
        ], 400);
    }

    public function showFinished($id) 
    {
        $slug = strtolower(Auth::user()->roles()->first()->slug);

        if($slug != 'pegawai' && $slug != 'admin') {
            $complaint = Complaint::with([
                'typeComplaint',
                'complainer',
                'assigned',
                'complaintTrackings'
            ])->find($id);

            return view('pages.complaints.finish', compact('complaint'));
        }

        return abort(404);
    
    }

    public function finishWorkComplaint(Request $req)
    {

    }
}
