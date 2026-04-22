<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\Complaint;
use App\Models\Followup;
use App\Models\JobOrder;
use App\Models\Lead;
use App\Models\NewComplaint;
use App\Models\NewQuotation;
use App\Models\Party;
use App\Models\ProformaInvoice;
use App\Models\PurchaseBill;
use App\Models\PurchaseReturn;
use App\Models\SaleBill;
use App\Models\SaleReturn;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BackendController
{
    public String $routePrefix = "dashboard";

    public function index()
    {
        $auth_user = Auth::user();

        if ($auth_user->isAdmin()) {
            return $this->admin();
        } elseif ($auth_user->isSalesManager()) {
            return $this->sales_manager();
        } else {
            return $this->other();
        }
    }

    public function admin()
    {
        $duration_type_list = [
            0 => "Today",
            'yesterday' => "Yesterday",
            'last_7_days' => "Last 7 Days",
            'last_15_days' => "Last 15 Days",
            'last_30_days' => "Last 30 Days",
            'last_60_days' => "Last 60 Days",
            'last_90_days' => "Last 90 Days",
            'this_month' => "This Month",
            'this_year' => "This Year",
        ];

        $this->setForView(compact("duration_type_list"));

        return $this->view(__FUNCTION__);
    }

    public function sales_manager()
    {
        $duration_type_list = [
            0 => "Today",
            'yesterday' => "Yesterday",
            'last_7_days' => "Last 7 Days",
            'last_15_days' => "Last 15 Days",
            'last_30_days' => "Last 30 Days",
            'last_60_days' => "Last 60 Days",
            'last_90_days' => "Last 90 Days",
            'this_month' => "This Month",
            'this_year' => "This Year",
        ];

        $this->setForView(compact("duration_type_list"));

        return $this->view(__FUNCTION__);
    }

    public function other()
    {
        $auth_user = Auth::user();

        $today = Carbon::today();
        $next7 = Carbon::today()->addDays(7);

        // All Complaints by status
        $complaintStatus["Pending"] = Complaint::where("status", "pending")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["In-Progress"] = Complaint::where("status", "in_progress")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["Hold"] = Complaint::where("status", "hold")->where('assign_to', $auth_user['id'])->count();
        $complaintStatus["Done"] = Complaint::where("status", "done")->where('assign_to', $auth_user['id'])->count();

        // All Complaints by level
        $complaintLevel["Hot"] = Complaint::where("level", "hot")->where('assign_to', $auth_user['id'])->count();
        $complaintLevel["Cold"] = Complaint::where("level", "cold")->where('assign_to', $auth_user['id'])->count();
        $complaintLevel["Warm"] = Complaint::where("level", "warm")->where('assign_to', $auth_user['id'])->count();

        // All Lead Levels
        $leadLevel["Hot"] = Lead::where("level", "hot")->where('assigned_user_id', $auth_user['id'])->count();
        $leadLevel["Cold"] = Lead::where("level", "cold")->where('assigned_user_id', $auth_user['id'])->count();
        $leadLevel["Warm"] = Lead::where("level", "warm")->where('assigned_user_id', $auth_user['id'])->count();

        // All Lead Status
        $leadStatus["Pending"] = Lead::where("status", "pending")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Not-interested"] = Lead::where("status", "not_interested")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Follow Up"] = Lead::where("status", "follow_up")->where('assigned_user_id', $auth_user['id'])->count();
        $leadStatus["Mature"] = Lead::where("status", "mature")->where('assigned_user_id', $auth_user['id'])->count();

        $tLeads = Lead::where('assigned_user_id', $auth_user['id'])->count();
        $tComplaints = Complaint::where('assign_to', $auth_user['id'])->count();

        $todayLeads = Lead::with('latestFollowUp')
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
            )
            ->get();

        $missingLeads = Lead::with('latestFollowUp')
            ->where('assigned_user_id', $auth_user['id'])
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', '<', $today);
            })
            ->orderByDesc(
                Followup::select('follow_up_date')
                    ->whereColumn('followups.lead_id', 'leads.id')
                    ->latest()
                    ->limit(1)
            )
            ->limit(10)
            ->get();

        $nextDaysLeads = Lead::with('latestFollowUp')
            ->where('assigned_user_id', $auth_user['id'])
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today, $next7) {
                $q->whereBetween('follow_up_date', [$today, $next7]);
            })
            ->orderByDesc(
                Followup::select('follow_up_date')
                    ->whereColumn('followups.lead_id', 'leads.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        $followtypeList = config('constant.followuptype');
        $statusList = config('constant.status');


        $this->setForView(compact("tLeads", "tComplaints", "complaintStatus", "complaintLevel", "leadLevel", "leadStatus", "todayLeads", "missingLeads", "nextDaysLeads", "followtypeList", "statusList"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_admin_role_counters($duration_type)
    {
        $date = date(DateUtility::DATE_FORMAT);
        if ($duration_type == "yesterday") {
            $date = DateUtility::change($date, -1, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_7_days") {
            $date = DateUtility::change($date, -7, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_15_days") {
            $date = DateUtility::change($date, -15, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_30_days") {
            $date = DateUtility::change($date, -30, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_60_days") {
            $date = DateUtility::change($date, -60, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_90_days") {
            $date = DateUtility::change($date, -90, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "this_month") {
            $date = date("Y-m-01");
        } else if ($duration_type == "this_year") {
            $date = date("Y-01-01");
        }

        // Complaints by status
        if ($duration_type == 'yesterday') {
            $complaint_counters["Pending"] = Complaint::where("status", "pending")->where("date", "=", $date)->count();
            $complaint_counters["In-Progress"] = Complaint::where("status", "in_progress")->where("date", "=", $date)->count();
            $complaint_counters["Hold"] = Complaint::where("status", "hold")->where("date", "=", $date)->count();
            $complaint_counters["Done"] = Complaint::where("status", "done")->where("date", "=", $date)->count();
        } else {
            $complaint_counters["Pending"] = Complaint::where("status", "pending")->where("date", ">=", $date)->count();
            $complaint_counters["In-Progress"] = Complaint::where("status", "in_progress")->where("date", ">=", $date)->count();
            $complaint_counters["Hold"] = Complaint::where("status", "hold")->where("date", ">=", $date)->count();
            $complaint_counters["Done"] = Complaint::where("status", "done")->where("date", ">=", $date)->count();
        }

        // All Complaints by status
        $complaintStatus["Pending"] = Complaint::where("status", "pending")->count();
        $complaintStatus["In-Progress"] = Complaint::where("status", "in_progress")->count();
        $complaintStatus["Hold"] = Complaint::where("status", "hold")->count();
        $complaintStatus["Done"] = Complaint::where("status", "done")->count();

        // Complaints by level
        if ($duration_type == 'yesterday') {
            $complaint_counters["Hot"] = Complaint::where("level", "hot")->where("date", "=", $date)->count();
            $complaint_counters["Cold"] = Complaint::where("level", "cold")->where("date", "=", $date)->count();
            $complaint_counters["Warm"] = Complaint::where("level", "warm")->where("date", "=", $date)->count();
        } else {
            $complaint_counters["Hot"] = Complaint::where("level", "hot")->where("date", ">=", $date)->count();
            $complaint_counters["Cold"] = Complaint::where("level", "cold")->where("date", ">=", $date)->count();
            $complaint_counters["Warm"] = Complaint::where("level", "warm")->where("date", ">=", $date)->count();
        }

        // All Complaints by level
        $complaintLevel["Hot"] = Complaint::where("level", "hot")->count();
        $complaintLevel["Cold"] = Complaint::where("level", "cold")->count();
        $complaintLevel["Warm"] = Complaint::where("level", "warm")->count();

        // Lead by status
        if ($duration_type == 'yesterday') {
            $lead_counters["Pending"] = Lead::where("status", "pending")->where("date", "=", $date)->count();
            $lead_counters["Not-interested"] = Lead::where("status", "not_interested")->where("date", "=", $date)->count();
            $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("date", "=", $date)->count();
            $lead_counters["Mature"] = Lead::where("status", "mature")->where("date", "=", $date)->count();
        } else {
            $lead_counters["Pending"] = Lead::where("status", "pending")->where("date", ">=", $date)->count();
            $lead_counters["Not-interested"] = Lead::where("status", "not_interested")->where("date", ">=", $date)->count();
            $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("date", ">=", $date)->count();
            $lead_counters["Mature"] = Lead::where("status", "mature")->where("date", ">=", $date)->count();
        }

        // Lead Levels
        $leadLevel["Hot"] = Lead::where("level", "hot")->count();
        $leadLevel["Cold"] = Lead::where("level", "cold")->count();
        $leadLevel["Warm"] = Lead::where("level", "warm")->count();

        // Lead Status
        $leadStatus["Pending"] = Lead::where("status", "pending")->count();
        $leadStatus["Not-interested"] = Lead::where("status", "not_interested")->count();
        $leadStatus["Follow Up"] = Lead::where("status", "follow_up")->count();
        $leadStatus["Mature"] = Lead::where("status", "mature")->count();

        // payment status
        $paymentStatus["Pending"] = Complaint::where("payment_status", "pending")->where("status", "done")->count();
        $paymentStatus["Received"] = Complaint::where("payment_status", "received")->where("status", "done")->count();

        // Lead by level
        if ($duration_type == 'yesterday') {
            $lead_counters["Hot"] = Lead::where("level", "hot")->where("date", "=", $date)->count();
            $lead_counters["Cold"] = Lead::where("level", "cold")->where("date", "=", $date)->count();
            $lead_counters["Warm"] = Lead::where("level", "warm")->where("date", "=", $date)->count();
        } else {
            $lead_counters["Hot"] = Lead::where("level", "hot")->where("date", ">=", $date)->count();
            $lead_counters["Cold"] = Lead::where("level", "cold")->where("date", ">=", $date)->count();
            $lead_counters["Warm"] = Lead::where("level", "warm")->where("date", ">=", $date)->count();
        }

        $tLeads = Lead::count();
        $tComplaints = Complaint::count();
        $tPI = ProformaInvoice::count();
        $tParties = Party::count();


        $today = Carbon::today();
        $next7 = Carbon::today()->addDays(7);

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

        $todayLeads = $todayLeadsQuery->get();
        $todayLeadCount = $todayLeadsQuery->count();


        // // 2. Missing Leads (follow-up date < today and still not done)

        $missingLeadQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', '<', $today);
            });

        $missingLeads = $missingLeadQuery->get();
        $missingLeadCount = $missingLeadQuery->count();

        $nextDaysLeadsQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today, $next7) {
                $q->whereDate('follow_up_date', '>', $today)
                    ->whereDate('follow_up_date', '<=', $next7);
            });

        $nextDaysLeads = $nextDaysLeadsQuery->get();
        $nextDaysLeadCount = $nextDaysLeadsQuery->count();

        $followtypeList = config('constant.followuptype');
        $statusList = config('constant.status');

        $this->setForView(compact("todayLeadCount", "nextDaysLeadCount", "missingLeadCount", "paymentStatus", "complaintStatus", "complaintLevel", "leadLevel", "leadStatus", "complaint_counters", "lead_counters", "tLeads", "tComplaints", "tPI", "tParties", "todayLeads", "missingLeads", "nextDaysLeads", "followtypeList", "statusList"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_sales_manager_role_counters($duration_type)
    {
        $date = date(DateUtility::DATE_FORMAT);
        if ($duration_type == "yesterday") {
            $date = DateUtility::change($date, -1, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_7_days") {
            $date = DateUtility::change($date, -7, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_15_days") {
            $date = DateUtility::change($date, -15, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_30_days") {
            $date = DateUtility::change($date, -30, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_60_days") {
            $date = DateUtility::change($date, -60, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_90_days") {
            $date = DateUtility::change($date, -90, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "this_month") {
            $date = date("Y-m-01");
        } else if ($duration_type == "this_year") {
            $date = date("Y-01-01");
        }

        // Complaints by status
        if ($duration_type == 'yesterday') {
            $complaint_counters["Pending"] = Complaint::where("status", "pending")->where("date", "=", $date)->count();
            $complaint_counters["In-Progress"] = Complaint::where("status", "in_progress")->where("date", "=", $date)->count();
            $complaint_counters["Hold"] = Complaint::where("status", "hold")->where("date", "=", $date)->count();
            $complaint_counters["Done"] = Complaint::where("status", "done")->where("date", "=", $date)->count();
        } else {
            $complaint_counters["Pending"] = Complaint::where("status", "pending")->where("date", ">=", $date)->count();
            $complaint_counters["In-Progress"] = Complaint::where("status", "in_progress")->where("date", ">=", $date)->count();
            $complaint_counters["Hold"] = Complaint::where("status", "hold")->where("date", ">=", $date)->count();
            $complaint_counters["Done"] = Complaint::where("status", "done")->where("date", ">=", $date)->count();
        }

        // All Complaints by status
        $complaintStatus["Pending"] = Complaint::where("status", "pending")->count();
        $complaintStatus["In-Progress"] = Complaint::where("status", "in_progress")->count();
        $complaintStatus["Hold"] = Complaint::where("status", "hold")->count();
        $complaintStatus["Done"] = Complaint::where("status", "done")->count();

        // Complaints by level
        if ($duration_type == 'yesterday') {
            $complaint_counters["Hot"] = Complaint::where("level", "hot")->where("date", "=", $date)->count();
            $complaint_counters["Cold"] = Complaint::where("level", "cold")->where("date", "=", $date)->count();
            $complaint_counters["Warm"] = Complaint::where("level", "warm")->where("date", "=", $date)->count();
        } else {
            $complaint_counters["Hot"] = Complaint::where("level", "hot")->where("date", ">=", $date)->count();
            $complaint_counters["Cold"] = Complaint::where("level", "cold")->where("date", ">=", $date)->count();
            $complaint_counters["Warm"] = Complaint::where("level", "warm")->where("date", ">=", $date)->count();
        }

        // All Complaints by level
        $complaintLevel["Hot"] = Complaint::where("level", "hot")->count();
        $complaintLevel["Cold"] = Complaint::where("level", "cold")->count();
        $complaintLevel["Warm"] = Complaint::where("level", "warm")->count();

        // Lead by status
        if ($duration_type == 'yesterday') {
            $lead_counters["Pending"] = Lead::where("status", "pending")->where("date", "=", $date)->count();
            $lead_counters["Not-interested"] = Lead::where("status", "not_interested")->where("date", "=", $date)->count();
            $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("date", "=", $date)->count();
            $lead_counters["Mature"] = Lead::where("status", "mature")->where("date", "=", $date)->count();
        } else {
            $lead_counters["Pending"] = Lead::where("status", "pending")->where("date", ">=", $date)->count();
            $lead_counters["Not-interested"] = Lead::where("status", "not_interested")->where("date", ">=", $date)->count();
            $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("date", ">=", $date)->count();
            $lead_counters["Mature"] = Lead::where("status", "mature")->where("date", ">=", $date)->count();
        }

        // Lead Levels
        $leadLevel["Hot"] = Lead::where("level", "hot")->count();
        $leadLevel["Cold"] = Lead::where("level", "cold")->count();
        $leadLevel["Warm"] = Lead::where("level", "warm")->count();

        // Lead Status
        $leadStatus["Pending"] = Lead::where("status", "pending")->count();
        $leadStatus["Not-interested"] = Lead::where("status", "not_interested")->count();
        $leadStatus["Follow Up"] = Lead::where("status", "follow_up")->count();
        $leadStatus["Mature"] = Lead::where("status", "mature")->count();

        // Lead by level
        if ($duration_type == 'yesterday') {
            $lead_counters["Hot"] = Lead::where("level", "hot")->where("date", "=", $date)->count();
            $lead_counters["Cold"] = Lead::where("level", "cold")->where("date", "=", $date)->count();
            $lead_counters["Warm"] = Lead::where("level", "warm")->where("date", "=", $date)->count();
        } else {
            $lead_counters["Hot"] = Lead::where("level", "hot")->where("date", ">=", $date)->count();
            $lead_counters["Cold"] = Lead::where("level", "cold")->where("date", ">=", $date)->count();
            $lead_counters["Warm"] = Lead::where("level", "warm")->where("date", ">=", $date)->count();
        }

        $tLeads = Lead::count();
        $tComplaints = Complaint::count();
        $tPI = ProformaInvoice::count();
        $tParties = Party::count();


        $today = Carbon::today();
        $next7 = Carbon::today()->addDays(7);

        // // 1. Today’s Leads
        $todayLeadsQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', $today);
            })
            ->latest('id');

        $todayLeads = $todayLeadsQuery->get();
        $todayLeadCount = $todayLeadsQuery->count();


        // // 2. Missing Leads (follow-up date < today and still not done

        $missingLeadQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today) {
                $q->whereDate('follow_up_date', '<', $today);
            });

        $missingLeads = $missingLeadQuery->get();
        $missingLeadCount = $missingLeadQuery->count();


        // // 3. Next 7 days leads
        $nextDaysLeadsQuery = Lead::with('latestFollowUp')
            ->whereNotIn('status', ['not_interested', 'mature'])
            ->whereHas('latestFollowUp', function ($q) use ($today, $next7) {
                $q->whereDate('follow_up_date', '>', $today)
                    ->whereDate('follow_up_date', '<=', $next7);
            });

        $nextDaysLeads = $nextDaysLeadsQuery->get();
        $nextDaysLeadCount = $nextDaysLeadsQuery->count();

        $followtypeList = config('constant.followuptype');
        $statusList = config('constant.status');

        $this->setForView(compact("nextDaysLeadCount", "missingLeadCount", "todayLeadCount", "complaintStatus", "complaintLevel", "leadLevel", "leadStatus", "complaint_counters", "lead_counters", "tLeads", "tComplaints", "tPI", "tParties", "todayLeads", "missingLeads", "nextDaysLeads", "followtypeList", "statusList"));

        return $this->view(__FUNCTION__);
    }
    // public String $routePrefix = "dashbaord";

    // public function index()
    // {
    //     $view_name = "admin";

    //     $msg = "Comming Soon";

    //     $this->setForView(compact("view_name", "msg"));

    //     return $this->view($view_name);
    // }
}
