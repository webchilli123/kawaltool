<?php

namespace App\Imports;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeadsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Lead([
            'date' => !empty($row['date']) && is_numeric($row['date'])
                        ? Date::excelToDateTimeObject($row['date'])->format('Y-m-d')
                        : (!empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('Y-m-d') : null),
            'level' => $row['level'] ?? 'cold',
            'party_id' => $row['party_id'] ?? null,
            'is_new' => $row['is_new'] ?? 1,
            'customer_name' => $row['customer_name'] ?? null,
            'firm_name' => $row['firm_name'] ?? null,
            'customer_email' => $row['customer_email'] ?? null,
            'customer_number' => $row['customer_number'] ?? null,
            'alternate_number' => $row['alternate_number'] ?? null,
            'customer_website' => $row['customer_website'] ?? null,
            'customer_address' => $row['customer_address'] ?? null,
            'status' => $row['status'] ?? null,
            'lead_source_id' => $row['lead_source_id'] ?? null,
            'not_in_interested_reason' => $row['not_in_interested_reason'] ?? null,
            'follow_up_date' => !empty($row['follow_up_date']) && is_numeric($row['follow_up_date'])
                                    ? Date::excelToDateTimeObject($row['follow_up_date'])->format('Y-m-d')
                                    : (!empty($row['follow_up_date']) ? \Carbon\Carbon::parse($row['follow_up_date'])->format('Y-m-d') : null),
            'follow_up_type' => $row['follow_up_type'] ?? null,
            'mature_action_type' => $row['mature_action_type'] ?? null,
            'comments' => $row['comments'] ?? null,
            'follow_up_user_id' => Auth::id(),
        ]);
    }
}
