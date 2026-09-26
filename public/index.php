<?php
// ============================================================
// NPCEA USSD Demo — Africa's Talking endpoint
// All data is hardcoded for demonstration. No database.
// ============================================================
header('Content-Type: text/plain');

// ---------- Africa's Talking request ----------
$sessionId   = $_POST['sessionId']   ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text']        ?? '';

// Normalise the phone: +254712345678 -> 254712345678
$phone = preg_replace('/\D/', '', $phoneNumber);

// Split accumulated input on '*'
$input = $text === '' ? [] : explode('*', $text);

// ============================================================
// HARDCODED OFFICER "DATABASE"
// Keyed by phone number so we can look up who is dialing.
// ============================================================
$officers = [
    '254712345678' => [
        'service_number' => '67181',
        'rank'           => 'Sergeant',
        'name'           => 'John Mwangi Kamau',
        'faculty'        => 'Alpha Faculty',
        'department'     => 'Traffic',
        'leave_balance'  => 22,
        'parade_today'   => 'Present',
        'parade_remarks' => 'Marked by Cmdr. Otieno at 06:12',
    ],
    '254798765432' => [
        'service_number' => '67182',
        'rank'           => 'Corporal',
        'name'           => 'Mary Njeri Wanjiku',
        'faculty'        => 'Bravo Faculty',
        'department'     => 'Communications',
        'leave_balance'  => 18,
        'parade_today'   => 'Sick',
        'parade_remarks' => 'Medical certificate pending',
    ],
    '254701111111' => [
        'service_number' => '67183',
        'rank'           => 'Inspector',
        'name'           => 'Peter Ochieng Odhiambo',
        'faculty'        => 'Alpha Faculty',
        'department'     => 'Investigations',
        'leave_balance'  => 30,
        'parade_today'   => 'Duty Else where',
        'parade_remarks' => 'Attached to HQ Nairobi',
    ],
];

// Look up the officer
$officer = $officers[$phone] ?? null;

// Short display name
$display_name = $officer ? ($officer['rank'] . ' ' . explode(' ', $officer['name'])[0]) : 'Officer';

// ============================================================
// MENU STATE MACHINE
// ============================================================

// Level 0: first dial — main menu
if ($text === '') {
    if (!$officer) {
        echo "END Your number is not registered.\n"
           . "Contact HQ on 020-XXX-XXXX to enroll.";
        exit;
    }

    $response  = "CON Welcome, {$display_name}\n";
    $response .= "Svc No: {$officer['service_number']}\n";
    $response .= "----------------\n";
    $response .= "1. My parade status\n";
    $response .= "2. My leave balance\n";
    $response .= "3. Apply for leave\n";
    $response .= "4. Report sick\n";
    $response .= "5. My posting details\n";
    $response .= "0. Exit";
    echo $response;
    exit;
}

// If we got this far, the officer must exist
if (!$officer) {
    echo "END Your number is not registered.";
    exit;
}

// ============================================================
// LEVEL 1 — first selection
// ============================================================
if (count($input) === 1) {

    switch ($input[0]) {

        // ---------- 1. Parade status ----------
        case '1':
            $status = $officer['parade_today'];

            $response  = "END Parade status - today\n";
            $response .= "----------------\n";
            $response .= "Status: {$status}\n";
            $response .= "----------------\n";
            $response .= "{$officer['parade_remarks']}";
            echo $response;
            exit;

        // ---------- 2. Leave balance ----------
        case '2':
            $response  = "END Leave balance\n";
            $response .= "----------------\n";
            $response .= "Remaining: {$officer['leave_balance']} days\n";
            $response .= "Entitlement: 30 days\n";
            $response .= "Used this year: " . (30 - $officer['leave_balance']) . " days";
            echo $response;
            exit;

        // ---------- 3. Apply for leave (sub-menu) ----------
        case '3':
            $response  = "CON Apply for leave\n";
            $response .= "Select type:\n";
            $response .= "1. Annual\n";
            $response .= "2. Sick\n";
            $response .= "3. Study\n";
            $response .= "4. Compassionate\n";
            $response .= "0. Back";
            echo $response;
            exit;

        // ---------- 4. Report sick ----------
        case '4':
            $response  = "CON Report sick\n";
            $response .= "This will:\n";
            $response .= "- Mark you Sick today\n";
            $response .= "- Notify your commander\n";
            $response .= "----------------\n";
            $response .= "1. Confirm\n";
            $response .= "2. Cancel";
            echo $response;
            exit;

        // ---------- 5. Posting details ----------
        case '5':
            $response  = "END My posting details\n";
            $response .= "----------------\n";
            $response .= "Rank: {$officer['rank']}\n";
            $response .= "Name: {$officer['name']}\n";
            $response .= "Svc No: {$officer['service_number']}\n";
            $response .= "Faculty: {$officer['faculty']}\n";
            $response .= "Dept: {$officer['department']}";
            echo $response;
            exit;

        case '0':
            echo "END Thank you. Dial again anytime.";
            exit;

        default:
            echo "END Invalid option.\nDial again to retry.";
            exit;
    }
}

