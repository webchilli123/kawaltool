<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DateUtility;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Followup;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // return $this->admin();

        $auth_user = Auth::user();

        if ($auth_user->isAdmin()) {
            return $this->admin();
        } else {
            return $this->other();
        }
    }

    public function admin()
    {

        $totalLeads = Lead::count();

        // Lead Levels
        $leadLevel["Hot"] = Lead::where("level", "hot")->count();
        $leadLevel["Cold"] = Lead::where("level", "cold")->count();
        $leadLevel["Warm"] = Lead::where("level", "warm")->count();

        // Lead Status
        $leadStatus["Pending"] = Lead::where("status", "pending")->count();
        $leadStatus["Not-interested"] = Lead::where("status", "not_interested")->count();
        $leadStatus["Follow Up"] = Lead::where("status", "follow_up")->count();
        $leadStatus["Mature"] = Lead::where("status", "mature")->count();

        $today = date(DateUtility::DATE_FORMAT);
        $next7 = DateUtility::change($today, 7, DateUtility::DAYS, DateUtility::DATE_FORMAT);

        // // 1. Today’s Leads
        $todayLeadsQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', $today);
            })
            ->orderByDesc(
                Followup::select('follow_up_date')
                    ->whereColumn('followups.lead_id', 'leads.id')
                    ->latest()
                    ->limit(1)
            );

        $todayFollowups = $todayLeadsQuery->get();
        $todayFollowupCount = $todayLeadsQuery->count();


        // // 2. Missing Leads (follow-up date < today and still not done)
        $missingLeadQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', '<', $today);
            });

        $missingFollowups = $missingLeadQuery->get();
        $missingFollowupCount = $missingLeadQuery->count();

        $nextDaysLeadsQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today, $next7) {
                $q->whereDate('follow_up_date', '>', $today)
                    ->whereDate('follow_up_date', '<=', $next7);
            });

        $nextFollowups = $nextDaysLeadsQuery->get();
        $nextFollowupCount = $nextDaysLeadsQuery->count();

        // ----------------Complaints-------------------//

        $totalComplaints = Complaint::count();

        $complaintStatus["Pending"] = Complaint::where("status", "pending")->count();
        $complaintStatus["In-Progress"] = Complaint::where("status", "in_progress")->count();
        $complaintStatus["Hold"] = Complaint::where("status", "hold")->count();
        $complaintStatus["Done"] = Complaint::where("status", "done")->count();

        $complaintLevel["Hot"] = Complaint::where("level", "hot")->count();
        $complaintLevel["Cold"] = Complaint::where("level", "cold")->count();
        $complaintLevel["Warm"] = Complaint::where("level", "warm")->count();

        return response()->json([
            'status' => true,
            'data' => [
                'totalLeads'  => $totalLeads,
                'leadLevel'  => $leadLevel,
                'leadStatus'    => $leadStatus,
                'todayFollowups'    => $todayFollowups,
                'todayFollowupCount'    => $todayFollowupCount,
                'missingFollowups'    => $missingFollowups,
                'missingFollowupCount'    => $missingFollowupCount,
                'nextFollowups'    => $nextFollowups,
                'nextFollowupCount'    => $nextFollowupCount,
                'totalComplaints'    => $totalComplaints,
                'complaintStatus'    => $complaintStatus,
                'complaintLevel'    => $complaintLevel,
            ]
        ]);
    }
   
    public function other()
    {
        $auth_user = Auth::user();
        $totalLeads = Lead::where('assigned_user_id', $auth_user['id'])->count();

        // Lead Levels
        $leadLevel["Hot"] = Lead::where("level", "hot")->where('assigned_user_id', $auth_user['id'])->count();
        $leadLevel["Cold"] = Lead::where("level", "cold")->where('assigned_user_id', $auth_user['id'])->count();
        $leadLevel["Warm"] = Lead::where("level", "warm")->where('assigned_user_id', $auth_user['id'])->count();

        // Lead Status
        $leadStatus["Pending"] = Lead::where("status", "pending")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Not-interested"] = Lead::where("status", "not_interested")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Follow Up"] = Lead::where("status", "follow_up")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Mature"] = Lead::where("status", "mature")->where('assigned_user_id', $auth_user['id'])->count();

        $today = date(DateUtility::DATE_FORMAT);
        $next7 = DateUtility::change($today, 7, DateUtility::DAYS, DateUtility::DATE_FORMAT);

        // // 1. Today’s Leads
        $todayLeadsQuery = Lead::with('latestFollowUp')
        ->where('assigned_user_id', $auth_user['id'])
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', $today);
            })
            ->orderByDesc(
                Followup::select('follow_up_date')
                    ->whereColumn('followups.lead_id', 'leads.id')
                    ->latest()
                    ->limit(1)
            );

        $todayFollowups = $todayLeadsQuery->get();
        $todayFollowupCount = $todayLeadsQuery->count();


        // // 2. Missing Leads (follow-up date < today and still not done)
        $missingLeadQuery = Lead::with('latestFollowUp')
        ->where('assigned_user_id', $auth_user['id'])
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', '<', $today);
            });

        $missingFollowups = $missingLeadQuery->get();
        $missingFollowupCount = $missingLeadQuery->count();

        $nextDaysLeadsQuery = Lead::with('latestFollowUp')
        ->where('assigned_user_id', $auth_user['id'])
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today, $next7) {
                $q->whereDate('follow_up_date', '>', $today)
                    ->whereDate('follow_up_date', '<=', $next7);
            });

        $nextFollowups = $nextDaysLeadsQuery->get();
        $nextFollowupCount = $nextDaysLeadsQuery->count();

        // -------------------Complaints-------------------------//

        $totalComplaints = Complaint::where('assign_to', $auth_user['id'])->count();

        $complaintStatus["Pending"] = Complaint::where("status", "pending")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["In-Progress"] = Complaint::where("status", "in_progress")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["Hold"] = Complaint::where("status", "hold")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["Done"] = Complaint::where("status", "done")->where('assign_to', $auth_user['id'])->count();

        // All Complaints by level
        $complaintLevel["Hot"] = Complaint::where("level", "hot")->where('assign_to', $auth_user['id'])->count();
        $complaintLevel["Cold"] = Complaint::where("level", "cold")->where('assign_to', $auth_user['id'])->count();
        $complaintLevel["Warm"] = Complaint::where("level", "warm")->where('assign_to', $auth_user['id'])->count();

        return response()->json([
            'status' => true,
            'data' => [
                'totalLeads'  => $totalLeads,
                'leadLevel'  => $leadLevel,
                'leadStatus'    => $leadStatus,
                'todayFollowups'    => $todayFollowups,
                'todayFollowupCount'    => $todayFollowupCount,
                'missingFollowups'    => $missingFollowups,
                'missingFollowupCount'    => $missingFollowupCount,
                'nextFollowups'    => $nextFollowups,
                'nextFollowupCount'    => $nextFollowupCount,
                'totalComplaints'    => $totalComplaints,
                'complaintStatus'    => $complaintStatus,
                'complaintLevel'    => $complaintLevel,
            ]
        ]);
    }
}