// ============================================================
// LEVEL 2 — leave sub-menu and confirmations
// ============================================================
if (count($input) === 2) {

    // ----- Apply for leave: type selected -----
    if ($input[0] === '3') {
        $types = [
            '1' => 'Annual',
            '2' => 'Sick',
            '3' => 'Study',
            '4' => 'Compassionate',
        ];

        if ($input[1] === '0') {
            echo "END Leave request cancelled.";
            exit;
        }

        if (!isset($types[$input[1]])) {
            echo "END Invalid leave type.";
            exit;
        }

        $response  = "CON {$types[$input[1]]} leave\n";
        $response .= "Enter start date (YYYYMMDD):\n";
        $response .= "Example: 20261001";
        echo $response;
        exit;
    }

    // ----- Report sick: confirmation -----
    if ($input[0] === '4') {
        if ($input[1] === '1') {
            $response  = "END Sick reported\n";
            $response .= "----------------\n";
            $response .= "Your commander has been notified.\n";
            $response .= "Ref: SICK-2026-0042";
            echo $response;
            exit;
        } else {
            echo "END Sick report cancelled.";
            exit;
        }
    }
}

// ============================================================
// LEVEL 3 — leave application flow
// ============================================================
if (count($input) === 3 && $input[0] === '3') {
    $start = $input[2];

    if (!preg_match('/^\d{8}$/', $start)) {
        $response  = "CON Invalid date format.\n";
        $response .= "Enter start date (YYYYMMDD):\n";
        $response .= "Example: 20261001";
        echo $response;
        exit;
    }

    $response  = "CON Enter end date (YYYYMMDD):\n";
    $response .= "Example: 20261005";
    echo $response;
    exit;
}

// ============================================================
// LEVEL 4 — leave confirmation
// ============================================================
if (count($input) === 4 && $input[0] === '3') {
    $start = $input[2];
    $end   = $input[3];

    if (!preg_match('/^\d{8}$/', $end)) {
        $response  = "CON Invalid end date.\n";
        $response .= "Enter end date (YYYYMMDD):";
        echo $response;
        exit;
    }

    if ($end < $start) {
        $response  = "CON End date must be after start date.\n";
        $response .= "Enter end date (YYYYMMDD):";
        echo $response;
        exit;
    }

    // Calculate days
    $start_ts = strtotime($start);
    $end_ts   = strtotime($end);
    $days     = (int) (($end_ts - $start_ts) / 86400) + 1;

    $start_fmt = date('d M Y', $start_ts);
    $end_fmt   = date('d M Y', $end_ts);

    $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
    $type  = $types[$input[1]] ?? 'Annual';

    $response  = "CON Confirm leave request:\n";
    $response .= "----------------\n";
    $response .= "Type: {$type}\n";
    $response .= "From: {$start_fmt}\n";
    $response .= "To: {$end_fmt}\n";
    $response .= "Days: {$days}\n";
    $response .= "----------------\n";
    $response .= "1. Submit\n";
    $response .= "2. Cancel";
    echo $response;
    exit;
}

// ============================================================
// LEVEL 5 — leave submitted
// ============================================================
if (count($input) === 5 && $input[0] === '3') {
    if ($input[4] === '1') {
        $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
        $type  = $types[$input[1]] ?? 'Annual';

        $response  = "END Leave request submitted\n";
        $response .= "----------------\n";
        $response .= "Type: {$type}\n";
        $response .= "Your commander will review shortly.\n";
        $response .= "Ref: LV-2026-0043";
        echo $response;
        exit;
    } else {
        echo "END Leave request cancelled.";
        exit;
    }
}

// ============================================================
// Fallback
// ============================================================
echo "END Something went wrong.\nPlease dial again.";
